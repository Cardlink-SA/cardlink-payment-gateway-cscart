# Cardlink gateway addon for CS-Cart

Cardlink Payment Gateway allows you to accept payment through various schemes such as Visa, Mastercard, Maestro, American Express, Diners, Discover cards on your website.


## Changelog

* 1.1.5
  * Made seller id optional for Nexi. (If you already use IRIS with your own seller id, DO NOT install the new version!)

* 1.1.4
  * Fixed RF code generation on Nexi+Cardlink.
  * Fixed digest check on Worldline

* 1.1.3
  * Enabled IRIS for Worldline and Cardlink. (If you already use IRIS with your own seller id, DO NOT install the new version!)
  
* 1.1.2
  * Fixed parameters order that in specific conditions could result in invalid digest.

* 1.1.1
  * Added fix for overrides by other addons. Currently payment_dependencies addon.

* 1.1.0
  * Added support for IRIS payments on Nexi. (If you already use IRIS with your own seller id, DO NOT install the new version!)
  * Now addon can add payment methods with images

* 1.0.3
  * Fixed extra check if bank not send field

* 1.0.2
  * Added ship params
  * Fixed invalid digest error

* 1.0.1
  * Added ALPHA Bonus

* 1.0.0
  * Initial release


## Description 

Cardlink Payment Gateway allows you to accept payment through various schemes such as Visa, Mastercard, Maestro, American Express, Diners, Discover cards on your website, with or without variable installments.
This plugin aims to offer new payment solutions to Cardlink merchants through the use of CMS plugin for their website creation and provide the possibility to add extra features without having web development knowledge. 
Merchants with e-shops (redirect cases only) will be able to integrate the Cardlink Payment Gateway to their checkout page using the CSS layout that they want. Also they could choose between redirect or iframe option for the payment enviroment. Once the payment is made, the customer returns to the online store and the order is updated.
Once you have completed the requested tests and any changes to your website, you can activate your account and start accepting payments. 

## Features 

1.	A dropdown option for instance between Worldline, Nexi και Cardlink.
2.	Option to enable test environment. All transactions will be re-directed to the endpoint that represents the production environment by default. The endpoint will be different depending on which acquirer has been chosen from instance dropdown option.
3.	Ability to define the maximum number of installments regardless the total order amount and ability to define the ranges of the total order amounts and the maximum installment for every range.
4.	Option for pre-authorization or sale transactions.
5.	Option for a user tokenization service. The card token will be stored at the merchant’s e-shop database and will be used by customers to auto-complete future payments. 
6.	Redirection option: user should have a check box to enable pop up with i-frame without redirection.
7.	A text field for providing the absolute or relative (to Cardlink Payment Gateway location on server) url of custom CSS stylesheet, to change css styles in payment page.
8.	Translation ready for Greek & English languages.
9.  IRIS payments for all acquirers. Uppon installation, an extra payment is created.
10. Not supporting iframe. The IRIS payments does not support the iframe feature.


## Installation

If you have a copy of the plugin as a zip file, you can manually upload it and install it through
the “Manage Addons &gt; Manual Installation” admin page.
This can be found by clicking the “Manual Installation” option as shown in the following
image:
   ![image001](https://developer.cardlink.gr/downloads/cardlink-payment-gateway-cscart-assets/image001.png)

1. Upload cardlink.zip as shown in the following image:
   ![image002](https://developer.cardlink.gr/downloads/cardlink-payment-gateway-cscart-assets/image002.png)
2. After upload, you should locate the Cardlink addon in the addons list and click “Install”.
3. Go to “Administration &gt; Payment methods ”. There will be two new payment methods named “Cardlink” & “IRIS by Cardlink”.
4. Click on main payment method (Cardlink) and then on click the &quot;Configure&quot; tab and then fill in the payment module settings as shown in the
   following image:
   [IMAGE CARDLINK]
   

## Update

Before update you need to have the ***Rebuild cache automatically*** switch to ON or otherwise addon changes might not appear as they should.
You can find the switch at: Website -> Themes (?dispatch=themes.manage) as shown in image:
[IMAGE REBUILD_CACHE]
  
