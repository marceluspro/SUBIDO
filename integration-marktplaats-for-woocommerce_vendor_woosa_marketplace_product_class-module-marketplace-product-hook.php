<?php
/**
 * Module Marketplace Product Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Product_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\product_task\create_task\exclude', [__CLASS__, 'exclude_sync_stock'], 10, 2);
      add_filter(PREFIX . '\module\product_task\create_account_task\exclude', [__CLASS__, 'exclude_sync_stock'], 10, 3);
   }



   /**
    * Excludes stock synchronization of its order account.
    *
    * @param boolean $exclude
    * @param Module_Meta $meta
    * @param string $account_id
    * @return bool
    */
   public static function exclude_sync_stock(bool $exclude, Module_Meta $meta, string $account_id = ''){

      $sync_stock = $meta->get('sync_stock');

      if( ! empty($account_id) ){
         $sync_stock = $meta->get($account_id . '_sync_stock');
      }

      if('no' === $sync_stock){

         $exclude = true;

         $meta->delete('sync_stock');
         $meta->delete($account_id . '_sync_stock');
         $meta->save();
      }

      if('yes' === $meta->get('reduced_stock_by_unimported_order')){

         $exclude = true;

         $meta->delete('reduced_stock_by_unimported_order');
         $meta->save();
      }

      return $exclude;
   }

}