<?php
/**
 * Module Product Data Tab Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Data_Tab_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_render_product_data_panel', [__CLASS__, 'handle_data_panel']);
      add_action('wp_ajax_' . PREFIX . '_render_product_data_panel_errors', [__CLASS__, 'handle_data_panel_errors']);

   }



   /**
    * Processes the request to render the content of product data panel.
    *
    * @return string
    */
   public static function handle_data_panel(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $product_id = Util::array($_POST)->get('product_id');
      $tab_key    = Util::array($_POST)->get('tab_key');
      $page       = (int) Util::array($_POST)->get('page');
      $product    = wc_get_product($product_id);
      $html       = '<p>'.__('Invalid product, please try again!', 'integration-marktplaats-for-woocommerce').'</p>';

      if($product instanceof \WC_Product){
         $html = Module_Product_Data_Tab::get_tab_panel($product, $tab_key, $page);
      }

      wp_send_json_success([
         'html' => $html,
      ]);

   }



   /**
    * Processes the request to render the errors on product data panel.
    *
    * @return string
    */
   public static function handle_data_panel_errors(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $args   = Util::array($_POST)->get('args');
      $errors = [];

      foreach($args as $arg){

         $meta = new Module_Meta($arg['product_id']);

         //set the account id to display the error for that account
         if( ! empty($arg['account_id']) ){
            $meta->set_account_id($arg['account_id']);
         }

         ob_start();
         ?>
         <div class="mr-10 mb-10 ml-10"><?php $meta->display_errors();?></div>
         <?php

         if( ! empty($arg['account_id']) ){

            $errors[$arg['product_id'] . '_' .$arg['account_id']] = ob_get_clean();

         }else{

            $errors[$arg['product_id']] = ob_get_clean();
         }
      }

      wp_send_json_success([
         'errors' => $errors,
      ]);
   }
}