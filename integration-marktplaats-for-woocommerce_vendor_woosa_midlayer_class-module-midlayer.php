<?php
/**
 * Module Midlayer
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer{


   /**
    * Username used for API key user.
    *
    * @var string
    */
   protected static $user_api_name = 'woosa_midlayer';


    /**
     * Email used for API key user
     *
     * @var string
     */
   protected static $user_api_email = 'woosa_midlayer@woosa.nl';



   /**
    * Retrieves the user name.
    *
    * @return string
    */
   public static function get_user_api_name(){
      return self::$user_api_name;
   }



   /**
    * Retrieves the user email.
    *
    * @return string
    */
   public static function get_user_api_email(){
      return self::$user_api_email;
   }



   /**
    * The service slug (vida-xl, big-buy, etc).
    *
    * @return string
    */
   public static function service($add_version = false){

      $result = Module_Core::config('service.slug', null, false);

      if($add_version){
         $result .= '-v' . substr(VERSION, 0, 1);
      }

      return $result;
   }



   /**
    * Base URL
    *
    * @param string $endpoint
    * @param bool $use_service - whether or not the endpoint should contain the service
    * @param bool $use_prefix - whether or not the endpoint should contain the prefix
    * @return string
    */
   public static function base_url($endpoint, $use_service = true, $use_prefix = true){

      $prefix = 'woocommerce';

      if($use_service){
         $endpoint = self::service() .'/'. ltrim($endpoint, '/');
      }

      if($use_prefix){
         $endpoint = $prefix .'/'. ltrim($endpoint, '/');
      }

      if( defined('\WOOSA_TEST') && \WOOSA_TEST ) return 'https://midlayer-dev.woosa.nl/'.ltrim($endpoint, '/');

      if( defined('\WOOSA_STA') && \WOOSA_STA ) return 'https://midlayer-sta.woosa.nl/'.ltrim($endpoint, '/');

      return 'https://midlayer.woosa.nl/'.ltrim($endpoint, '/');
   }



   /**
    * How long the request connection should stay open in seconds. Default 30.
    *
    * @return int
    */
   public static function timeout(){

      $default_time = 30;
      $exec_time    = ini_get('max_execution_time');
      $value        = $exec_time > $default_time ? $exec_time : $default_time;

      return $value;
   }



   /**
    * List of request headers
    *
    * @param array $items
    * @return array
    */
   public static function headers(array $items = []){

      $default = apply_filters(PREFIX . '\midlayer\headers', [
         'x-woosa-domain'           => parse_url(home_url(), PHP_URL_HOST),
         'x-woosa-license'          => Option::get('license_key', ''),
         'x-woosa-plugin-version'   => VERSION,
         'x-woosa-plugin-slug'      => DIR_NAME,
         'x-woosa-plugin-name'      => self::service(true),
         'x-woosa-marketplace-name' => self::service(),
      ]);

      $default['x-woosa-domain'] = strtolower($default['x-woosa-domain'] ?? '');

      return array_merge($items, $default);
   }



   /**
    * Generates WC API keys.
    *
    * @return array|\WP_Error
    */
   protected static function generate_wc_api_keys(){

      global $wpdb;

      $user_id         = username_exists( self::$user_api_name );
      $permissions     = 'read_write';
      $consumer_key    = 'ck_' . wc_rand_hash();
      $consumer_secret = 'cs_' . wc_rand_hash();
      $description     = __('Woosa Midlayer', 'integration-marktplaats-for-woocommerce');

      if ( ! $user_id and email_exists( self::$user_api_email ) == false ) {

         $password = wp_generate_password( $length=12, $include_standard_special_chars=false );
         $user_id  = wp_create_user( self::$user_api_name, $password, self::$user_api_email );

         if(is_wp_error( $user_id )){

            Util::log()->error([
               'error' => [
                  'message' => $user_id->get_error_message(),
                  'code'    => $user_id->get_error_code(),
               ],
               'detail' => [
                  'user_name' => self::$user_api_name,
                  'email'     => self::$user_api_email,
                  'password'  => $password,
               ]
            ], __FILE__, __LINE__);

            return new \WP_Error('invalid_wc_api_user', __('The user that is necessary for creating WooCommerce API key & secret could not be created. Check logs.', 'integration-marktplaats-for-woocommerce'));
         }

         $user = new \WP_User($user_id);
         $user->set_role('administrator');
      }

      $result = $wpdb->insert(
         $wpdb->prefix . 'woocommerce_api_keys',
         [
            'user_id'         => $user_id,
            'description'     => $description,
            'permissions'     => $permissions,
            'consumer_key'    => wc_api_hash( $consumer_key ),
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr( $consumer_key, -7 ),
         ],
         [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
         ]
      );

      if( $result ){

         return [
            'key' => $consumer_key,
            'secret' => $consumer_secret
         ];

      }

      Util::log()->error([
         'error' => [
            'message' => $wpdb->last_error,
         ],
         'detail' => [
            'query' => $wpdb->last_query,
         ]
      ], __FILE__, __LINE__);

      return new \WP_Error('invalid_wc_api_credentials', __('The WooCommerce API key & secret could not be created. Check logs.', 'integration-marktplaats-for-woocommerce'));
   }



   /**
    * Check if is WC API Keys page.
    *
    * @return boolean
    */
   public static function is_wc_api_keys_page() {
      return isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'advanced' === $_GET['tab'] && 'keys' === $_GET['section'];
   }



   /**
    * Check if the shop is registered
    *
    * @return boolean
    */
   public static function is_shop_registered() {

      global $wpdb;

      $output = true;
      $user_id = username_exists( self::$user_api_name );

      //check if woosa_midlayer exists
      if ( $user_id and email_exists( self::$user_api_email ) ) {

         //check if woosa_midlayer username has a valid API key
         $api_key = $wpdb->get_row(
            $wpdb->prepare(
               "SELECT key_id, description, permissions, truncated_key, last_access
               FROM {$wpdb->prefix}woocommerce_api_keys
               WHERE user_id = %d",
               $user_id
            )
         );

         if ( empty( $api_key ) ) {

            $output = false;

         } else {

            //check if the last 7 characters of the key are saved and/or are valid
            $key_checker = Option::get('woosa_truncated_api_key', false, false);

            if ( empty( $key_checker ) ) {
               //the last 7 characters aren't saved, save them now
               Option::set('woosa_truncated_api_key', $api_key->truncated_key, false, false);
            } else {
               //verify that the keys saved when creating the midlayer request are the same with the current ones.
               if ( $api_key->truncated_key !== $key_checker ) {
                  $output = false;
               }
            }
         }

      } else {

         $output = false;
      }

      return $output;

   }



   /**
    * Registers the shop.
    *
    * @return object|\WP_Error
    */
   public static function register_shop(){

      $wc_api = self::generate_wc_api_keys();

      if(is_wp_error( $wc_api )){
         return $wc_api;
      }

      $api_key    = Util::array($wc_api)->get('key');
      $api_secret = Util::array($wc_api)->get('secret');

      //make sure all filters are removed
      remove_all_filters( 'home_url' );

      $response = Module_Midlayer_API::register($api_key, $api_secret);

      if( $response->status != 204 ) {

         $user_id = username_exists( self::$user_api_name );

         wp_delete_user( $user_id );

         Option::set('woosa_truncated_api_key', 'revoked', false, false);
      }

      if($response->status == 204) {

         Option::set('woosa_truncated_api_key', substr( $api_key, -7 ), false, false);

         Option::delete('midlayer:shop_instance_changed');

         Module_Core::set_instance();

         $as = new Module_Action_Scheduler();
         $as->set_group('midlayer');
         $as->set_hook('send_activation_state');
         $as->set_callback([Module_Midlayer_API::class, 'send_activation_state']);
         $as->set_single();
         $as->save();
      }

      return $response;

   }



   /**
    * Revoke the access to the service for all environments.
    *
    * @return void
    */
   public static function disconnect_shop(){

      $envs = ['live', 'test'];

      foreach($envs as $env){

         $ma = new Module_Authorization();
         $ma->set_env($env);

         if($ma->is_authorized()){
            $ma->disconnect();
         }
      }

   }


}