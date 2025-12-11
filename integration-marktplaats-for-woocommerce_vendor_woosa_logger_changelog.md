## 3.2.2 - 2025-09-01

### Changed

* Use `Module_Settings::get_page_slug()` method instead of constants

## 3.2.1 - 2025-08-06

### Fixed

* The warning `Cannot modify header information - headers already sent by [...]`
* The page is not kept after going back from a file view

## 3.2.0 - 2025-01-16

### Added

* New hook `\logger\get_file_dir`

## 3.1.2 - 2024-12-18

### Changed

* Include a hash string in the file name to enhance security and prevent unauthorized access

## 3.1.1 - 2024-12-10

### Fixed

* The log file can be accessed externally

### Changed

* Increase the logs per page from `10` to `20`

## 3.1.0 - 2024-04-26

### Added

* Remove logs older then 30 days

## 3.0.0 - 2024-03-20

### Added

* New logic for creating and displaying the logs

## 2.0.1 - 2023-12-06

### Fixed

* The pagination is not working due to old settings design

## 2.0.0 - 2023-09-26

### Changed

* Modify the way how the output is insterted for the Settigns module v2