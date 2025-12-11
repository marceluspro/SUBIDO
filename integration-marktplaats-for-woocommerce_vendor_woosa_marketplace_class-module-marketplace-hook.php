<?php
/**
 * Module Marketplace Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\settings\menu_icon', [__CLASS__, 'define_settings_menu_icon']);
      add_filter(PREFIX . '\module\settings\menu_position', [__CLASS__, 'define_settings_menu_position']);

      add_action('admin_init', [__CLASS__, 'disable_midlayer_hooks'], 99);

      add_filter(PREFIX . '\module\synchronization\settings_tab_description', [__CLASS__, 'define_synchronization_tab_description']);

      add_filter('is_protected_meta', [__CLASS__, 'hide_metadata'], 90, 3);
   }



   /**
    * Defines the SVG as Settings menu icon.
    *
    * @param string $icon
    * @return string
    */
   public static function define_settings_menu_icon($icon){

      $icon = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path fill="#a2aab2" d="M64 24C64 10.7 74.7 0 88 0h45.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H234.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H552c13.3 0 24 10.7 24 24s-10.7 24-24 24H263.7c-34.6 0-64.3-24.6-70.7-58.5l-51.6-271c-.7-3.8-4-6.5-7.9-6.5H88C74.7 48 64 37.3 64 24zM225.6 240H523.2c10.9 0 20.4-7.3 23.2-17.8L584.7 80H195.1l30.5 160zM192 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96zM24 96h80c13.3 0 24 10.7 24 24s-10.7 24-24 24H24c-13.3 0-24-10.7-24-24s10.7-24 24-24zm0 80h96c13.3 0 24 10.7 24 24s-10.7 24-24 24H24c-13.3 0-24-10.7-24-24s10.7-24 24-24zm0 80H136c13.3 0 24 10.7 24 24s-10.7 24-24 24H24c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/></svg>');

      return $icon;
   }



   /**
    * Defines the position of Settings menu.
    *
    * @param string $icon
    * @return string
    */
   public static function define_settings_menu_position($position){

      $position = 3;

      return $position;
   }



   /**
    * Disables some hooks of Midlayer module.
    *
    * @return void
    */
   public static function disable_midlayer_hooks(){

      remove_filter(PREFIX . '\authorization\connect', [Module_Midlayer_Hook_Authorization::class, 'connect_env'], 90);
      remove_filter(PREFIX . '\authorization\disconnect', [Module_Midlayer_Hook_Authorization::class, 'disconnect_env'], 90);
   }



   /**
    * Define the description of the `Synchronization` tab.
    *
    * @param string $desc
    * @return string
    */
   public static function define_synchronization_tab_description($desc){

      $desc =  __('Sync products & import orders', 'integration-marktplaats-for-woocommerce');

      return $desc;
   }



   /**
    * Hides all our metadata. This prevent overwriting value accidentally.
    *
    * @param bool $protected
    * @param string $meta_key
    * @param string $meta_type
    * @return bool
    */
   public static function hide_metadata($protected, $meta_key, $meta_type){

      if(strpos($meta_key, PREFIX . '_') !== false){
         $protected = true;
      }

      return $protected;
   }

}