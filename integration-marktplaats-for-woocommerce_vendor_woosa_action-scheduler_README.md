## Introduction

This module gives the ability to schedule actions (tasks) via https://actionscheduler.org/ and comes with the following:

* It schedules single actions with the ability to merge the arguments of the same actions instead to create multiple actions.
* It schedules recurring actions
* It schedules async actions

## Dependency

* [Action Scheduler](https://github.com/woocommerce/action-scheduler) package

## Installation (via composer)

* In case the plugin is developed by using our [boilerplate](https://gitlab.com/woosa/dev-tools/wp-plugin-starter) you only have to either run `composer require woosa/action-scheduler:version` or add `"woosa/action-scheduler": "version"` in the `composer.json` of the plugin then run `npm start`
* In case the plugin is **NOT** developed by using our [boilerplate](https://gitlab.com/woosa/dev-tools/wp-plugin-starter) then you have to:
  * run `composer require woosa/action-scheduler:version`
  * include the `index.php` file from the root in your plugin logic
  * open the `index.php` file and below the line `defined( 'ABSPATH' ) || exit;` define the following constants:
    *  `define(__NAMESPACE__ . '\PREFIX', '');` - this represents your unique prefix
  * replace all occurences of `_wsa_namespace_` with your unique namespace

## Usage

Example of how to define a single action:

```php
$as = new Module_Action_Scheduler();
$as->set_group('my-custom-group');
$as->set_hook('my_hook_name');
$as->set_callback([__CLASS__, 'my_callback_function']);
$as->set_args(['arg_1', 'arg_2']);
$as->set_single(); //the action type should be always after all other methods have been set
$as->save();
```

Example of how to define a recurring action:

```php
$as = new Module_Action_Scheduler();
$as->set_group('my-custom-group');
$as->set_hook('my_hook_name');
$as->set_callback([__CLASS__, 'my_callback_function']);
$as->set_args(['arg_1', 'arg_2']);
$as->set_recurring(\MINUTE_IN_SECONDS);  //the action type should be always after all other methods have been set
$as->save();
```

Example of how to unschedule all occurrences of an action:

```php
$as = new Module_Action_Scheduler();
$as->set_group('my-custom-group');//optional
$as->set_hook('my_hook_name');
$as->unschedule();
```

Example of how to unschedule all actions:

```php
Module_Action_Scheduler::unschedule_actions();
```