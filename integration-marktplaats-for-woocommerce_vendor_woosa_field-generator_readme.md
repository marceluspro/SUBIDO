## Introduction

This module gives the ability to generate the HTML of input fields from an array.

## How to use

Example of how to generate input fields:

```php

$fields = [
   [
      'id'       => 'title',
      'name'     => 'Title',
      'type'     => 'text',
      'required' => 0,
      'custom_attributes' => [],
   ],
   [
      'id'       => 'description',
      'name'     => 'Description',
      'type'     => 'editor',
      'required' => 0,
      'custom_attributes' => [],
   ],
   [
      'id'       => 'height',
      'name'     => 'Height',
      'type'     => 'number',
      'required' => 0,
      'custom_attributes' => [],
   ],
   [
      'id'    => 'use_wc_price',
      'name'  => __('Use WooCommerce price', 'woosa-bol'),
      'type'  => 'use_wc_price',
      'value' => '',
      'price_addition' => ''
      'custom_attributes' => []
   ],
   [
      'id'    => 'bundle_discounts',
      'name'  => 'Bundle price discounts',
      'type'  => 'bundle_discounts',
      'value' => '',
      'custom_attributes' => []
   ],
   [
      'id'    => 'enable_new_func',
      'name'  => 'Enable new functionality',
      'type'  => 'toggle',
   ],
   [
      'id'    => 'new_func_setting',
      'name'  => 'Setting for the new functionality',
      'type'  => 'toggle',
      'show_if' => 'enable_new_func'//or an array with the id and value of the parent field, like: ['id' => 'enable_new_func', 'value' => 'yes']
   ],
];

//with no context
$mfg = new Module_Field_Generator;
$mfg->set_fields($fields);
$mfg->render();

//with context
$mfg = new Module_Field_Generator;
$mfg->set_fields($fields, 'my_context_here');
$mfg->render();

//the context is useful when filtering the fields, having a context will help you to filter the fields only for that context and not for all
```
