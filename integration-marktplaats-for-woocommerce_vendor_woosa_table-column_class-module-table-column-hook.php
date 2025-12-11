<?php
/**
 * Module Table Column Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Table_Column_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp', [__CLASS__, 'process_column_list']);
      add_action('current_screen', [__CLASS__, 'process_column_list_for_HPOS']);

   }



   /**
    * Defines the hooks for the column list.
    *
    * @return void
    */
   public static function process_column_list(){
      self::define_hooks();
   }



   /**
    * Defines the hooks for the column list when HPOS is enabled.
    *
    * @return void
    */
   public static function process_column_list_for_HPOS() {

      global $current_screen;

      $is_hpos = Module_Core::is_HPOS_enabled() && (isset($current_screen->id) && 'woocommerce_page_wc-orders' === $current_screen->id);

      self::define_hooks($is_hpos);

   }



   /**
    * Defines the necessary hooks.
    *
    * @param boolean $is_hpos
    * @return void
    */
   private static function define_hooks($is_hpos = false){

      foreach(Module_Table_Column::get_post_types() as $post_type){

         $initiate = apply_filters(PREFIX . '\table_column\initiate', true, $post_type);

         if($initiate){

            if ('shop_order' === $post_type && $is_hpos) {

               add_filter('woocommerce_shop_order_list_table_columns', [Module_Table_Column::class, 'table_head_columns']);
               add_action('manage_woocommerce_page_wc-orders_custom_column', [Module_Table_Column::class, 'table_content_columns'], 10, 2);

            }else{

               add_filter('manage_edit-'.$post_type.'_columns', [Module_Table_Column::class, 'table_head_columns']);
               add_action('manage_'.$post_type.'_posts_custom_column', [Module_Table_Column::class, 'table_content_columns'], 10, 2);

               //terms
               add_action('manage_'.$post_type.'_custom_column', [Module_Table_Column::class, 'table_content_columns_terms'], 10, 3);
            }
         }
      }
   }


}