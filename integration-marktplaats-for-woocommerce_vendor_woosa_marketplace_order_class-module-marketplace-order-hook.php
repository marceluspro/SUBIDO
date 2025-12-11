<?php
/**
 * Module Marketplace Order Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Order_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\worker\action_list', [__CLASS__, 'define_action_list']);

   }



   /**
    * Defines the actions into Worker list.
    *
    * @param array $list
    * @return array
    */
   public static function define_action_list($list){

      $import_frequency  = Option::get('import_order_frequency', 'hourly');

      switch ($import_frequency) {

         case 'every_10_minutes':

            $import_recurrence = 10 * \MINUTE_IN_SECONDS;

            break;

         case 'every_30_minutes':

            $import_recurrence = 30 * \MINUTE_IN_SECONDS;

            break;

         default:

            $import_recurrence = \HOUR_IN_SECONDS;
      }

      $import_order = [
         'id'         => 'import_order',
         'priority'   => 10,
         'recurrence' => $import_recurrence,
         'context'    => 'import_order',
         'callback'   => [Module_Marketplace_Order::class, 'import_orders'],
      ];

      $mwa = new Module_Worker_Action($import_order);
      $ma  = new Module_Authorization;

      if( $ma->is_authorized() ){
         $mwa->set_prop('status', 'active');
      }else{
         $mwa->set_prop('status', 'inactive');
      }

      $list[] = $import_order;

      return $list;
   }

}