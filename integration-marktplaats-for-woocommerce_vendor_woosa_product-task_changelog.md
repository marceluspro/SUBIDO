## 1.4.3 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants

## 1.4.2 - 2025-06-25

### Fixed

* Product types are not automatically created if they do not exist
* Variable products are not updated when their variations are updated

### Changed

* Add extra checks to avoid PHP warnings

## 1.4.1 - 2025-05-14

### Changed

* Use the dedicated method to decode task payload

### Fixed

* `CRITICAL Uncaught Error: Cannot use object of type WP_Error as array`

## 1.4.0 - 2025-02-27

### Added

* The action id as extra parameter for hooks: `\module\product_task\service_accounts`, `\module\product_task\create_task\exclude`, `\module\product_task\create_account_task\exclude`

## 1.3.2 - 2025-02-12

### Fixed

* Exclude invalid product attributes

## 1.3.1 - 2024-10-31

### Changed

* Check for mapped terms before create the term

## 1.3.0 - 2024-09-18

### Added

* New util method to create product task
* New hooks: `\module\product_task\create_task\exclude`, `\module\product_task\create_account_task\exclude`

### Fixed

* The method `Module_Product_Task_Util::process_price()` returns float value while it should return the value as it is

## 1.2.2 - 2024-08-15

### Changed

* The deprecated `delete_product` action has been removed

### Fixed

* Default attributes are not set for variable products
* The product is not published in case the price is `0`

## 1.2.1 - 2024-05-30

### Fixed

* Download images does not work for product variations

## 1.2.0 - 2023-12-06

### Changed

* Do not reschedule the tasks for actions `update_product_stock` and `update_product_price` when there are no tasks for action `create_or_update_product`
* For actions `update_product_stock` and `update_product_price` the reschedule time is calculated dynamically based on the amount of tasks

## 1.1.1 - 2023-10-24

### Fixed

* Check whether or not the category id is set in the payload
* Check if the product is valid WC product before trigger WC hooks

## 1.1.0 - 2023-09-14

### Added

* A new filter in the method `Module_Product_Task::process_images()` to disable/enable the process
* A new action called `delete_or_trash_product`
* A new action called `delete_shop_category`
* The ability to exclude unavailable products
* Reset category and attribute meta data when the product categories or attributes are changed
* Update term count before deleting terms

### Changed

* Define new action priorities
* Adjusted and renamed the hooks at price processing
* Check if price is greater then `0` instead of empty string

### Deprecated

* The action called `delete_product`

## 1.0.1 - 2023-05-10

### Changed

* Do not extract preserve offest stock value anymore since this can be done in the plugin via payload formatter logic if it's necessary