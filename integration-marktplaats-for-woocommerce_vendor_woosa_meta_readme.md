# Introduction

This module gives the ability to manipulate metadata of any post type. It comes with the following pre-installed features and methods:

* It has support for `WP_Post`, `WC_Product` and `WC_Order` instances
* It inserts WooCommerce webhook topic for update for product & order when meta is updated
* General methods:
   * `get()` - *retrieve a meta*
   * `set()` - *set a meta*
   * `delete()` - *remove a meta*
   * `is_checked()` - *check meta value if has values like: true/false or yes/no*
   * `is_empty()` - *check meta is empty*
   * `save()` - *save the changes applied on the object meta*
* Methods for `WP_Post`:
   * `get_post()` - *retrieve the instance of `WP_Post`*
   * `is_post_type()` - *check the post type*
   * `is_post_published()` - *check whether or not the post is published*
* Methods for `WC_Product` and `WC_Order`:
   * `get_order()` - *retrieve the instance of `WC_Order`*
   * `get_product()` - *retrieve the instance of `WC_Product`*
   * `is_product_type()` - *check the product type*
   * `is_published()` - *check whether or not a product was published or an order was submitted to the marketplace*
   * `get_status()` - *the status of the product/order on the marketplace*
   * `set_status()` - *set the status*
   * `delete_status()` - *delete the status*
   * `display_status()` - *shows the status*
   * `get_errors()` - *get the list of errors occurrs on the product/order*
   * `set_error()` - *set an error*
   * `delete_error()` - *delete specific error*
   * `delete_errors()` - *delete all errors*
   * `display_errors()` - *shows the errors*
   * `get_connected_category()` - *retrieves the service category connected with the product category*
   * `get_ian_value()` - *retrieves the product IAN code value based on the global settings (default, SKU, Attribute, Custom field)*

## Dependencies

* [Option](https://gitlab.com/woosa/wp-plugin-modules/option)
* [Util](https://gitlab.com/woosa/wp-plugin-modules/util)

## How to use

Example of how to set a meta on any post types:

```php
$post_id = 123;
$meta = new Module_Meta($post_id);
$meta->set('my_custom_meta', 'some value here');
$meta->set('my_2nd_custom_meta', 'some other value here');
$meta->save();
```

Example of how to get a default WooCommerce product meta:

```php
$proudct_id = 123;
$meta = new Module_Meta($proudct_id);
$price = $meta->get_product()->get_price();
$stock = $meta->get_product()->get_stock_quantity();
```