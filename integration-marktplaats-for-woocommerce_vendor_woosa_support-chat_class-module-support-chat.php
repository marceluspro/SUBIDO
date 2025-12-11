<?php
/**
 * Module Support Chat
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Support_Chat {


   /**
    * Gets the chat service id (or secret key).
    *
    * @return string
    */
   public static function get_app_id() {
      return '34765ce8-9380-46f1-8765-fce1c326e10a';
   }



   /**
    * Gets user name.
    *
    * @return string
    */
   public static function get_user_name() {
      $current_user = wp_get_current_user();
      return esc_html( $current_user->display_name );
   }



   /**
    * Gets user email address.
    *
    * @return string
    */
   public static function get_user_email() {

      $iv = 'ab86d144ab86d144';
      $cipher = "aes-128-ctr";
      $license_key = Option::get('license_key', '');

      if ( ! empty($license_key) && ( strlen( $license_key ) % 2 == 0 ) && ctype_xdigit( $license_key ) ) {

         $license_decoded = openssl_decrypt( hex2bin($license_key), $cipher, '', OPENSSL_RAW_DATA, $iv );

         $license_decoded_parts = explode('*', $license_decoded);

         $email = Util::array($license_decoded_parts)->get(1);

         if(is_email($email)){
            return $email;
         }
      }

      $current_user = wp_get_current_user();

      return $current_user->user_email;
   }
}
