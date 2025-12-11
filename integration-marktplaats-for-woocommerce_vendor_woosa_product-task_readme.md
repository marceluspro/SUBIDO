## Introduction

This module is responsible to process the tasks for creating, updating or deleting products.

## Dependency

* [Abstract](https://gitlab.com/woosa/wp-plugin-modules/abstract)
* [Task](https://gitlab.com/woosa/wp-plugin-modules/task)
* [Term](https://gitlab.com/woosa/wp-plugin-modules/term)


## Payload structure

This is the structure that is expecting by the module to be in the task payload:

```php
$payload = [
   'id'                => 0,
   'type'              => 'simple', //`variation`, `variable`
   'name'              => 'Dummy title',
   'status'            => 'publish',
   'description'       => 'Long and with HTML description',
   'short_description' => 'Short description',
   'parent_id'         => 0, //the shop parent id
   'meta_data'         => [
      '{prefix}_sku'        => '34343RR',
      '{prefix}_stock'      => 11,
      '{prefix}_ean'        => '3434343454545',
      '{prefix}_mkt_price'  => 21,
      '{prefix}_b2b_price'  => 19,
      '{prefix}_rrp_price'  => 22,
      '{prefix}_parent_id'  => 3232, //the service parent id
      '{prefix}_weight'     => 13,
      '{prefix}_vat'        => 21,
      '{prefix}_backorder'  => false,
      '{prefix}_categories' => [
         [
            'id'        => 123,
            'parent_id' => 0,
            'name'      => 'Furniture',
         ],
      ],
      '{prefix}_attributes' => [
         [
            'name'               => 'Brand',
            'value'              => ['sony', 'apple'],
            'used_for_variation' => false
         ],
      ],
      '{prefix}_dimensions' => [
         'length' => 43,
         'width'  => null,
         'height' => 12
      ],
      '{prefix}_images' => [
         'https://example.com/wp-content/uploads/2017/03/T_1.jpg',
         'https://example.com/wp-content/uploads/2017/03/T_2.jpg',
      ]
   ]
]
```