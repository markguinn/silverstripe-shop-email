<?php
/**
 * Checks carts and orders against any auto-responders that have been set up.
 * This would need to be run nightly.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 09.30.2014
 * @package shop_email
 * @subpackage tasks
 */
class FollowUpEmailTask extends BuildTask
{
	protected $title = 'Send Follow Up Emails';
	protected $description = 'Checks carts and orders against any auto-responders that have been set up.';

	public function run($request) {
		foreach (FollowUpEmail::get() as $followUp) {
			/** @var FollowUpEmail $followUp */
			if ($followUp->Active) {
				$followUp->sendToApplicableOrders(function($msg){
					if (Director::is_cli()) {
						echo $msg . "\n";
					} else {
						echo $msg . "<br>\n";
					}
				});
			}
		}
	}
} 
