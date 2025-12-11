<?php
/**
 * Module Meta Util
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Meta_Util{


   /**
    * List of available product meta keys for status.
    *
    * @return array
    */
   public static function product_status_meta(){
      return [
         PREFIX . '_product_status',//default
         'kfd' => PREFIX . '_product_unit_status',//Kaufland
         'amz' => PREFIX . '_publish_status',//Amazon
         PREFIX . '_price_status',//CDON
         PREFIX . '_stock_status',//CDON
         PREFIX . '_availability_status',//CDON
         PREFIX . '_media_status',//CDON
      ];
   }



   /**
    * List of available product meta keys for error.
    *
    * @return array
    */
   public static function product_error_meta(){
      return [
         PREFIX . '_product_errors',//default
         PREFIX . '_product_unit_errors',//Kaufland
         PREFIX . '_price_errors',//CDON
         PREFIX . '_stock_errors',//CDON
         PREFIX . '_availability_errors',//CDON
         PREFIX . '_media_errors',//CDON
         PREFIX . '_errors',//Amazon
      ];
   }



   /**
    * List of available product meta keys for identifier.
    *
    * @return array
    */
   public static function product_id_meta(){
      return [
         PREFIX . '_product_id',//default
         PREFIX . '_product_unit_id',//Kaufland
      ];
   }



   /**
    * List of available order meta keys for status.
    *
    * @return array
    */
   public static function order_status_meta(){
      return [
         PREFIX . '_order_status',//default
      ];
   }



   /**
    * List of available order meta keys for error.
    *
    * @return array
    */
   public static function order_error_meta(){
      return [
         PREFIX . '_order_errors',//default
         PREFIX . '_order_error',
      ];
   }



   /**
    * List of available order meta keys for identifier.
    *
    * @return array
    */
   public static function order_id_meta(){
      return [
         PREFIX . '_order_id',//default
         PREFIX . '_id_order',//Kaufland
      ];
   }
}