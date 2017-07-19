=== YITH WooCommerce Request a Quote ===

Contributors: yithemes
Tags: request a quote, quote, yithemes, message, woocommerce, shop, ecommerce, e-commerce
Requires at least: 3.5.1
Tested up to: 4.8
Stable tag: 1.7.7

The YITH Woocommerce Request A Quote plugin lets your customers ask for an estimate of a list of products they are interested into.

== Changelog ==
= 1.7.7 - Released on Jun 22, 2017 =
New: Support to WooCommerce 3.1 RC
Update: DOMPDF Library 0.8.0
Dev: "ywraq_quote_accepted_statuses_send" and "ywraq_quote_accepted_statuses_edit" filters
Dev: added backorder filter for out of stock items
Dev: added attachment filter on emails
Fix: Redirect to thank you page after Gravity form is sent
Fix: Gravity form get_forms function
Fix: Hide add to cart in loop
Fix: Total price of product with add-ons (YITH WooCommerce Product Add-Ons Premium)

= 1.7.6 - Released on Jun 05, 2017 =
Fix: Missing form in the request a quote email
Fix: Wpml cart redirect
Fix: Double meta with product add-on free

= 1.7.5 - Released on May 30, 2017 =
New: Support to WooCommerce 3.0.7
New: Option to show total in quote list
New: Map between quote and extra fields in default form
Fix: Pdf pagination option
Fix: Cart page as redirect after quote acceptance
Fix: Contact form 7 with WPML
Fix: Add to quote for grouped
Fix: Date format in quote email
Fix: Add to quote button with product addons in loop
Fix: Vendor user doesn't receive the quote email notification
Fix: Hide button in variable products
Fix: Fix variation thumbnail in pdf
Update: Plugin Core

= 1.7.4 - Released on Apr 26, 2017 =
New: Support to WooCommerce 3.0.4
Update: Plugin Core
Fix: Quantity in single product page
Fix: Display of thumbnails in some email clients
Fix: Removed loading of PrettyPhoto in single product page
Fix: Email to Vendors in the integration with YITH WooCommerce Multi Vendor Premium

= 1.7.3 - Released on Apr 19, 2017 =
Dev: Added a filter 'ywraq_hide_add_to_cart_single'
Fix: Thumbnail view in some mail client
Fix: Transform the quote into order after that the quote is accepted
Fix: Select of products and categories into exclusion tab
Update: Plugin Core

= 1.7.2 - Released on Mar 17, 2017 =
New: Support to WooCommerce 3.0 RC 2
New: Added a check if user email is valid
Fix: Hide price in product variations
Fix: Additional information in Requesta a quote page for YITH WooCommerce Product Add-Ons Premium
Fix: Additional information in Requesta a quote page for YITH Composite Product for WooCommerce
Dev: Added filter for Gravity Form 'ywraq_gravity_form_installation'
Update: Plugin Core


= 1.7.1 - Released on Mar 09, 2017 =
Fixed: Issue with YITH WooCommerce Product Add-Ons Premium
Fixed: Issue with Request a quote button and the button add to cart in variable products
Update: Plugin Core

= 1.7.0 - Released on Mar 06, 2017 =
New: Support to WooCommerce 2.7 RC 1
New: Support to 'upload' type file of YITH WooCommerce Product Add-Ons Premium 1.2.4
Update: Plugin Core

= 1.6.3 - Released on Jan 30, 2017  =
New: Option to override the shipping of shop from the quote
New: Option to override the billing/shipping info in the checkout page after that the quote is accepted
New: Option to lock billing/shipping info in the checkout page after that the quote is accepted
New: DOM PDF Library ready for the font 'fireflysung' Chinese font
New: DOM PDF Library ready for the font 'nanumbarungothic' Korean font
Tweak: Empty cart if a customer deletes a payment after that the quote is accepted
Tweak: Added the filter 'ywraq_meta_data_carret' to format the metadata in the single item of quote
Tweak: Compatibility with YITH WooCommerce Minimum Maximum Quantity
Tweak: Item data value on variation products
Fix: Automate quote process
Fix: Display button in single product page

= 1.6.2.3 - Released on Jan 11, 2017  =
Fix: Logo image in pdf quote

