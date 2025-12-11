<?php
/**
 * Module Action Scheduler Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Action_Scheduler_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('init', [__CLASS__, 'process_action_list']);

      add_action(PREFIX . '\core\state\deactivated', [__CLASS__, 'remove_action_list']);
      add_action(PREFIX . '\authorization\access_revoked', [__CLASS__, 'remove_action_list']);
   }



   /**
    * Define the hooks for all actions.
    *
    * @return void
    */
   public static function process_action_list(){

      foreach(Module_Action_Scheduler::get_actions() as $hook => $callback){

         if ( class_exists( $callback[0] ) && method_exists( $callback[0], $callback[1] ) ) {

            $class    = new $callback[0];//init the class
            $function = $callback[1];

            add_action($hook, [$class, $function], 10, 10);
         }

      }
   }



   /**
    * Removes all actions.
    *
    * @return void
    */
   public static function remove_action_list(){
      Module_Action_Scheduler::unschedule_actions();
   }
}