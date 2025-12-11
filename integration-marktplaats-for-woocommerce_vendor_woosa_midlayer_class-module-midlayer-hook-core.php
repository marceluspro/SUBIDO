<?php
/**
 * Module Midlayer Core Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_Hook_Core implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action(PREFIX . '\core\state\activated', [__CLASS__, 'notify_activation']);
      add_action(PREFIX . '\core\state\deactivated', [__CLASS__, 'notify_deactivation']);
      add_action(PREFIX . '\core\state\upgraded', [__CLASS__, 'notify_upgrade']);
      add_action(PREFIX . '\core\state\uninstalled', [__CLASS__, 'notify_uninstalling']);
      add_action(PREFIX . '\core\invalid_instance', [__CLASS__, 'reset_registration'], 10, 3);
   }



   /**
    * Notifies when activation event is triggered.
    *
    * @return void
    */
    public static function notify_activation(){

      if(Module_Midlayer::is_shop_registered()){
         Module_Midlayer_API::send_activation_state();
      }
   }



   /**
    * Notifies when deactivation event is triggered.
    *
    * @return void
    */
   public static function notify_deactivation(){

      if(Module_Midlayer::is_shop_registered()){
         Module_Midlayer_API::send_deactivation_state();
      }
   }



   /**
    * Notifies when upgrade event is triggered.
    *
    * @return void
    */
   public static function notify_upgrade(){

      if(Module_Midlayer::is_shop_registered()){

         //send deactivation immediately since we have here the old plugin version
         Module_Midlayer_API::send_deactivation_state();

         //schedule activation for later because the new plugin version is available after the old one is replaced
         $as = new Module_Action_Scheduler();
         $as->set_group('midlayer');
         $as->set_hook('deliver_activation_state');
         $as->set_callback([Module_Midlayer_API::class, 'send_activation_state']);
         $as->set_single();
         $as->save();
      }
   }



   /**
    * Notifies when uninstalling event is triggered.
    *
    * @return void
    */
   public static function notify_uninstalling(){

      if(Module_Midlayer::is_shop_registered()){
         Module_Midlayer_API::send_uninstalling_state();
      }
   }



   /**
    * Resets the shop registration when the plugin instance has changed.
    *
    * @param array $instance
    * @param string $url
    * @param string $domain
    * @return void
    */
   public static function reset_registration($instance, $url, $domain){

      $user_id = username_exists( Module_Midlayer::get_user_api_name() );

      if( ! function_exists('wp_delete_user') ){
         require_once ABSPATH . 'wp-admin/includes/user.php';
      }

      wp_delete_user( $user_id );

      Option::set('midlayer:shop_instance_changed', 'yes');
   }

}