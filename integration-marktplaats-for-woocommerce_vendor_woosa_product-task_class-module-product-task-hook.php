<?php
/**
 * Module Product Task Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Task_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('init', [__CLASS__, 'register_status']);

      add_filter(PREFIX . '\abstract\entity_post\create\columns', [__CLASS__, 'before_create_product'], 10, 2);
      add_filter(PREFIX . '\abstract\entity_post\create_metadata\entries', [__CLASS__, 'pre_process_metadata'], 10, 2);
      add_filter(PREFIX . '\abstract\entity_post\update_metadata\entries', [__CLASS__, 'pre_process_metadata'], 10, 2);

      add_action(PREFIX . '\product_task\process_task', [__CLASS__, 'maybe_trigger_wc_hooks'], 99, 10);

      add_action('added_term_relationship', [__CLASS__, 'reset_atribute_and_category_list'], 10, 3);
      add_action('deleted_term_relationships', [__CLASS__, 'reset_atribute_and_category_list'], 10, 3);

      add_filter(PREFIX . '\product_task\create_attributes\attributes', [__CLASS__, 'exclude_invalid_attributes']);

   }



   /**
    * Registers the post status
    *
    * @return void
    */
   public static function register_status(){

      register_post_status(Util::prefix('in_progress'), [
         'label'                     => sprintf(__('%s: in-progress', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
         'label_count'               => _n_noop(
            sprintf('%s: in-progress <span class="count">(%s)</span>', Module_Core::config('service.name'), '%s'),
            sprintf('%s: in-progress <span class="count">(%s)</span>', Module_Core::config('service.name'), '%s'),
            'integration-marktplaats-for-woocommerce'
         ),
         'public'                    => true,
         'exclude_from_search'       => false,
         'show_in_admin_all_list'    => true,
         'show_in_admin_status_list' => true,
      ]);

   }



   /**
    * Pre-filter the post columns before to create.
    *
    * @param array $columns
    * @param Module_Abstract_Entity_Post $class
    * @return array
    */
   public static function before_create_product(array $columns, $class){

      if($class->is_supported_type()){

         $columns['post_type']   = 'product';
         $columns['post_status'] = Util::prefix('in_progress');
      }

      //for variations
      if('variation' === Util::array( $class->get_data() )->get('type')){

         $columns['post_type']   = 'product_variation';
         $columns['post_status'] = 'publish'; //this should be always publish
      }

      return $columns;
   }



   /**
    * Pre-process the internal metadata to define the default WC metadata.
    *
    * @param array $metadata
    * @param Module_Abstract_Entity_Post $class
    * @return array
    */
   public static function pre_process_metadata(array $metadata, $class){

      foreach($metadata as $key => $value){

         switch($key){

            case Util::prefix('sku'):

               $metadata['_sku'] = $value;

               break;

            case Util::prefix('weight'):

               $metadata['_weight'] = $value;

               break;

            case Util::prefix('backorder'):

               $metadata['_backorders'] = $value ? 'yes' : 'no';

               break;

            case Util::prefix('dimensions'):

               $metadata['_length'] = Util::array($value)->get('length');
               $metadata['_height'] = Util::array($value)->get('height');
               $metadata['_width']  = Util::array($value)->get('width');

               break;
         }

      }

      return $metadata;

   }



   /**
    * Maybe trigger the WooCommerce essential hooks.
    *
    * @param string $action
    * @param Module_Product_Task $product_task
    * @return void
    */
   public static function maybe_trigger_wc_hooks(string $action, Module_Product_Task $product_task){

      //create event
      if( array_intersect(['post.created', 'postmeta.created'], $product_task->get_event_types()) ){

         $product = wc_get_product($product_task->get_id());

         //stop here if not a valid product
         if( ! $product instanceof \WC_Product){
            return;
         }

         //variation product
         if( $product->is_type('variation') ) {

            do_action( 'woocommerce_new_product_variation', $product->get_id(), $product );

         }else{

            do_action( 'woocommerce_new_product', $product->get_id(), $product );
         }

      //update event
      }elseif( array_intersect(['post.updated', 'postmeta.updated'], $product_task->get_event_types()) ){

         $product = wc_get_product($product_task->get_id());

         //stop here if not a valid product
         if( ! $product instanceof \WC_Product){
            return;
         }

         //variation product
         if( $product->is_type('variation') ) {

            if('update_product_stock' === $action){

               do_action( 'woocommerce_variation_set_stock', $product );

            }else{

               do_action( 'woocommerce_update_product_variation', $product->get_id(), $product );
            }

         }else{

            if('update_product_stock' === $action){

               do_action( 'woocommerce_product_set_stock', $product );

            }else{

               do_action( 'woocommerce_update_product', $product->get_id(), $product );
            }
         }
      }

   }



   /**
    * Sets empty the meta which stores the list of categories & attributes when a term relationship has been added or deleted.
    * In this way we can create and assign the categories & atttributes back when the synchronization is running.
    *
    * @param int $object_id
    * @param int $tt_id
    * @param string $taxonomy
    * @return void
    */
   public static function reset_atribute_and_category_list($object_id, $tt_id, $taxonomy){

      if( ( isset($_POST['post_ID']) || isset($_POST['post_id']) || //if is deleted from the post page
         isset($_POST['tag_ID']) || isset($_GET['delete']) || isset($_POST['delete_tags']) ) //if is deleted from categories or attributes page
         && metadata_exists('post', $object_id, Util::prefix('plugin_version'))
      ){

         if('product_cat' === $taxonomy){

            update_post_meta($object_id, Util::prefix('categories'), []);

         }else{

            $atts = get_post_meta($object_id, '_product_attributes', true);

            if(isset($atts[$taxonomy])){
               update_post_meta($object_id, Util::prefix('attributes'), []);
            }
         }
      }

   }



   /**
    * Exclude attributes that have either the name longer than 30 chars or their values longer than 35 chars.
    *
    * @param $attributes
    * @return array
    */
   public static function exclude_invalid_attributes($attributes) {

      if (is_array($attributes)) {

         foreach ($attributes as $key => $attribute) {

            $has_valid_attributes = false;

            if (array_key_exists('name', $attribute) && strlen($attribute['name']) > 30) {

               unset($attributes[$key]);

            } else {

               if (array_key_exists('value', $attribute)) {

                  if (is_array($attribute['value'])) {

                     foreach($attribute['value'] as $term_key => $term_name) {

                        if (strlen($term_name) > 35) {

                           unset($attributes[$key]['value'][$term_key]);

                        } else {

                           $has_valid_attributes = true;

                        }

                     }

                  } else {

                     if (strlen($attribute['value']) > 35) {

                        unset($attributes[$key]['value']);

                     } else {

                        $has_valid_attributes = true;

                     }

                  }

               }

            }

            if (!$has_valid_attributes && isset($attributes[$key])) {

               unset($attributes[$key]);

            }

         }

      }

      return $attributes;
   }


}
