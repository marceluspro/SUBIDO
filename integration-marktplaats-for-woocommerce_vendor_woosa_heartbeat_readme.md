## Introduction

This module schedules an action which runs every 1 minute.

It also gives the ability to run an external cron job on the endpoint: `/wp-json/woosa-heartbeat/perform`.

## Dependency

* [Action Scheduler](https://gitlab.com/woosa/wp-plugin-modules/action-scheduler)

## Installation

* Run composer `require woosa/heartbeat:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/heartbeat": "<version>"` directly in `composer.json` and then run `npm start`

## Usage

How to run your code via Heartbeat:

```php
add_action('woosa\heartbeat\perform', 'my_perform');

function my_perform($list){

  //run your code
}
```