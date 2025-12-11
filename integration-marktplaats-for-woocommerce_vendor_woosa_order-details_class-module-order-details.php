<?php
/**
 * Module Order Details
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Details {


   /**
    * Retrieves the list of dropdown options based on the given type.
    *
    * @param string $type
    * @param \WC_Order $order
    * @return array
    */
   public static function get_dropdown_options($type, \WC_Order $order){

      $ship   = apply_filters(PREFIX . '\module\order_details\ship_items_dropdown', [], $order);
      $cancel = apply_filters(PREFIX . '\module\order_details\cancel_items_dropdown', [], $order);

      return empty(${$type}) ? [] : ${$type};
   }



   /**
    * Retrieves the shipping carrier of an order item.
    *
    * @param \WC_Order_Item  $item
    * @param string $account_id
    * @return string
    */
   public static function get_order_item_shipping_carrier(\WC_Order_Item $item, string $account_id){

      $value = $item->get_meta(Util::prefix('carrier_code'));

      if(empty($value)){

         $account = Module_Multiple_Account::get_account($account_id);
         $value   = Util::array($account)->get('shipping_carrier');
      }

      return $value;
   }



   /**
    * Retrieves the tracking number of an order item.
    *
    * @param string $account_id
    * @param \WC_Order_Item $item
    * @return string
    */
   public static function get_order_item_tracking_number(\WC_Order_Item $item){

      $value = $item->get_meta(Util::prefix('tracking_numbers'));

      if(empty($value)){
         $order_id = $item->get_order_id();
         $order    = wc_get_order($order_id);
         $value    = self::extract_tracking_number($order);
      }

      return $$value;
   }



   /**
    * Extracts tracking number based on the meta key path.
    *
    * @param \WC_Order|\WC_Order_Item $entity
    * @return string
    */
   public static function extract_tracking_number($entity){

      if (!($entity instanceof \WC_Order) && !($entity instanceof \WC_Order_Item)) {
        throw new \InvalidArgumentException('Entity must be an instance of WC_Order or WC_Order_Item.');
      }

      $meta_key = Option::get('tracking_code_meta_key', '');
      $keys     = array_filter(explode('.', $meta_key));

      //does not have multi-levels
      if(count($keys) == 1){
         $value = $entity->get_meta($meta_key);
      }else{

         // Extract base meta key (first segment)
         $base_key = array_shift($keys);
         $data     = Util::obj_to_arr( Util::maybe_decode_json( $entity->get_meta($base_key) ) );
         $value    = self::walk_key_path($data, $keys);
      }

      return is_array($value) || is_object($value) ? '' : (string) $value;
   }



   /**
    * Walks through the given keys to return the found data.
    *
    * @param mixed $data
    * @param array $keys
    * @return mixed
    */
   protected static function walk_key_path($data, array $keys) {
      foreach ($keys as $key) {
         if ($key === '*') {
            // Wildcard: iterate over all children
            if (!is_array($data)) {
               return null;
            }

            $results = [];
            foreach ($data as $subData) {
               $result = self::walk_key_path($subData, array_slice($keys, 1));
               if (!is_null($result)) {
                  $results[] = $result;
               }
            }

            //only the last item
            return end($results);
         }

         if (!is_array($data) || !array_key_exists($key, $data)) {
            return null;
         }

         $data = $data[$key];
      }

      return $data;
   }



   /**
    * Marks the order as processed as long as its all items have been processed.
    *
    * @return void
    */
   public static function mark_order_as_processed($order_id){

      $order = wc_get_order($order_id);

      if( ! $order instanceof \WC_Order ){
         return;
      }

      $registered = [];
      $cancelled  = [];
      $failed     = [];
      $so_item    = [];

      $meta = new Module_Meta($order->get_id());

      foreach($order->get_items() as $item){

         $status            = $item->get_meta(PREFIX . '_order_line_status');
         $fulfilment_method = $item->get_meta(PREFIX . '_fulfilment_method');

         //backward compatibility - Bol
         if(empty($fulfilment_method)){
            $old_order_item    = $item->get_meta( '_' . PREFIX . '_order_item');
            $fulfilment_method = Util::array(Util::obj_to_arr($old_order_item))->get('fulfilment/method');
         }

         if('registered' === $status){
            $registered[] = $item->get_id();
         }elseif('cancelled' === $status){
            $cancelled[] = $item->get_id();
         }elseif('error' === $status){
            $failed[] = $item->get_id();
         }elseif(self::is_fulfiled_by_marketplace($fulfilment_method)){
            $so_item[] = $item->get_id();
         }
      }

      if( count( $order->get_items() ) == ( count($registered) + count($cancelled) + count($so_item) ) ){

         $message = __('All order items have been processed.', 'integration-marktplaats-for-woocommerce');

         if( empty($registered) ){
            $order->set_status('cancelled', $message);
         }else{
            $order->set_status('completed', $message);
         }

         $meta->set_status('processed');
         $meta->save();

         $order->save();

         return;
      }

      if( ! empty($failed) ){
         $meta->set_status('error');
         $meta->set_error(__('Some order items could have not been processed.', 'integration-marktplaats-for-woocommerce'));
         $meta->save();
      }
   }



   /**
    * Checks whether or not is fulfilled by marketplace service.
    *
    * @param string|null $fulfilment_method
    * @return boolean
    */
   public static function is_fulfiled_by_marketplace(?string $fulfilment_method){
      return in_array($fulfilment_method, [
         'FBB', //Bol
         'fulfilled_by_kaufland', //Kaufland
      ]);
   }
}