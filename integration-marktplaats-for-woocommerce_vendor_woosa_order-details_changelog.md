## 1.5.4 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants

## 1.5.3 - 2025-07-29

### Fixed

* The order items are not available for being processed due to having status `need_to_be_sent`

## 1.5.2 - 2025-06-12

### Fixed

* Exclude order item formatted meta - this is displayed in order email notification

## 1.5.1 - 2025-05-28

### Fixed

* Union type in method argument is supported only from PHP 8

## 1.5.0 - 2025-04-30

### Added

* Wild (*) support for extracting the tracking number from meta value

## 1.4.1 - 2025-01-27

### Fixed

* Hide meta key `_shipping_label_offer_id`

## 1.4.0 - 2024-12-04

### Added

* Support for retrieve tracking code from meta with array/object values

## 1.3.1 - 2024-11-06

### Fixed

* The argument of the `Module_Order_Details::is_fulfiled_by_marketplace()` is strictly set to string

## 1.3.0 - 2024-10-17

### Changed

* No need to use class `Module_Order_Details_Item` anymore
* General adjustments to make the module more extendable

## 1.2.0 - 2024-09-18

### Changed

* Update all template absolute and relative paths
* New meta keys added to be hidden for order items
* Change box class based on status

## 1.1.0 - 2024-02-13

### Added

* Install `Meta` module v2