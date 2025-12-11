## 3.0.1 - 2025-10-29

### Changed

* Define multiple colors in the color scheme of settings page

## 3.0.0 - 2025-09-04

### Changed

* Upgrade `Interface` module to `v2.0.0`
* In case `select2` is not enqueued then will be used local script

## 2.2.0 - 2025-09-01

### Added

* New method called `Module_Core::config()`

## 2.1.8 - 2025-05-05

### Fixed

* When a product is duplicated, its meta keys are also copied

## 2.1.7 - 2025-02-14

### Fixed

* Set missing TB popup height value
* Fix product table column width

## 2.1.6 - 2025-01-29

### Fixed

* The child field of a conditional field is hidden when it's selected itself

## 2.1.5 - 2024-12-24

### Fixed

* Conditional field does not work properly since last updates

## 2.1.4 - 2024-12-13

### Fxied

* Changing the hook `plugins_loaded` with `init` for the callback `'run'` causes problems for some plugins which need to hook earlier than `init` (for example Adyen Hosted Checkout)

## 2.1.3 - 2024-12-10

### Fixed

* Function `_load_textdomain_just_in_time` was called incorrectly

## 2.1.2 - 2024-12-04

### Changed

* Allow conditional field logic for select field type

## 2.1.1 - 2024-11-04

### Changed

* Move the hook that adds rewrite rule to prevent abort and timeout to the Tools module

## 2.1.0 - 2024-10-28

### Added

* New rewrite rule to prevent abort and timeout

## 2.0.1 - 2024-05-16

### Changed

* Replace deprecated `Util` methods for logs
* Added `Logger` as dependency

### Fixed

* Warning at plugin update process

## 2.0.0 - 2023-01-30

### Added

* Define the compatibility for HPOS
* Added as dependency some generic modules such as: `abstract`, `request`, `third-party`
* SweetAlert JS script has been integrated

## 1.5.0 - 2023-09-14

### Added

* Bootstrap grid CSS
* jQuery blockUI plugin
* Plugin version in the stored instance

### Removed

* jQuery sortable is not used anymore

## 1.4.0 - 2023-06-27

### Add

* Added new CSS styles for display nice status

## 1.3.0 - 2022-03-16

### Add

* Added CSS styles for the box headline
* Added support for tablist to work with URL hash
* Added Dependency module as dependency


## 1.2.3 - 2022-10-26

* [TWEAK] - Add module interface as dependency

## 1.2.2 - 2022-07-18

* [FIX] - Solve the error `$(...).sortable() is not a function`
* [TWEAK] - Correct the size of the TB and add a resize method available to the public object
* [TWEAK] - Add block for tickbox as well
* [TWEAK] - Trigger event on ajax save button action

## 1.2.1 - 2022-06-08

* [TWEAK] - Initiate the sortable JS

## 1.2.0 - 2022-05-16

* [TWEAK] - Add margin and padding until 30
* [TWEAK] - Add new classes for success and error

## 1.1.1 - 2022-03-15

* [FIX] - Wrong padding value for .p-20
* [TWEAK] - Move in Core module the CSS style for tablist

## 1.1.0 - 2022-03-01

* [TWEAK] - Move in Core module the JS logic for tablist