<?php
/**
 * Module Product Task Hook Worker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Task_Hook_Worker implements Interface_Hook_Worker_Run_Task{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\worker\action_list', [__CLASS__, 'define_action_list']);
      add_filter(PREFIX . '\worker\run\task', [__CLASS__, 'process_task'], 10, 2);

   }



   /**
    * Defines the actions in the Worker's list.
    *
    * @param array $list
    * @return array
    */
   public static function define_action_list($list){

      $list = array_merge($list, Module_Product_Task::action_list());

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

      if('shop' === $target && in_array($action, Module_Product_Task::action_list('id'))){

         $payload = Module_Task::decode_payload( Util::array($task)->get('payload') );

         $product_task = new Module_Product_Task();
         $product_task->set_data($payload);
         $product_task->set_id( $product_task->get_id_from_data() );

         $GLOBALS[PREFIX . '_lock_product_change'] = 'yes';

         $processed = self::process_action($product_task, $action);

         /**
          * Let 3rd-party to hook into this only if there were fired events.
          * @param string $action
          * @param Module_Product_Task $product
          */
         if( ! empty($product_task->get_event_types()) ){
            do_action(PREFIX . '\product_task\process_task', $action, $product_task);
         }

         $GLOBALS[PREFIX . '_lock_product_change'] = 'no';
      }

      return $processed;

   }



   /**
    * Processes the entity based on the given action.
    *
    * @param Module_Product_Task $entity
    * @param string $action
    * @return bool|array
    */
   public static function process_action($entity, string $action){

      $processed = true;

      switch($action){

         case 'create_or_update_product':

            if($entity->get_id()){

               $entity->update();
               $entity->process_images();
               $entity->update_attributes();
               $entity->update_categories();
               $entity->update_metadata();

               $entity->maybe_update_parent();

            }else{

               $processed = $entity->maybe_process_parent();

               if( $processed ){

                  $entity->create();
                  $entity->process_images();
                  $entity->create_metadata();
                  $entity->create_attributes();
                  $entity->create_categories();
                  $entity->set_visibility('hidden');
                  $entity->set_type();

               }else{

                  return [
                     'retry_after' => MINUTE_IN_SECONDS
                  ];
               }

            }

            break;

         case 'assign_product_attribute':

            $processed = $entity->assign_attributes();

            if( ! $processed ){
               return [
                  'retry_after' => MINUTE_IN_SECONDS
               ];
            }

            break;

         case 'assign_product_category':

            $entity->assign_categories();

            break;

         case 'download_product_image':

            $entity->download_images();

            break;

         case 'update_product_stock':

            $processed = $entity->update_stock();

            if( ! $processed ){
               return self::maybe_retry_task($action);
            }

            break;

         case 'update_product_price':

            $processed = $entity->update_price();

            if( ! $processed ){
               return self::maybe_retry_task($action);
            }

            break;

         case 'delete_or_trash_product':

            if($entity->move_to_trash()){

               $entity->trash();

            }else{

               $entity->delete_images();
               $entity->delete();
            }

            break;

         case 'delete_shop_category':

            $processed = $entity->delete_shop_category();

            if( ! $processed ){
               return [
                  'retry_after' => DAY_IN_SECONDS
               ];
            }

            break;
      }

      return $processed;
   }



   /**
    * Decides whether or not the tasks should be rescheduled or not.
    * By default this reschedules the task by using the default time interval decided by Module Task.
    *
    * @param string $action
    * @return true|array
    */
   public static function maybe_retry_task(string $action){

      switch($action){

         case 'update_product_stock':
         case 'update_product_price':

            $tasks = Module_Task::count_action_tasks('create_or_update_product');

            //in case there are no tasks for creating or updating products then do not reschedule the task but consider it processed and let it be deleted
            if(empty($tasks)){
               return true;
            }

            //reschedule after a specific time interval
            return [
               'retry_after' => Module_Task::calculate_reschedule_time($action)
            ];

            break;
      }

      return []; //reschedule with the default time interval decided by Module Task

   }

}