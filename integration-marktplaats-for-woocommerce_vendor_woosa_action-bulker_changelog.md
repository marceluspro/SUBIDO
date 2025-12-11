## 2.1.1 - 2025-02-27

### Changed

* Add support for action property `id`

## 2.1.0 - 2024-09-02

### Added

* Displaying action bulkers on default category and product category pages

## 2.0.0 - 2024-02-19

### Added

* Added support for WC HPOS

## 1.2.1 - 2023-10-25

### Fixed

* Add back a hook which was removed by mistake in the previous version

## 1.2.0 - 2022-07-05

### Add

* Add the ability to create task for the actions

## 1.1.2 - 2022-10-25

* [FIX] - Check and remove action if the post type does not match
* [TWEAK] - Add module interface as dependency

## 1.1.1 - 2022-03-23

* [FIX] - Do not pass the second argument to `call_user_func_array()` as key array, this might cause errors like `Uncaught Error: Unknown named parameter...` if it's not correctly implemented

## 1.1.0 - 2022-02-28

* [FEATURE] - Add the ability to run an action for each item individually