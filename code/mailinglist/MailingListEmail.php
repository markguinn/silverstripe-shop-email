<?php
/**
 * Represents an email that can be sent to one or more mailing lists.
 * If you're using Silverstripe's newsletter module, this will overlap
 * some functionality and you may want to disable it in the EmailMarketingAdmin
 * and use Newsletter instead.
 *
 * @property string ListID
 * @property string Subject
 * @property string Content
 * @property string SentAt
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 * @subpackage mailinglist
 */
class MailingListEmail extends DataObject
{
	private static $db = array(
		'ListID' => 'Varchar(255)',
		'Subject' => 'Varchar(255)',
		'Content' => 'HTMLText',
		'SentAt' => 'Datetime',
	);

	private static $summary_fields = array(
		'ListID'  => 'List',
		'Subject' => 'Subject',
		'SentAt'  => 'Sent',
	);

	private static $searchable_fields = array(
		'ListID',
		'Subject',
		'Content',
	);

	private static $better_buttons_actions = array('send', 'duplicateEmail', 'sendPreview');

	private static $default_adapter_class = 'NullMailingListAdapter';

	/** @var MailingListAdapter $adapter */
	private static $adapter;


	// This allows anyone with the permissions to SEE the email admin to use it
	function canCreate($member=null) {return true;}
	function canEdit($member=null) {return true;}
	function canDelete($member=null) {return true;}


	/**
	 * @return MailingListAdapter
	 */
	public static function adapter() {
		if (!isset(self::$adapter)) {
			self::$adapter = Injector::inst()->get(self::config()->default_adapter_class);
		}

		return self::$adapter;
	}


	/**
	 * @param MailingListAdapter $adapter
	 */
	public static function set_adapter(MailingListAdapter $adapter) {
		self::$adapter = $adapter;
	}


	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = new FieldList(array(
			DropdownField::create('ListID', 'Mailing List', self::adapter()->getMailingLists()),
			TextField::create('Subject', 'Subject'),
			HtmlEditorField::create('Content', 'Content'),
		));

		return $fields;
	}


	/**
	 * @return FieldList
	 */
	public function getBetterButtonsActions() {
		$fields = parent::getBetterButtonsActions();

		$fields->push(
			BetterButtonCustomAction::create('sendPreview', 'Send Preview to Me')
				->setRedirectType(BetterButtonCustomAction::REFRESH)
				->setSuccessMessage('Sent to ' . Member::currentUser()->Email)
		);

		if (!$this->SentAt) {
			$fields->push(
				BetterButtonCustomAction::create('send', 'Send to List')
					->setRedirectType(BetterButtonCustomAction::REFRESH)
					->setSuccessMessage('Sent to mailing list.')
			);
		} else {
			$fields->push(
				BetterButtonCustomAction::create('duplicateEmail', 'Duplicate')
					->setRedirectType(BetterButtonCustomAction::GOBACK)
					->setSuccessMessage('Mailing was duplicated.')
			);
		}

		return $fields;
	}



	/**
	 * @return string
	 */
	public function getFrom() {
		return Email::config()->admin_email;
	}


	/**
	 * Sends the email via the current adapter (or default)
	 */
	public function send() {
		if (!$this->SentAt) {
			self::adapter()->send($this, $this->ListID);
			$this->SentAt = SS_Datetime::now()->RAW();
			$this->write();
		}
	}


	/**
	 * Sends the email via the current adapter (or default)
	 */
	public function sendPreview() {
		$member = Member::currentUser();

		Requirements::clear();
		$body = $this->renderWith(array('MailingListEmail'));
		$body = str_replace(
			array('{{{FullName}}}', '{{{FirstName}}}', '{{{LastName}}}', '{{{Email}}}', '{{{UnsubscribeLink}}}'),
			array($member->getName(), $member->FirstName, $member->Surname, $member->Email, '#'),
			$body
		);

		$email = new Email($this->getFrom(), $member->Email, $this->Subject, $body);
		$email->send();
	}



	/**
	 * @return DataObject
	 */
	public function duplicateEmail() {
		$clone = $this->duplicate();
		$clone->SentAt = null;
		$clone->write();
		return $clone;
	}
}
