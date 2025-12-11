<?php
/**
 * Invoice PDF Hook Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Invoice_PDF_Hook implements Interface_Hook {


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init() {

      add_filter( 'wpo_wcpdf_order_item_data', [__CLASS__, 'order_item_data'], 10, 3);

   }



   /**
    * Filter the sensitive meta items from invoice and packing-slip
    *
    * @param array $data
    * @param \WC_Order $order
    * @param string $type
    * @return array
    */
   public static function order_item_data($data, $order, $type) {

      if (in_array($type, apply_filters(PREFIX . '\invoice_pdf\order_item_data_type', ['invoice', 'packing-slip']))) {

         $items = $order->get_items();

         if ( count( $items ) > 0 ) {

            foreach ($items as $item_id => $item) {

               if ($data['item_id'] === $item_id) {

                  add_filter('woocommerce_order_item_get_formatted_meta_data', [__CLASS__, 'remove_meta_items_from_invoice']);

                  $data['meta'] = wc_display_item_meta( $item, array( 'echo' => false ) );

                  remove_filter('woocommerce_order_item_get_formatted_meta_data', [__CLASS__, 'remove_meta_items_from_invoice']);

               }

            }

         }


      }

      return $data;

   }



   /**
    * Remove meta items from invoice
    *
    * @param $formatted_meta
    * @return mixed
    */
   public static function remove_meta_items_from_invoice($formatted_meta) {

      foreach ($formatted_meta as $key => $value) {

         if (in_array($value->key, Module_Order_Details_Hook::hide_order_item_meta([]))) {
            unset($formatted_meta[$key]);
         }

      }

      return $formatted_meta;
   }


}
