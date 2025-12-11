## 1.3.0 - 2025-09-22

### Added

* New hook: `PREFIX . '\module\category_seletion\level'`

### Deprecated

* Hook: `PREFIX . '\category_selection\service_items'` use instead: `PREFIX . '\module\category_selection\service_items'`

## 1.2.1 - 2025-06-02

### Fixed

* Searching items parents fails due to infinite recursion

## 1.2.0 - 2025-04-22

### Changed

* Improve the search functionality to return results with full hierarchy for better UX

## 1.1.0 - 2025-02-27

### Added

* UI improvements and fixes according to Bol upload content feature

## 1.0.4 - 2022-10-26

### Changed

* Add module interface as dependency

## 1.0.3 - 2022-09-23

### Changed

* For `Module_Category_Selection::get_subitems()` make sure the type is the same when the variable is empty