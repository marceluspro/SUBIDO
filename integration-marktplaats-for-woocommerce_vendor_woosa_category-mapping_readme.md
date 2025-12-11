## Introduction

This module adds a section in the plugin settings called `Category Mapping` which gives the ability to connect a service category with a shop category and comes with the following:

* The service category id is stored on the shop category in the term metadata `PREFIX . '_mapped_category_id'`
* It supports multiple service categories to be connected with one shop category

## Dependency

* [Category Selection](https://gitlab.com/woosa/wp-plugin-modules/category-selection)

## Installation

* Run composer `require woosa/category-mapping:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/category-mapping": "<version>"` directly in `composer.json` and then run `npm start`

## Usage

Example of how to show config button only for the categories that has some configuration:

```php
add_filter(PREFIX . '\category-mapping\category-config\display-configure-button', 'enable_config_button');

/**
 * Whether or not to enable the config button.
 *
 * @param bool $has_category_config Default false
 * @param int|string $category_id
 * @return bool
 */
function enable_config_button($has_category_config, $category_id){

   if (!empty($category_id)) {

      //do your stuff
      $has_category_config = true;
   }

  return $has_category_config;
}
```

Example of how to get the category to display in the config popup:

```php
add_filter(PREFIX . '\category-mapping\category-config\get-category', 'get_category');

/**
 * Get the category
 *
 * @param array $category
 * @param int $category_id
 * @return array
 */
function get_category($category, $category_id) {

   if (!empty($category_id)) {

      //do your stuff
      $category = [
         'name' => 'Category_name',
         'fields' => [
            'full_name' => [
               'name'     => 'Full name',
               'required' => true,
               'values'   => ['First name', 'Last name'],
            ],
            'type' => [
               'name'     => 'Type',
               'required' => true,
               'values'   => ['box', 'envelope'],
            ],
         ],
      ]
   }

   return $category;
}
```