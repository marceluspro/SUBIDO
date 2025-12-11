<?php
/**
 * Module Authorization Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Authorization_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_'.PREFIX.'_process_authorization', [__CLASS__, 'process_authorization']);
   }



   /**
    * Processes the authorization.
    *
    * @return string
    */
   public static function process_authorization(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      parse_str(Util::array($_POST)->get('fields'), $fields);

      $action     = Util::array($_POST)->get('auth_action');
      $save_extra = apply_filters(PREFIX . '\authorization\save_extra_fields', true);

      if($save_extra){
         foreach($fields as $key => $value){
            if(strpos($key, PREFIX .'_') !== false){
               $value = apply_filters(PREFIX . '\authorization\extra_field_value', $value, $key);
               Option::set($key, $value);
            }
         }
      }

      if('save' === $action){
         wp_send_json_success();
      }

      $ma = new Module_Authorization();
      $ma->set_env($ma->get_env());

      do_action(PREFIX . '\module\authorization\before_process_authorization', $action, $ma);

      if('authorize' === $action){

         $result = $ma->connect();

      }else{

         $result = $ma->disconnect();
      }

      if(!empty($result['success']) || !empty($result['redirect_url'])){

         wp_send_json_success($result);

      }else{

         wp_send_json_error([
            'message' => $ma->get_formatted_error(Util::array($result)->get('message'))
         ]);
      }
   }

}