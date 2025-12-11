<?php
/**
 * Module Midlayer API
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_API{


   const IP_ADDRESS_LIST_TOKEN = 'e6ddf9e3-9b20-4d5b-9c2a-c983bca308e6';


   /**
    * Sends the activation state.
    *
    * @return object
    */
   public static function send_activation_state(){

      $request = Request::POST([
         'use_signature' => true,
      ]);

      $request->send( Module_Midlayer::base_url('plugin-activation', false) );

      return $request->get_response();
   }



   /**
    * Sends the deactivation state.
    *
    * @return object
    */
   public static function send_deactivation_state(){

      $request = Request::DELETE([
         'use_signature' => true,
      ]);

      $request->send( Module_Midlayer::base_url('plugin-activation', false) );

      return $request->get_response();
   }



   /**
    * Sends the uninstalling state.
    *
    * @return object
    */
   public static function send_uninstalling_state(){

      $request = Request::DELETE([
         'use_signature' => true,
      ]);

      $request->send( Module_Midlayer::base_url('plugin', false) );

      return $request->get_response();
   }



   /**
    * Sends the registration request.
    *
    * @param string $key
    * @param string $secret
    * @param string $auth
    * @param bool $use_woosa_secret
    * @return object
    */
   public static function register($key, $secret, $auth = 'query_string', $use_woosa_secret = false){

      $body = [
         "url"     => untrailingslashit(home_url('/', 'https')),
         "key"     => $key,
         "secret"  => $secret,
         "version" => "wc/v3",
         "auth"    => $auth
      ];

      $shop_instance_changed = Util::string_to_bool(Option::get('midlayer:shop_instance_changed', 'no'));

      if ( ! $shop_instance_changed || $use_woosa_secret ) {
         $body['woosa_secret'] = Option::get('woosa_secret', '', false);
      }

      $response = Request::POST([
         'body' => json_encode($body),
      ])->send(Module_Midlayer::base_url('shops', false));

      if(// if we get an error on register woosa secret invalid
         $response->status === 401
         && !empty($response->body->message)
         && 'Woosa secret key is invalid' === $response->body->message
      ) {

         // initial try, use auth query string
         if (!$use_woosa_secret) {
            $response = self::register($key, $secret, 'query_string', true);
         }

         //try with basic auth
         if($response->status != 204 && 'query_string' === $auth) {
            $response = self::register($key, $secret, 'basic', true);
         }

         //try with oauth
         if($response->status != 204 && 'basic' === $auth) {
            $response = self::register($key, $secret, 'oauth', true);
         }

      } else {// status not 401 (and error) but not 204

         //try with basic auth
         if($response->status != 204 && 'query_string' === $auth) {
            $response = self::register($key, $secret, 'basic');
         }

         //try with oauth
         if($response->status != 204 && 'basic' === $auth) {
            $response = self::register($key, $secret, 'oauth');
         }

      }

      return $response;
   }



   /**
    * Sends the connection request.
    *
    * @return object
    */
   public static function connect(){

      $request = Request::POST([
         'use_signature' => true,
      ]);

      $request->send(Module_Midlayer::base_url('connection'));

      return $request->get_response();
   }



   /**
    * Sends the disconnection request.
    *
    * @return object
    */
   public static function disconnect(){

      $request = Request::DELETE([
         'use_signature' => true,
      ]);

      $request->send(Module_Midlayer::base_url('connection'));

      return $request->get_response();
   }



   /**
    * Sends the plugin heartbeat.
    *
    * @return object
    */
   public static function plugin_heartbeat() {

      $request = Request::POST([
         'use_signature' => true,
      ]);

      $request->send(Module_Midlayer::base_url('plugin-heartbeat', false));

      return $request->get_response();
   }



   /**
    * Gets the ip whitelist.
    *
    * @return array
    */
   public static function get_ip_whitelist() {

      $request = Request::GET([
         'cache' => true
      ]);

      $response = $request->send(
         add_query_arg(
            'token',
            self::IP_ADDRESS_LIST_TOKEN,
            Module_Midlayer::base_url('ip-address-list', false, false)
         )
      );

      if (200 === $response->status) {
         return $response->body;
      }

      return [];
   }



   /**
    * Create the ping cron job for the shop
    *
    * @return object
    */
   public static function create_shop_cron_job() {

      $request = Request::POST([
         'use_signature' => true,
         'body' => json_encode([
            "woocommerce_path" => str_replace(home_url(), '', get_rest_url(null, '/woosa-heartbeat/perform')),
            "schedule" => "* * * * *",
         ]),
      ]);

      $request->send(Module_Midlayer::base_url('ping-shop-cron-job', false));

      return $request->get_response();
   }



   /**
    * Delete the ping cron job for the shop
    *
    * @return object
    */
   public static function delete_shop_cron_job() {

      $request = Request::DELETE([
         'use_signature' => true,
      ]);

      $request->send(Module_Midlayer::base_url('ping-shop-cron-job', false));

      return $request->get_response();
   }

}