# e-Commerce Checkout Payment Gateway

Readme for CS-Cart

Date: 27/05/2026

## Worldline Checkout Payment Gateway

## Description

Worldline Payment Gateway allows you to accept payment through various schemes such as Visa, Mastercard, Maestro, American Express, Diners and Discover cards on your website, with or without variable installments. This plugin aims to offer new payment solutions to Worldline merchants through the use of a CMS plugin for their website creation and provides the possibility to add extra features without web development knowledge.

Merchants with e-shops, for redirect cases only, can integrate the Worldline Payment Gateway into their checkout page using the CSS layout they want. They can also choose between redirect or i-frame option for the payment environment. Once the payment is made, the customer returns to the online store and the order is updated.

Once you have completed the requested tests and any changes to your website, you can activate your account and start accepting payments.

## Main Features

- Card payments through Cardlink Checkout, Nexi Checkout or Worldline Greece Checkout.
- Test and live mode selection.
- Sale and pre-authorization transaction types.
- Installment rules by minimum order amount, with a maximum installment value of 60.
- Optional customer card tokenization for future purchases.
- Optional iframe payment mode for card payments.
- Custom CSS URL support for the hosted payment page.
- IRIS payment method created on addon installation.
- Optional IRIS RF payment code generation when an IRIS Customer Code is configured.
- Apple Pay and Google Pay wallet buttons when wallets are enabled.
- Server-to-server background confirmation URL for Cardlink back-office setup.
- Order page transaction history with status, settlement state and transaction IDs.
- Direct capture, void and refund actions from the CS-Cart admin order page.
- Greek and English language files.
- Automatic payment method icon setup on installation.

## Changelog

### 1.3.1

- Adds wallet support controls and checkout wallet rendering.
- Adds Google Pay 3D Secure finalize flow.
- Adds background confirmation URL display and endpoint.
- Adds improved direct transaction history parsing.
- Adds admin order transaction history table.
- Adds direct capture, void and refund controls.
- Adds settlement-aware action routing.
- Adds support for transaction-level action selection when multiple captures exist.

### 1.1.5

- Made seller ID optional for Nexi.

### 1.1.4

- Fixed RF code generation on Nexi and Cardlink.
- Fixed digest check on Worldline.

### 1.1.3

- Enabled IRIS for Worldline and Cardlink.

### 1.1.2

- Fixed parameter order that in specific conditions could result in invalid digest.

### 1.1.1

- Added fix for overrides by other addons. Currently `payment_dependencies` addon.

### 1.1.0

- Added support for IRIS payments on Nexi.
- Now addon can add payment methods with images.

### 1.0.3

- Fixed extra check if bank does not send field.

### 1.0.2

- Added shipping parameters.
- Fixed invalid digest error.

### 1.0.1

- Added ALPHA Bonus.

### 1.0.0

- Initial release.

## Effort

1. A dropdown option for instance between Worldline, Nexi and Cardlink.
2. Option to enable test environment. All transactions are redirected to the endpoint that represents the production environment by default. The endpoint is different depending on which acquirer has been chosen from the instance dropdown option.
3. Ability to define the maximum number of installments, with a maximum limit of 60, regardless of the total order amount.
4. Option for pre-authorization or sale transactions.
5. Option for a user tokenization service. The card token is stored in the merchant's e-shop database and is used by customers to auto-complete future payments.
6. Redirection option: user should have a checkbox to enable i-frame payment mode without redirection.
7. A text field for providing the absolute or relative URL, relative to Cardlink Payment Gateway location on server, of a custom CSS stylesheet to change styles in the payment page.
8. Translation ready for Greek and English languages.
9. IRIS payments for all acquirers. Upon installation, an extra payment is created.
10. IRIS payments do not support the iframe feature.

## Installation

If you have a copy of the plugin as a ZIP file, you can manually upload it and install it through the **Manage Add-ons > Manual Installation** admin page. This can be found by clicking the **Manual Installation** option.

1. Upload `cardlink.zip`.
2. After upload, locate the Cardlink addon in the add-ons list and click **Install**.
3. Go to **Administration > Payment methods**. There will be two new payment methods named **Cardlink** and **IRIS by Cardlink**.
4. Click on the main payment method, **Cardlink**, then click the **Configure** tab and fill in the payment module settings.

## Cardlink Payment Method Settings

### Acquirer

Select the acquiring gateway instance:

- Cardlink Checkout
- Nexi Checkout
- Worldline Greece Checkout

The selected acquirer controls the test and live endpoint used by the addon.

### Merchant ID

The merchant identifier provided by Cardlink or the selected acquirer.

### Shared Secret

The shared secret used to calculate and validate request and response digests. This value must match the one configured for the merchant account.

### Enable Secondary Actions

Configuration flag for direct post-payment actions from the CS-Cart order page, such as capture, void and refund.

When the transaction supports it, the admin order page can show:

- Full capture.
- Partial capture.
- Capture remaining amount.
- Void transaction.
- Refund amount.
- Transaction history actions per capture row.

### Enable Wallets

Enables Apple Pay and Google Pay buttons in checkout.

### Custom CSS

Optional URL of a custom stylesheet for the hosted gateway payment page.

### Currency

Currently the payment method configuration exposes `EUR`.

### Pre Authorize

Controls the transaction type:

- Disabled: sale/purchase transaction.
- Enabled: pre-authorized transaction.

Pre-authorized orders are stored as `Open` in CS-Cart when the gateway returns `AUTHORIZED`. They can later be captured or voided if secondary actions are available.

### Store Card Details

Allows logged-in customers to save tokenized card details for future purchases.

Notes:

- The merchant account must support tokenization.
- Tokenization is disabled when iframe mode is enabled.
- Stored cards are saved in `cardlink_cards`.
- Customers can remove stored cards from checkout.

### Enable Payment Iframe

Displays the card payment page inside the store checkout instead of redirecting the customer away from the site.

Notes:

- The store must use a valid SSL certificate.
- Some checkout functionality may be unavailable in iframe mode.
- IRIS payments do not support iframe mode.
- Tokenization and iframe mode are mutually exclusive in the admin UI.

### Test/Live Mode

Selects whether requests are sent to test or production endpoints.

### Background Confirmation URL

Configure this URL in the Cardlink back-office as the server-to-server confirmation endpoint. The endpoint validates the digest and updates the order independently from the customer browser return flow.

### Installments

Installment rules are configured as rows:

- Minimum amount.
- Number of installments.

At checkout, the addon shows only installment options whose minimum amount is lower than the current cart subtotal. The selected installment count is stored in the cart session and sent to the gateway as `extInstallmentperiod`.

The addon limits installments to a maximum of 60.

## Update

Before update, you need to have the **Rebuild cache automatically** switch set to **ON**, otherwise addon changes might not appear as they should.

You can find the switch at:

`Website > Themes (?dispatch=themes.manage)`

