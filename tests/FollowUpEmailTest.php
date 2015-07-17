<?php
/**
 * 
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 09.30.2014
 * @package shop_email
 * @subpackage tests
 */
class FollowUpEmailTest extends SapphireTest
{
	protected static $fixture_file = array('FollowUpEmail.yml');

	public function test_email_is_sent() {
		$this->updateCartDates(2);
		$followUp = $this->objFromFixture('FollowUpEmail', 'cart1'); /** @var FollowUpEmail $followUp */
		$msg = '';
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg = $m; });
		$this->assertEmailSent('bob@somewhere.com');
		$this->assertStringStartsWith("Sending '1: 2 days after Cart' to bob@somewhere.com for order ", $msg);
	}

	public function test_email_is_not_sent_before_time_window() {
		$this->updateCartDates(1);
		$followUp = $this->objFromFixture('FollowUpEmail', 'cart1'); /** @var FollowUpEmail $followUp */
		$msg = '';
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg = $m; });
		$this->assertFalse( (bool)$this->findEmail('bob@somewhere.com') );
		$this->assertEquals('', $msg);
	}

	public function test_email_is_not_sent_after_time_window() {
		$this->updateCartDates(3);
		$followUp = $this->objFromFixture('FollowUpEmail', 'cart1'); /** @var FollowUpEmail $followUp */
		$msg = '';
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg = $m; });
		$this->assertFalse( (bool)$this->findEmail('bob@somewhere.com') );
		$this->assertEquals('', $msg);
	}

	public function test_email_is_not_sent_for_other_statuses() {
		$this->updateCartDates(2);
		$followUp = $this->objFromFixture('FollowUpEmail', 'cart1'); /** @var FollowUpEmail $followUp */
		$followUp->sendToApplicableOrders();
		$this->assertFalse( (bool)$this->findEmail('test@example.com') );
	}

	public function test_email_is_not_sent_more_than_once() {
		$this->updateCartDates(2);
		$followUp = $this->objFromFixture('FollowUpEmail', 'cart1'); /** @var FollowUpEmail $followUp */
		$msg = array();
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg[] = $m; });
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg[] = $m; });
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg[] = $m; });
		$this->assertEmailSent('bob@somewhere.com');
		$this->assertEquals(1, count($msg));
	}

	public function test_substitutes_values() {
		$this->updateCartDates(2);
		$followUp = $this->objFromFixture('FollowUpEmail', 'cart1'); /** @var FollowUpEmail $followUp */
		$followUp->sendToApplicableOrders();
		$email = $this->findEmail('bob@somewhere.com');
		$this->assertRegExp('/200.00/', $email['content']); // checks for {{{Order}}} substitution
		$this->assertRegExp('/FollowUpController\/claim/', $email['content']); // checks for {{{ClaimButton}}} substitution
	}

	public function test_uses_sent_at_for_quotes() {
		$this->updateCartDates(10, 'QuoteSentAt');
		$followUp = $this->objFromFixture('FollowUpEmail', 'quote1'); /** @var FollowUpEmail $followUp */
		$msg = '';
		$followUp->sendToApplicableOrders(function($m) use (&$msg) { $msg = $m; });
		$this->assertEmailSent('test@example.com');
		$this->assertStringStartsWith("Sending '2: 10 days after QuoteSent' to test@example.com for order ", $msg);
		$email = $this->findEmail('test@example.com');
		$this->assertRegExp('/Checkout Now/', $email['content']); // checks for {{{QuoteButton}}} substitution
	}

	protected function updateCartDates($days, $field='LastEdited') {
		$date = date('Y-m-d H:i:s', time() - $days * 24 * 60 * 60 - 3600);
		DB::query("UPDATE \"Order\" SET \"$field\" = '$date'");
	}
} 
