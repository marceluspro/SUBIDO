<?php
/**
 * Change Tracker Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Change_Tracker_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\change_tracker\updated_product_meta\meta_keys', [__CLASS__, 'define_product_meta_keys']);
   }



   /**
    * Defines the meta keys that should be checked when the product is updated.
    * When any of these meta keys is updated, will create a task to update the offer in Bol.
    *
    * @param array $keys
    * @return array
    */
   public static function define_product_meta_keys($keys){

      $keys[] = '_thumbnail_id';
      $keys[] = '_product_image_gallery';
      $keys[] = '_stock_status';
      $keys[] = Util::prefix('title');
      $keys[] = Util::prefix('price_type');
      $keys[] = Util::prefix('price');
      $keys[] = Util::prefix('category');
      $keys[] = Util::prefix('cpc');
      $keys[] = Util::prefix('cpc_total_budget');
      $keys[] = Util::prefix('cpc_automatic');
      $keys[] = Util::prefix('shipping_type');
      $keys[] = Util::prefix('shipping_cost');
      $keys[] = Util::prefix('shipping_time');
      $keys[] = Util::prefix('shipping_pickup_location');
      $keys[] = Util::prefix('allow_contact_by_email');
      $keys[] = Util::prefix('salutation');
      $keys[] = Util::prefix('seller_name');
      $keys[] = Util::prefix('phone');
      $keys[] = Util::prefix('footer_description');

      //when sync price is disabled then remove default WC price meta keys from being watched
      if(Module_Synchronization::is_product_price_sync_disabled()){
         foreach(['_price', '_regular_price'] as $k){
            $key = array_search($k, $keys);
            if(isset($keys[$key])){
               unset($keys[$key]);
            }
         }
      }

      return $keys;
   }
}
