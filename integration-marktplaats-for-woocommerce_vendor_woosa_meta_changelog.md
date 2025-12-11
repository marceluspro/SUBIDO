## 2.4.1 - 2025-06-11

### Changed

* Add extra checks to avoid the warnings

## 2.4.0 - 2025-05-06

### Changed

* Use a dedicated status for excluded product account

## 2.3.0 - 2025-01-27

### Added

* Get field source value for the option `Global Unique ID`

## 2.2.0 - 2024-09-18

### Added

* Support for multiple accounts
* New case for method `Module_Meta::get_value_by_source()` to return product id

### Changed

* Non-string errors are displayed as JSON encoded
* Retrieve product variations via `wc_get_products()` function

## 2.1.0 - 2024-07-29

### Changed

* General fixes and improvements for the HPOS support

## 2.0.0 - 2024-02-13

### Added

* Added support for WC HPOS

## 1.0.5 - 2022-09-20

### Fixed

* The method `Module_Meta::get_status()` returns the value `in_progress` in case the status is in progress even if there are error messages set