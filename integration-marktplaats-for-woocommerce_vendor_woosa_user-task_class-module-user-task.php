<?php
/**
 * Module User Task
 *
 * Payload structure:
 * [
 *    'id'        => 0,
 *    'username'  => '',
 *    'password'  => '',
 *    'nickname'  => '',
 *    'email'     => '',
 *    'meta_data' => [
 *       'first_name'    => '',
 *       'last_name'     => '',
 *       '{prefix}_role' => '',
 *    ],
 * ]
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_User_Task extends Module_Abstract_Entity_User implements Interface_Entity_Task{


   /**
    * List of supported actions.
    *
    * @param string $prop - the array item propriety to be return
    * @return array
    */
   public static function action_list($prop = ''){

      $list = [
         [
            'id'       => 'create_or_update_user',
            'priority' => 8,
            'context'  => 'user_task',
         ],
         [
            'id'       => 'delete_user',
            'priority' => 42,
            'context'  => 'user_task',
         ],
      ];

      if( ! empty($prop) ){
         return array_column($list, $prop);
      }

      return $list;
   }



   /**
    * Retrieves the entity id from the service.
    *
    * @return string|int
    */
   public function get_remote_id(){}



   /**
    * Checks whether or not the entity is valid.
    *
    * @return boolean
    */
   public function is_valid(){}



   /**
    * Retrieves the entity type.
    *
    * @return string
    */
   public function get_type(){}



   /**
    * Checks whether or not the entity type is supported.
    *
    * @return boolean
    */
   public function is_supported_type(){}



   /**
    * Sets the entity errors.
    *
    * @param string $message
    * @param string $key - the key which the error message will be set in the list of errors.
    * @return void
    */
   public function set_error($message, $key = ''){}



   /**
    * Deletes the entity errors.
    *
    * @param string $key - the key which the error message will be set in the list of errors.
    * @return void
    */
   public function delete_error($key = ''){}



   /**
    * Sets the entity status.
    *
    * @param string $status
    * @return void
    */
   public function set_status($status){}



   /**
    * Deletes the entity status.
    *
    * @return void
    */
   public function delete_status(){}



   /**
    * Sets the references between service & shop entity.
    *
    * @return void
    */
   public function set_reference(){}



   /**
    * Deletes the references between service & shop entity.
    *
    * @return void
    */
   public function delete_reference(){}

}