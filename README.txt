=== Plugin Name ===
Contributors: neuropassenger
Donate link: https://neuropassenger.ru
Tags: cf7, spam, form
Requires at least: 5.7.2
Tested up to: 6.1.1
Stable tag: 1.7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin protects Contact Form 7 from spam.

== Installation ==

1. Unzip `bs-spam-protector.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.7.2 =
* Correction after updating Contact Form 7 to version 5.7.

= 1.7.1 =
* Code improvements.

= 1.7.0 =
* The ability to adjust the severity of the check for the time it takes to fill out the form.

= 1.6.1 =
* After the plugin update, the time interval for filling out forms is set to 12 hours for the validator to work correctly.

= 1.6.0 =
* SPAM Protector information is now stored in the Flamingo 'meta' column.

= 1.5.0 =
* More accurate calculation of the filling time for the textarea field.

= 1.4.0 =
* Setting for the time interval for filling out the form.

= 1.3.4 =
* Fixed a bug related to the calculation of the time to select files for uploading.

= 1.3.0 =
* Enhanced spam protection, new verification criteria.

= 1.2.2 =
* Now the button for submitting Contact Form 7 will be inactive while the validation key is received from the server.

= 1.2.1 =
* Fixes conflicts with cache.

= 1.2.0 =
* Added detailed log system for the validation process.

= 1.1.3 =
* Implemented earlier JavaScript loading for more reliable initialization of the validation process.

= 1.1.2 =
* Added the ability to use protection on several forms on a single page.

= 1.1.1 =
* Security fixes.

= 1.1 =
* Added a settings page for changing the secret key.
* The secret key is automatically generated upon plugin activation and deleted upon deactivation.

= 1.0 =
* First stable version.

== Upgrade Notice ==

= 1.2.1 =
* If you are experiencing problems with the validation code and are using caching, this update is highly recommended.

= 1.1.3 =
* It is recommended that you update if some submitted forms are incorrectly validated.

= 1.1.2 =
* If you are using more than one form on a single page, you need this update.

= 1.1.1 =
This version fixes security related issues.  Upgrade immediately.

= 1.1 =
More robust validation key generation.