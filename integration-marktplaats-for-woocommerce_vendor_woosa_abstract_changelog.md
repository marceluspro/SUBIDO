## 1.2.1 - 2025-09-11

### Changed

* Use `$data` as first argument and `$account` as second for `Module_Abstract_Product_Task_Marketplace`. This helps for cases where `$account` is not necessary.

## 1.2.0 - 2025-09-01

### Added

* New abstract dedicated to API client

## 1.1.3 - 2025-07-24

### Changed

* Check stock management and set `99` as stock amount

## 1.1.2 - 2025-06-04

### Fixed

* The description for variation products is not added or updated

## 1.1.1 - 2024-11-20

### Fixed

* The method `Module_Abstract_Entity_Post::get_id_from_data()` does not check the post existance

## 1.1.0 - 2024-10-15

### Added

* New abstract for processing product task for marketplace

## 1.0.1 - 2023-07-27

### Changed

* Remove unnecessary parameter `$this->data` from the hooks, the instance of the class should be enough
* On update post it checks if the array keys exist and only then it will define the columns
* Adjust method `Module_Abstract_Entity_Post::get_id_from_data()` to use only the metadata which is not empty

### Fixed

* The method `Module_Abstract_Entity_Post::trash()` is deleteing the post completely instead to move it to trash
* The SQL statement `INSERT IGNORE INTO...` for creating metadata can cause the error `Deadlock found when trying to get lock; try restarting transaction` therefor let's use `add_post_meta()` instead