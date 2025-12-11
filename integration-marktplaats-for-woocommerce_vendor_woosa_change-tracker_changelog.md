## 2.3.0 - 2024-12-04

### Added

* New hook: `\module\change_tracker\created_product\enable`

### Fixed

* Task is created for updated product even if the product it's not linked

## 2.2.1 - 2024-10-17

### Fixed

* The creation of product task is not triggered when a custom meta is updated

## 2.2.0 - 2024-09-18

### Changed

* Use Product Task util method to create tasks
* Prefix all hooks with `module` word
* Tasks are created only for the allowed product types

## 2.1.0 - 2024-02-13

### Added

* Install `Meta` module v2

### Changed

* The tasks are not created by default anymore, you have to enable (via hook) what you need to use

## 2.0.0 - 2024-01-29

### Changed

* Remove `Product Push` and `Order Push` module dependencies

## 1.0.1 - 2023-09-19

### Changed

* The task action `delete_product` has been changed to `delete_or_trash_product`