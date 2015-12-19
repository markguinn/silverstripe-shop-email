<?php
/**
 * Doesn't do anything.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 * @subpackage mailinglist
 */
class NullMailingListAdapter extends MailingListAdapter
{
    /**
     * @param MailingListEmail $email
     * @param string           $listID
     */
    public function send(MailingListEmail $email, $listID = '')
    {
    }


    /**
     * @param string $emailAddress
     * @param array  $data - any additional data
     * @param string $listID
     */
    public function add($emailAddress, array $data = array(), $listID = '')
    {
    }
}
