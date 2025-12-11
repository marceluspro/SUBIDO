<?php
/**
 * Module Heartbeat REST API Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Heartbeat_REST_API_Hook implements Interface_Hook_Register_REST_API_Endpoints{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('rest_api_init', [__CLASS__, 'register_endpoints']);
   }



   /**
    * Registers endpoints.
    *
    * @return void
    */
   public static function register_endpoints() {

      // backward compatability
      register_rest_route(
         PREFIX . '/heartbeat',
         '/perform',
         [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'process_perform_request' ],
            'permission_callback' => '__return_true',
         ]
      );

      register_rest_route(
         'woosa-heartbeat',
         '/perform',
         [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'process_perform_request' ],
            'permission_callback' => '__return_true',
         ],
         true
      );

   }



   /**
    * Processes the requests received.
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
    public static function process_perform_request($request){

      $response = new \WP_Error( 'endpoint_disabled', 'This endpoint is currently disabled.', ['status' => 403] );

      if('yes' === Option::get('use_external_cronjob', 'no')){

         $response_string = "ok";

         if ( isset( $_GET['check-cache'] ) ) {
            $response_string .= " - " . time();
         }

         $response = new \WP_REST_Response( $response_string, 200 );
         $response->set_headers( wp_get_nocache_headers() );

         Module_Heartbeat::perform();
      }

      return $response;
   }

}