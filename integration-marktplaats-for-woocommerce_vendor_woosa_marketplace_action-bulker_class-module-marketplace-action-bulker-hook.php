<?php
/**
 * Module Marketplace Action Bulker Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Action_Bulker_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\action_bulker\actions', [__CLASS__, 'add_bulk_actions']);
      add_action(PREFIX . '\action_bulker\perform_task\item', [__CLASS__, 'process_bulk_action'], 10, 2);

   }



   /**
    * Adds bulk actions.
    *
    * @return array
    */
   public static function add_bulk_actions($items){
      return array_merge($items, Module_Marketplace_Action_Bulker::get_list());
   }



   /**
    * Processes the applied actions.
    *
    * @param array $action
    * @param string|int $item_id
    * @return void
    */
   public static function process_bulk_action($action, $item_id){

      //process for products if any
      Module_Marketplace_Action_Bulker::run_for_product($item_id, $action);
   }
}