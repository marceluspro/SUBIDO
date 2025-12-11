<?php
/**
 * Module Midlayer OAuth
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_OAuth{


   /**
    * Saves API credentials.
    *
    * @param array $data
    * @return void
    */
   public static function save_api_credentials(array $data){

      $data['authorized_at'] = time();

      Option::set('api_credentials', $data);
   }



   /**
    * Retrieves API credentials.
    *
    * @return array
    */
   public static function get_api_credentials(){
      return Option::get('api_credentials', []);
   }



   /**
    * Deletes API credentials.
    *
    * @return void
    */
   public static function delete_api_credentials(){
      Option::delete('api_credentials');
   }



   /**
    * Retrieves the access token.
    *
    * @return string
    */
   public static function get_access_token(){

      $api_credentials = Module_Midlayer_OAuth::get_api_credentials();
      $access_token    = Util::array($api_credentials)->get('access_token');
      $refresh_token   = Util::array($api_credentials)->get('refresh_token');

      if(self::is_access_token_expired()){

         $url = apply_filters(PREFIX . '\module\midlayer_oauth\refresh_acces_token\url', Module_Midlayer::base_url('/oauth/refresh'));
         $response = Request::POST([
            'use_signature' => true,
            'body' => json_encode([
               'refresh_token' => $refresh_token
            ])
         ])->send($url);

         if( 200 === $response->status ){

            $api_credentials = Util::obj_to_arr($response->body);
            $access_token    = Util::array($api_credentials)->get('access_token');

            Module_Midlayer_OAuth::save_api_credentials($api_credentials);
         }

      }

      return $access_token;
   }



   /**
    * Checks whether or not the access token has expired.
    *
    * @return boolean
    */
   public static function is_access_token_expired(){

      $api_credentials = Module_Midlayer_OAuth::get_api_credentials();
      $expires_in      = (int) Util::array($api_credentials)->get('expires_in');
      $authorized_at   = (int) Util::array($api_credentials)->get('authorized_at');

      return (time() - $authorized_at) >= $expires_in;
   }
}