<?php
/**
 * Authorization Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Authorization_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action(PREFIX . '\module\midlayer_oauth\initiate_oauth\url', [__CLASS__, 'define_init_oauth_request_url']);
      add_action(PREFIX . '\module\midlayer_oauth\refresh_acces_token\url', [__CLASS__, 'define_refresh_access_token_request_url']);
   }



   /**
    * Defines the URL where the requests of initiating oauth process will go.
    *
    * @param string $url
    * @return string
    */
   public static function define_init_oauth_request_url($url){

      $url = Module_Midlayer::base_url('/v2/oauth/login-page-uri');

      return $url;
   }



   /**
    * Defines the URL where the requests of refreshing the access token will go.
    *
    * @param string $url
    * @return string
    */
   public static function define_refresh_access_token_request_url($url){

      $url = Module_Midlayer::base_url('/v2/oauth/refresh');

      return $url;
   }


}
