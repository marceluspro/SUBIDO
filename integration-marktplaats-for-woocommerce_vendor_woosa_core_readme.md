## Introduction

This is the most important module because it loads and initiates the rest of the modules and comes with the following:

* It fires hooks for each state: `activated`, `deactivated`, `upgraded` and `uninstalled`
* Used in conjunction with [Module Dependency](#module-dependency) it performs a check for the dependencies before initiating the plugin
* It has util CSS classes - check the file [module-core.css](https://gitlab.com/woosa/wp-plugin-modules/core/-/blob/master/assets/css/module-core.css)
* It initiates util JS scripts - check the file [module-core.js](https://gitlab.com/woosa/wp-plugin-modules/core/-/blob/master/assets/js/module-core.js)
* It gives the ability to insert action links to the plugin (e.g. Settings, Logs, Doc, etc) - check method `Module_Core_Hook::init_plugin_action_links()`
* It loads the translation based on the given text domain
* It sets an instance of the website including the website `url`, `domain` and `version`

## Dependency

* [Interface](https://gitlab.com/woosa/wp-plugin-modules/interface)
* [Option](https://gitlab.com/woosa/wp-plugin-modules/option)
* [Util](https://gitlab.com/woosa/wp-plugin-modules/util)

## Installation

* Run composer `require woosa/core:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/core": "<version>"` directly in `composer.json` and then run `npm start`


## Usage

To initialize the module in your code just add:

```php
Module_Core_Hook::init();
```