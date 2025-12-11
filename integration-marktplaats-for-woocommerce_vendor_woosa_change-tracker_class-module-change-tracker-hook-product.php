<?php
/**
 * Module Change Tracker Hook Product
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Change_Tracker_Hook_Product implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('save_post_product', [__CLASS__, 'trigger_on_created_product'], 10, 3);
      add_action('save_post_product_variation', [__CLASS__, 'trigger_on_created_product'], 10, 3);

      add_action('post_updated', [__CLASS__, 'trigger_on_updated_product'], 10, 3);

      add_action('added_post_meta', [__CLASS__, 'trigger_on_updated_product_meta'], 20, 3);
      add_action('updated_post_meta', [__CLASS__, 'trigger_on_updated_product_meta'], 20, 3);

      add_action('woocommerce_new_product', [__CLASS__, 'add_create_or_update_product_task'], 30, 2);
      add_action('woocommerce_new_product_variation', [__CLASS__, 'add_create_or_update_product_task'], 30, 2);

      add_action('woocommerce_update_product', [__CLASS__, 'add_create_or_update_product_task'], 30, 2);
      add_action('woocommerce_update_product_variation', [__CLASS__, 'add_create_or_update_product_task'], 30, 2);

      add_action('woocommerce_variation_set_stock', [__CLASS__, 'add_create_or_update_product_task_on_stock_update']);
      add_action('woocommerce_product_set_stock', [__CLASS__, 'add_create_or_update_product_task_on_stock_update']);

      add_action('before_delete_post', [__CLASS__, 'add_delete_or_trash_product_task']);
      add_action('trashed_post', [__CLASS__, 'add_pause_or_unpause_product_task']);

   }



   /**
    * Marks the product as it requires task to process the created product.
    *
    * @param string $post_id
    * @param \WP_Post $post
    * @param bool $update
    * @return void
    */
   public static function trigger_on_created_product($post_id, $post, $update){

      //avoid auto-saves or revisions
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
         return;
      }

      $enable = apply_filters(PREFIX . '\module\change_tracker\created_product\enable', false, $post_id);

      if( ! $enable ){
         return;
      }

      //new only
      if( ! $update ){
         $product = wc_get_product($post_id);
         $product->update_meta_data(PREFIX . '_change_tracker:create_task', 'yes');
         $product->save_meta_data();
      }

   }



   /**
    * Marks the product as it requires task to process the updated product.
    *
    * @param string|int $post_id
    * @param \WP_Post $post_after
    * @param \WP_Post $post_before
    * @return void
    */
   public static function trigger_on_updated_product($post_id, $post_after, $post_before){

      //avoid auto-saves or revisions
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
         return;
      }

      if( ! in_array($post_after->post_type, ['product', 'product_variation'])){
         return;
      }

      $enable = apply_filters(PREFIX . '\module\change_tracker\updated_product\enable', false, $post_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_product_change');
      $update = false;

      if( ! $enable || 'yes' === $lock){
         return;
      }

      $property_keys = apply_filters(PREFIX . '\module\change_tracker\updated_product\property_keys', [
         'post_title',
         'post_content',
         'post_excerpt',
      ]);

      foreach($property_keys as $prop){
         if( property_exists($post_before, $prop) && $post_before->{$prop} !== $post_after->{$prop} ){
            $update = true;
            break;
         }
      }

      if($update && self::is_linked($post_id) ){
         $product = wc_get_product($post_id);
         $product->update_meta_data(PREFIX . '_change_tracker:create_task', 'yes');
         $product->save();
      }
   }



   /**
    * Marks the product as it requires task to process the updated product metadata.
    *
    * @param int $meta_id
    * @param int $post_id
    * @param string $meta_key
    * @return void
    */
   public static function trigger_on_updated_product_meta($meta_id, $post_id, $meta_key){

      $enable = apply_filters(PREFIX . '\module\change_tracker\updated_product_meta\enable', false, $post_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_product_change');

      if( ! $enable || 'yes' === $lock){
         return;
      }

      $meta_keys = apply_filters(PREFIX . '\module\change_tracker\updated_product_meta\meta_keys', [
         '_price',
         '_regular_price',
      ]);

      if( in_array($meta_key, $meta_keys) && self::is_linked($post_id) ){
         $product = wc_get_product($post_id);
         $product->update_meta_data(PREFIX . '_change_tracker:create_task', 'yes');
         $product->save();
      }

   }



   /**
    * Adds task for action `create_or_update_product` when product gets created/updated.
    *
    * @param string|int $product_id
    * @param \WC_Product $product
    * @return void
    */
   public static function add_create_or_update_product_task($product_id, $product){

      $enable = apply_filters(PREFIX . '\module\change_tracker\create_or_update_product\enable', false, $product_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_product_change');

      if( ! $enable || 'yes' === $lock || empty($product->get_price())){
         return;
      }

      $require_task = $product->get_meta(PREFIX . '_change_tracker:create_task', true);

      if('yes' === $require_task){

         $types = apply_filters(PREFIX . '\module\change_tracker\create_or_update_product\types', ['simple', 'variation'], $product);

         if(in_array($product->get_type(), $types)){
            Module_Product_Task_Util::create_task($product_id, 'create_or_update_product');
         }

         $product->delete_meta_data(PREFIX . '_change_tracker:create_task');
         $product->save();
      }

   }



   /**
    * Adds task for action `create_or_update_product` when stock gets changed.
    *
    * @param \WC_Product $product
    * @return void
    */
   public static function add_create_or_update_product_task_on_stock_update($product){

      $enable = apply_filters(PREFIX . '\module\change_tracker\updated_product_meta\enable', false, $product->get_id());
      $types  = apply_filters(PREFIX . '\module\change_tracker\updated_product_meta\types', ['simple', 'variation'], $product);

      if( ! $enable || ! self::is_linked($product->get_id()) || ! in_array($product->get_type(), $types) ){
         return;
      }

      Module_Product_Task_Util::create_task($product->get_id(), 'create_or_update_product');
   }



   /**
    * Adds task for action `delete_or_trash_product` when product gest completely deleted.
    *
    * @param int|string $post_id
    * @return void
    */
   public static function add_delete_or_trash_product_task($post_id){

      $enable = apply_filters(PREFIX . '\module\change_tracker\delete_or_trash_product\enable', false, $post_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_product_change');

      if( ! $enable || ! self::is_linked($post_id) || 'yes' === $lock){
         return;
      }

      Module_Product_Task_Util::create_task($post_id, 'delete_or_trash_product');
   }



   /**
    * Adds task for action `pause_or_unpause_product`. In case it's not enabled it will add task for `delete_or_trash_product`.
    *
    * @param int|string $post_id
    * @return void
    */
   public static function add_pause_or_unpause_product_task($post_id){

      $enable = apply_filters(PREFIX . '\module\change_tracker\pause_or_unpause_product\enable', false, $post_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_product_change');

      if('yes' === $lock || ! self::is_linked($post_id)){
         return;
      }

      if( ! $enable ){
         return Module_Product_Task_Util::create_task($post_id, 'delete_or_trash_product');
      }

      Module_Product_Task_Util::create_task($post_id, 'pause_or_unpause_product', [
         'force_pause' => true
      ]);
   }



   /**
    * Checks whether or not the product is linked with the service via some references meta.
    *
    * @param string|int $product_id
    * @return boolean
    */
   protected static function is_linked($product_id){

      global $wpdb;

      $result = true;
      $sku    = get_post_meta($product_id, PREFIX . '_sku', true);
      $p_id   = get_post_meta($product_id, PREFIX . '_product_id', true);

      //search for any meta key that matches PREFIX . '_{$account_id}_product_id'
      $query = $wpdb->get_var(
         $wpdb->prepare("SELECT meta_key FROM {$wpdb->postmeta}
            WHERE post_id = %d
               AND meta_key LIKE %s
               AND meta_value != ''
            LIMIT 1",
            $product_id,
            PREFIX . '\_%\_product_id'
         )
      );

      if( empty($sku) && empty($p_id) && empty($query)){
         $result = false;
      }

      return apply_filters(PREFIX . '\module\change_tracker\product\is_linked', $result, $product_id);
   }

}