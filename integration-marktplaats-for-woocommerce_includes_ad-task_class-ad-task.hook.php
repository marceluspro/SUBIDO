<?php
/**
 * Ad Task Hook Worker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;



//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Ad_Task_Hook_Worker implements Interface_Hook_Worker_Run_Task{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\worker\run\task', [__CLASS__, 'process_task'], 10, 2);

      //remove import_order action
      remove_filter(PREFIX . '\worker\action_list', [Module_Marketplace_Order_Hook::class, 'define_action_list']);
   }



   /**
    * Processes the task.
    *
    * @param bool|array $processed
    * @param array $task
    * @return bool
    */
   public static function process_task($processed, array $task){

      $action = Util::array($task)->get('action');
      $target = Util::array($task)->get('target');

      if('service' === $target && in_array($action, Ad_Task::action_list())){

         $payload = Module_Task::decode_payload( Util::array($task)->get('payload') );
         $ad_task = new Ad_Task($payload);

         self::process_action($ad_task, $action);
      }

      return $processed;
   }



   /**
    * Processes the entity based on the given action.
    *
    * @param $entity
    * @param string $action
    * @return bool|array
    */
   public static function process_action($entity, string $action){

      $processed = true;

      switch($action){

         case 'create_or_update_product':

            if (empty($entity->get_remote_id())) {

               $entity->create();

            } else {

               $entity->update();
            }

            break;

         case 'pause_or_unpause_product':

            $entity->update( ! $entity->is_on_hold() );

            break;

         case 'delete_or_trash_product':

            $entity->delete();

            break;

         case 'upload_product_content':

            $entity->upload_content();

            break;

      }

      return $processed;
   }

}