= 1.6.2.2 - Released on Jan 11, 2017  =
New: Russian translation
New: Quantity validation in single product page
Fix: Check on quantity fields in quote list
Fix: Contact form 7 additional fields the quote metabox
Fix: Automate quote process

= 1.6.2.1 - Released on Dec 09, 2016  =
Fixed: Show button in single product page
Fixed: Quote list in my account

= 1.6.2 - Released on Dec 07, 2016  =
Added: Support to Wordpress 4.7
Fixed: Show button in single product page

= 1.6.1 - Released on Dec 03, 2016  =
Fixed: Hide add to cart button in single product page

= 1.6.0 - Released on Dec 02, 2016  =
Added: Integration with Gravity Forms plugin to create custom forms for quote requests
Added: Create quotes in the backend
Added: Option to show the "Request a quote" button next to the "Add to Cart" button in single product page
Added: Show/hide the "Request a Quote" button on out of stock products
Added: Filter arguments for the button template using  'ywraq_add_to_quote_args'
Added: WPML string translation in the request-a-quote email
Added: Method to add an item in the list from query string
Tweak: Hide add to cart button
Fixed: Removed Notice when the redirect to a thank you page is set
Updated: Plugin Framework


= 1.5.8 - Released on Oct 03, 2016  =
Added: Integration with plugin YITH Composite Product for WooCommerce 1.0.1

= 1.5.7 - Released on Sep 29, 2016  =
Added: Integration with plugin YITH WooCommerce Sequential Order Number Premium v.1.0.8
Added: Integration with plugin YITH WooCommerce Product Bundles Premium v.1.1
Added: Request a quote button visible in variation products
Added: Filter 'ywraq_exclusion_limit' to change the number or products in page in the exclusion list
Added: Shortocode [yith_ywraq_number_items] to show the number of items in list
Updated: Plugin Framework

= 1.5.6 - Released on Aug 26, 2016 =
Added: Triggers in javascript add to quote events
Added: Added total on request a quote list and email request a quote
Fixed: some issue with WooCommerce Multilingual issue

= 1.5.5 - Released on Aug 01, 2016 =
Fixed: Issue in the quote number

= 1.5.4 - Released on Jul 07, 2016 =
Added: an option to add default shipping cost on quote
Fixed: save option of single Product Settings for quote requests issue
Fixed: some issue with WPML

= 1.5.3 - Released on Jul 07, 2016  =
Added: Spanish translation
Added: Filter 'ywraq_pdf_file_name' to change the pdf file name
Tweak: Option to site old price in the quote details, email and pdf document
Tweak: Removed quote without items in my account page for YITH WooCommerce Multi Vendor Premium compatibility
Fixed: Double orders when a payment is made with a gateway like Paypal
Fixed: Shipping Fee for wc 2.6

= 1.5.2 - Released on Jun 28, 2016 =
Added: Norwegian translation
Added: {quote_number} as placeholder in the request quote and quote email
Tweak: Pdf creation when WooCommerce PDF Invoices & Packing Slips is installed
Fixed: Shipping tax from quote to order
Fixed: Template Reject quote

= 1.5.1 - Released on Jun 10, 2016 =
Added: Support to WooCommerce 2.6 RC1
Fixed: Auto Save of quantity for formatted input numbers

= 1.5.0 - Released on Jun 01, 2016 =
Fixed: Cron to clean the session on database
Fixed: Optional argument to function yith_ywraq_get_product_meta
Updated: Plugin Core Framework

= 1.4.9 - Released on May 25, 2016 =
Added: Support to WooCommerce 2.6 beta 2
Added: [yith-request-a-quote-list] tag to Contact Form 7 legend
Added: Options to manage the 'Return to Shop' button
Added: Option to send quote automatically
Added: Associate guests' quotes to newly registered customers using the same email address
Fixed: Thank-you page redirect from Contact form 7
Fixed: Wrong quote number and link in vendor quote emails

= 1.4.8 - Released on May 05, 2016 =
Added: Option to force users to register when requesting a quote
Added: Javascript min files

= 1.4.7 - Released on May 04, 2016 =
Added: pt_BR translation
Added: Compatibility with WooCommerce Advanced Quantity
Fixed: Compatibility with YITH WooCommerce Product Add-Ons 1.0.8
Fixed: Compatibility with WooCommerce Product Add-ons 2.7.17
Fixed: Woocommerce Taxes in order created from a request
Fixed: Variation's thumbnails in the quote email and pdf

