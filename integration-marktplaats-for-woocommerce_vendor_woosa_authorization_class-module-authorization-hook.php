<?php
/**
 * Module Authorization Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Authorization_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\action_bulker\initiate', [__CLASS__, 'is_access_granted']);
      add_filter(PREFIX . '\action_bulker\allow_perform', [__CLASS__, 'is_access_granted']);

      add_filter(PREFIX . '\category_mapping\initiate', [__CLASS__, 'is_access_granted']);

      add_filter(PREFIX . '\dropshipping\product_filter\initiate', [__CLASS__, 'is_access_granted']);

      add_filter(PREFIX . '\module\synchronization\initiate', [__CLASS__, 'is_access_granted']);

      add_filter(PREFIX . '\heartbeat\initiate', [__CLASS__, 'is_access_granted']);

      add_filter(PREFIX . '\order_details\display_process_box', [__CLASS__, 'is_access_granted']);

      add_filter(PREFIX . '\validation\disabled_field', [__CLASS__, 'is_access_granted']);

      add_action(PREFIX . '\request\sent', [__CLASS__, 'set_as_unauthorized'], 10, 2);
   }



   /**
    * Whether or not the access is granted.
    *
    * @param bool $bool
    * @return boolean
    */
   public static function is_access_granted($bool){

      $ma = new Module_Authorization();

      if( ! $ma->is_authorized() ){
         $bool = false;
      }

      return $bool;
   }



   /**
    * Sets as unauthorized if the remote request is flagged as `authorized` but it gets 401 status.
    *
    * @param object $response
    * @param Request $request
    * @return void
    */
   public static function set_as_unauthorized($response, $request){

      if(Util::array($request->get_args())->get('authorized', false)){

         $ma = new Module_Authorization();

         if( $ma->is_authorized() && 401 == $response->status){
            $ma->set_as_unauthorized();
         }
      }

   }

}