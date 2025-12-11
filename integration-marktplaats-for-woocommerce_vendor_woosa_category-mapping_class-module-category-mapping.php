<?php
/**
 * Category Mapping
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Category_Mapping {


   /**
    * Displays the UI which allows the user to map datamodel category fields with shop fields.
    *
    * @param $term_id
    * @param $config_fields
    * @param $category_fields
    * @param boolean $required - if the field is or it is not required
    * @return void
    */
   public static function render_fields($term_id, $config_fields, $category_fields, $required) {

      $fields   = [];

      foreach($category_fields as $field) {

         if ($required && !$field['required']) {
            continue;
         }
         if (!$required && $field['required']) {
            continue;
         }

         if (empty($field['values'])) {

            if(empty($field['type'])){
               $field['type'] = 'data-mapper';
            }

            $field['config_fields']= $config_fields;
            $field['value']        = Util::array($config_fields)->get($field['id']);
            $field['source']       = Util::array($config_fields)->get($field['id']);
            $field['source_value'] = Util::array($config_fields)->get($field['id'] . '__' . Util::array($config_fields)->get($field['id']) . '_name');

            $fields[] = $field;

         } else {

            $options = [];

            foreach($field['values'] as $value){
               $options[$value] = $value;
            }

            if(count($options) == 0){
               $options = [
                  'custom_field' => __('Product custom field', 'integration-marktplaats-for-woocommerce'),
                  'attribute'    => __('Product attribute', 'integration-marktplaats-for-woocommerce'),
               ];
            }

            if(empty($field['type'])){
               $field['type'] = 'data-mapper';
            }

            $field['config_fields']= $config_fields;
            $field['value']        = Util::array($config_fields)->get($field['id']);
            $field['source']       = Util::array($config_fields)->get($field['id']);
            $field['source_value'] = Util::array($config_fields)->get($field['id'] . '__' . Util::array($config_fields)->get($field['id']) . '_name');
            $field['options']      = $options;

            $fields[] = $field;
         }

         //add an extra field for units as well
         if( ! empty($field['units']) ){

            $u_options = [];

            foreach($field['units'] as $unit){
               $u_options[$unit['name']] = $unit['name'];
            }

            if(count($u_options) == 0){
               $u_options = [
                  'custom_field' => __('Product custom field', 'integration-marktplaats-for-woocommerce'),
                  'attribute'    => __('Product attribute', 'integration-marktplaats-for-woocommerce'),
               ];
            }

            $unit_id = $field['id'] . '_unit';
            $fields[] = [
               'type'         => 'data-mapper',
               'id'           => $unit_id,
               'name'         => $field['name'] .' '. __('(Unit)', 'integration-marktplaats-for-woocommerce'),
               'options'      => $u_options,
               'source'       => Util::array($config_fields)->get($unit_id),
               'source_value' => Util::array($config_fields)->get($unit_id . '__' . Util::array($config_fields)->get($unit_id) . '_name'),
            ];
         }

      }

      $mfg = new Module_Field_Generator;
      $mfg->set_fields($fields, 'category_field_mapping');
      $mfg->render();

   }


}
