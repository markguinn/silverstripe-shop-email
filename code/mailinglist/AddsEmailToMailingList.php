<?php
/**
 * Add this extension to Order and Member (and anything else with
 * and email address) and it will trigger after write to save
 * to the mailing list.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 * @subpackage mailinglist
 */
class AddsEmailToMailingList extends DataExtension
{
	public function onAfterWrite() {
		try {
			if (
				$this->owner->isChanged('ID')
				|| $this->owner->isChanged('Email')
				|| $this->owner->isChanged('MemberID')
				|| $this->owner->isChanged('FirstName')
				|| $this->owner->isChanged('Surname')
			) {
				$data = CustomerDataExtractor::inst()->extract($this->owner);
				SS_Log::log("Adding/updating mailing list user: ".json_encode($data), SS_Log::INFO);
				if (!empty($data) && !empty($data['Email'])) {
					$this->owner->extend('updateMailingListData', $data);
					MailingListEmail::adapter()->add($data['Email'], $data);
				}
			}
		} catch(Exception $e) {
			SS_Log::log("Unable to add {$data['Email']} to mailing list: " . $e->getMessage(), SS_Log::WARN);
		}
	}
} 
