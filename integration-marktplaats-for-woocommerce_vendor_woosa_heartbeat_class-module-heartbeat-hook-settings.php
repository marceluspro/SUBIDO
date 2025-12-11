<?php
/**
 * Module Heartbeat Hook Settings
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Heartbeat_Hook_Settings implements Interface_Hook_Settings_Tab{


   /**
    * The id of the tab.
    *
    * @return string
    */
   public static function id(){
      return 'heartbeat';
   }



   /**
    * The name of the tab.
    *
    * @return string
    */
   public static function name(){
      return __('Heartbeat', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The description of the tab.
    *
    * @return string
    */
   public static function description(){
      return __('External cron job', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The icon URL of the tab.
    *
    * @return string
    */
   public static function icon_url(){
      return file_get_contents(untrailingslashit(plugin_dir_path(__FILE__)) . '/assets/images/icons/heart-pulse.svg');
   }



   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('admin_init', [__CLASS__, 'maybe_init']);
   }



   /**
    * Initiates the section under a condition.
    *
    * @return void
    */
   public static function maybe_init(){

      $initiate = apply_filters(PREFIX . '\heartbeat\initiate', true);

      if($initiate){

         add_filter(PREFIX . '\module\settings\page\tabs', [__CLASS__, 'add_tab'], 30);
         add_filter(PREFIX . '\module\settings\page\content\fields\\' . self::id(), [__CLASS__, 'add_tab_fields']);
         add_action('woocommerce_admin_field_' . PREFIX .'_heartbeat_ui', [__CLASS__, 'output_section']);
         add_action( PREFIX . '\field_generator\render\\' . PREFIX .'_heartbeat_ui', [__CLASS__, 'output_section'] );

      }
   }



   /**
    * Adds the tab in the list.
    *
    * @param array $tabs
    * @return array
    */
   public static function add_tab(array $tabs){

      $tabs[self::id()] = [
         'name'        => self::name(),
         'description' => self::description(),
         'slug'        => self::id(),
         'icon'        => self::icon_url(),
      ];

      return $tabs;
   }



   /**
    * Adds the fields of the tab.
    *
    * @param array $items
    * @return array
    */
   public static function add_tab_fields(array $items){

      $items = array_merge([
         [
            'type' => 'title',
            'id'   => PREFIX . '_heartbeat_ui_start',
         ],
         [
            'id'   => PREFIX .'_heartbeat_ui_id',
            'type'    => PREFIX . '_heartbeat_ui',
         ],
         [
            'type' => 'sectionend',
            'id'   => PREFIX . '_heartbeat_ui_end',
         ],
      ], $items);

      return $items;
   }



   /**
    * Adds the submit button.
    *
    * @param array $items
    * @return array
    */
   public static function add_submit_button(array $items){

      $items = array_merge($items, [
         [
            'type' => 'title',
            'id'   => PREFIX . '_submit_button',
         ],
         [
            'id'   => PREFIX .'_save_settings',
            'type' => 'submit_button',
         ],
         [
            'type' => 'sectionend',
            'id'   => PREFIX . '_submit_button_end',
         ],
      ]);

      return $items;
   }



   /**
    * Useful in conjunction with the hook `woocommerce_admin_field_{$field}` to completely render a custom content in the section.
    *
    * @param array $values
    * @return void
    */
   public static function output_section($values) {
      Module_Heartbeat::render($values);
   }

}