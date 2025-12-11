## 1.1.1 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants

## 1.1.0 - 2024-09-18

### Added

* New hook: `PREFIX . '\module\order_column_status\column_output'`

### Changed

* In case the column is hidden then will not process the output anymore
* Display the status via AJAX call