<?php
/**
 * Module Abstract API Client
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Module_Abstract_API_Client implements Interface_API_Client, Interface_Proxy_Client {


   /**
    * The user ID to assign the API access to.
    *
    * @var int|null
    */
   protected $user_id = null;


   /**
    * The authorization code received when creating the authorization.
    *
    * @var string|null
    */
   protected $authorization_code = null;


   /**
    * The state used for authorization URL.
    *
    * @var string|null
    */
   protected $authorization_state = null;



   /**
    * The API version.
    *
    * @return string
    */
   public function version() {
      return 'v1';
   }



   /**
    * Builds the API endpoint.
    *
    * @param string $endpoint
    * @return string
    */
   public function endpoint(string $endpoint) {

      $base_url = $this->is_proxy_available() ? $this->proxy_url() : $this->base_url();

      if($this->is_test_mode()){
         $base_url = $this->is_proxy_available() ? $this->proxy_url_dev() : $this->base_url_dev();
      }

      return trailingslashit($base_url) . trailingslashit($this->version()) . ltrim($endpoint, '/');
   }



   /**
    * Checks whether or not the test mode is enabled.
    *
    * @return bool
    */
   public function is_test_mode() {
      return (defined('\WOOSA_MIDLAYER_DEV') && \WOOSA_MIDLAYER_DEV || defined('\WOOSA_MIDLAYER_STA') && \WOOSA_MIDLAYER_STA);
   }



   /**
    * Checks whether or not the access token has expired.
    *
    * @return boolean
    */
   public function is_access_token_expired(){

      $authorized_at = (int) $this->get_credentials('authorized_at');
      $expires_in    = (int) $this->get_credentials('expires_in');

      return (time() - $authorized_at) >= $expires_in;
   }



   /**
    * Whether or not the proxy is available.
    *
    * @return boolean
    */
   public function is_proxy_available(){
      return ! Util::string_to_bool(Transient::get('proxy_down')) && ! empty($this->proxy_url()) && ! empty($this->proxy_url());
   }



   /**
    * Sends the request.
    *
    * @param string $endpoint
    * @param array $payload
    * @param string $method
    * @param array $args - headers, timeout, etc
    * @return object
    */
   public function send_request(string $endpoint, array $payload = [], string $method = 'POST', array $args = []) {

      if($this->is_access_token_expired()){
         $this->authorize();
      }

      $response = Request::{$method}(array_merge([
         'headers' => $this->headers(),
         'body'    => empty($payload) ? '' : json_encode($payload),
      ], $args))->send($this->endpoint($endpoint));

      //disable proxy and re-try the request
      if($response->status >= 404 && $this->is_proxy_available()){
         Transient::set('proxy_down', 'yes', HOUR_IN_SECONDS);
         $response = $this->send_request($endpoint, $payload, $method, $args);
      }

      return $response;
   }



   /**
    * Revokes granted access.
    *
    * @return bool
    */
   public function revoke(){
      return $this->delete_credentials();
   }



   /**
    * Sets the user id.
    *
    * @param int $id
    * @return void
    */
   public function set_user_id(int $id){
      $this->user_id = $id;
   }



   /**
    * Sets the value of the authorization code.
    *
    * @param string $code
    * @return void
    */
   public function set_authorization_code(string $code){
      $this->authorization_code = $code;
   }



   /**
    * Sets the value of the authorization state.
    *
    * @param string $state
    * @return void
    */
   public function set_authorization_state(string $state){
      $this->authorization_state = $state;
   }



   /**
    * Gets the URL where user will be redirected after granting access.
    *
    * @return string
    */
   public function get_redirect_url(){
      return add_query_arg([
         Util::prefix('redirect', true) => md5(home_url())
      ], home_url());
   }



   /**
    * Gets the API credentials.
    *
    * @param string|null $key
    * @return mixed
    */
   public function get_credentials(?string $key = ''){

      if($this->user_id){
         $data = get_user_meta($this->user_id, Util::prefix('api_credentials'), true);
      }else{
         $data = Option::get('api_credentials');
      }

      if(isset($data[$key])){
         return $data[$key];
      }

      return $data;
   }



   /**
    * Sets the API credentials.
    *
    * @param array $data
    * @return bool
    */
   public function set_credentials(array $data){

      if($this->user_id){
         return update_user_meta($this->user_id, Util::prefix('api_credentials'), $data);
      }

      return Option::set('api_credentials', $data);
   }



   /**
    * Deletes the API credentials.
    *
    * @return bool
    */
   public function delete_credentials(){

      if($this->user_id){
         return delete_user_meta($this->user_id, Util::prefix('api_credentials'));
      }

      return Option::delete('api_credentials');
   }
}