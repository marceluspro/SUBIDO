<?php
/**
 * Module Meta Abstract
 *
 * A wrapper for raw meta.
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Module_Meta_Abstract extends Module_Meta_Abstract_Post{


   /**
    * Meta type.
    *
    * Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user', or any other object type with an associated meta table.
    *
    * @var string
    */
   public $meta_type = 'post';


   /**
    * List of meta fields.
    *
    * @var array
    */
   protected $data = [];



   /**
    * Construct of the class.
    *
    * @param integer $object_id
    * @param string $meta_type
    */
   public function __construct($object_id = 0, $meta_type = 'post'){
      parent::__construct($object_id);

      $this->meta_type = $meta_type;
      $this->order     = $this->get_order();
   }



   /**
    * Retrieves the value of meta field.
    *
    * @param string $key
    * @param boolean $single
    * @param boolean $prefix - whether or not to use the prefix
    * @return mixed - An array if $single is false. The value of the meta field if $single is true. False for an invalid $object_id.
    */
   public function get($key, $single = true, $prefix = true){

      $key = $prefix ? Util::prefix($key) : $key;

      if(isset($this->data[$key])){

         $value = $this->data[$key]['value'];

      }else{

         if($this->order instanceof \WC_Order){

            $exist = $this->order->meta_exists($key);
            $value = $this->order->get_meta($key, $single);

         }else{

            $get_meta = "get_{$this->meta_type}_meta";
            $exist    = metadata_exists($this->meta_type, $this->object_id, $key);
            $value    = $get_meta($this->object_id, $key, $single);

         }

         $value = apply_filters(PREFIX . '\meta\get_' . str_replace(PREFIX . '_', '', $key), $value, $this);
         $value = apply_filters(PREFIX . '\meta\get', $value, $key, $this);

         $excl_save = array_merge(
            Module_Meta_Util::product_status_meta(),
            Module_Meta_Util::product_error_meta(),
            Module_Meta_Util::product_id_meta(),
            Module_Meta_Util::order_status_meta(),
            Module_Meta_Util::order_error_meta(),
            Module_Meta_Util::order_id_meta()
         );

         if( $exist || '' !== $value ){

            $this->data[$key] = [
               'value'      => $value,
               'prev_value' => $value,
               'exist'      => $exist,
               'save'       => in_array($key, $excl_save) ? false : true,
               'delete'     => false,
            ];
         }
      }

      return $value;

   }



   /**
    * Sets/updates the value of a meta field.
    *
    * @param string $key
    * @param mixed $value
    * @param mixed $prev_value
    * @param boolean $prefix - whether or not to use the prefix
    * @return self
    */
   public function set($key, $value, $prev_value = '', $prefix = true){

      $key = $prefix ? Util::prefix($key) : $key;

      $this->data[$key] = [
         'value'      => is_array($value) || is_object($value) ? $value : (string) $value,
         'prev_value' => is_array($prev_value) || is_object($prev_value) ? $prev_value : (string) $prev_value,
         'exist'      => false,
         'save'       => true,
         'delete'     => false,
      ];

      return $this;

   }



   /**
    * Removes a meta field.
    *
    * @param string $key
    * @param mixed $value - Metadata value. If provided, rows will only be removed that match the value. Must be serializable if non-scalar.
    * @param boolean $prefix
    * @return self
    */
   public function delete($key, $value = '', $prefix = true){

      $key = $prefix ? Util::prefix($key) : $key;

      $this->data[$key] = [
         'delete' => true,
      ];

      return $this;

   }



   /**
    * Checks whether or not the given meta field (especially checkboxes) was checked (values: true/false or yes/no).
    *
    * @param string $key
    * @param boolean $prefix
    * @return boolean
    */
   public function is_checked($key, $prefix = true){
      return Util::string_to_bool( $this->get($key, true, $prefix) );
   }



   /**
    * Checks whether or not the given meta field is empty.
    *
    * @param string $key
    * @param boolean $prefix
    * @return boolean
    */
   public function is_empty($key, $prefix = true){
      return empty( $this->get($key, true, $prefix) ) ? true : false;
   }



   /**
    * Saves the changes.
    *
    * @return void
    */
   public function save(){

      $run_hook = false;

      foreach($this->data as $key => $item){

         if($item['delete']){

            if($this->order instanceof \WC_Order){

               $this->order->delete_meta_data($key);

            }else{

               $delete = "delete_{$this->meta_type}_meta";
               $deleted = $delete($this->object_id, $key);

               if($deleted){
                  $run_hook = true;
               }
            }

         }else{

            if( ! $item['save'] || ( $item['exist'] && ($item['value'] === $item['prev_value']) ) ){
               continue;
            }

            $valid = apply_filters(PREFIX . '\meta\validate_item', true, $this, Util::prefix($key), $item['value'], $item['prev_value']);

            if($valid){

               if($this->order instanceof \WC_Order){

                  $this->order->update_meta_data($key, $item['value']);

               }else{

                  $update  = "update_{$this->meta_type}_meta";
                  $updated = $update($this->object_id, $key, $item['value'], $item['prev_value']);

                  if($updated){
                     $run_hook = true;
                  }
               }
            }
         }
      }

      if($this->order instanceof \WC_Order){
         $this->order->save_meta_data(); //save only metadata to avoid infinite loop!
      }

      if($run_hook){
         do_action(PREFIX . "\meta\\{$this->get_post_type()}_saved", $this->object_id, $this);
      }

   }

}