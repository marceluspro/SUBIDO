## 1.3.3 - 2025-10-23

### Fixed

* Missing validation to prevent stock from being reduced multiple times by the same order when "Import orders" setting is disabled
* Shop tax may not be properly excluded from product prices depending on the "Calculate tax based on" setting

## 1.3.2 - 2025-10-02

### Fixed

* Restore the change ~~Do not use the property `meta_query` directly in `wc_get_orders()` but only via hook~~
* Fix variable reference warning

## 1.3.1 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants
* Do not use the property `meta_query` directly in `wc_get_orders()` but only via hook

## 1.3.0 - 2025-07-25

### Added

* Update product stock even when the import order is disabled
* New hook: `PREFIX . '\module\order_task\allow_status_update'`

### Fixed

* Order status is set no matter what current order status is

## 1.2.5 - 2025-06-12

### Fixed

* The order total is `0.00` in the order email notifiation

## 1.2.4 - 2025-05-14

### Changed

* Use the dedicated method to decode task payload

## 1.2.3 - 2025-04-16

### Fixed

* Identifying products does not take into account the setting field EAN source

## 1.2.2 - 2024-11-20

### Fixed

* Shop tax is not calculated on order items
* Product is not correctly identified by order meta

## 1.2.1 - 2024-10-17

### Changed

* Remove deletion of cancelled order items

## 1.2.0 - 2024-09-18

### Added

* New hook: `\module\order_task\get_id_from_data\sql`
* Support for `meta_query` when using `wc_get_orders()` function
* Flag on product to exclude stock synschronization for the account that order is imported from

### Changed

* The meta `_product_sku` and `_product_ean` have been changed to `_sku` and `_ean`
* The method `Module_Order_Task::get_product_id_by_meta()` is searchin for EAN as well

### Fixed

* Make sure price is float and quantity is integer
* General HPOS fixes

## 1.1.0 - 2023-10-26

### Changed

* The structure of action list array has been changed and now the method `Module_Order_Task::action_list()` has a new parameter