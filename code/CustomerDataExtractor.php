<?php
/**
 * Pulls customer name and email data from an order in a standard way.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 */
class CustomerDataExtractor
{
    /**
     * @return CustomerDataExtractor
     */
    public static function inst()
    {
        return Injector::inst()->get('CustomerDataExtractor');
    }

    /**
     * @param $obj - Order or Member object (or anything with similar fields)
     * @return array
     */
    public function extract($obj)
    {
        $data = array(
            'FirstName' => 'Customer',
            'LastName'  => 'Customer',
            'FullName'  => 'Customer',
            'Company'   => '',
        );

        // Choose the email and name from the Order or Member if present
        if ($obj->Email) {
            $data['Email'] = $obj->Email;
            if ($obj->FirstName) {
                $data['FirstName'] = $obj->FirstName;
            }
            if ($obj->Surname) {
                $data['LastName'] = $obj->Surname;
            }
            if ($obj->Company) {
                $data['Company'] = $obj->Company;
            }
        } elseif ($obj->hasMethod('Member') && $member = $obj->Member()) {
            $data['Email'] = $member->Email;
            if ($member->FirstName) {
                $data['FirstName'] = $member->FirstName;
            }
            if ($member->Surname) {
                $data['LastName'] = $member->Surname;
            }
            if ($member->Company) {
                $data['Company'] = $member->Company;
            }
        }

        // Set the full name if appropriate
        if ($data['LastName'] !== 'Customer') {
            $data['FullName'] = $data['FirstName'] . ' ' . $data['LastName'];
        }

        return $data;
    }
}
