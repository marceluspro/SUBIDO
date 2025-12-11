<?php
/**
 * Module Order Task Hook Worker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Task_Hook_Worker implements Interface_Hook_Worker_Run_Task{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\worker\action_list', [__CLASS__, 'define_action_list']);
      add_filter(PREFIX . '\worker\run\task', [__CLASS__, 'process_task'], 10, 2);

      add_filter('woocommerce_order_data_store_cpt_get_orders_query', [__CLASS__, 'add_meta_query_support'], 10, 2);
   }



   /**
    * Defines the actions into Worker list.
    *
    * @param array $list
    * @return array
    */
   public static function define_action_list($list){

      $list = array_merge($list, Module_Order_Task::action_list());

      return $list;
   }



   /**
    * Processes the task with the target `shop`.
    *
    * @param bool|array $processed
    * @param array $task
    * @return bool
    */
   public static function process_task($processed, array $task){

      $action = Util::array($task)->get('action');
      $target = Util::array($task)->get('target');

      if('shop' === $target && in_array($action, Module_Order_Task::action_list('id'))){

         $payload = Module_Task::decode_payload( Util::array($task)->get('payload') );

         $order_task = new Module_Order_Task();
         $order_task->set_data($payload);
         $order_task->set_id( $order_task->get_id_from_data() );

         $GLOBALS[PREFIX . '_lock_order_change'] = 'yes';

         self::process_action($order_task, $action);

         $GLOBALS[PREFIX . '_lock_order_change'] = 'no';

      }

      return $processed;

   }



   /**
    * Processes the entity based on the given action.
    *
    * @param object $entity
    * @param string $action
    * @return void
    */
   public static function process_action($entity, string $action){

      switch($action){

         case 'create_or_update_order':

            if($entity->get_id()){

               $entity->update();
               $entity->update_metadata();
               $entity->process_line_items();
               $entity->save();

            }else{

               $entity->create();
               $entity->create_metadata();
               $entity->create_fee_lines();
               $entity->create_shipping_lines();
               $entity->process_line_items();
               $entity->save();
            }

            break;

         case 'delete_order':

            $entity->delete();

            break;
      }
   }



   /**
    * Adds support for `meta_query` when using `wc_get_orders()`. Needed for legacy orders post type.
    *
    * @param array $query
    * @param array $query_vars
    * @return array
    */
   public static function add_meta_query_support($query, $query_vars){

      if(empty($query_vars['meta_query'])) {
         return $query;
      }

      if(empty($query['meta_query'])){
         $query['meta_query'] = $query_vars['meta_query'];
      }else{
         $query['meta_query'] = array_merge($query['meta_query'], $query_vars['meta_query']);
      }

      return $query;
   }


}