= 1.4.6 - Released on Apr 19, 2016 =
Added: Option to disable/enable orders
Added: External/Affiliate products
Fixed: Issue in the request a quote email
Fixed: Variation details in the order

= 1.4.5 - Released on Apr 12, 2016 =
Fixed: Contact form 7 issue after the latest update
Fixed: The add to quote of grouped products

= 1.4.4 - Released on Apr 11, 2016 =
Added: An option to hide or show the details of the quote after send the request of quote
Added: A button "Return to shop" when the list is empty
Added: A button "Return to shop" at the bottom of the list
Added: Css classes inside the message when the list is empty
Added: Compatibility with YITH WooCommerce Advanced Product Options
Added: Compatibility with WooCommerce Composite Products
Added: Options to customize the text message to show after request a quote sending
Added: Options hide "Accept" button in the Quote
Added: Options to change "Accept" button Label
Added: Option to choose the page linked by Accept Quote Button. The default value is the page Checkout, change the page to disable the checkout process
Added: Options hide "Reject" button in the Quote
Added: Options to change "Reject" Button Label
Added: A new order status Accepted used when the process to checkout is disabled
Added: For default form you can choose now if each additional field is required or not
Added: Option to hide the total column from the list
Updated: Template email quote-table.php and request-quote-table.php removed double border to the table
Updated: Plugin Core Framework
Tweak: Contact form 7 hidden when the list is empty
Tweak: Shipping methods and shipping prices are now set in the checkout
Tweak: Compatibility with YITH Woocommerce Email Templates Premium
Fixed: Download PDF now is showed after that the order is completed
Fixed: Additional Field on Contact form 7 now are added into the quote email and in the Quote page details
Removed: File inlcudes/hooks.php all content now is in  YITH_YWRAQ_Frontend Class constructor

= 1.4.3 - Released on Mar 14, 2016 =
Added: compatibility with YITH WooCommerce Minimum Maximum Quantity
Added: compatibility with YITH WooCommerce Customize My Account Page
Added: Attribute 'show_form' on shortcode 'yith_ywraq_request_quote' can be 'yes'|'no'

= 1.4.2 - Released on Mar 07, 2016 =
Fixed: Ajax Calls for WooCommerce previous to 2.4.0
Fixed: Notice in compatibility with Multi Vendor Premium
Updated: Plugin Core Framework

= 1.4.1 - Released on Mar 04, 2016 =
Fixed: Request a quote order settings saving fields
Fixed: Enable CC Options in Request a quote email settings

= 1.4.0 - Released on Mar 02, 2016 =
Added: YITH WooCommerce Multi Vendor Premium 1.9.5 compatibility
Added: Filter 'ywraq_clear_list_after_send_quote' to clear/not the list in request quote page
Added: More details in the Quote Order Metabox
Updated: button loading time for variations products
Fixed: Loading of metabox in specific pages
Fixed: Calculation totals for enables taxes

= 1.3.5 - Released on Jan 19, 2016 =
Added: WooCommerce 2.5 compatibility
Fixed: Send quote issue

= 1.3.4 - Released on Jan 18, 2016 =
Added: Two more text field in default form
Added: WooCommerce 2.5 RC 3 compatibility
Fixed: compatibility with WooCommerce Product Addons
Updated: Plugin Core Framework

= 1.3.3 - Released on Dec 30, 2015 =
Fixed: Update plugin error

= 1.3.2 - Released on Dec 30, 2015 =
Added: WooCommerce 2.5 beta 3 compatibility
Fixed: Endpoints for View Detail page
Fixed: Email recipients settings to send quote

= 1.3.1 - Released on Dec 15, 2015 =
Fixed: Issue on Number of Request Quote Details after sent the request
Fixed: Issues on Contact Form 7 list in settings

