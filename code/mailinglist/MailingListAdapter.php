<?php
/**
 * Extracts out mailing list functionality to be able to use different
 * providers such as Mailgun or Silverstripe's Newsletter module.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 * @subpackage mailinglist
 */
abstract class MailingListAdapter extends Object
{
    /**
     * @param MailingListEmail $email
     * @param string           $listID
     */
    abstract public function send(MailingListEmail $email, $listID='');


    /**
     * @param string $emailAddress
     * @param array  $data - any additional data
     * @param string $listID
     */
    abstract public function add($emailAddress, array $data = array(), $listID='');


    /**
     * This will usually be overridden.
     * @return array - key=ID, value=Title
     */
    public function getMailingLists()
    {
        return array('default' => 'Default Mailing List');
    }


    /**
     * @return string
     */
    public function getDefaultListID()
    {
        if ($id = $this->config()->default_list) {
            return $id;
        } else {
            $lists = $this->getMailingLists();
            if (count($lists)) {
                reset($lists);
                return key($lists);
            }
        }

        return '';
    }
}
