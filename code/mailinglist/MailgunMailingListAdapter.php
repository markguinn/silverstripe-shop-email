<?php
use Mailgun\Mailgun;

/**
 * Uses the mailing list feature of the mailgun api.
 * http://documentation.mailgun.com/api-mailinglists.html#mailing-lists
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 10.01.2014
 * @package shop_email
 * @subpackage mailinglist
 */
class MailgunMailingListAdapter extends MailingListAdapter
{
    private static $api_key = '';
    private static $domain = '';
    private static $test_mode = 'no';

    /**
     * @return Mailgun
     */
    protected function getApi()
    {
        return new Mailgun(Config::inst()->get('MailgunMailingListAdapter', 'api_key'));
    }


    /**
     * @return string
     */
    protected function getDomain()
    {
        return Config::inst()->get('MailgunMailingListAdapter', 'domain');
    }


    /**
     * @param MailingListEmail $email
     * @param string           $listID
     */
    public function send(MailingListEmail $email, $listID='')
    {
        if (empty($listID)) {
            $listID = $this->getDefaultListID();
        }

        Requirements::clear();
        $body = $email->renderWith(array('MailingListEmail'));
        $body = str_replace(
            array('{{{FullName}}}', '{{{FirstName}}}', '{{{LastName}}}', '{{{Email}}}', '{{{UnsubscribeLink}}}'),
            array('%recipient_name%', '%recipient_fname%', '%recipient_lname%', '%recipient_email%', '%unsubscribe_url%'),
            $body
        );

        $this->getApi()->sendMessage($this->getDomain(), array(
            'from'       => $email->getFrom(),
            'to'         => $listID,
            'subject'    => $email->Subject,
            'html'       => $body,
            'o:testmode' => Config::inst()->get('MailgunMailingListAdapter', 'test_mode'),
        ));
    }


    /**
     * @param string $emailAddress
     * @param array  $data - any additional data
     * @param string $listID
     */
    public function add($emailAddress, array $data = array(), $listID='')
    {
        if (empty($listID)) {
            $listID = $this->getDefaultListID();
        }

        $this->getApi()->post("lists/$listID/members", array(
            'address'    => $emailAddress,
            'name'       => $data['FullName'],
            'subscribed' => 'yes',
            'upsert'     => 'yes',
        ));
    }


    /**
     * @return array - key=ID, value=Title
     */
    public function getMailingLists()
    {
        //		$cache = SS_Cache::factory('mailgun');
        $lists = array();

        $r = $this->getApi()->get('lists');
        if ($r->http_response_code == 200 && !empty($r->http_response_body->items)) {
            foreach ($r->http_response_body->items as $item) {
                $lists[ $item->address ] = $item->name;
            }
        }

        asort($lists);

        return $lists;
    }
}
