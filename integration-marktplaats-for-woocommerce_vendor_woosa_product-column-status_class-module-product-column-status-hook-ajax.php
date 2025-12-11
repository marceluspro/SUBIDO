<?php
/**
 * Module Product Column Status Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Column_Status_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_render_product_table_column_status', [__CLASS__, 'handle_table_column_status']);

   }



   /**
    * Processes the request to render the status on product table column.
    *
    * @return string
    */
   public static function handle_table_column_status(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $product_id  = Util::array($_POST)->get('args/product_id');
      $column_name = Util::array($_POST)->get('args/column_name');

      preg_match('/' . PREFIX . '_account_([a-zA-Z0-9]+)/', $column_name, $results);

      $account_id = Util::array($results)->get('1');

      ob_start();

      $meta = new Module_Meta($product_id);

      //in case there are general errors then show the status for all accounts
      if($meta->get_errors() && 'yes' !== $meta->get($account_id . '_exclude_account')){

         $meta->display_product_status();

      }else{

         $meta->set_account_id($account_id);
         $meta->display_product_status();
      }

      $output = ob_get_clean();

      wp_send_json_success([
         'template' => $output
      ]);
   }
}