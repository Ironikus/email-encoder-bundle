=== Email Encoder - Protect Email Address ===
Contributors: ironikus
Tags: email address, protect, antispam, mailto, spambot, secure, e-mail, email, mail, obfuscate, encode, encoder, encrypt, hide, bot, crawl, spider, robots, spam, protection, harvest, harvesting, security
Requires at least: 4.0
Requires PHP: 5.1
Tested up to: 5.2.3
Stable tag: 1.53
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Encode mailto links, email addresses, phone numbers or any text to hide it from spam-bots. Mailto links are protected automatically.

== Description ==

Encode mailto links, email addresses, phone numbers or any text to hide it from spam-bots and data harvesters.

= Features =
* Protect mailto links and plain email addresses
* Protect phone numbers (or any text or html)
* Also supports special chars, like é, â, ö, Chinese characters etcetera
* Also protect your RSS feeds
* Use shortcodes, template functions, action and filter hooks
* Use the Encoder Form to manually create encoded scripts

= Easy to use  =
After activating the plugin all mailto links will be protected automatically.
You could use shortcodes or template functions to protect plain email addresses, phone numbers or other text.

= Support =
* Documentation - When activated check the Help tab on the plugin options page
* [FAQ](http://wordpress.org/extend/plugins/email-encoder-bundle/faq/)

= Like this plugin? =
[Please Review it](http://wordpress.org/support/view/plugin-reviews/email-encoder-bundle)

== Installation ==

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add new`
3. Search for `Email Encoder` and click 'Install Now' or click on the `upload` link to upload `email-encode-bundle.zip`
4. Click on `Activate plugin`

== Frequently Asked Questions ==

= How can I test if an email address (or other content) is encoded? =

First you can enable the plugin option (in the admin panel) called *"Show 'successfully encoded' text for all encoded content, only when logged in as admin user."*.
When you are logged in and look on the page there will be a sign on the right side of the email address confirming it was successfully encoded.

The other way is to check the source code yourself by right-clicking on the page and select *Page Source Code* (the exact text depends on the browser).
Now your (real) source code will be shown. Your email address should not be shown in a readable way in the source.

**Important:** in the element inspector of the browser the email address is *always* shown, so don't worry about that. That is because the inspector shows a real time representation of the page. This means an encoded email address is already decoded and made usable for the visitor of the page.

= How do I encode my email address(es)? =

In the posts you can use this shortcode:
`[eeb_email email="myname@test.nl" display="My Email"]`

But mailto links will be encoded automatically (option is on by default):
`<a href="mailto:myname@test.nl">My Email</a>`

The visitors will see everything as normal, but the source behind it will now be encoded (for spambots), and looks like:
`<script type="text/javascript">/*<![CDATA[*/ML="mo@k<insc:r.y=-Ehe a\">f/lMt";MI="4CB8HC77=D0C5HJ1>H563DB@:AF=D0C5HJ190<6C0A2JA7J;6HDBBJ5JHA=DI<B?0C5HDEI<B?0C5H4GCE";OT="";for(j=0;j<MI.length;j++){OT+=ML.charAt(MI.charCodeAt(j)-48);}document.write(OT);/*]]>*/</script><noscript>*protected email*</noscript>`

This code is not readable by spambots and protects your email address.

= How do I encode phone numbers or other text? =

Just use the following shortcode within your posts:
`[eeb_content]35-01235-468113[/eeb_content]`

For other parts of your site you can use the template function `eeb_content()`.

= Email address in a form field is being encoded in a strange way. What to do? =

An email address in a form field will not be encoded correctly.
There are 2 ways to solve this problem:

1. Turn off the option "Replace plain email addresses to protected mailto links". Keep in mind that this will be the case for the whole site.
1. Add the page ID of the form to the option "Do not apply Auto-Protect on posts with ID". The page content will be skipped by the plugin.

= How to use email encoding in Custom Fields? =

You will have to use the template function `eeb_email()` or `eeb_content()`.
For example, if your template contains:
`echo get_post_meta($post->ID, 'email address', true);`

Then change it to:
`$emailaddress = get_post_meta($post->ID, 'email address', true);
echo eeb_email($emailaddress, 'Mail Me');`

= How to create mailto links that opens in a new window? =

You could add extra params to the mailto link and add `target='_blank'` for opening them in a new window, like:
`[eeb_email email="yourmail@test.nl" display="My Mail" extra_attrs="target='_blank'"]`

In HTML this will look like:
`<a href="mailto:yourmail@test.nl" target="_blank">My Mail</a>`

= How can I encode content of BBPress, WP e-Commerce or other plugins? =

If you use other plugins that needs to be encoded you can add a callback to the action "init_email_encoder_bundle".
For Example:

`add_action('eeb_ready', 'extra_encode_filters');

function extra_encode_filters($filter_callback) {
	// add filters for BBPress
	add_filter('bbp_get_reply_content', $filter_callback);
	add_filter('bbp_get_topic_content', $filter_callback);
}`

= Can I use special characters (like Chinese)? =

Yes, since version 1.3.0 also special characters are supported.

= How to encode emails in all widgets (and not only text widgets)? =

If the option 'All text widgets' is activated, only text widgets will be filtered for encoding.
It's possible to filter all widgets by using the [Widget Logic Plugin](https://wordpress.org/plugins/widget-logic/) and activate the 'widget_content' filter.


== Screenshots ==

1. Admin Options Page
1. Check encoded email/content when logged in as admin
1. Email Encoder Form on the Site

== Other Notes
= Credits =
* [Adam Hunter](http://blueberryware.net) for the encode method 'JavaScript Escape' which is taken from his plugin [Email Spam Protection](http://blueberryware.net/2008/09/14/email-spam-protection/)
* [Tyler Akins](http://rumkin.com) for the encode method 'JavaScript ASCII Mixer'
* Title icon on Admin Options Page was made by [Jack Cai](http://www.doublejdesign.co.uk/)

== Changelog ==

= 1.53 =
* PLEASE READ BEFORE UPDATE
* THIS PLUGIN WILL BE REFACTORED WITH THE NEXT UPDATE
* TO PREPARE YOURSELF, YOU WILL FIND A LIST DOWN BELOW WITH THE CHANGES THAT AWAIT YOU
* - The plugin will be simplified using automatically the best protection for your site
* - The plugin will protect yout site out-of-the-box 
* - We introduce a site-wide protection, not only based on WordPress shortcodes (This includes protection for your FULL site)
* - You will be able to choose the protection type. Available will be: Automatically (using Javascript), Automatically (Without Javascript), Protection Text, Entity encode
* - The plugin structure will be optimized using the current WordPress standards
* - Switch between full site protection and only WordPress filters
* - (Optional) Protect emails by converting them to PNG images (where applicable)
* - Round-robin method for javascript based protection (Choose the best method automatically from similar protection methods)
* - Tons of bugfixes
* - All settings will be fully compatible in any combination
* - The encoding form will continue to exists
* - All settings that have been available in the old version and will be available in the new version are backwards compatible
* - Display admin notice to all encoded emails (where applicable)
* ###################
* THE UPDATE WILL BE LAUNCHED WITHIN OCTOBER
* Tweak: Introduce our new partner MailOptin

= 1.5.2 =
* Tweak: Add popup window for admin success message of hidden email
* Fix: Fix bug for non-available antispambot() function
* Ironikus takes over development (https://ironikus.com)

= 1.51 =
* 2019-03-25
* minor bug fixes
* 161,000 downloads; 30,000 installs

= 1.4.6 =
* Fixed bug retina png and gif images

= 1.4.5 =
* Fixed ? params bug

= 1.4.4 =
* Fixed skip responsive images containing @

= 1.4.3 =
* Changed content

= 1.4.2 =
* Fixed potential xss vulnerability

= 1.4.1 =
* Fixed [preserving classes on mailto links](https://wordpress.org/support/topic/preserve-link-classes)

= 1.4.0 =
* Fixed bug prefilled email address in input fields
* Added option protection text for encoded content (other than email addresses)

= 1.3.0 =
* Also support special chars for the javascript methods, like é, â, ö, Chinese chars etcetera
* Fixed bug unchecking options "use shortcode" and "use deprecated"

= 1.2.1 =
* Fixed bug index php error

= 1.2.0 =
* Added filter for Encoder Form content (eeb_form_content)

= 1.1.0 =
* Added filters for changing regular expression for mailto links and email addresses
* Fixed bug don't encode when loading admin panel

= 1.0.2 =
* Fixed bug wrong "settings" link
* Fixed bug removing shortcodes RSS feed

= 1.0.1 =
* Fixed PHP support (same as WordPress)

= 1.0.0 =
* NOW ONLY SUPPORT FOR WP 3.4.0+
* Fixed bug deleting setting values when unregister (will now be deleted on uninstall)
* Fixed bug also possible to set protection text when RSS disabled
* Fixed bug saving metaboxes settings
* Added option support shortcodes in widgets
* Added option removing shortcodes for RSS feed
* Removed "random" method option
* Changed names for action and shortcode (prefixed with eeb_), optional the old names will still be supported
* Added template function for creating the encoder form
* Changed class en id names of the Encoder Form

= 0.80 =
* Added screen settings
* Registered metaboxes
* Fixed bug random method
* Workaround for display with special characters (like Chinese), works only with enc_html

= 0.71 =
* Option to make own menu item (in admin panel) for this plugin
* Option for showing "successfully encoded" check
* Fixed bug showing errors for calling wrong translate function
* Fixed bug always showing encoded check on site (for html encode method)
* Added workaround for saving disabled checkboxes in options table
* Fixed bug where encoded check was also applied on output of encoding form

= 0.70 =
* Fixed bug with extra params
* Changed texts and added help tabs on admin options page
* Changed visual check for encoded mails/content by showing icon and success message
* Solved that all attributes of mailto links remain when encoding

= 0.60 =
* Added hook "init_email_encoder_form" to add custom filters (of other plugins)
* Added JavaScript code encapsulation for ASCII method
* Solved reinstalling bug for setting right encoding method
* Fixed bug shortcodes encoded with HTML method

= 0.50 =
* Added encode method for all kind of contents (template function and shortcode "encode_content")
* Added extra param for additional html attributes (f.e. target="_blank")
* Added option to skip certain posts from being automatically encoded
* Added option custom protection text
* Removed "method" folder. Not possible to add own methods anymore.
* Other small changes and some refactoring

= 0.42 =
* Widget Logic options bug

= 0.41 =
* Fixed bug by improving regular expression for mailto links
* Changed script attribute `language` to `type`
* Script only loaded on options page (hopefully this solves the dashboard toggle problem some people are experiencing)
* Added support for widget_content filter of the Logic Widget plugin

= 0.40 =
* Added option for setting CSS classes
* Improved RSS protection
* Removed Lim_Email_Encoder class (now all handled by the main class)
* Enabled setting checkbox for filtering posts
* Fixed PHP / WP notices
* Added param for encode methods: $obj

= 0.32 =
* Fix IE bug
* Bug plain emails
* Optional "method" param for tag and template function, f.e. [encode_email email="test@domain.com" method="ascii"]
* Small adjustments

= 0.31 =
* Fixed tiny bug (incorrect var-name $priority on line 100 of email-encoder-bundle.php)

= 0.30 =
* Added protection for emails in RSS feeds
* Improved filtering tags [encode_email ... ]
* Improved ASCII and Escape method and added noscript message
* Solved an option bug (encode mailto links VS encode plain emails)
* Made some cosmetical adjustments on the options page
* Code refactoring

= 0.22 =
* First decodes entities before encoding email
* Added more wp filters for encoding

= 0.21 =
* Changed Encoder Form: HTML markup and JavaScript
* Made some minor adjustments and fixed little bugs

= 0.20 =
* Implemented internalization (including translation for nl_NL)
* Improved user-interface of the Admin Settings Page and the Encoder Form
* Added template function: encode_email_filter()
* Kept and added only high-quality encoding methods
* Refactored the code and changed method- and var-names within the classes
* Removed 3rd param $encode_display out of the encoding methods, display should always be encoded
* Added prefix 'lim_email_' to the encoding methods

= 0.12 =
* Nothing changed, but 0.11 had some errors because /methods directory was missing in the repository.

= 0.11 =
* also possible to use encode tag in widgets by activating the "filter widget" option

= 0.10 =
* Works with PHP4 and PHP5
* Methods: default_encode, wp_antispambot, anti_email_spam, email_escape, hide_email
* Use the tags: `[email_encode email=".." display=".."]`, `[email_encoder_form]`
* Template function: `email_encode()`
