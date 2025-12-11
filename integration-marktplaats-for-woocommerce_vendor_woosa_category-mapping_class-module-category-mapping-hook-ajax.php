<?php
/**
 * Module Category Mapping Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Category_Mapping_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_cm_connect', [__CLASS__, 'handle_connect']);
      add_action('wp_ajax_' . PREFIX . '_cm_remove', [__CLASS__, 'handle_remove']);

      add_action('wp_ajax_' . PREFIX . '_config_category', [__CLASS__, 'config_category']);
      add_action('wp_ajax_' . PREFIX . '_save_category_config', [__CLASS__, 'save_category_config']);
      add_action('wp_ajax_' . PREFIX . '_copy_category_config', [__CLASS__, 'copy_category_config']);


   }



   /**
    * Processes the request to connect categories.
    *
    * @return void
    */
   public static function handle_connect(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $url = str_replace('&removed=yes', '', Util::array($_POST)->get('url'));
      $cat = array_filter((array) Util::array($_POST)->get('cat'));

      if(count($cat) != 2){
         wp_send_json_error([
            'message' =>__('Please select the categories you want to connect.', 'integration-marktplaats-for-woocommerce'),
         ]);
      }

      $category_id = $cat[0];//sevice category
      $term_id     = $cat[1];//shop category

      $values = array_filter( (array) get_term_meta($term_id, PREFIX . '_mapped_category_id') );

      if( in_array($category_id, $values) ){
         wp_send_json_error([
            'message' =>__('These categories are already connected.', 'integration-marktplaats-for-woocommerce'),
         ]);
      }

      //set new cat id
      add_term_meta($term_id, PREFIX . '_mapped_category_id', $category_id);

      if(strpos($url, 'connected=yes') === false){
         $url .= '&connected=yes';
      }

      wp_send_json_success([
         'redirect_url' => $url,
      ]);

   }



   /**
    * Processes the request to remove connected categories.
    *
    * @return void
    */
   public static function handle_remove(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $url     = str_replace('&connected=yes', '', Util::array($_POST)->get('url'));
      $term_id = Util::array($_POST)->get('term_id');
      $cat_id  = Util::array($_POST)->get('cat_id');

      delete_term_meta($term_id, PREFIX . '_mapped_category_id', $cat_id);
      delete_term_meta($term_id, PREFIX . '_category');//remove old meta

      if(strpos($url, 'removed=yes') === false){
         $url .= '&removed=yes';
      }

      wp_send_json_success([
         'redirect_url' => $url,
      ]);

   }



   /**
    * Processes the request to connect categories.
    *
    * @return void
    */
   public static function config_category(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $term_id     = Util::array( $_GET )->get('term_id');
      $category_id = Util::array( $_GET )->get('category_id');

      $category = array_filter((array)apply_filters(PREFIX . '\category-mapping\category-config\get-category', [], $category_id));

      if(empty($category)) {
         return;
      }

      $config_fields = get_term_meta( $term_id, PREFIX . '_config_fields', true );

      echo Util::get_template('popup-map-field.php', [
         'category_id' => $category_id,
         'term_id' => $term_id,
         'config_fields' => $config_fields,
         'category_fields' => $category['fields'] ?? null,
         'category_name' => $category['name'] ?? null,
         'mapped_terms'   => self::get_mapped_terms($term_id),
      ], dirname(dirname(__FILE__)), 'category-mapping/templates');

      exit;

   }



   /**
    * Get the terms already mapped
    *
    * @param int $exclude_term
    * @return array
    */
   public static function get_mapped_terms($exclude_term = 0) {

      $mapped_terms = get_terms([
         'taxonomy'   => 'product_cat',
         'hide_empty' => false,
         'number'     => -1,
         'orderby'    => 'name',
         'order'      => 'ASC',
         'exclude'    => $exclude_term,
         'fields'     => 'id=>name',
         'meta_query' => [
            [
               'key'       => PREFIX . '_mapped_category_id',
               'compare'   => 'EXISTS'
            ]
         ]
      ]);
      if (!empty($mapped_terms) && !is_wp_error($mapped_terms)) {
         return $mapped_terms;
      }
      return [];
   }




   /**
    * Saves the fields for a category configuration
    *
    * @return void
    */
   public static function save_category_config() {

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      parse_str($_POST['fields'] ?? '', $fields);

      $wc_category_id = intval( Util::array($_POST)->get('wc_category_id') );

      update_term_meta($wc_category_id, PREFIX . '_config_fields', $fields);

      wp_send_json_success([
         'message' => __('Changes have been saved!', 'integration-marktplaats-for-woocommerce')
      ]);

   }



   /**
    * Saves the fields for a category configuration
    *
    * @return void
    */
   public static function copy_category_config() {

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }


      $wc_category_id = intval( Util::array($_POST)->get('wc_category_id') );
      $copy_category = intval( Util::array($_POST)->get('copy_category') );

      $fields = get_term_meta($copy_category, PREFIX . '_config_fields', true);

      if (empty($fields)) {
         wp_send_json_error([
            'message' => __('Nothing to copy!', 'integration-marktplaats-for-woocommerce')
         ]);
      }

      update_term_meta($wc_category_id, PREFIX . '_config_fields', $fields);

      wp_send_json_success([
         'message' => __('Changes have been copied!', 'integration-marktplaats-for-woocommerce')
      ]);

   }


}
