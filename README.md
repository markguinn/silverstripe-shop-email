SS Shop Email Extensions
========================

Provides automated followup emails and mass mailings for Silverstripe's Shop 
module (https://github.com/burnbright/silverstripe-shop).

[![Latest Stable Version](https://poser.pugx.org/markguinn/silverstripe-cloudassets/v/stable.png)](https://packagist.org/packages/markguinn/silverstripe-cloudassets)
[![Latest Unstable Version](https://poser.pugx.org/markguinn/silverstripe-cloudassets/v/unstable.png)](https://packagist.org/packages/markguinn/silverstripe-cloudassets)
[![Build Status](https://travis-ci.org/markguinn/silverstripe-shop-email.svg?branch=master)](https://travis-ci.org/markguinn/silverstripe-shop-email)
[![License](https://poser.pugx.org/markguinn/silverstripe-cloudassets/license.png)](https://packagist.org/packages/markguinn/silverstripe-cloudassets)


Follow up emails:
-----------------

These allow you to send a given email (with some substitutions for customer
name, order contents, etc) to all order with a given status a certain number
of days after the last touch date. If the order is still a cart you can
also insert a button in the email to re-claim the cart even if the user wasn't
logged in.

### Examples:

* Send an "are you still interested?" email to abandoned carts 3 days after the
  last edit (i.e. the last time they added or removed an item).
* Send a thank you note 7 days after an order
* Send a coupon code 30 days after an order to attract repeat business.


Mass Mailings:
--------------

When this feature is on, all orders and member accounts will automatically be
added to a mailing list and you'll have a CMS interface to create and send
mailing to this list.

The only adapter included is for Mailgun, but you could easily write one for
MailChimp, ConstantContact or Silverstripe's email newsletter module.

By default this feature is turned off. To turn it off add something like the
following yml config:

```
MailingListEmail:
  default_adapter_class: MailgunMailingListAdapter
MailgunMailingListAdapter:
  api_key: XXXX
  domain: yourdomain.com
  test_mode: no
```


Install
-------

1. `composer require markguinn/silverstripe-shop-email`
2. Visit /dev/build at your site's URL (or `framework/sake dev/build`) 
3. Set up a cron job to run `dev/tasks/FollowUpEmailTask` every night.


Developer(s)
------------
- Mark Guinn <mark@adaircreative.com>

Contributions welcome by pull request and/or bug report.
Please follow Silverstripe code standards (tests would be nice).


License (MIT)
-------------
Copyright (c) 2015 Mark Guinn

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is furnished to do so, subject
to the following conditions:

The above copyright notice and this permission notice shall be included in all copies
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
