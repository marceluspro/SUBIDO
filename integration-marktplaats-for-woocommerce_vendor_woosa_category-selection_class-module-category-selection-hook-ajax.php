<?php
/**
 * Module Category Selection Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Category_Selection_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_cs_load_items', [__CLASS__, 'handle_load_items']);
      add_action('wp_ajax_' . PREFIX . '_cs_search_items', [__CLASS__, 'handle_search_items']);

   }



   /**
    * Processes the request to load items.
    *
    * @return string
    */
   public static function handle_load_items(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $item_id = Util::array($_POST)->get('item_id');
      $source  = Util::array($_POST)->get('source');
      $level   = Util::array($_POST)->get('level');

      $mcs   = new Module_Category_Selection($source, $level);
      $items = $mcs->get_subitems($item_id);
      $items = array_slice($items, 0, Module_Category_Selection::SHOW_ITEMS_LIMIT);
      $list  = $mcs->get_list_template($items);
      $trail = $mcs->get_trail_template($item_id);

      wp_send_json_success([
         'list'  => $list,
         'trail' => $trail,
         'last'  => empty($items) ? true : false,
      ]);

   }



   /**
    * Processes the request to search by item name.
    *
    * @return void
    */
   public static function handle_search_items(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $search = stripslashes(Util::array($_POST)->get('search'));
      $source = Util::array($_POST)->get('source');
      $level  = Util::array($_POST)->get('level');
      $mcs    = new Module_Category_Selection($source, $level);

      if(strlen($search) > 0 && strlen($search) < 3){
         wp_send_json_error([
            'template' => '<p class="text-color--error">'. __('You have to type at least 3 characters!', 'integration-marktplaats-for-woocommerce') .'</p>',
         ]);
      }

      if(empty($search)){
         $filtered_items = array_slice($mcs->get_subitems(0), 0, Module_Category_Selection::SHOW_ITEMS_LIMIT);

      }else{

         $items_map = [];
         $all_items = $mcs->get_items();

         foreach ($all_items as $item) {
            $items_map[$item['id']] = $item;
         }

         $highlight_term = function($text, $term) {
            return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<strong><u>$1</u></strong>', $text);
         };

         $build_path = function($item_id, $search) use ($items_map, $highlight_term) {
            $path = [];

            if(isset($items_map[$item_id]['parent_id'])){
               $current_id = $items_map[$item_id]['parent_id'];

               while ($current_id != 0 && isset($items_map[$current_id])) {
                  $name = $items_map[$current_id]['name'];
                  if (!empty($search)) {
                     $name = $highlight_term($name, $search);
                  }
                  array_unshift($path, $name);
                  $current_id = $items_map[$current_id]['parent_id'];
               }
            }

            return $path;
         };

         $filtered_items = [];

         foreach ($all_items as $item) {
            $matches = empty($search) || stripos($item['name'], $search) !== false;

            if ($matches) {
               $path = $build_path($item['id'], $search);
               $item_name = $item['name'];

               if (!empty($search)) {
                  $item_name = $highlight_term($item_name, $search);
               }

               if (!empty($path)) {
                  $item['name'] = implode(' » ', $path) . ' » ' . $item_name;
               } else {
                  $item['name'] = $item_name;
               }

               $filtered_items[] = $item;
            }
         }
      }

      $list = $mcs->get_list_template($filtered_items);

      if(empty($filtered_items)){
         wp_send_json_error([
            'template' => '<p class="text-color--error">'. __('No results found.', 'integration-marktplaats-for-woocommerce') .'</p>',
         ]);
      }

      wp_send_json_success([
         'template' => $list,
      ]);
   }
}