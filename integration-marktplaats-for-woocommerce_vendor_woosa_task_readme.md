## Introduction

This module gives the ability to have a dedicated DB table called `{prefix}_tasks` where we can insert any type of tasks in order to process different actions of the plugin.

## Dependency

## Installation (via composer)

* **For plugins developed with our [boilerplate](https://gitlab.com/woosa/dev-tools/wp-plugin-starter):**

  * Either run `composer require woosa/task:version` or add `"woosa/task": "version"` in the `composer.json` file then run `npm start`

* **For plugins developed without our boilerplate:**

  * replace all occurences of `_wsa_namespace_` with your unique namespace
  * the following constants should be defined in your plugin:
    * `define(__NAMESPACE__ . '\PREFIX', 'your_prefix_here');`

## Usage

How to retrieve a list of tasks based on multiple `ids` and `actions`, `sources`, `targets` and `resource_ids`:

```php
Module_Task::get_entries([
   'ids'          => [11,344],
   'actions'      => ['create_product', 'update_product'],
   'sources'      => ['shop', 'shop'],
   'targets'      => ['service', 'shop'],
   'resource_ids' => [1234, 56565],
   'limit'        => [0, 1000],
]);
```

How to update or create a list of tasks:

```php
Module_Task::update_entries([
   [
      'action'      => 'test_action',
      'source'      => 'shop',
      'target'      => 'service',
      'payload'     => '',
      'resource_id' => '123',
      'priority'    => '10',
   ],
   [
      'action'      => 'another_test_action',
      'source'      => 'service',
      'target'      => 'shop',
      'payload'     => [
         'arg_1' => 'some cool value'
      ],
      'resource_id' => '5454',
      'priority'    => '10',
   ],
]);
```

How to delete a list of tasks:

```php
Module_Task::get_entries([
   'ids'          => [11,344],
   'actions'      => ['create_product', 'update_product'],
   'sources'      => ['shop', 'shop'],
   'targets'      => ['service', 'shop'],
   'resource_ids' => [1234, 56565],
]);
```