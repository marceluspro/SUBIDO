<?php
/**
 * Module Midlayer Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\request\args', [__CLASS__, 'add_extra_request_args'], 90, 2);

      add_filter(PREFIX . '\module\settings\page\content\fields\dashboard', [__CLASS__, 'add_extra_wc_general_fields'], 99);
      add_filter('woocommerce_settings-general', [__CLASS__, 'add_woosa_secret_field'], 99);

      add_action('deleted_user', [__CLASS__, 'wp_user_deleted' ], 10, 3);
      add_action('admin_init', [__CLASS__, 'wc_api_keys_revoked'], 0);

      add_filter(PREFIX . '\logger\criteria_list', [__CLASS__, 'permalinks_check_criteria_list']);

      add_filter(PREFIX . '\module\tools\ip-whitelist', [__CLASS__, 'tools_ip_whitelist']);

      add_filter(PREFIX . '\worker\action_list', [__CLASS__, 'define_action_list']);

      add_filter('woosa\module\heartbeat\toggle_cron_job_status', [__CLASS__, 'process_heartbeat_cron_job_status'], 10, 3);
   }



   /**
    * Adds extra arguments for remote requests.
    *
    * @param array $args
    * @param string $url
    * @return array
    */
   public static function add_extra_request_args($args, $url){

      $args = array_merge($args, [
         'headers' => Module_Midlayer::headers( Util::array($args)->get('headers', []) ),
         'timeout' => Module_Midlayer::timeout(),
      ]);

      return $args;
   }



   /**
    * Adds woosa_secret field for dashboard settings.
    *
    * @param array $fields
    * @return array $fields
    */
   public static function add_extra_wc_general_fields( $fields ) {

      $found = array_search('woosa_secret', array_column($fields, 'id'));

      if($found === false){

         $_fields = [];

         foreach ($fields as $field) {

            if (PREFIX .'_settings_end' === $field['id']) {
               $_fields[] = [
                  'name'       => __( 'Shop secret key', 'integration-marktplaats-for-woocommerce' ),
                  'desc'       => __( 'This key is used to sign the requests to our servers.', 'integration-marktplaats-for-woocommerce' ),
                  'id'         => 'woosa_secret',
                  'option_key' => 'woosa_secret',
                  'type'       => 'password',
                  'default'    => Option::get('woosa_secret', false, false),
                  'class'      => 'always_disabled',
                  'custom_attributes' => [
                     'disabled' => 'disabled'
                  ]
               ];
            }

            $_fields[] = $field;
         }

         return $_fields;

      }

      return $fields;
   }



   /**
    * Add field woosa secret to use in REST API responses/updates
    *
    * @param $settings
    * @return mixed
    */
   public static function add_woosa_secret_field($settings) {

      $settings[] = [
         'name'       => __( 'Shop secret key', 'integration-marktplaats-for-woocommerce' ),
         'id'         => 'woosa_secret',
         'option_key' => 'woosa_secret',
         'type'       => 'password',
         'autoload'   => false,
         'custom_attributes' => [
            'disabled' => 'disabled'
         ]
      ];

      return $settings;
   }



   /**
    * Take actions when `woosa_midlayer` user is deleted
    *    - remove the last 7 characters from the woosa_truncated_api_key
    *    - disconnect shop
    *
    * @param int $user_id
    * @param int|null $reassign
    * @param \WP_User $user
    * @return void
    */
   public static function wp_user_deleted( $user_id, $reassign, $user ) {

      if ( 'woosa_midlayer' === $user->user_login ) {
         Option::set('woosa_truncated_api_key', 'revoked', false, false);
         Module_Midlayer::disconnect_shop();
      }

   }



   /**
    * Take actions when the WC API keys linked to `woosa_midlayer` user is revoked.
    *    - remove the last 7 characters from the woosa_truncated_api_key
    *    - disconnect shop
    *
    * @return void
    */
   public static function wc_api_keys_revoked() {

      global $wpdb;

      if ( Module_Midlayer::is_wc_api_keys_page() ) {

         if ( isset( $_REQUEST['revoke-key'] ) ) {

            check_admin_referer( 'revoke' );

            $key_id  = absint( $_REQUEST['revoke-key'] );
            $user_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}woocommerce_api_keys WHERE key_id = %d", $key_id ) );
            $user = get_user_by( 'id', $user_id );

            if ( $user ) {
               if ( 'woosa_midlayer' === $user->user_login ) {
                  Option::set('woosa_truncated_api_key', 'revoked', false, false);
                  Module_Midlayer::disconnect_shop();
               }
            }

         }
      }

   }



   /**
    * Runs the heartbeat.
    *
    * @return void
    */
   public static function run_heartbeat() {

      $hour = Option::get('midlayer_heartbeat_hour');

      if (empty($hour) && 0 !== $hour) {
         $hour = rand(0,23);
         Option::set('midlayer_heartbeat_hour', $hour);
      }

      $is_ready = (int) date('H') === (int) $hour;

      if( $is_ready && Module_Midlayer::is_shop_registered()){
         Module_Midlayer_API::plugin_heartbeat();
      }

      //mark for reschedule
      Option::set('action:midlayer_plugin_heartbeat:reschedule', true);

   }



   /**
    * Check the permalinks on admin init
    *
    * @param $items
    * @return array
    */
   public static function permalinks_check_criteria_list($items) {

      $items['permalinks_structure_warning'] = [
         'type'    => 'warning',
         'message' => __('We detected that your shop permalinks are set on Plain, please change it to any other option otherwise the plugin will not work properly!', 'integration-marktplaats-for-woocommerce'),
         'hook'    => 'admin_init',
         'active'  => empty(get_option( 'permalink_structure' )),
      ];

      return $items;
   }



   /**
    * Add the ip whitelist ip's from the midlayer in the tools section
    *
    * @param $ip_whitelist
    * @return array
    */
   public static function tools_ip_whitelist($ip_whitelist) {

      $midlayer_ip_whitelist = Module_Midlayer_API::get_ip_whitelist();

      if (!empty($midlayer_ip_whitelist)) {
         $ip_whitelist = array_merge($ip_whitelist, $midlayer_ip_whitelist);
      }

      return $ip_whitelist;
   }



   /**
    * Defines the list of actions for Worker module.
    *
    * @param array $list
    * @return array
    */
   public static function define_action_list($list){

      $list[] = [
         'id'         => 'midlayer_plugin_heartbeat',
         'priority'   => 999,
         'recurrence' => HOUR_IN_SECONDS,
         'callback'   => [__CLASS__, 'run_heartbeat'],
         'context'    => 'midlayer',
      ];

      return $list;

   }



   /**
    * Processes the Heartbeat action to create or delete the cron job.
    *
    * @param array $result
    * @param string $action
    * @param string $plugin_prefix
    * @return array
    */
   public static function process_heartbeat_cron_job_status(array $result, string $action, string $plugin_prefix){

      if(PREFIX !== $plugin_prefix){
         return $result;
      }

      //stop if not successfully
      if( ! $result['success']){
         return $result;
      }

      switch($action){

         case 'enable':

            $response = Module_Midlayer_API::create_shop_cron_job();

            if (204 !== $response->status) {
               $result = [
                  'success' => false,
                  'message' => $response->body->message
               ];
            }

            break;

         case 'disable':

            $response = Module_Midlayer_API::delete_shop_cron_job();

            if (204 !== $response->status) {
               $result = [
                  'success' => false,
                  'message' => $response->body->message
               ];
            }

            break;
      }

      return $result;
   }
}