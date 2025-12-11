<?php
/**
 * Module User Task Util
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_User_Task_Util{


   /**
    * Retrieves the payload schema for the given user.
    *
    * @param int|\WP_User $user
    * @return array
    */
    public static function get_payload($user){

      $payload = [];
      $user   = $user instanceof \WP_User ? $user : new \WP_User($user);

      if($user instanceof \WP_User){

         $payload = [
            'id'        => $user->ID,
            'username'  => $user->user_login,
            'password'  => $user->user_pass,
            'nickname'  => $user->user_nicename,
            'email'     => $user->user_email,
            'meta_data' => [
               'billing_first_name' => get_user_meta($user->ID, 'billing_first_name', true),
               'billing_last_name'  => get_user_meta($user->ID, 'billing_last_name', true),
               'billing_company'    => get_user_meta($user->ID, 'billing_company', true),
               'billing_address_1'  => get_user_meta($user->ID, 'billing_address_1', true),
               'billing_address_2'  => get_user_meta($user->ID, 'billing_address_2', true),
               'billing_city'       => get_user_meta($user->ID, 'billing_city', true),
               'billing_state'      => get_user_meta($user->ID, 'billing_state', true),
               'billing_postcode'   => get_user_meta($user->ID, 'billing_postcode', true),
               'billing_country'    => get_user_meta($user->ID, 'billing_country', true),
               'billing_phone'      => get_user_meta($user->ID, 'billing_phone', true),
               PREFIX . '_user_id'  => get_user_meta($user->ID, PREFIX . '_user_id', true),
            ],
         ];
      }

      return apply_filters(PREFIX . '\order_task\util\get_payload', $payload, $user);
   }
}