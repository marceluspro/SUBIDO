## 3.2.3 - 2025-10-02

### Fixed

* When WP object cache is enabled the actions are always displayed as `queue...`

## 3.2.2 - 2025-01-30

### Changed

* Time greater then 24 hours will be displayed as days

## 3.2.1 - 2024-11-07

### Changed

* Enable the tool `Allow long-running requests`

## 3.2.0 - 2024-01-25

### Deprecated

* The hook `PREFIX . '\heartbeat\perform'` is deprecated and is replaced by `woosa\heartbeat\perform`

## 3.1.0 - 2023-12-06

### Added

* The ability to set status for the actions

### Fixed

* In some cases there are multiple actions set as active

## 3.0.0 - 2023-09-14

### Add

* Let module to hook on Heartbeat action to be performed
* Added task as second parameter of the filter `\worker\delay_next_process`
* Save the last processed action in case it stopped before finishing the entire action list, in the next run cycle it will process from there
* Use AJAX requests to calculate the total action tasks

### Changed

* Scheduling a recurring action is now conditioned by flag. In this way the action is scheduled only after the action finishes all the processes
* The class `Module_Worker_Action` is converted in a class with instance instead of a static one
* Backward compatibility for `next_process_at` because in the end the `retry_after` will replace `next_process_at`
* Let module `Task` to calculate `next_process_at`

## 2.0.0 - 2022-03-16

### Add

* Display the list of actions as a separate section of the settings

### Change

* Loop through tasks instead of action payloads

## 1.3.0 - 2022-10-11

* [CHANGE] - Deprecate all the methods related to action properties

## 1.2.0 - 2022-06-21

* [TWEAK] - Do not lock the process anymore since it's done in the Heartbeat module

## 1.1.0 - 2022-05-20

* [IMPROVEMENT] - New methods to work with the action props
* [TWEAK] - Check if time and memory limit are exceeded before running an item
* [TWEAK] - New hooks added: `\worker\action\get_status`, `\worker\action\get_payload`, `\worker\action\set_payload`