<?php
/**
 * Module User Task Hook Worker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_User_Task_Hook_Worker implements Interface_Hook_Worker_Run_Task{


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
    * Defines the actions into Worker list.
    *
    * @param array $list
    * @return array
    */
   public static function define_action_list($list){

      $list = array_merge($list, Module_User_Task::action_list());

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

      $action  = Util::array($task)->get('action');
      $target  = Util::array($task)->get('target');

      if('shop' === $target && in_array($action, Module_User_Task::action_list('id'))){

         $payload = Module_Task::decode_payload( Util::array($task)->get('payload') );

         $user_task = new Module_User_Task();
         $user_task->set_data($payload);
         $user_task->set_id( $user_task->get_id_from_data() );

         $GLOBALS[PREFIX . '_lock_user_change'] = 'yes';

         self::process_action($user_task, $action);

         $GLOBALS[PREFIX . '_lock_user_change'] = 'no';
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

      switch($action){

         case 'create_or_update_user':

            if($entity->get_id()){

               $entity->update();
               $entity->update_metadata();

            }else{

               $entity->create();
               $entity->create_metadata();

            }

            break;

         case 'delete_user':

            $entity->delete();

            break;
      }
   }

}