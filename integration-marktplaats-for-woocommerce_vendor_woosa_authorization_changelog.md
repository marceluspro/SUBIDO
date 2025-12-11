## 3.4.0 - 2025-09-01

### Added

* The ability to use a template file for authorization fields

### Changed

* Use `Module_Core::config()` instead of constants

### Fixed

* In case there is a `redirect_url` property in the authorization result, the user is not redirected there

## 3.3.1 - 2025-07-01

### Changed

* Hide the box section which handles the order processing when the plugin is unauthorized
* Do not not hide the table columns when the plugin is unauthorized

### Fixed

* The authorization error message is not displayed anymore

## 3.3.0 - 2024-01-16

### Added

* Group authorization fields per context to allow setting fields to be always editable

## 3.2.0 - 2024-11-29

### Added

* New hook: `\module\authorization\before_process_authorization`
* New methods: `Module_Authorization::set_error()`, `Module_Authorization::delete_error()`, `Module_Authorization::get_formatted_error()`, `Module_Authorization::delete_error()`

## 3.1.1 - 2024-10-09

### Fixed

* Update old doc links

## 3.1.0 - 2024-09-18

### Added

* New hooks: `\module\authorization\settings_tab_name`, `\module\authorization\settings_tab_description`

### Changed

* Update Synchronization module hook names
* Remove deprecated `dropsync` hooks

## 3.0.0 - 2023-09-14

### Changed

* Modify the way how the output is insterted for the Settigns module v2

## 2.1.3 - 2023-04-19

### Changed

* A description was added beneath the authorization status which inclues a link to our wiki article

## 2.1.2 - 2022-10-26

* [TWEAK] - Add module interface as dependency

## 2.1.1 - 2022-06-07

* [TWEAK] - Revoke the authorization if the remote request gets 401

## 2.1.0 - 2022-05-03

* [IMPROVEMENT] - New UI for the output section which comes with the status and the submit button, the fields must be added by the plugin via the hook
* [TWEAK] - Implement the new method added ot the settings interface

## 2.0.2 - 2022-03-18

* [FIX] - Save extra fields before init the class and let the env to be defined based on the setting option
* [TWEAK] - Add fallback to `testmode` option