= 1.3.0 - Released on Dec 10, 2015 =
Added: Wordpress 4.4 compatibility
Added: Optional Attachment in the email of quote
Added: Fee and shipping cost to the email and pdf document of quote
Added: Two text field to show before and after the product table in the quote email and pdf
Added: Admin notice if WooCommerce Coupons are disabled
Added: Product Grouped can be added into the request
Added: A tab in the settings of the plugin to manage pdf options
Added: An option to show "Download PDF" in my account page
Added: Option to add a footer in the pdf document
Added: An option to show Accept/Reject Quote in pdf document
Added: An option to show the button only for out of stock products
Added: Autosave increase/decrease quantity in the request quote page
Added: The possibility to increase price of products on the quote
Added: The possibility to choose the rule of users to show the request a quote button
Added: Compatibility with WooCommerce Min/Max Quantities
Added: Compatibility with WooCommerce Subscriptions
Updated: Changed Text Domain from 'ywraq' to 'yith-woocommerce-request-a-quote'
Updated: Plugin Core Framework
Fixed: Email settings on request quote

= 1.2.3 - Released on Oct 02, 2015 =
Added: Select products to exclude by category

= 1.2.2 - Released on Sep 30, 2015 =
Fixed: Product quantity when button Request a Quote is clicked
Added: Woocommerce Addons details in Request Quote Email
Added: Compatibily with YITH Essential Kit for WooCommerce #1

= 1.2.1 - Released on Sep 21, 2015 =
Fix: Show button for Guests
Updated: Plugin Core Framework

= 1.2.0 - Released on Sep 11, 2015 =
Fix: Quote send options
Fix: Contact form 7 send email
Added: WooCommerce Subscriptions

= 1.1.9 - Released on Aug 11, 2015 =
Added: WooCommerce 2.4.1 compatibility
Updated: Changed the spinner file position, it is added to the plugin assets/images
Fixed: Email Send Quote changed order id with order number in Accepted/Reject link

= 1.1.8 - Released on Jul 27, 2015 =
Added: 'ywraq_quantity_max_value' for max quantity in the request a quote list
Added: Compatibility with WooCommerce Product Add-ons
Added: Compatibility with YITH WooCommerce Email Templates Premium
Added: Option to choose the link to quote request details to show in "Request a Quote" email
Added: Option to choose if after click the button "Request a Quote" go to the list page
Added: Options to choose Email "From" Name and Email "From" Address in Woocommerce > Settings > Emails
Fixed: Refresh the page after that contact form 7 sent email
Fixed: Default Request a Quote form
Fixed: Line breaks in request message
Fixed: Minor bugs

= 1.1.7 - Released on Jul 03, 2015 =
Fixed: Sending double email for quote
Fixed: Reverse exclusion list in single product

= 1.1.6 - Released on Jun 29, 2015 =
Added: Option to show the product sku on request list and quote
Added: Option to show the product image on request list and quote
Added: Reverse exclusion list
Added: Send an email to Administrator when a Quote is Accepted/Rejected
Fixed: Contact form 7 send email
Fixed: Hide price in variation products

= 1.1.5 - Released on Jun 10, 2015 =
Added: filter for 'add to quote' button label the name is 'ywraq_product_add_to_quote'
Fixed: PDF Options settings

= 1.1.4 - Released on Jun 04, 2015 =
Fixed: Show quantity if hide add to cart button
Fixed: Minor bugs in backend panel

= 1.1.3 - Released on May 28, 2015 =
Added: Additional text field in default form
Added: Additional upload field in default form
Fixed: Price of variation in email table
Fixed: Request Number in Contact form 7

= 1.1.2 - Released on May 21, 2015 =
Added: Compatibility with YITH Woocommerce Quick View
Fixed: Message of success for guest users
Fixed: Show quantity if hide add to cart button
Fixed: Layout option tab issue with YIT Framework

= 1.1.1 - Released on May 06, 2015 =
Added: Compatibility with YITH WooCommerce Catalog Mode
Fixed: When hide "add to cart" button, the variation will not removed

= 1.1.0 - Released on Apr 21, 2015 =
Added: Wrapper div to 'yith_ywraq_request_quote' shortcode
Updated: Plugin Core Framework
Fixed: add_query_arg() and remove_query_arg() usage
Fixed: Minor bugs

= 1.0.2 - Released on Apr 21, 2015 =
Added: Attach PDF quote to the email
Updated: Compatibility with YITH Infinite Scrolling
Updated: Plugin Core Framework
Fixed: Template to overwrite

= 1.0.1 - Released: Mar 31, 2015 =
Updated: Plugin Core Framework

= 1.0.0 =
Initial release
