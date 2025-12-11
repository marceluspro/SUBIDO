<?php
/**
 * Module Change Tracker Hook Order
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Change_Tracker_Hook_Order implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('woocommerce_new_order', [__CLASS__, 'notify_create'], 10, 2);
      add_action('woocommerce_update_order', [__CLASS__, 'notify_update'], 10, 2);

      add_action('before_delete_post', [__CLASS__, 'notify_delete']);
      add_action('trashed_post', [__CLASS__, 'notify_delete']);
      add_action('untrashed_post', [__CLASS__, 'notify_restore'], 10, 2);

   }



   /**
    * Creates task to notify that an order has been created.
    *
    * @param int|string $order_id
    * @param \WC_Order $order
    * @return void
    */
   public static function notify_create($order_id, \WC_Order $order){

      $enable = apply_filters(PREFIX . '\module\change_tracker\notify_create_order\enable', false, $order);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_order_change');

      if($enable && 'yes' !== $lock){

         $payload = Module_Order_Task_Util::get_payload($order_id);

         $meta = new Module_Meta($order_id);
         $meta->delete_errors();
         $meta->set_status('in_progress');
         $meta->save();

         Module_Task::update_entries([
            [
               'action'      => 'create_or_update_order',
               'source'      => 'shop',
               'target'      => 'service',
               'payload'     => $payload,
               'resource_id' => $order_id,
            ]
         ]);
      }
   }



   /**
    * Creates task to notify that an order has been updated.
    * This ensures the update is only for orders that have been already linked with the service.
    *
    * @param int|string $order_id
    * @param \WC_Order $order
    * @return void
    */
   public static function notify_update($order_id, \WC_Order $order){

      $enable = apply_filters(PREFIX . '\module\change_tracker\notify_update_order\enable', false, $order);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_order_change');

      if($enable && 'yes' !== $lock){

         if( self::is_linked($order_id) ){

            $payload = Module_Order_Task_Util::get_payload($order_id);

            $meta = new Module_Meta($order_id);
            $meta->delete_errors();
            $meta->set_status('in_progress');
            $meta->save();

            Module_Task::update_entries([
               [
                  'action'      => 'create_or_update_order',
                  'source'      => 'shop',
                  'target'      => 'service',
                  'payload'     => $payload,
                  'resource_id' => $order_id,
               ]
            ]);
         }
      }
   }



   /**
    * Creates task to notify that an order has been trashed or completely deleted.
    * This ensures the update is only for orders that have been already linked with the service.
    *
    * @param int|string $order_id
    * @return void
    */
   public static function notify_delete($order_id){

      $enable = apply_filters(PREFIX . '\module\change_tracker\notify_delete_order\enable', false, $order_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_order_change');

      if($enable && 'yes' !== $lock){

         if( self::is_linked($order_id) ){

            $payload = Module_Order_Task_Util::get_payload($order_id);

            $meta = new Module_Meta($order_id);
            $meta->delete_errors();
            $meta->set_status('in_progress');
            $meta->save();

            Module_Task::update_entries([
               [
                  'action'      => 'delete_order',
                  'source'      => 'shop',
                  'target'      => 'service',
                  'payload'     => $payload,
                  'resource_id' => $order_id,
               ]
            ]);
         }
      }
   }



   /**
    * Notifies when an order has been restored from trash.
    *
    * @param int|string $order_id
    * @param string $previous_status
    * @return void
    */
   public static function notify_restore($order_id, $previous_status){

      if('shop_order' === get_post_type($order_id)){

         $order = wc_get_order($order_id);

         self::notify_create($order_id, $order);
      }
   }



   /**
    * Checks whether or not the order is linked with the service via some references meta.
    *
    * @param string|int $order_id
    * @return boolean
    */
   protected static function is_linked($order_id){

      $result   = true;
      $order_id = get_post_meta($order_id, PREFIX . '_order_id', true);

      if( empty($order_id) ){
         $result = false;
      }

      return $result;
   }

}