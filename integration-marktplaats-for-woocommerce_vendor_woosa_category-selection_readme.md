## Introduction

This module gives the ability o display a UI for selecting a multi-level category.

## Installation

* Run composer `require woosa/category-mapping:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/category-mapping": "<version>"` directly in `composer.json` and then run `npm start`


## Usage

Example of how to display the selection on any pages:

```php
$source = 'shop'; //the source of the items - supports `shop` and `service`
$level  = 'leaf'; //`leaf` means until to the last sub-category and `tree` is for an entire level of categories

Module_Category_Selection::render($source, $level)
```

Example of how to display the selection on a WooCommerce product page (this requires [Meta](https://gitlab.com/woosa/wp-plugin-modules/Mmeta) module):

```php
$source     = 'service'; //the source of the items - supports `shop` and `service`
$level      = 'tree'; //`leaf` means until to the last sub-category and `tree` is for an entire level of categories
$product_id = 123;
$meta       = new Module_Meta($product_id); //instance

Module_Category_Selection::render_on_product($source, $level, $meta);
```