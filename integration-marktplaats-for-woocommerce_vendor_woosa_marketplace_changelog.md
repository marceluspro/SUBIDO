## 2.0.0 - 2025-09-22

### Added

* New method `Module_Marketplace_Product::get_product_image_urls()` to retrieve product image URLs

### Changed

* Upgrade `Core` module to `v3.0.0`

## 1.6.3 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants
* Do not use the property `meta_query` directly in `wc_get_orders()` but only via hook

## 1.6.2 - 2025-07-25

### Changed

* Do not disable action `import_order` when the setting option is off
* Exclude sync stock for unimported orders that are shipped by marketplace
* Remove forcing the stock management to be enabled with stock value `99`

## 1.6.1 - 2025-07-01

### Fixed

* Call to undefined method `Module_Order_Details_Hook::hide_item_meta`
* The action `import_order` is still active even the plugin is not authorized

## 1.6.0 - 2025-02-27

### Added

* New methods: `Module_Marketplace_Action_Bulker::get_list()`, `Module_Marketplace_Action_Bulker::get_action()`
* New hook: `\module\marketplace\action_bulker\list`

### Changed

* The method `Module_Marketplace::process_product_bulk_action()` has been changed and moved to `Module_Marketplace_Action_Bulker::run_for_product()`

## 1.5.4 - 2025-01-27

### Fixed

* Hide our meta keys from being displayed on the order invoice and packing slips

## 1.5.3 - 2025-01-20

### Changed

* Allow dynamic import order recurrence

## 1.5.2 - 2024-12-04

### Changed

* Set a flag when a bulk action is applied

## 1.5.1 - 2024-10-22

### Changed

* Use a filter hook to define the list of accounts instead of method `Module_Multiple_Account::get_accounts()`
* Add Resource usage module as dependency

## 1.5.0 - 2024-10-17

### Added

* Define the `import_order` action

### Changed

* Remove the metabox that dislay cancelled order items

## 1.4.0 - 2024-09-18

### Added

* Support for multiple accounts
* Add bulk action to pause or unpause the product
* Enable product stock management and set quantity `99`

### Changed

* Do not create task for `variable` product but only for its variations

## 1.3.0 - 2024-04-26

### Changed

* Upgrade module Logger to `v3.0.0`

## 1.2.0 - 2024-02-28

### Added

* Display a text which explains how Category Mapping works

## 1.1.0 - 2024-02-14

### Added

* Install `Heartbeat` module v4 which automates the cronjob process
* Install `Order-Column-Status` module for displaying order status
* Display cancelled order items (if available from service)
* Require WooCommerce as plugin dependency