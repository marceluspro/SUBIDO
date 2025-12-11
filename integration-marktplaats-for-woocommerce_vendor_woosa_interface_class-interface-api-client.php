<?php
/**
 * Interface API Client
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


interface Interface_API_Client {


   /**
    * The API version.
    *
    * @return string
    */
   public function version();



   /**
    * The API base URL.
    *
    * @return string
    */
   public function base_url();



   /**
    * The API base URL for development.
    *
    * @return string
    */
   public function base_url_dev();



   /**
    * Builds the API URL with the given endpoint.
    *
    * @param string $endpoint
    * @return string
    */
   public function endpoint(string $endpoint);



   /**
    * The list of headers.
    *
    * @param array $headers
    * @return array
    */
   public function headers(array $headers = []);



   /**
    * Checks whether or not the test mode is enabled.
    *
    * @return bool
    */
   public function is_test_mode();



   /**
    * Sends the request.
    *
    * @param string $endpoint
    * @param array $payload
    * @param string $method
    * @param array $args - headers, timeout, cache, etc
    * @return object
    */
   public function send_request(string $endpoint, array $payload = [], string $method = 'POST', array $args = []);



   /**
    * Grants access.
    *
    * @return bool|array|void
    */
   public function authorize();



   /**
    * Revokes granted access.
    *
    * @return bool|array|void
    */
   public function revoke();
}