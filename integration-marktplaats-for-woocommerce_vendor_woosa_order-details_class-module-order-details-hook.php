<?php
/**
 * Module Order Details Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Details_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter('woocommerce_admin_order_data_after_order_details', [__CLASS__, 'display_extra_details'], 99);

      add_filter('woocommerce_hidden_order_itemmeta', [ __CLASS__, 'hide_order_item_meta' ]);
      add_filter('woocommerce_order_item_get_formatted_meta_data', [__CLASS__, 'exclude_order_item_formatted_meta'], 10, 2);

      add_filter('woocommerce_shop_order_search_fields', [__CLASS__, 'extra_search_fields']);

      add_filter('woocommerce_new_order_item', [__CLASS__, 'set_default_item_name'], 10, 3);
   }



   /**
    * Displays extra details.
    *
    * @param \WC_Order $order
    * @return string|void
    */
   public static function display_extra_details($order){

      echo '<div class="clear"><br></div>'; //need this to clear the shitty floats

      //show errors
      $meta = new Module_Meta($order->get_id());
      $meta->display_errors();

      //process box
      if( apply_filters(PREFIX . '\order_details\display_process_box', true, $order) ){

         $line_items   = [];
         $number       = $meta->get('order_id');
         $status       = $meta->get_status();
         $button_label = 'processed' === $status ? __('View Status', 'integration-marktplaats-for-woocommerce') : __('Process Order', 'integration-marktplaats-for-woocommerce');
         $button_class = 'processed' === $status ? '' : 'button-primary';
         $box_class    = 'processed' === $status ? 'alertbox--blue' : 'alertbox--yellow';
         $output       = 'processed' === $status ? '<p>' . sprintf(__('This order #%s has been proccessed. Click the button below to see the status.', 'integration-marktplaats-for-woocommerce'), "<b>{$number}</b>") . '</p>' : '<p>' . sprintf(__('This order #%s is not proccessed yet. Click the button below to see the options.', 'integration-marktplaats-for-woocommerce'), "<b>{$number}</b>") . '</p>';

         if(empty($number)){
            return;
         }

         foreach($order->get_items() as $item){

            $item_status       = $item->get_meta(PREFIX . '_order_line_status');
            $fulfilment_method = $item->get_meta(PREFIX . '_fulfilment_method');

            //backward compatibility - Bol
            if(empty($fulfilment_method)){
               $old_order_item    = $item->get_meta( '_' . PREFIX . '_order_item');
               $fulfilment_method = Util::array(Util::obj_to_arr($old_order_item))->get('fulfilment/method');
            }

            if(
               Module_Order_Details::is_fulfiled_by_marketplace($fulfilment_method) ||
               'cancelled' === $item_status ||
               'open' === $item_status && 'processed' === $status ||
               '' === $item_status && 'processed' === $status
            ){
               continue;
            }

            $line_items[] = $item;
         }

         if(empty($line_items)){

            $box_class = 'alertbox--blue';

            $output = '<p>' . __('There are no items to process for this order. Possible reasons:', 'integration-marktplaats-for-woocommerce') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . sprintf(__('All order items have been cancelled or are fulfilled by %s', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')) . '</li>';
            $output .= '<li>' . __('The order was already processed at the time of import', 'integration-marktplaats-for-woocommerce') . '</li>';
            $output .= '</ul>';
         }

         echo Util::get_template('process-box.php', [
            'output'       => $output,
            'number'       => $number,
            'button_label' => empty($line_items) ? '' : $button_label,
            'button_class' => $button_class,
            'box_class'    => $box_class,
            'order'        => $order,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');
      }
   }



   /**
    * Hides particular meta data of an order item.
    *
    * @param array $order_itemmeta
    * @return array
    */
   public static function hide_order_item_meta( $order_itemmeta ) {

      $extra = apply_filters(PREFIX . '\order_details\hidden_item_meta',
         [
            PREFIX . '_order_line_id',
            PREFIX . '_order_line_status',
            PREFIX . '_id_order_unit',
            PREFIX . '_product_id',
            PREFIX . '_product_reference',
            PREFIX . '_sku',
            PREFIX . '_ean',
            PREFIX . '_processed',
            PREFIX . '_processed_as',
            PREFIX . '_error',
            PREFIX . '_tracking_numbers',
            PREFIX . '_carrier_code',
            PREFIX . '_refund_reason',
            PREFIX . '_refund_amount',
            PREFIX . '_cancel_reason',
            PREFIX . '_fulfilment_method',
            PREFIX . '_shipping_label_id',
            PREFIX . '_shipping_label_offer_id',
            PREFIX . '_transporter_code',
            PREFIX . '_tracking_code',
         ]
      );

      $order_itemmeta = array_merge($order_itemmeta, $extra);

      return $order_itemmeta;
   }



   /**
    * Removes all prefixed formatted meta of an order item.
    *
    * @param array $formatted_meta
    * @param \WC_Order_Item $class
    * @return array
    */
   public static function exclude_order_item_formatted_meta($formatted_meta, $class){

      $formatted_meta = array_filter($formatted_meta, function($item) {
         return strpos($item->key, PREFIX . '_') !== 0;
      });

      return $formatted_meta;
   }



   /**
    * Adds extra meta fields to search in.
    *
    * @param array $fields
    * @return array
    */
   public static function extra_search_fields( $fields ){

      $extra = [
         PREFIX . '_order_id',
         PREFIX . '_id_order',
      ];

      $fields = array_merge($fields, $extra);

      return $fields;
   }



   /**
    * Sets default order item name for unknown products.
    *
    * @param int $item_id
    * @param \WC_Order_Item_Product $item
    * @param int $order_id
    * @return void
    */
   public static function set_default_item_name( $item_id, $item, $order_id ) {

      if ( empty ( $item->get_name() ) ) {
         $item->set_name( __( 'This is an external unknown product', 'integration-marktplaats-for-woocommerce' ) );
      }
   }

}