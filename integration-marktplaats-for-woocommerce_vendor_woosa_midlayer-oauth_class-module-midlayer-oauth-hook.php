<?php
/**
 * Module Midlayer OAuth Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_OAuth_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\authorization\before_process_authorization', [__CLASS__, 'initiate_oauth_process'], 10, 2);
      add_filter(PREFIX . '\module\authorization\before_process_authorization', [__CLASS__, 'delete_api_credentials'], 10, 2);
   }



   /**
    * Initiates the OAuth process via ML.
    *
    * @param string $action
    * @param Module_Authorization $ma
    * @return string
    */
   public static function initiate_oauth_process($action, $ma){

      if('authorize' !== $action){
         return;
      }

      $url = apply_filters(PREFIX . '\module\midlayer_oauth\initiate_oauth\url', Module_Midlayer::base_url('/oauth/login-page-uri'));
      $response = Request::GET([
         'use_signature' => true,
         'cache' => false
      ])->send($url);

      if( 200 !== $response->status ){

         wp_send_json_error([
            'message' => $ma->get_formatted_error($response->body->message),
         ]);
      }

      wp_send_json_success([
         'redirect_url' => $response->body->uri,
      ]);
   }



   /**
    * Removes API credentials when the access is revoked.
    *
    * @param string $action
    * @param Module_Authorization $ma
    * @return string
    */
   public static function delete_api_credentials($action, $ma){

      if('revoke' !== $action){
         return;
      }

      Module_Midlayer_OAuth::delete_api_credentials();
   }

}