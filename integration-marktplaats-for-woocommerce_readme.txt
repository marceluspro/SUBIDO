=== Woosa - Marktplaats for WooCommerce ===
Contributors: woosa
Donate link: https://woosa.com
Tags: comments, spam
Requires at least: 5.5
Tested up to: 6.8
Stable tag: 2.0.2
Requires PHP: 7.1
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connects WooCommerce with Marktplaats platform



== Description ==

This is the long description.  No limit, and you can use Markdown (as well as in the following sections).



== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woosa-marktplaats` directory, or install the plugin through the WordPress plugins section directly.
1. Activate the plugin through the 'Plugins' section in WordPress
1. Go to admin menu `Marktplaats` to configure the plugin



== Changelog ==

## 2.0.2 - 2025-10-31

### Fixed

* Removed outdated files that caused fatal errors

## 2.0.1 - 2025-10-30

### Changed

* Updated the settings page with a new logo and refreshed color scheme
* Switched support chat provider from Intercom to Help Scout
* If the "Website URL" setting is not provided, the product page URL will be used automatically
* Long ad titles are now automatically shortened

## 2.0.0 - 2025-10-16

### Changed

* The entire plugin has been re-built using our latest logic and task management framework, resulting in enhanced stability and significant performance improvements

## 1.8.2 - 2025-03-20

### Fixed

* Fatal error when saving plugin settings

## 1.8.1 - 2025-01-16

### Fixed

* Compatibility issue with PHP v8.3.13
* Product description missing
* Fatal error when deactivating the plugin

## 1.8.0 - 2024-09-12

### Added

* The ability to set auto-bid with total budget for each advertisement

### Fixed

* The `cpc` value is not using the default value from the product category

## 1.7.0 - 2023-12-21

### Added

* The plugin is compatible with HPOS of WooCommerce

## 1.6.4 - 2023-11-09

### Fixed

* In some cases the product cannot be published due to an error for CPC field value

## 1.6.3 - 2023-06-07

### Changed

* Improve the description for the Authorization settings page

## 1.6.2 - 2023-04-20

### Fixed

* In case the product variation description is empty it does not take as fallback the description of the parent product
* New created products are automatically pushed to Markplaats even the required data is not filled in

### Changed

* Long product titles are not shirked based on the a setting option
* In case the license key is not activated a warning message will be displayed

## 1.6.1 - 2023-03-23

### Fixed

* The category mapping and the field `Cost Per Click` are not working properly when product has multiple categogires

## 1.6.0 - 2023-01-17

### Added

* New setting option in glogal settings and per product to decide whether or not to allow contact by email from the published ad

### Fixed

* Solve the error `CRITICAL Uncaught InvalidArgumentException: The $action["callback"][0] must be the name of the class but not the instance`
* After the product is deleted is not possible to publish it again

## 1.5.0 - 2022-10-26

* [FIX] - The shop tax is not taken into account when the option **Prices entered with tax** is set on **No**
* [FIX] - The spaces from the postcode give length error
* [TWEAK] - The currency symbol is always for Euro
* [TWEAK] - The category label is always for `nl_NL` locale
* [FEATURE] - Automatically trigger the updates when something changes on the product data


## 1.4.0 - 2022-09-05

* [FEATURE] - Added new setting option to define custom text which will be displayed at the bottom of the advertisement
* [FEATURE] - Added new setting option to decide whether or not to automatically pause/unpause the advertisement if the product is out of stock
* [FEATURE] - Added the ability to define the title separately than the default product title
* [FEATURE] - Added the ability to define the website URL and phone number
* [FEATURE] - Added the bulk action to pause/unpause the advertisement
* [FEATURE] - Added the bulk action to delete the advertisement
* [TWEAK] - Send notification to our Midlayer when the plugin state changes

## 1.3.3 - 2022-07-05

* [FIX] - Solve error `CRITICAL Uncaught TypeError: Cannot access offset of type string on string`
* [FIX] - Solve error `CRITICAL Uncaught TypeError: number_format(): Argument #1 ($num) must be of type float`
* [FIX] - For some shops with custom root folder the redirection after authorization fails

## 1.3.2 - 2021-11-03

* [FIX] - Authorization status goes to a wrong endpoint and fails
* [FIX] - Product status is set in `processing` when is not necessary

## 1.3.1 - 2021-10-12

* [FIX] - Error thrown while saving the settings
* [TWEAK] - Show a message when the authorization is manually cancelled

## 1.3.0 - 2021-09-10

* [FIX] - Some errors thrown by title length
* [FIX] - Not updating postcode value on each product if it was updated in general settings
* [FIX] - Global settings are not saved as product meta when bulk action is applied
* [FEATURE] - Added support to publish variable products

## 1.2.1 - 2021-04-12

* [IMPROVEMENT] - Rebuilt license management and the logic of receiving updates

## 1.2.0 - 2021-01-14

* [FEATURE] - Added a new section called "Category Mapping" in the settings for connecting WooCommece categories with marktplaats.nl categories
* [FEATURE] - Added new fields in the settings to allow defining default values for: Price Type, Salutation, Seller name and Shipping Type
* [FEATURE] - Added the ability to publish/update products in bulk
* [TWEAK] - Added a new column in the products list page to display the status of the product on marktplaats.nl

## 1.1.0 - 2020-09-03

* [FIX] - Fixed missing title/description length limits
* [FIX] - Fixed JS scripts issue in Wordpress 5.5
* [TWEAK] - Rearranged setting sections for a better view
* [TWEAK] - Settings are now accessible even if the license is inactive

## 1.0.4 - 2020-05-13

* [FIX] - Exclude particular data when a product is duplicated to avoid conflicts
* [FIX] - Added a fallback when user is redirected back to admin for shops which have a custom admin path
* [TWEAK] - Improved the error messages when plugin cannot connect with our midlayer

## 1.0.3 - 2019-11-26

* [FIX] - Fixed selecting category issue

## 1.0.2 - 2019-10-31

* [FIX] - Client Id and Client Secret are no longer required
* [TWEAK] - Added length validation for title and description of the product

## 1.0.1 - 2019-10-09

* [FIX] - Fixed license activation issue
* [Tweak] - Adjust API calls to WooCommerce to avoid getting errors for certain servers configuration

## 1.0.0 - 2019-09-13

* This is the first release, yeey!