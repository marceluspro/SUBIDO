<?php
/**
 * Module Worker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Worker{


   /**
    * Checks whether or not the process is locked.
    *
    * @return bool
    */
   public static function is_locked(){
      return Transient::get("worker:run:locked");
   }



   /**
    * Sets the flag to mark the process as locked.
    *
    * @return void
    */
   protected static function lock(){
      Transient::set("worker:run:locked", true, MINUTE_IN_SECONDS * 5);
   }



   /**
    * Removes the flag to unlock the process.
    *
    * @return void
    */
   protected static function unlock(){
      Transient::delete("worker:run:locked");
   }



   /**
    * Sets the current action that is being processed.
    *
    * @param string $action
    * @return void
    */
   protected static function set_current_action(string $action){
      Transient::set("worker:current_action", $action, MINUTE_IN_SECONDS * 5);
   }



   /**
    * Getd the current action that is being processed.
    *
    * @return string
    */
   public static function get_current_action(){
      return Transient::get("worker:current_action");
   }



   /**
    * Deletes the current action that has been processed.
    *
    * @return void
    */
   protected static function delete_current_action(){
      Transient::delete("worker:current_action");
   }



   /**
    * Sets the last action processed.
    *
    * @param int $index
    * @return void
    */
   protected static function set_last_processed($index){

      $index = $index > count(self::action_list()) ? 0 : $index - 1; //substract `1` to set the actually last action processed

      Transient::set('worker:last_processed', abs($index), HOUR_IN_SECONDS);
   }



   /**
    * Gets the last action processed.
    *
    * @return int
    */
   public static function get_last_processed(){
      return (int) Transient::get('worker:last_processed');
   }



   /**
    * Initiates instance of `Module_Worker_Action`.
    *
    * @param array $action
    * @return Module_Worker_Action
    */
   public static function action(array $action){
      return new Module_Worker_Action($action);
   }



   /**
    * Enables the given list of actions.
    *
    * @param array $list
    * @return void
    */
   public static function enable_actions($list){

      $list = array_filter( (array) $list );

      foreach($list as $action){
         $action = Module_Worker::action($action);
         $action->delete_prop('status');
      }
   }



   /**
    * Disables the given list of actions.
    *
    * @param array $list
    * @return void
    */
   public static function disable_actions($list){

      $list = array_filter( (array) $list );

      foreach($list as $action){
         $action = Module_Worker::action($action);
         $action->set_prop('status', 'inactive');
      }
   }



   /**
    * Loops through the action list and based on the action type either runs its callback or queris
    * the list of tasks and let 3rd-party to actually process each task.
    *
    * @return void
    */
   public function run(){

      $start_time = microtime(true);

      if(self::is_locked()){
         return;
      }

      self::lock();

      $index   = 1;
      $timeout = false;

      foreach(self::action_list() as $item){

         $action = self::action($item);

         if( $timeout ){
            break;
         }

         if(
            $action->is_inactive() ||
            $action->is_scheduled() ||
            $index <= self::get_last_processed()
         ){
            $index++;
            continue;
         }

         $delete = [];
         $update = [];

         self::set_current_action($action->get_id());

         if(empty($action->get_callback())){

            $tasks = Module_Task::get_entries(['actions' => [$action->get_id()]]);

            if(empty($tasks)){

               Module_Task::delete_counted_action_tasks($action->get_id());

            }else{

               foreach($tasks as $task){

                  if( Util::is_time_exceeded($start_time) || Util::is_memory_exceeded() ){
                     $timeout = true;
                     break;
                  }

                  $task = apply_filters(PREFIX . '\worker\task', $task);

                  //consider processed to be deleted
                  if(empty($task)){

                     $processed = true;

                  }else{

                     $processed = apply_filters(PREFIX . '\worker\run\task', true, $task);
                  }

                  if($processed === true){

                     $delete[] = $task['id'];

                  }else{

                     $next_process_at = (int) $action->get_recurrence();

                     if(is_array($processed)){

                        /**
                         * Backward compatibility for tasks which still return `next_process_at`
                        * @since 2.1.0
                        */
                        if(array_key_exists('retry_after', $processed)){

                           $next_process_at = (int) $processed['retry_after'];

                        }elseif(array_key_exists('next_process_at', $processed)){

                           $next_process_at = (int) $processed['next_process_at'];

                        }
                     }

                     $update[] = [
                        'action'          => $task['action'],
                        'source'          => $task['source'],
                        'target'          => $task['target'],
                        'payload'         => $task['payload'],
                        'resource_id'     => $task['resource_id'],
                        'priority'        => $task['priority'],
                        'next_process_at' => $next_process_at,
                     ];

                  }

               }

               Module_Task::update_entries($update);
               Module_Task::delete_entries(['ids' => $delete]);
            }

         }else{

            call_user_func_array($action->get_callback(), [$item]);
         }

         $action->maybe_schedule();

         self::delete_current_action();

         //keep this for backward compatilbity - it will delete old flag for current action
         $action->delete_prop('current');

         $index++;

      }

      self::set_last_processed($index);

      self::unlock();

   }



   /**
    * The list of available actions based on the context.
    *
    * @param string $context
    * @return array
    */
   public static function action_list($context = ''){

      $list     = [];
      $raw_list = apply_filters(PREFIX . '\worker\action_list', []);

      //make sure the list has unique actions in the correct context
      foreach($raw_list as $value){
         if( empty($context) || ($context === Util::array($value)->get('context', ''))){
            $list[$value['id']] = $value;
         }
      }

      //sort by priority ASC
      usort($list, function($a, $b) {
         return $a['priority'] <=> $b['priority'];
      });

      return $list;
   }



   /**
    * Displays the list of actions that will be performed.
    *
    * @param string $context
    * @return string
    */
   public static function render($context = ''){

      echo Util::get_template('worker-ui.php', [
         'actions' => self::action_list($context),
      ], dirname(dirname(__FILE__)), 'worker/templates');
   }

}