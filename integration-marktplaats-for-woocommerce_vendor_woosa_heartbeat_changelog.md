## 4.1.0 - 2024-08-08

### Changed

* Instead to check whether or not the Heartbeat is enabled/disabled via option hook, now we have a new general hook `woosa\module\heartbeat\toggle_cron_job_status` which should be used to control the Heartbeat action enable/disable
* Enable/disable the heartbeat at plugin activation/deactivation
* Improve the section output for better clarity

## 4.0.0 - 2024-01-25

### Changed

* Add a generic endpoint instead of a specific per each plugin. NOTE: This requires Midlayer plugin to add/remove the cron job in our cron ui.

## 3.0.0 - 2023-09-14

### Changed

* Modify the way how the output is insterted for the Settigns module v2
* The method `perform()` uses now the `do_action()` instead to run `Worker` module

## 2.0.1 - 2023-04-19

### Changed

* The description of `Heartbeat` setting option was adjusted to include our wiki article

## 2.0.0 - 2023-03-16

* [CHANGE] - Move action list logic to Woker module.

## 1.3.2 - 2022-11-18

* [TWEAK] - Add the ability to check if the endpoint is cached or not by using the query parameter `check-cache`
* [TWEAK] - Show an info message if there are no actions yet
* [TWEAK] - Make `status` property optional

## 1.3.1 - 2022-11-10

* [TWEAK] - Add module interface as dependency

## 1.3.0 - 2022-10-11

* [CHANGE] - Migrate the methods related to action properties from `Module_Workder_Action` for a better logic

## 1.2.3 - 2022-09-12

* [TWEAK] - Add nocache header to perform endpoint response

## 1.2.2 - 2022-08-04

* [FIX] - Use default value `no` when retrieve the setting option `use_external_cronjob`.

## 1.2.1 - 2022-07-25

* [FIX] - The perform scheduled action is not removed if the setting option of using an external cron job is added for the first time to database
* [FIX] - The perform scheduled action is created back when the plugin is activated/authorized even if the option of using an external cron job is enabled

## 1.2.0 - 2022-06-21

* [FEATURE] - Add a REST API endpoint to perform the actions by an external request
* [FEATURE] - New setting option to switch on/off the default scheduled actions processor

## 1.1.1 - 2022-05-18

* [TWEAK] - Let the module to render the list of actions
* [TWEAK] - Change `name` property to `id`

## 1.1.0 - 2022-02-28

* [IMPROVEMENT] - Reschedule the action when is canncelled or failed