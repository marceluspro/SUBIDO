<?php
/**
 * Module Marketplace Order
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Order{


   /**
    * This is the callback of the action `import_order`.
    *
    * @return bool - `true` for processed task or `false` to retry (until order list empty)
    */
   public static function import_orders() {

      $result   = true;
      $accounts = Module_Marketplace::get_accounts();

      if(empty($accounts)){

         $tasks  = [];
         $orders = [];
         $page   = Option::get("action:import_order:page", 1);
         $orders = apply_filters(PREFIX . '\module\marketplace\orders_to_import', [], [], $page);

         if(empty($orders)){

            //reset the page
            Option::set("action:import_order:page", 1);

         }else{

            foreach($orders as $order){

               $order_id = null;
               $oi_keys  = [
                  'orderId', //Bol, eBay
                  'id_order', //Kaufland
               ];

               foreach ($oi_keys as $key) {
                  if (isset($order[$key])) {
                     $order_id = $order[$key];
                     break;
                  }
               }

               //skip if no order ID found
               if (is_null($order_id)) {
                  continue;
               }

               //skip processed order
               if(self::is_order_processed($order_id)){
                  continue;
               }

               $tasks[] = [
                  'action'      => 'create_or_update_order',
                  'source'      => 'service',
                  'target'      => 'shop',
                  'payload'     => [
                     'order_id'   => $order_id,
                  ],
                  'resource_id' => $order_id,
                  'priority'    => 10,
               ];
            }

            //increment the page
            Option::set("action:import_order:page", $page+1);

            Module_Task::update_entries($tasks);

            $result = false;
         }

      }else{

         foreach($accounts as $account){

            $tasks      = [];
            $orders     = [];
            $account_id = $account['account_id'];
            $page       = Option::get("action:import_order:{$account_id}:page", 1);
            $orders     = apply_filters(PREFIX . '\module\marketplace\orders_to_import', [], $account, $page);

            if(empty($orders)){

               //reset the page
               Option::set("action:import_order:{$account_id}:page", 1);

            }else{

               foreach($orders as $order){

                  $order_id = null;
                  $oi_keys  = [
                     'orderId', //Bol, eBay
                     'id_order', //Kaufland
                  ];

                  foreach ($oi_keys as $key) {
                     if (isset($order[$key])) {
                        $order_id = $order[$key];
                        break;
                     }
                  }

                  //skip if no order ID found
                  if (is_null($order_id)) {
                     continue;
                  }

                  //skip processed order
                  if(self::is_order_processed($order_id, $account_id)){
                     continue;
                  }

                  $tasks[] = [
                     'action'      => 'create_or_update_order',
                     'source'      => 'service',
                     'target'      => 'shop',
                     'payload'     => [
                        'account_id' => $account_id,
                        'order_id'   => $order_id,
                     ],
                     'resource_id' => $order_id . '-' . $account_id,
                     'priority'    => 10,
                  ];
               }

               //increment the page
               Option::set("action:import_order:{$account_id}:page", $page+1);

               Module_Task::update_entries($tasks);

               $result = false;
            }
         }
      }

      if($result){
         Option::set('action:import_order:reschedule', true);
      }

      return $result;
   }



   /**
    * Checks whether or not the given order is already processed.
    *
    * @param string $order_id
    * @param string $account_id
    * @return boolean
    */
   public static function is_order_processed($order_id, $account_id = ''){

      $meta_query = [
         [
            'key' => PREFIX . '_order_id',
            'value' => $order_id,
         ],
         [
            'key' => PREFIX . '_order_status',
            'value' => 'processed',
         ],
      ];

      if( ! empty($account_id) ){
         $meta_query[] = [
            'key' => PREFIX . '_account_id',
            'value' => $account_id,
         ];
      }

      $orders = wc_get_orders([
         'status' => array_merge(array_keys(wc_get_order_statuses()), ['trash']),
         'limit'  => 1,
         'return' => 'ids',
         'meta_query' => $meta_query,
      ]);

      if(empty($orders)){
         return false;
      }

      return true;
   }
}