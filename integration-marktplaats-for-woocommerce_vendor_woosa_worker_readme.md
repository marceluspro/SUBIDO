## Introduction

This module iterates through a list of actions and, based on the action type, either runs the action’s callback directly or queries a list of tasks, allowing third-party scripts to process each task individually.

**Notes:**

* Only one instance runs at a time to ensure reliable processing.
* To enable automatic processing of the action list, install the [Heartbeat](https://gitlab.com/woosa/wp-plugin-modules/heartbeat) module.

## Dependency

* [Task](https://gitlab.com/woosa/wp-plugin-modules/task)

## Installation

* Run composer `require woosa/worker:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/worker": "<version>"` directly in `composer.json` and then run `npm start`

## Usage

How to define the action list:

```php
add_filter(PREFIX . '\worker\action_list', 'my_actions');

function my_actions($list){

   $list = array_merge($list, [
      //action with callback
      [
         'id'         => 'my_action_id',
         'priority'   => 10, //a lower value means a higher priority
         'callback'   => ['class_name', 'method'],
         'context'    => 'my_context', //useful to be able to filter the list of action
      ],
      //action with callback and recurrence
      [
         'id'         => 'my_action_id_2',
         'priority'   => 10,
         'callback'   => ['class_name', 'method'],
         'recurrence' => \HOUR_IN_SECONDS,
         'context'    => 'my_context',
      ],
      //action with tasks
      [
         'id'         => 'my_action_id_3',
         'priority'   => 10,
         'context'    => 'my_context',
      ],
   ]);

   return $list;
}
```

How to manually run the action list:

```php
$worker = new Module_Worker;
$worker->run();
```

How to automatically run the action list:

* install the [Heartbeat](https://gitlab.com/woosa/wp-plugin-modules/heartbeat) module.

Ho to process action's tasks:

```php
add_filter(PREFIX . '\worker\run\task', 'process_task', 10, 2);

function process_task($processed, array $task){

   //process the $task

   return $processed;
}
```