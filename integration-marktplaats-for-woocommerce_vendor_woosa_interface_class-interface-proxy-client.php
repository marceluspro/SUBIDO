<?php
/**
 * Interface Proxy Client
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


interface Interface_Proxy_Client {


   /**
    * The proxy URL.
    *
    * @return string
    */
   public function proxy_url();



   /**
    * The proxy URL for development.
    *
    * @return string
    */
   public function proxy_url_dev();



   /**
    * Whether or not the proxy is available.
    *
    * @return boolean
    */
   public function is_proxy_available();

}