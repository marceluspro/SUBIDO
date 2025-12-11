<?php
/**
 * Module Midlayer OAuth REST API Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_OAuth_REST_API_Hook implements Interface_Hook_Register_REST_API_Endpoints{


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

      $basename = Module_Midlayer::service();

      register_rest_route( 'wc/v3', "{$basename}/authorization/status", [
         'methods' => 'GET',
         'callback' => [__CLASS__, 'handle_oauth_result'],
         'permission_callback' => '__return_true',
      ]);

   }



   /**
    * Handles the OAth result.
    *
    * @param \WP_REST_Request $request
    * @return string|void
    */
   public static function handle_oauth_result($request){

      $oauth_status  = $request->get_param('oauth_status');
      $access_token  = $request->get_param('access_token');
      $refresh_token = $request->get_param('refresh_token');
      $expires_in    = $request->get_param('expires_in');
      $message       = $request->get_param('message');

      if( empty($oauth_status) || ! in_array($oauth_status, ['success', 'error']) ){
         return new \WP_Error( 'invalid_parameter', 'The parameter `oauth_status` has invalid value.', [ 'status' => 404 ] );
      }

      $ma = new Module_Authorization();
      $ma->set_env($ma->get_env());

      if('success' === $oauth_status){

         if(empty($access_token) || empty($refresh_token) || empty($expires_in)){

            $ma->set_as_unauthorized();
            $ma->set_error('The application token is missing.');

            return new \WP_Error( 'invalid_parameter', 'One of the parameters `access_token`, `refresh_token` or `expires_in` has invalid value.', [ 'status' => 404 ] );

         }else{

            Module_Midlayer_OAuth::save_api_credentials([
               'access_token'  => $access_token,
               'refresh_token' => $refresh_token,
               'expires_in'    => $expires_in,
            ]);

            $ma->set_as_authorized();
            $ma->delete_error();
         }

      }else{

         $ma->set_as_unauthorized();
         $ma->set_error($message);
      }

      wp_redirect(add_query_arg([
         'tab' => 'authorization',
      ], Module_Settings::get_page_url()));

      exit;
   }

}