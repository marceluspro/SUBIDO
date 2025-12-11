<?php
/**
 * Category Selection Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Category_Selection_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_render_product_cpc_field', [__CLASS__, 'handle_product_cpc_field']);

   }



   /**
    * Processes the request to display the CPC field on product.
    *
    * @return string
    */
   public static function handle_product_cpc_field(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $category_id = Util::array($_POST)->get('category_id');
      $api_category = new Service_API_Category;
      $config = $api_category->get_config($category_id);

      wp_send_json_success([
         'cpc' => $config['cpc'],
         'cpc_total_budget' => $config['cpc_total_budget'],
      ]);
   }
}