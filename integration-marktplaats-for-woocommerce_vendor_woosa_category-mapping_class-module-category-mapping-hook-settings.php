<?php
/**
 * Module Category Mapping Hook Settings
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Category_Mapping_Hook_Settings implements Interface_Hook_Settings_Tab{


   /**
    * The id of the tab.
    *
    * @return string
    */
   public static function id(){
      return 'category_mapping';
   }



   /**
    * The name of the tab.
    *
    * @return string
    */
   public static function name(){
      return __('Category Mapping', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The description of the tab.
    *
    * @return string
    */
   public static function description(){
      return __('Connect categories', 'integration-marktplaats-for-woocommerce');
   }



   /**
    * The icon URL of the tab.
    *
    * @return string
    */
   public static function icon_url(){
      return file_get_contents(untrailingslashit(plugin_dir_path(__FILE__)) . '/assets/images/icons/paperclip.svg');
   }



   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init() {

      add_action('admin_init', [__CLASS__, 'maybe_init']);

   }



   /**
    * Initiates the section under a condition.
    *
    * @return void
    */
   public static function maybe_init(){

      $initiate = apply_filters(PREFIX . '\category_mapping\initiate', true);

      if($initiate){

         add_filter(PREFIX . '\module\settings\page\tabs', [__CLASS__, 'add_tab'], 15);
         add_filter(PREFIX . '\module\settings\page\content\fields\\' . self::id(), [__CLASS__, 'add_tab_fields']);

         add_action(PREFIX . '\field_generator\render\\' . PREFIX . '_category_mapping_ui', [__CLASS__, 'render_category_mapping_ui']);
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
            'id'   => PREFIX . '_category_mapping',
            'type' => 'title',
         ],
         [
            'id'   => PREFIX . '_category_mapping_ui',
            'type' => PREFIX . '_category_mapping_ui',
         ],
         [
            'id'   => PREFIX . '_category_mapping_end',
            'type' => 'sectionend',
         ],
      ], $items);

      return $items;
   }



   /**
    * Renders the output of `category_mapping_ui` field.
    *
    * @param array $values
    * @return void
    */
   public static function render_category_mapping_ui($values){

      global $wpdb;

      $limit  = 20;
      $paged  = isset($_GET['term_page']) ? (int) $_GET['term_page'] : 0;
      $offset = $paged > 0 ? $limit * ($paged - 1) : 0;
      $total  = $wpdb->get_var( sprintf("SELECT COUNT(tm.term_id) AS total FROM $wpdb->terms as tm LEFT JOIN $wpdb->termmeta as tmm ON tmm.meta_key = '%1\$s_mapped_category_id' WHERE tm.term_id = tmm.term_id", PREFIX));
      $pages  = ceil($total / $limit);

      $query = sprintf("SELECT
            tm.term_id AS term_id,
            tmm.meta_value AS category_id
         FROM $wpdb->terms as tm
         LEFT JOIN $wpdb->termmeta as tmm
            ON tmm.meta_key = '%1\$s_mapped_category_id'
         WHERE tm.term_id = tmm.term_id
            LIMIT $offset, $limit
      ", PREFIX);

      $results = $wpdb->get_results($query, 'ARRAY_A');

      //reset page if no results
      if( empty($results) && Util::array($_GET)->get('term_page') > 1 ){
         wp_redirect(add_query_arg([
            'section' => 'category_mapping'
         ], Module_Settings::get_page_url()));
         exit;
      }

      echo Util::get_template('category-mapping-ui.php', [
         'results' => $results,
         'pages' => $pages,
      ], dirname(dirname(__FILE__)), 'category-mapping/templates');

   }

}