# Shop Email Module

## Follow-up Emails

Use the "Email Marketing" admin area to set up automatic follow up emails. These can be triggered for
any set of order statuses to be triggered X days after the last touch. For example, to send an email
to the customer 3 days after an abandoned cart, use the following settings:
 
* Statuses: Cart
* Days: 3
* Active: YES
* To: Customer
* Include {{{Order}}} and {{{ClaimButton}}} in the copy of the email.

To send a notification to an admin if an email is still unpaid after 7 days of being placed, use:

* Statuses: Unpaid
* Days: 7
* Active: YES
* To: Admin

The email will only be sent once per order, even if the criteria is met multiple times.

**NOTE:** Right now only the LastEdited field is used for timing. That means if you change the status or
 anything about the order in the backend, it will reset the counter. This may need to be changed to 
 use Placed or Paid date for different statuses.


## Mailing Lists

The module supports automatically adding customers to a mailing list and then being able to
send newsletters and marketing emails to those lists. It's set up to support using different
services to collect and send to these lists. The default adapter does nothing, but there's an
adapter included to use Mailgun and it would be easy enough to write others, including one for
the Silverstripe Newsletter module.

To set up with Mailgun, make sure to install 'mailgun/mailgun-php' via composer. Then add the
following yml config somewhere in your project:

```
MailgunMailingListAdapter:
  api_key: XXXXXX
  domain: yourdomain.com
MailingListEmail:
  default_adapter_class: MailgunMailingListAdapter
```

You'll need to set up at least one mailing list in Mailgun as well.
