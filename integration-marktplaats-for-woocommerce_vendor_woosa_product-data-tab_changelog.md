## 1.3.1 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants

## 1.3.0 - 2024-09-18

### Added

* Support for multiple accounts
* New hooks: `\module\product_data_tab\panel\tab_nav`, `\module\product_data_tab\panel\tab_content`

### Changed

* Adjustments to the way of how errors are handled
* All existing hooks have been renamed to include `module` word
* Retrieve product variations via `wc_get_products()` function

## 1.2.0 - 2024-02-13

### Added

* Install `Meta` module v2

## 1.1.1 - 2022-12-14

### Fixed

* Saving the meta fields fails since the GET parameters are not available when the data is submitted

## 1.1.0 - 2022-09-22

### Added

* New hook `\product_data_tab\initiate` to initiate/disable the logic of the module
* New hooks to let 3rd-party to filter the output of the data panel
* Add support for variable product