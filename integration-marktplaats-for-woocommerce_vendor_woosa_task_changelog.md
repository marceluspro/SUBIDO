## 1.2.0 - 2025-05-14

### Changed

* Encode the payload as JSON instead of serialization

## 1.1.1 - 2024-09-18

### Fixed

* Deleting old entries cause error in case the DB table does not exist

## 1.1.0 - 2023-12-06

### Added

* New methods to count and delete the action tasks
* New hooks to allow adjusting the limits for different query types

### Changed

* Cleanup of old tasks has been changed from 10 days to 30 days

### Fixed

* Calculation of next process is not working properly
* Cleaning old tasks is not working properly

## 1.0.3 - 2023-11-01

### Fixed

* Change `255` to `191` for `VARCHAR` table columns to avoid the error `Specified key was too long; max key length is 767 bytes`

## 1.0.2 - 2023-09-14

## Changed

* Retrieve tasks sorted by priority
* Check whether the `next_process_at` is a valid date format
* Change `168` hours to `10` days
* Update the class according to the interface

## 1.0.1 - 2023-07-17

### Fixed

* Removed the code which checks twice for `resource_ids`