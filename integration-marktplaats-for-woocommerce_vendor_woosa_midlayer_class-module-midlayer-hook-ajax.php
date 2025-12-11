<?php
/**
 * Module Midlayer Hook AJAX
 *
 * This class is responsible for processing AJAX calls.
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_process_registration', [__CLASS__, 'process_registration']);

   }



   /**
    * Progress the request to register the shop.
    *
    * @return string
    */
   public static function process_registration() {

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $response = Module_Midlayer::register_shop();

      if(is_wp_error( $response )){
         wp_send_json_error([
            'message' => $response->get_error_message()
         ]);
      }

      if ($response->status == 204) {
         wp_send_json_success();
      }

      $message = empty($response->body->message) ? __('The shop could not be registered. Check if the permalinks are not set on Plain or check if your shop is public and accessible then try again.', 'integration-marktplaats-for-woocommerce') : $response->body->message;

      wp_send_json_error([
         'message' => $message
      ]);

   }
}