# Introduction

This module gives the ability to extend easily the table columns of any post types.

## Dependencies

* [Util](https://gitlab.com/woosa/wp-plugin-modules/util)

## Setup

* Installing via composer requires only to include the `index.php` file from root in your code
* Replace all occurences of `_wsa_namespace_` with your unique namespace

## How to use

Example of how to define a table column for WooCommerce products:

```php
add_filter(PREFIX . '\table_column\columns', 'my_custom_columns');

function my_custom_columns($items){

   $items[PREFIX . '_product_col'] = [
      'label'        => __('Cool Column', '_wsa_text_domain_'),
      'post_type'    => ['product'], //the post type where to add the column
      'after_column' => 'product_cat', // after which column to be insterted
      'callback'     => [__CLASS__, 'my_callback_function'],
   ];

   return $items;
}
```