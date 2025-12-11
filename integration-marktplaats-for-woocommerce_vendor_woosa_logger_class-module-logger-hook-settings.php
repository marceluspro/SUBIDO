<?php
/**
 * Module Logger Hook Settings
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Logger_Hook_Settings implements Interface_Hook_Settings_Tab{


   /**
    * The id of the tab.
    *
    * @return string
    */
   public static function id(){
      return 'logs';
   }



   /**
    * The name of the tab.
    *
    * @return string
    */
   public static function name(){
      return __('Logs', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The description of the tab.
    *
    * @return string
    */
   public static function description(){
      return __('List of logs', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The icon URL of the tab.
    *
    * @return string
    */
   public static function icon_url(){
      return file_get_contents(untrailingslashit(plugin_dir_path(__FILE__)) . '/assets/images/icons/list-timeline.svg');
   }



   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\settings\page\tabs', [__CLASS__, 'add_tab'], 100);
      add_filter(PREFIX . '\module\settings\page\content\fields\\'.self::id(), [__CLASS__, 'add_tab_fields']);

      add_action(PREFIX . '\field_generator\render\\' . PREFIX . '_logs_ui', [__CLASS__, 'render_logs_ui']);
   }



   /**
    * Initiates the section under a condition.
    *
    * @return void
    */
   public static function maybe_init(){}



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
            'name' => self::get_logs_title(),
            'id'   => PREFIX . '_logs',
            'type' => 'title',
         ],
         [
            'id'   => PREFIX .'_logs_ui',
            'type' => PREFIX .'_logs_ui',
         ],
         [
            'id'   => PREFIX . '_logs_end',
            'type' => 'sectionend',
         ],
      ], $items);

      return $items;
   }



   /**
    * Get the logs title
    *
    * @return string
    */
   public static function get_logs_title() {

      if (!empty($_GET['log_file'])) {

         $log_file = base64_decode($_GET['log_file']);
         $log_file_path = Module_Logger::get_file_dir() . $log_file;

         $log_file = preg_replace(
            '/^(.+?-\d{4}-\d{2}-\d{2})(?:-[a-f0-9]{32})?(?:-\d+)?\.log$/',
            '$1',
            $log_file
         );

         if (file_exists($log_file_path)) {
            return $log_file;
         }
      }

   }



   /**
    * Renders the output of `authorization_ui` field.
    *
    * @param array $values
    * @return void
    */
   public static function render_logs_ui($values){

      if (!empty($_GET['log_file'])) {

         $log_file = base64_decode($_GET['log_file']);
         $log_file_path = Module_Logger::get_file_dir() . $log_file;

         if (file_exists($log_file_path)) {
            ?>
            <a class="button button-small mb-10" href="<?php echo add_query_arg([
                  'tab' => 'logs',
                  'paged' => Util::array($_GET)->get('paged', 1),
               ],
               Module_Settings::get_page_url()
            );?>"><?php _e('Back to logs', 'integration-marktplaats-for-woocommerce');?></a>

            <div id="log-viewer">
               <pre><?php echo esc_html( file_get_contents( $log_file_path ) ); ?></pre>
            </div>
            <?php
            return;
         }

      }

      $logger_table = new Module_Logger_Table();
      $logger_table->prepare_items();
      ?>
      <tr class="<?php echo PREFIX;?>-style">
         <td class="p-0">
            <div class="<?php echo PREFIX;?>-logs-table-wrap">
               <?php $logger_table->search_box(__('Search', 'integration-marktplaats-for-woocommerce'), 'search_file'); ?>
               <?php $logger_table->display(); ?>
            </div>
         </td>
      </tr>

      <?php

   }


}
