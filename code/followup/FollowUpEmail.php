<?php
/**
 * Automated email that can be sent for carts matching a
 * certain status after X days. If the order is still a
 * Cart status it includes a link to reclaim the cart.
 * It can optionally include a summary of the order in the
 * email.
 *
 * @property string Subject
 * @property string To
 * @property string Statuses
 * @property int DaysAfter
 * @property bool Active
 * @property string HtmlContent
 * @property string PlainContent
 * @method ManyManyList Orders()
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 09.30.2014
 * @package shop_email
 */
class FollowUpEmail extends DataObject
{
	const TO_ADMIN = 'Admin';
	const TO_CUSTOMER = 'Customer';

	private static $db = array(
		'Statuses'     => 'Varchar(255)',
		'DaysAfter'    => 'Int',
		'Subject'      => 'Varchar(255)',
		'To'           => "Enum('Customer,Admin','Customer')",
		'Active'       => 'Boolean',
		'HtmlContent'  => 'HTMLText',
	);

	private static $many_many = array(
		'Orders' => 'Order', // an order is added to this list once the email has been sent
	);

	private static $defaults = array(
		'Statuses'  => 'Cart',
		'DaysAfter' => 3,
	);

	private static $summary_fields = array(
		'Statuses'   => 'Statuses',
		'DaysAfter'  => 'Days',
		'Subject'    => 'Subject',
		'To'         => 'To',
		'ActiveNice' => 'Active?',
	);

	private static $searchable_fields = array(
		'Subject',
		'To',
		'HtmlContent',
		'Active',
	);


	// This allows anyone with the permissions to SEE the email admin to use it
	function canCreate($member=null) {return true;}
	function canEdit($member=null) {return true;}
	function canDelete($member=null) {return true;}


	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Orders');

		$fields->replaceField('Statuses', CheckboxSetField::create('Statuses', 'Statuses', Order::get_order_status_options()));
		$fields->replaceField('Active', DropdownField::create('Active', 'Active', array(0 => 'No', 1 => 'Yes')));

		$instructions = '';
		foreach ($this->getSubstitutions() as $k => $v) {
			$instructions .= "<li><strong>$k</strong>: $v</li>";
		}

		$fields->addFieldToTab('Root.Main', LiteralField::create(
			'contentinstructions',
			"<p>The following will be replaced anywhere they appear in the content of the email:</p>"
			. "<ul>$instructions</ul>"
		), 'HtmlContent');

