<?php
/**
 * Module Action Bulker Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Action_Bulker_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp', [__CLASS__, 'process_action_list']);
      add_action('current_screen', [__CLASS__, 'process_action_list_for_HPOS']);

      add_action('init', [__CLASS__, 'handle_action_callback']);
   }



   /**
    * Defines the hooks to the bulk actions.
    *
    * @return void
    */
   public static function process_action_list(){
      self::define_hooks();
   }



   /**
    * Defines the hooks to the bulk actions when HPOS is enabled.
    *
    * @return void
    */
   public static function process_action_list_for_HPOS() {

      global $current_screen;

      $is_hpos = Module_Core::is_HPOS_enabled() && (isset($current_screen->id) && 'woocommerce_page_wc-orders' === $current_screen->id);

      self::define_hooks($is_hpos);

   }



   /**
    * Defines the hook to the handle the action callback.
    *
    * @return void
    */
   public static function handle_action_callback(){

      foreach(Module_Action_Bulker::get_post_types(false) as $post_type){

         $initiate = apply_filters(PREFIX . '\action_bulker\initiate', true, $post_type);

         if($initiate){

            if ('shop_order' === $post_type && Module_Core::is_HPOS_enabled()) {

               add_action('handle_bulk_actions-woocommerce_page_wc-orders', [Module_Action_Bulker::class, 'handle_bulk_actions'], 10, 3);

            } else {

               add_action('handle_bulk_actions-edit-' . $post_type, [Module_Action_Bulker::class, 'handle_bulk_actions'], 10, 3);

            }
         }

      }
   }



   /**
    * Defines the necessary hooks.
    *
    * @param boolean $is_hpos
    * @return void
    */
   private static function define_hooks($is_hpos = false){

      foreach(Module_Action_Bulker::get_post_types() as $post_type){

         $initiate = apply_filters(PREFIX . '\action_bulker\initiate', true, $post_type);

         if($initiate){

            if ('shop_order' === $post_type && $is_hpos) {

               add_filter('bulk_actions-woocommerce_page_wc-orders', [Module_Action_Bulker::class, 'add_bulk_actions']);

            } else {

               add_filter('bulk_actions-edit-'.$post_type, [Module_Action_Bulker::class, 'add_bulk_actions']);

            }

         }

      }
   }
}