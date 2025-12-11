<?php
/**
 * Module Worker Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Worker_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('woosa\heartbeat\perform', [__CLASS__, 'maybe_run']);

      //DEPERECATED - we still keep it because of Hearbeat module v3 but in the future should be removed
      add_action(PREFIX . '\heartbeat\perform', [__CLASS__, 'maybe_run']);

      add_filter(PREFIX . '\module\tools\allow_long_run_requests\hidden', '__return_false');
   }



   /**
    * In case the Heartbeat is used then it will trigger the Worker.
    *
    * @return void
    */
   public static function maybe_run(){

      $worker = new Module_Worker();
      $worker->run();

   }

}