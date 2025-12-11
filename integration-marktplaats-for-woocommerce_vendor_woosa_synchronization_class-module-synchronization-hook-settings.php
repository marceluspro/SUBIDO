<?php
/**
 * Module Synchronization Hook Settings
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Synchronization_Hook_Settings implements Interface_Hook_Settings_Tab{


   /**
    * The id of the tab.
    *
    * @return string
    */
   public static function id(){
      return 'synchronization';
   }



   /**
    * The name of the tab.
    *
    * @return string
    */
   public static function name(){
      return __('Synchronization', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The description of the tab.
    *
    * @return string
    */
   public static function description(){
      return apply_filters(PREFIX . '\module\synchronization\settings_tab_description', __('Import & sync products', 'integration-marktplaats-for-woocommerce'));
   }



   /**
    * The icon URL of the tab.
    *
    * @return string
    */
   public static function icon_url(){
      return apply_filters(PREFIX . '\module\synchronization\settings_tab_icon', file_get_contents(untrailingslashit(plugin_dir_path(dirname(__FILE__))) . '/util/assets/images/icons/rotate.svg'));
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

      $initiate = apply_filters(PREFIX . '\module\synchronization\initiate', true);

      if($initiate){

         add_filter(PREFIX . '\module\settings\page\tabs', [__CLASS__, 'add_tab'], 20);
         add_filter(PREFIX . '\module\settings\page\content\fields\\' . self::id(), [__CLASS__, 'add_tab_fields']);

         add_action(PREFIX . '\field_generator\render\\' . PREFIX . '_synchronization_ui', [__CLASS__, 'render_synchronization_ui']);
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
            'id'   => PREFIX . '_synchronization',
            'type' => 'title',
         ],
         [
            'name' => __( 'Key', 'integration-marktplaats-for-woocommerce' ),
            'id'   => PREFIX . '_synchronization_ui',
            'type' => PREFIX . '_synchronization_ui',
         ],
         [
            'id'   => PREFIX . '_synchronization_end',
            'type' => 'sectionend',
         ],
      ], $items);

      return $items;
   }



   /**
    * Renders the output of `synchronization_ui` field.
    *
    * @param array $values
    * @return string
    */
   public static function render_synchronization_ui($values){
      echo Util::get_template('synchronization-ui.php', [], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');
   }

}