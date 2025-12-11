<?php
/**
 * Module Marketplace Worker Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Worker_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\worker\action_list', [__CLASS__, 'define_action_list'], 99);

   }



   /**
    * Defines the actions in the Worker's list.
    *
    * @param array $list
    * @return array
    */
   public static function define_action_list($list){

      foreach($list as $index => $item){

         //remove some actions
         if(in_array($item['id'], [
            'assign_product_attribute',
            'assign_product_category',
            'delete_shop_category',
            'update_product_stock',
            'update_product_price',
            'delete_user',
            'delete_order',
            'update_product_lookup_table',
            'download_product_image',
         ])){
            unset($list[$index]);
         }
      }

      $list[] = [
         'id'       => 'pause_or_unpause_product',
         'priority' => 6,
      ];

      return $list;
   }
}