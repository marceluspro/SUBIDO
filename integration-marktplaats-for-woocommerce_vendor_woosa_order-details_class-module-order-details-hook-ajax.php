<?php
/**
 * Module Order_Details Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Details_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_order_details_handle_open_popup', [__CLASS__, 'handle_open_popup']);
      add_action('wp_ajax_' . PREFIX . '_order_details_handle_check_delivery_options', [__CLASS__, 'handle_check_delivery_options']);
      add_action('wp_ajax_' . PREFIX . '_order_details_handle_ship_items', [__CLASS__, 'handle_ship_items']);
      add_action('wp_ajax_' . PREFIX . '_order_details_handle_cancel_items', [__CLASS__, 'handle_cancel_items']);
   }



   /**
    * Handles the request to open the popup.
    *
    * @return string
    */
   public static function handle_open_popup(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $order_id = Util::array($_GET)->get('order_id');
      $order    = wc_get_order($order_id);

      if( ! $order instanceof \WC_Order ){
         echo '<p>' . __('Invalid order, try again!', 'integration-marktplaats-for-woocommerce') . '</p>';
         exit;
      }

      echo Util::get_template('popup-content.php', [
         'order' => $order,
      ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');

      exit;
   }



   /**
    * Handles the request to check the delivery options.
    *
    * @return void
    */
   public static function handle_check_delivery_options(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      parse_str(Util::array($_POST)->get('fields'), $fields);

      //get only selected items
      $fields['items'] = array_filter(Util::array($fields)->get('items', []), function($item) { return $item['selected'] === 'yes';});

      $items    = $fields['items'];
      $order_id = $fields['order_id'];
      $order    = wc_get_order($order_id);

      if(empty($items)){
         wp_send_json_error([
            'message' => __('Please specify the order items you want to check delivery options for.', 'integration-marktplaats-for-woocommerce'),
         ]);
      }

      do_action(PREFIX . '\module\order_details\handle_check_delivery_options', $fields, $order);

      wp_send_json_success([
         'template' => Util::get_template('popup-content.php', [
            'order' => $order,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates'),
      ]);
   }



   /**
    * Handles the request to cancel order items.
    *
    * @return string
    */
   public static function handle_ship_items(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      parse_str(Util::array($_POST)->get('fields'), $fields);

      //get only selected items
      $fields['items'] = array_filter(Util::array($fields)->get('items', []), function($item) { return $item['selected'] === 'yes';});

      $items    = $fields['items'];
      $order_id = $fields['order_id'];
      $order    = wc_get_order($order_id);

      if(empty($items)){
         wp_send_json_error([
            'message' => __('Please specify the order items you want to ship.', 'integration-marktplaats-for-woocommerce'),
         ]);
      }

      if( empty($fields['delivery_option_support']) ){

         foreach($items as $item){
            if( empty($item['carrier_code']) ){
               wp_send_json_error([
                  'message' => sprintf(__('Please make sure you specified the shipping carrier for the item: %s!', 'integration-marktplaats-for-woocommerce'), $item['wc_item_name']),
               ]);
            }
            if( empty($item['tracking_numbers']) ){
               wp_send_json_error([
                  'message' => sprintf(__('Please make sure you specified the tracking code for the item: %s!', 'integration-marktplaats-for-woocommerce'), $item['wc_item_name']),
               ]);
            }
         }
      }

      do_action(PREFIX . '\module\order_details\handle_ship_order_items', $fields, $order);

      wp_send_json_success([
         'template' => Util::get_template('popup-content.php', [
            'order' => $order,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates'),
      ]);
   }



   /**
    * Handles the request to cancel order items.
    *
    * @return string
    */
   public static function handle_cancel_items(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      parse_str(Util::array($_POST)->get('fields'), $fields);

      //get only selected items
      $fields['items'] = array_filter(Util::array($fields)->get('items', []), function($item) { return $item['selected'] === 'yes';});

      $items    = $fields['items'];
      $order_id = $fields['order_id'];
      $order    = wc_get_order($order_id);

      if(empty($items)){
         wp_send_json_error([
            'message' => __('Please specify the order items you want to cancel.', 'integration-marktplaats-for-woocommerce'),
         ]);
      }

      foreach($items as $item){
         if( empty($item['reason']) ){
            wp_send_json_error([
               'message' => sprintf(__('Please make sure you specified the cancellation reason for the item: %s!', 'integration-marktplaats-for-woocommerce'), $item['wc_item_name']),
            ]);
         }
      }

      do_action(PREFIX . '\module\order_details\handle_cancel_order_items', $fields, $order);

      wp_send_json_success([
         'template' => Util::get_template('popup-content.php', [
            'order' => $order,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates'),
      ]);
   }
}