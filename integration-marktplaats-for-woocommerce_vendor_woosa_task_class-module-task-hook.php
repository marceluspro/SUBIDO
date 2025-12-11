<?php
/**
 * Module Task Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Task_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action(PREFIX . '\core\state\activated', [Module_Task::class, 'create_table']);
      add_action(PREFIX . '\core\state\uninstalled', [__CLASS__, 'delete_table']);

      add_action('init', [__CLASS__, 'delete_old_entries']);
   }



   /**
    * In case the `remove config` setting is enabled then delete DB table.
    *
    * @return void
    */
   public static function delete_table(){

      if('yes' === Option::get('remove_config')){
         Module_Task::delete_table();
      }
   }



   /**
    * Removes tasks which are old.
    *
    * @return void
    */
   public static function delete_old_entries(){

      $cleaned = Transient::get('task:old_cleaned');

      if( empty($cleaned) ){

         $start_time = microtime(true);

         Module_Task::create_table();

         do{

            $deleted = Module_Task::delete_old_entries();

            if($deleted === 0){
               Transient::set('task:old_cleaned', 'yes', DAY_IN_SECONDS);
            }

         }while( $deleted > 0 && ! Util::is_time_exceeded($start_time) && ! Util::is_memory_exceeded());
      }
   }
}