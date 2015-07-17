<?php
/**
 * Gives you an endpoint to reclaim a cart from the email
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 */
class FollowUpController extends Controller
{
	private static $allowed_actions = array('claim');


	/**
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function claim(SS_HTTPRequest $request) {
		/** @var Order $order */
		$order    = Order::get()->byID($request->param('ID'));
		$hash     = $request->param('OtherID');
		$realHash = FollowUpEmail::generate_hash($order);
		if (!$order || !$order->exists() || empty($hash) || $hash !== $realHash) {
			$this->httpError(404);
		}

		// Require a login if the order is attached to an account
		if ($order->MemberID && $order->MemberID != Member::currentUserID()) {
			return Security::permissionFailure($this->owner, _t('ShopEmail.NotYourOrder', 'You must log in to access this order.'));
		}

		// Otherwise if all is good, proceed to checkout
		ShoppingCart::singleton()->setCurrent($order);
		return $this->redirect( CheckoutPage::get()->first()->Link() );
	}

} 
