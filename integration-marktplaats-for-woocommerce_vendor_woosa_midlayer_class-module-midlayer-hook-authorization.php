<?php
/**
 * Module Midlayer Authorization Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Midlayer_Hook_Authorization implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\authorization\connect', [__CLASS__, 'connect_env'], 90);
      add_filter(PREFIX . '\authorization\disconnect', [__CLASS__, 'disconnect_env'], 90);

      add_filter(PREFIX . '\module\settings\page\content\fields\authorization', [__CLASS__, 'add_registration_fields'], 99);
      add_action(PREFIX . '\field_generator\render\\' . PREFIX . '_registration_ui', [__CLASS__, 'render_registration'], 99);

      add_filter(PREFIX . '\authorization\is_authorized', [__CLASS__, 'restrict_authorization'], 10, 2);
   }



   /**
    * Grants the access to the service.
    *
    * @param array $output
    * @return array
    */
   public static function connect_env($output){

      $response = Module_Midlayer_API::connect();

      if( $response->status != 204 ){

         $output = [
            'success' => false,
            'message' => __('Granting authorization has failed, please check the logs.', 'integration-marktplaats-for-woocommerce'),
         ];

      }

      return $output;
   }



   /**
    * Revokes the access to the service.
    *
    * @param array $output
    * @return array
    */
   public static function disconnect_env($output){

      Module_Midlayer_API::disconnect();

      return $output;
   }



   /**
    * Replaces the authorization fields with registration fields.
    *
    * @param array $items
    * @return array
    */
   public static function add_registration_fields($items){

      if ( ! Module_Midlayer::is_shop_registered() ) {

         $items = [
            [
               'name' => __('Shop Registration', 'integration-marktplaats-for-woocommerce'),
               'id'   => PREFIX . '_registration_title',
               'type' => 'title',
            ],
            [
               'id'   => PREFIX . '_registration_ui_id',
               'type' => PREFIX . '_registration_ui',
            ],
            [
               'id'   => PREFIX . '_registration_sectionend',
               'type' => 'sectionend',
            ],
         ];
      }

      return $items;
   }



   /**
    * Renders the output of the field `registration_ui`.
    *
    * @return string
    */
   public static function render_registration(){
      echo Util::get_template('shop-registration.php', [], dirname(dirname(__FILE__)), 'midlayer/templates');
   }



   /**
    * Restrict authorization when shop is not registered.
    *
    * @param bool $is_authorized
    * @param Module_Authorization $class
    * @return bool
    */
   public static function restrict_authorization($is_authorized, $class){

      if( ! Module_Midlayer::is_shop_registered() ){
         $is_authorized = false;
      }

      return $is_authorized;
   }

}