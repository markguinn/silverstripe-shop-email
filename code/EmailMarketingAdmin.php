<?php
/**
 * ModelAdmin for auto-responders and mailings
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 09.30.2014
 * @package shop_email
 */
class EmailMarketingAdmin extends ModelAdmin
{
    private static $url_segment = 'email-marketing';
    private static $menu_title = 'Email Marketing';
    private static $managed_models = array('FollowUpEmail', 'MailingListEmail');
    private static $menu_priority = 1;
    public $showImportForm = false;
}
