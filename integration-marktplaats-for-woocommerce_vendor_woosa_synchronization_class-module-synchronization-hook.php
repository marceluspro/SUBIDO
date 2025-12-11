<?php
/**
 * Module Synchronization Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Synchronization_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\change_tracker\created_product\enable', [Module_Synchronization::class, 'is_new_product_sync_enabled']);
      add_filter(PREFIX . '\module\change_tracker\updated_product\enable', [Module_Synchronization::class, 'is_product_sync_enabled']);
      add_filter(PREFIX . '\module\change_tracker\updated_product_meta\enable', [Module_Synchronization::class, 'is_product_sync_enabled']);

      add_filter(PREFIX . '\module\change_tracker\create_or_update_product\enable', [Module_Synchronization::class, 'is_product_sync_enabled']);
      add_filter(PREFIX . '\module\change_tracker\delete_or_trash_product\enable', [Module_Synchronization::class, 'is_product_sync_enabled']);
      add_filter(PREFIX . '\module\change_tracker\pause_or_unpause_product\enable', [Module_Synchronization::class, 'is_pause_trashed_product_enabled']);


      add_filter(PREFIX . '\module\change_tracker\notify_create_order\enable', [Module_Synchronization::class, 'is_order_sync_enabled']);
      add_filter(PREFIX . '\module\change_tracker\notify_update_order\enable', [Module_Synchronization::class, 'is_order_sync_enabled']);
      add_filter(PREFIX . '\module\change_tracker\notify_delete_order\enable', [Module_Synchronization::class, 'is_order_sync_enabled']);

      add_filter(PREFIX . '\module\synchronization\sections', [Module_Synchronization::class, 'synchronization_sections']);

      add_filter(PREFIX . '\module\settings\page\content\fields\synchronization', [__CLASS__, 'add_submit_button'], 99);

      add_action('woocommerce_email_enabled_customer_completed_order', [__CLASS__, 'disable_customer_completed_email'], 10, 2);
      add_action('woocommerce_email_enabled_customer_processing_order', [__CLASS__, 'disable_customer_processing_email'], 10, 2);

   }



   /**
    * Adds the submit button.
    *
    * @param array $items
    * @return array
    */
   public static function add_submit_button(array $items){

      $items = array_merge($items, [
         [
            'type' => 'title',
            'id'   => PREFIX . '_submit_button',
         ],
         [
            'id'   => PREFIX .'_save_settings',
            'type' => 'submit_button',
         ],
         [
            'type' => 'sectionend',
            'id'   => PREFIX . '_submit_button_end',
         ],
      ]);

      return $items;
   }



   /**
    * Disables the customer emails for completed orders.
    *
    * @param bool $enable
    * @param \WC_Order $order
    * @return bool
    */
   public static function disable_customer_completed_email( $enable, $order ) {

      $disable = Util::string_to_bool(Option::get('disable_completed_order_email', 'yes'));

      if($disable && $order instanceof \WC_Order){
         $order_id = $order->get_meta(PREFIX . '_order_id');

         if ( ! empty($order_id) ) {
            $enable = false;
         }
      }

      return $enable;
   }



   /**
    * Disables the customer emails for processing orders.
    *
    * @param bool $enable
    * @param \WC_Order $order
    * @return bool
    */
   public static function disable_customer_processing_email( $enable, $order ) {

      $disable = Util::string_to_bool(Option::get('disable_processing_order_email', 'yes'));

      if($disable && $order instanceof \WC_Order){
         $order_id = $order->get_meta(PREFIX . '_order_id');

         if ( ! empty($order_id) ) {
            $enable = false;
         }
      }

      return $enable;
   }

}