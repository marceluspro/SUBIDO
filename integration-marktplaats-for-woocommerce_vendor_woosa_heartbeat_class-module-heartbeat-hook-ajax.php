<?php
/**
 * Module Authorization Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Heartbeat_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_'.PREFIX.'_process_heartbeat', [__CLASS__, 'process_heartbeat']);

   }



   /**
    * Processes the authorization.
    *
    * @return void
    */
   public static function process_heartbeat(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $action  = Util::array($_POST)->get('args/action');
      $perform = Module_Heartbeat::toggle_cron_job_status($action);

      if( ! Util::array($perform)->get('success') ){
         wp_send_json_error([
            'message' => Util::array($perform)->get('message', 'An error has occurred, check logs!'),
         ]);
      }

      wp_send_json_success();
   }
}
