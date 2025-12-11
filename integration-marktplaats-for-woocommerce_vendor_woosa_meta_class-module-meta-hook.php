<?php
/**
 * Module Meta Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Meta_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter('woocommerce_webhook_topic_hooks', [__CLASS__, 'add_webhook_topic_hooks'], 10, 2);

      add_filter(PREFIX . '\meta\get_preserve_stock_offset', [__CLASS__, 'get_preserve_stock_offset'], 10, 2);

   }



   /**
    * Adds hooks to specific webhook topics.
    *
    * @param array $topic_hooks
    * @param \WC_Webhook $class
    * @return array
    */
   public static function add_webhook_topic_hooks($topic_hooks, $class){

      if(isset($topic_hooks['product.updated'])){
         $topic_hooks['product.updated'][] = PREFIX . '\meta\product_saved';
         $topic_hooks['product.updated'][] = PREFIX . '\meta\product_variation_saved';
      }

      if(isset($topic_hooks['order.updated'])){
         $topic_hooks['order.updated'][] = PREFIX . '\meta\shop_order_saved';
      }

      return $topic_hooks;
   }



   /**
    * Uses fallback from global settings if `preserve_stock_offset` empty.
    *
    * @param mixed $value
    * @param Module_Meta $meta
    * @return mixed
    */
   public static function get_preserve_stock_offset($value, $meta){

      $gs_value = Option::get('preserve_stock_offset', '0');

      if($meta->is_post_published() && $value !== $gs_value){
         $value = $gs_value;
         $meta->set('preserve_stock_offset', $value)->save();
      }

      return $value;
   }

}