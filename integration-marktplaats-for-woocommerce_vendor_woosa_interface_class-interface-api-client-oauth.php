<?php
/**
 * Interface API Client OAuth.
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


interface Interface_API_Client_OAuth {


   /**
    * The base URL of authorization page.
    *
    * @return string
    */
   public function base_auth_url();



   /**
    * The base URL of authorization page for development.
    *
    * @return string
    */
   public function base_auth_url_dev();



   /**
    * Gets the URL where user will be redirected after granting access.
    *
    * @return string
    */
   public function get_redirect_url();



   /**
    * Gets the page URL where user will login and grant access.
    *
    * @return string
    */
   public function get_authorization_url();
}