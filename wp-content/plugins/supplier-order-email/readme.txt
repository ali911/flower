=== Supplier Order Email ===
Contributors: mcidesarrollo
Tags: suppliers, emails, order, order supplier, order email, supplier email, emails de pedido, pedido al proveedor, orders emails
Requires at least: 4.6
Tested up to: 6.1
Stable tag: 2.2.11
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://mci-desarrollo.es/plugins/

Sends an automatic order emails to the suppliers to send the corresponding products to the customer.

== Description ==

Sends an automatic order email to the supplier to send the corresponding products to the customer.
The mail is sent when the order goes to the "Processing" status in Woocommerce.
Creates a new taxonomy (Suppliers) and a new selection box for each product.
Subject and introductory text can be customized for automated emails.
Option to also send an email to the admin when sending an email to suppliers.

https://youtu.be/Vp2zs_aBgjs

[Open the mini-tutorial video of the plugin](https://youtu.be/Vp2zs_aBgjs)

== Installation ==

1. Install the plugin through the WordPress plugins screen directly or upload the "Supplier Order Email" plugin to the / wp-content / plugins /
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Create new suppliers in Products / Suppliers
4. Select the supplier of the products when editing each one in a new selection box 'Suppliers' that appears.
5. Set your preferences in "Supplier Order Email" settings page.
6. This is all. When an order changes to "Processing" status, an automatic order email is sent to the supplier to send the corresponding products to the customer.


== Screenshots ==

1. Product edit page with new selection box.

2. Suppliers page.

3. "Supplier Order Email" settings page.

4. Email sent to the supplier.


== Changelog ==

= 2.2.11 =
* Added a new optional parameter $order to the function that sends the data to the _mcisoe_email_customer_data.php template in order to receive any order data.

= 2.2.10 =
* Add lang parameter to plugin link.

= 2.2.9 =
* New link to mini-tutorial.

= 2.2.8 =
* Fix bug in _delivery_date custom field.

= 2.2.7 =
* Compatible with WordPress 6.1

= 2.2.6 =
* Increased security using the product_id when connecting to the authentication api.
* Fixed bug closing of html tag in email footer.
* Specify fonts so that some mail servers interpret them correctly.
* Added constant 'MCISOE_REAL_ENVIRONMENT' to develop works.

= 2.2.5 =
* Fixed a bug that affected user translations when the order went to "Processing" status from a non-registered user purchase.

= 2.2.4 =
* Added filter by suppliers in products list.
* Added native WooCommerce product export.
* Added Yoast SEO compatibility when several suppliers are selected.

= 2.2.3 =
* Improvements in internationalization for English and Spanish.

= 2.2.2 =
* The plugin now supports native WooCommerce product import.

= 2.2.1 =
* Fixed text in changelog.
* Fixed conditional problem when showing shipping method and payment method in table.

= 2.2.0 =
* Created a new field supplier_custom_text for each supplier that can display a personalized text in the subject or the introductory text of the email by supplier.

= 2.1.5 =
* Include link to plugin manual in settings panel page.
* New translation in title of the button for manual orders.
* Added email address detail to admin email.
* Added optional order_date tag to customize email subject or introductory text.
* Added option to show product description in emails.
* Added option to show payment method in emails.
* Added option to show shipping method in emails.

= 2.1.4 =
* Changes in help text.
* Solved bug with undefinded variable of cost format.
* Include link to plugin manual in help and settings panel page.

= 2.1.3 =
* Improved logo html/css optimization in the template.
* New translations.

= 2.1.2 =
* Allow line breaks in introductory text.
* Improvements in the registration of the first supplier.

= 2.1.1 =
* Fixed bug that caused a problem saving the introductory text in the plugin settings.

= 2.1.0 =
* Including the option to replace the text header of emails with the store logo.

= 2.0.4 =
* Fixed a bug that caused a conflict by hiding fields when creating categories.

= 2.0.3 =
* Prices now adapt to the format that has been configured in WooCommerce/Settings/General to adapt the currency symbol, position, space, decimal separators and thousands separators.

= 2.0.2 =
* Fixed a bug that prevented the correct completion of the order and the sending of emails when the payment method makes the order go directly to the "Processing" status and the user does not have access permissions to the backoffice.

= 2.0.1 =
* Include compatibility with plugin Cost of Goods (SkyVerge) and add 2 new checkboxes when plugin is active.

= 2.0.0 =
* Code refactoring to make the plugin more scalable, more fast, more light, and ready to receive new features.
* Changed plugin access item in WordPress sidebar menu. Now it is no longer a submenu of WooCoomerce.
* Converting the plugin tables to the native option storage system in the database.
* Suppliers no longer have to be created in 2 places. Suppliers are now created in Products/Suppliers in the native WordPress way.
* Changed the layout, navigation and buttons for a better user experience.
* Text and visual aids have been added to tell the user how to create suppliers when none exist yet.
* Added notice warnings if WooCommerce is not activated.
* Added multiple copies of supplier emails. Sent to the admin if the option is enabled.
* Changed the product list style to a html table format.
* Product Attributes and Product Variations-Custom fields, have been separated into two selectable options to choose what to display in emails more precisely.
* Added option delete all data when uninstall the plugin.
* Added footer in emails.
* Optimize HTML code in emails.
* Added functionality to override templates in child theme.
* New help section in menu.

= 1.5.14 =
* Tested up to WordPress 6

= 1.5.13 =
* New link to trial.

= 1.5.12 =
* Minor bug corrected.

= 1.5.11 =
* Free Premium trial modified.

= 1.5.10 =
* Free Premium trial added.

= 1.5.9 =
* Code optimization when connecting to APIs in the premium version.

= 1.5.8 =
* Configuration checkbox to choose whether to show custom product fields in emails.

= 1.5.7 =
* Now supports custom fields on order products
* Tested compatibility with WooCommerce 6.5

= 1.5.6 =
* Fixed incompatibilities with the new version of WooCommerce 6.4
* Improved the login section for the premium plugin
* New custom order fields in emails

= 1.5.5 =
* Fixed bug that prevented premium authentication.

= 1.5.4 =
* Added Accept header for authentication requests.

= 1.5.3 =
* Corrected warning bugs.

= 1.5.2 =
* Corrected notice styles.
* Fixed issue in activation if some data is empty.

= 1.5.1 =
* Improvement for update of old premium users.

= 1.5.0 =
* New improvement in authentication system.

= 1.4.1 =
* Corrected text for traduction in options panel.

= 1.4.0 =
* New functionality that allows including customer notes in emails.
* New functionality that allows you to include the order number in the subject of the email.
* Fixed issue on installation if there are no tables yet.

= 1.3.7 =
* Resolved other compatibility issues with Yoast Seo.

= 1.3.6 =
* Code change: get_id to get_product_id.

= 1.3.5 =
* Code cleaning in form.

= 1.3.4 =
* Fixed bug that caused a problem when Yoast Seo was active and a primary category had not been defined in the product.

= 1.3.3 =
* Added custom field _delivery_date to emails if it exists in the order.

= 1.3.2 =
* Fixed a bug in a feature that improves compatibility with Yoast Seo and caused shipments to wrong suppliers.

= 1.3.1 =
* Fixed bug that sent email if supplier had not been selected in the product if that product had previously had a supplier.

= 1.3.0 =
* Fixed bug that showed a warning when there is no password.

= 1.2.9 =
* Improved class encapsulation and security level.
* New functionality that allows you to manually send the order emails to the suppliers (premium version).
* Add the shipping phone number when it has been filled in.
* Tested in WooCommerce 5.6

= 1.2.8 =
* Tested in WordPress 5.8
* Added option to display customer email in supplier emails

= 1.2.7 =
* Tested in WordPress 5.7.2
* Added option to hide buyer data in order emails in premium version.
* Solved delete table issue to unnistall plugin.
* Eliminated the space that appeared by default in the input text of the plugin configuration page.

= 1.2.6 =
* Added header to emails with selectable color in premium version.

= 1.2.5 =
* Added the product attributes in the premium version.

= 1.2.4 =
* Solved error in the column names of the database that caused an error when updating the plugin.
* New functionality with option to show the price of products in emails (premium version).
* New functionality with option to show the total price of all products by supplier in emails (premium version).

= 1.2.3 =
* Changed the column name "options" of the table to avoid incompatibility with some servers

= 1.2.2 =
* Solved issue in form premium

= 1.2.1 =
* Solved issue in Plugin URI

= 1.2.0 =
* Check compatibility with Wordpress 5.6
* Modification so that the url of the suppliers is always myshop.com/supplier/namesupplier. The parent folder has been removed from the url even though one has been customized in "Permalinks".
* Add translations.
* Add show the order number in emails (premium version).
* Add Show the product EAN numbers in emails (premium version).

= 1.1.9 =
* Fixed bug to stop showing the notice to create new suppliers in Wordpress.
* Changed the color of the warnings to orange.
* Change notice translation.

= 1.1.8 =
* Fixed issue in button text link translation.

= 1.1.7 =
* Improvement: Added notices on the desktop when suppliers are not created and buttons to create them.
* Improvement: New notice before deleting a supplier and buttons to delete or cancel.
* Improvement: Corrected excessive line breaks in the email to the provider.

= 1.1.6 =
* fixed a lack in shipping address line breaks

= 1.1.5 =
* Improvement: Now the phone is not sent to the supplier if the shipping address is empty. Instead, a warning is displayed.
* Improvement: The source from where the shipping address is obtained has been modified. Now the Province / State is shown with the full name.
* Change: The 'Send email to admin' box has been predefined as enabled the first time the plugin is installed.
* Fixed issue: When there was an order whose products did not have suppliers selected and the box 'Send email to admin' was not selected, an email was also sent.
* Add new translations.

= 1.1.4 =
* Fixed new issue table modifications. Now a system has been defined that checks if the installed version is different from the version record in the database.

= 1.1.3 =
* Fixed issue table modification when updating

= 1.1.2 =
* New selectable functionality: Use the customer's billing address if the order does not have a shipping addresss.
* Fixed error in the table when it was updated to version 1.1.0

= 1.1.1 =
* Fixed issue when a product has multiple suppliers. In that case, the product is now ordered from the primary supplier.
* New notification email to the administrator when the order does not have a shipping address with recommendations to solve it in Woocommerce.
* Now it does not send the email to the administrator until the emails have been sent to all suppliers.
* Add translations.
* Tested in Wordpress 5.5

= 1.1.0 =
* Control panel completely redesigned to make it more user-friendly.
* Unlimited suppliers can now be added.
* Option to send an email to the admin when sending an email to suppliers.
* Add translations.

= 1.0.5 =
* Change permissions to manage_categories.

= 1.0.4 =
* Prevent the text "Ref." if the product does not have SKU.
* New background color when a provider is active.
* New translation of the "Show order" button.
* Include text "Do you need us to customize the plugin for you?".
* Include link to plugin rating. 

= 1.0.3 =
* Include versions in readme.txt

= 1.0.2 =
* Fixed subject translations and email introduction.

= 1.0.1 =
* Fixed bug in readme "Donate".
* Fixed bug in placeholder-email.

= 1.0 =
* Initial release