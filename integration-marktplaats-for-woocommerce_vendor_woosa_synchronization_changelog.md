## 2.4.1 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants
* Use `Util::get_svg_icon()` method instead of the local one

## 2.4.0 - 2025-01-20

### Added

* New setting option to define import order frequency

## 2.3.2 - 2024-12-04

### Changed

* Improve shipping field texts
* New methods: `Module_Synchronization::is_new_product_sync_disabled()`, `Module_Synchronization::is_new_product_sync_enabled()`, `Module_Synchronization::is_product_price_sync_disabled()`
* Define conditional field logic

## 2.3.1 - 2024-10-31

### Fixed

* In some cases, the second argument in the callback function for the `woocommerce_email_enabled_{$id}` hook may be null

## 2.3.0 - 2024-10-15

### Added

* Define `reference`, `ean`, `condition` fields as default

## 2.2.0 - 2024-09-18

### Added

* New hooks: `\module\synchronization\settings_tab_name`, `\module\synchronization\settings_tab_description`

### Changed

* Use the new CI file
* General adjustments
* Prefix all hooks with `\module\` word
* Re-order the setting fields

## 2.1.0 - 2023-12-05

### Changed

* Adjust the output to use collapsible sections instead of tabs

## 2.0.0 - 2023-09-14

### Changed

* Modify the way how the output is insterted for the Settigns module v2