		return $fields;
	}


	/**
	 * @return string
	 */
	public function ActiveNice() {
		return $this->Active ? 'Yes' : 'No';
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->ID . ': ' . $this->DaysAfter . ' days after ' . $this->Statuses;
	}


	/**
	 * @return array
	 */
	public function getStatusesArray() {
		return empty($this->Statuses) ? array() : explode(',', $this->Statuses);
	}


	/**
	 * @param Order $order
	 * @return string
	 */
	public static function generate_hash(Order $order) {
		return sha1($order->ID . $order->Created . $order->Email);
	}


	/**
	 * @return array
	 */
	public function getSubstitutions() {
		$out = array(
			'Order'       => 'Contents of the order including products and totals',
			'Reference'   => 'Order reference number (if applicable)',
			'LastTouch'   => 'Date the order/cart was last touched',
			'DatePlaced'  => 'Date the order was placed (if applicable)',
			'ClaimLink'   => 'The URL to claim the cart back',
			'ClaimButton' => 'A button link that says "Reclaim Your Cart Now"',
			'FirstName'   => 'Customer\'s first name, if available (may not be for carts) or \'Customer\' if not',
			'LastName'    => 'Customer\'s last name, if available (may not be for carts) or \'Customer\' if not',
			'FullName'    => 'Customer\'s full name, if available (may not be for carts) or \'Customer\' if not',
			'Company'     => 'Customer\'s company name, if available (may not be for carts)',
		);

		if (class_exists('Quotable')) {
			$out += array(
				'DateSent'    => 'Date the quote was sent (if applicable)',
				'QuoteLink'   => 'The URL to claim a quote (only applies to quotes)',
				'QuoteButton' => 'A button link that says "Checkout Now"',
			);
		}

		$this->extend('updateSubstitutions', $out);

		return $out;
	}


	/**
	 * @param array $data
	 * @param Order $order
	 * @return array
	 */
	public function performSubstitutions(array $data, Order $order) {
		$data['Order'] = $order->renderWith(array('Order_Content'));
		$data['ClaimLink'] = Director::absoluteURL('FollowUpController/claim/' . $order->ID . '/' . self::generate_hash($order));
		$data['ClaimButton'] = '<a href="' . $data['ClaimLink'] . '" class="claim-button">Reclaim Your Cart Now</a>';
		$data['LastTouch'] = date('n/j/Y', strtotime($order->LastEdited));
		$data['DatePlaced'] = $order->Placed ? date('n/j/Y', strtotime($order->Placed)) : '';

		if (class_exists('Quotable')) {
			$data['DateSent'] = $order->QuoteSentAt ? date('n/j/Y', strtotime($order->QuoteSentAt)) : '';
			$data['QuoteLink'] = $order->getQuoteLink();
			$data['QuoteButton'] = '<div style="text-align:center; padding:15px">'
				. '<a href="' . $order->getQuoteLink()
				. '" style="font-size:1.5em; background:darkred; border-radius:5px; color:white; padding:10px; text-decoration:none">'
				. 'Checkout Now</a></div>';
			$data['Reference'] = $order->QuoteFriendlyReference();
		} else {
			$data['Reference'] = $order->Reference;
		}

		$this->extend('updatePerformSubstitutions', $data, $order);

		return $data;
	}


	/**
	 * Constructs an email object for the given order (if possible)
	 * @param Order $order
	 * @return Email|null
	 */
	public function getEmailForOrder(Order $order) {
		$data = CustomerDataExtractor::inst()->extract($order);
		$emailAddress = $data['Email'];

		// Send to admin?
		if ($this->To === self::TO_ADMIN) {
			$emailAddress = Email::config()->admin_email;
		}

		// Send the email if possible
		if (!empty($emailAddress)) {
			// fill in some additional templating
			$data = $this->performSubstitutions($data, $order);

			// build the email
			$email = new Email();
			$email->setFrom(Email::config()->admin_email);
			$email->setTo($emailAddress);
			$email->setSubject($this->Subject);

			$body = $this->customise($data)->renderWith(array('FollowUpEmail'));
			foreach ($data as $k => $v) {
				$body = str_replace(
					array('http://{{{'.$k.'}}}', '{{{'.$k.'}}}'),
					array($v, $v),
					$body
				);
			}
			$email->setBody($body);

			return $email;
		} else {
			return null;
		}
	}


	/**
	 * Sends all
	 */
	public function sendToApplicableOrders($logFunc = null) {
		$field = 'Placed';
		if (strpos($this->Statuses, 'Quote') !== false) $field = 'QuoteSentAt';
		if (strpos($this->Statuses, 'Cart') !== false) $field = 'LastEdited';

		$orders = Order::get()
			->leftJoin('FollowUpEmail_Orders',
				"\"Order\".\"ID\" = \"FollowUpEmail_Orders\".\"OrderID\"
					AND \"FollowUpEmail_Orders\".\"FollowUpEmailID\" = '{$this->ID}'")
			->where('FollowUpEmail_Orders.FollowUpEmailID IS NULL')
			->filter(array(
				'Status'             => $this->getStatusesArray(),
				"$field:LessThan"    => date('Y-m-d H:i:s', time() - ($this->DaysAfter * 24 * 60 * 60)),
				"$field:GreaterThan" => date('Y-m-d H:i:s', time() - (($this->DaysAfter + 1) * 24 * 60 * 60)),
			))
		;

		foreach ($orders as $order) {
			$this->Orders()->add($order);
			$email = $this->getEmailForOrder($order);

			if ($email) {
				if ($logFunc && is_callable($logFunc)) {
					call_user_func($logFunc, "Sending '{$this->getTitle()}' to {$email->To()} for order {$order->ID}");
				}

				$email->send();
			}
		}
	}
} 
