<?php
/**
 * Module Order Task Util
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Task_Util{



   /**
    * Format order lines and get only essential information.
    *
    * @param \WC_Order $order
    * @param string $line_type
    * @return array
    */
   public static function format_order_line(\WC_Order $order, $line_type = 'line_item'){

      $items = [];

      foreach($order->get_items($line_type) as $item){

         switch($line_type){

            case 'line_item':

               $total                = $order->get_line_total($item, false, false );//excl Tax
               $total_tax            = $item->get_total_tax();
               $total_tax_percentage = $total_tax > 0 ? number_format($total_tax * 100 / $total, 2, '.', '') : 0;

               $items[] = [
                  'id'                   => $item->get_id(),
                  'name'                 => $item->get_name(),
                  'quantity'             => $item->get_quantity(),
                  'product_id'           => $item->get_product_id(),
                  'variation_id'         => $item->get_variation_id(),
                  'price'                => $order->get_item_subtotal($item, false, false),//excl Tax
                  'subtotal'             => $order->get_line_subtotal($item, false, false),//excl Tax
                  'total'                => $total,
                  'subtotal_tax'         => $item->get_subtotal_tax(),
                  'total_tax'            => $total_tax,
                  'total_tax_percentage' => $total_tax_percentage,
                  'total_discount'       => floatval( $item->get_subtotal( 'edit' ) - $item->get_total( 'edit' ) ),//this is taken from the WC method `get_item_coupon_amount()`
                  'meta_data'            => self::format_meta_data($item->get_meta_data()),
               ];

               break;

            case 'tax':

               $items[] = [
                  'name'         => $item->get_label(),
                  'percentage'   => $item->get_rate_percent(),
                  'amount'       => $item->get_tax_total(),
                  'total'        => number_format($item->get_tax_total() + $item->get_shipping_tax_total(), 2, '.', ''),
                  'shipping_tax' => $item->get_shipping_tax_total(),
                  'is_compound'  => $item->is_compound(),
                  'meta_data'    => self::format_meta_data($item->get_meta_data()),
               ];

               break;

            case 'shipping': case 'fee':

               $items[] = [
                  'name'      => $item->get_name(),
                  'total'     => $order->get_line_total($item, false, false),//excl Tax
                  'total_tax' => $order->get_line_tax($item),
                  'meta_data' => self::format_meta_data($item->get_meta_data()),
               ];

               break;

            case 'coupon':

               $items[] = [
                  'name'         => $item->get_name(),
                  'discount'     => $item->get_discount(),
                  'discount_tax' => $item->get_discount_tax(),
                  'meta_data'    => [],//leave it empty for now
               ];

               break;
         }
      }

      return $items;
   }



   /**
    * Formats an array of \WC_Meta_Data.
    *
    * @param array $meta_data
    * @return array
    */
   protected static function format_meta_data(array $meta_data){

      $results = [];

      foreach($meta_data as $meta){

         if(method_exists($meta, 'get_data')){
            $data = $meta->get_data();
            $results[$data['key']] = $data['value'];
         }
      }

      return $results;
   }



   /**
    * Retrieves the payload schema for the given order.
    *
    * @param int|\WC_Order $order
    * @return array
    */
   public static function get_payload($order){

      $payload = [];
      $order   = $order instanceof \WC_Order ? $order : wc_get_order($order);

      if($order instanceof \WC_Order){

         $payload = [
            'id'             => $order->get_id(),
            'status'         => $order->get_status(),
            'payment_method' => $order->get_payment_method(),
            'created_via'    => $order->get_created_via(),
            'parent_id'      => $order->get_parent_id(),
            'transaction_id' => $order->get_transaction_id(),
            'customer_id'    => $order->get_customer_id(),
            'customer_note'  => $order->get_customer_note(),
            'billing'        => [
               'email'      => $order->get_billing_email(),
               'first_name' => $order->get_billing_first_name(),
               'last_name'  => $order->get_billing_last_name(),
               'company'    => $order->get_billing_company(),
               'address_1'  => $order->get_billing_address_1(),
               'address_2'  => $order->get_billing_address_2(),
               'city'       => $order->get_billing_city(),
               'state'      => $order->get_billing_state(),
               'postcode'   => $order->get_billing_postcode(),
               'country'    => $order->get_billing_country(),
               'phone'      => $order->get_billing_phone(),
            ],
            'shipping'        => [
               'first_name' => $order->get_shipping_first_name(),
               'last_name'  => $order->get_shipping_last_name(),
               'company'    => $order->get_shipping_company(),
               'address_1'  => $order->get_shipping_address_1(),
               'address_2'  => $order->get_shipping_address_2(),
               'city'       => $order->get_shipping_city(),
               'state'      => $order->get_shipping_state(),
               'postcode'   => $order->get_shipping_postcode(),
               'country'    => $order->get_shipping_country(),
               'phone'      => $order->get_shipping_phone(),
            ],
            'tax_lines'      => Module_Order_Task_Util::format_order_line($order, 'tax'),
            'shipping_lines' => Module_Order_Task_Util::format_order_line($order, 'shipping'),
            'coupon_lines'   => Module_Order_Task_Util::format_order_line($order, 'coupon'),
            'fee_lines'      => Module_Order_Task_Util::format_order_line($order, 'fee'),
            'line_items'     => Module_Order_Task_Util::format_order_line($order, 'line_item'),
            'meta_data'      => Util::get_prefixed_meta_data($order->get_id()),
         ];
      }

      return apply_filters(PREFIX . '\order_task\util\get_payload', $payload, $order);
   }
}