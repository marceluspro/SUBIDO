<?php
/**
 * Module Change Tracker Hook User
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Change_Tracker_Hook_User implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('user_register', [__CLASS__, 'notify_create']);

      add_action('profile_update', [__CLASS__, 'notify_update']);

      add_action('delete_user', [__CLASS__, 'notify_delete']);

   }



   /**
    * Creates task to notify that a user has been created.
    *
    * @param int|string $user_id
    * @return void
    */
   public static function notify_create($user_id){

      $enable = apply_filters(PREFIX . '\module\change_tracker\notify_create_user\enable', false, $user_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_user_change');

      if($enable && 'yes' !== $lock){

         Module_Task::update_entries([
            [
               'action'      => 'create_or_update_user',
               'source'      => 'shop',
               'target'      => 'service',
               'payload'     => Module_User_Task_Util::get_payload($user_id),
               'resource_id' => $user_id,
            ]
         ]);
      }
   }



   /**
    * Creates task to notify that a user has been updated.
    * This ensures the update is only for users that have been already linked with the service.
    *
    * @param int|string $user_id
    * @return void
    */
   public static function notify_update($user_id){

      $enable = apply_filters(PREFIX . '\module\change_tracker\notify_update_user\enable', false, $user_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_user_change');

      if($enable && 'yes' !== $lock){

         if( self::is_linked($user_id) ){

            Module_Task::update_entries([
               [
                  'action'      => 'create_or_update_user',
                  'source'      => 'shop',
                  'target'      => 'service',
                  'payload'     => Module_User_Task_Util::get_payload($user_id),
                  'resource_id' => $user_id,
               ]
            ]);
         }
      }
   }



   /**
    * Creates task to notify that a user has been deleted.
    * This ensures the delete is only for users that have been already linked with the service.
    *
    * @param int|string $user_id
    * @return void
    */
   public static function notify_delete($user_id){

      $enable = apply_filters(PREFIX . '\module\change_tracker\notify_delete_user\enable', false, $user_id);
      $lock   = Util::array($GLOBALS)->get(PREFIX . '_lock_user_change');

      if($enable && 'yes' !== $lock){

         if( self::is_linked($user_id) ){

            Module_Task::update_entries([
               [
                  'action'      => 'delete_user',
                  'source'      => 'shop',
                  'target'      => 'service',
                  'payload'     => Module_User_Task_Util::get_payload($user_id),
                  'resource_id' => $user_id,
               ]
            ]);
         }
      }
   }



   /**
    * Checks whether or not the user is linked with the service via some references meta.
    *
    * @param string|int $user_id
    * @return boolean
    */
   protected static function is_linked($user_id){

      $result  = true;
      $user_id = get_user_meta($user_id, PREFIX . '_user_id', true);

      if( empty($user_id) ){
         $result = false;
      }

      return $result;
   }

}