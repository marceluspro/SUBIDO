<?php
/**
 * Module Worker Hook Settings
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Worker_Hook_Settings implements Interface_Hook_Settings_Tab{


   /**
    * The id of the tab.
    *
    * @return string
    */
   public static function id(){
      return 'action_list';
   }



   /**
    * The name of the tab.
    *
    * @return string
    */
   public static function name(){
      return __('Action List', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The description of the tab.
    *
    * @return string
    */
   public static function description(){
      return __('List of background actions', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The icon URL of the tab.
    *
    * @return string
    */
   public static function icon_url(){
      return file_get_contents(untrailingslashit(plugin_dir_path(__FILE__)) . '/assets/images/icons/list-check.svg');
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

      $initiate = apply_filters(PREFIX . '\worker\action_list\initiate', true);

      if($initiate){

         add_filter(PREFIX . '\module\settings\page\tabs', [__CLASS__, 'add_tab'], 99);
         add_filter(PREFIX . '\module\settings\page\content\fields\\' . self::id(), [__CLASS__, 'add_tab_fields']);

         add_action(PREFIX . '\field_generator\render\\' . PREFIX . '_worker_ui', [__CLASS__, 'render_worker_ui']);

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
            'name' => '',
            'id'   => PREFIX . '_worker',
            'type' => 'title',
         ],
         [
            'name' => __( 'Key', 'integration-marktplaats-for-woocommerce' ),
            'id'   => PREFIX . '_worker_ui',
            'type' => PREFIX . '_worker_ui',
         ],
         [
            'id'   => PREFIX . '_worker_end',
            'type' => 'sectionend',
         ],
      ], $items);

      return $items;
   }



   /**
    * Renders the output of `worker_ui` field.
    *
    * @param array $values
    * @return string
    */
   public static function render_worker_ui($values){

      echo '<tr class="'.PREFIX.'-style">';
         echo '<td class="p-0">';
            echo Module_Worker::render();
         echo '</td>';
      echo '</tr>';

   }

}