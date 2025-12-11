<?php
/**
 * Service API
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Service_API extends Module_Abstract_API_Client {


   /**
    * The API resource.
    *
    * @var string
    */
   protected $resource = '';



   /**
    * The API version.
    *
    * @return string
    */
   public function version(){
      return 'v5';
   }



   /**
    * The API base URL.
    *
    * @return string
    */
   public function base_url(){
      return 'https://admarkt.marktplaats.nl';
   }



   /**
    * The API base URL for development.
    *
    * @return string
    */
   public function base_url_dev(){
      return $this->base_url();
   }



   /**
    * The proxy URL.
    *
    * @return string
    */
   public function proxy_url(){
      return 'https://midlayer.woosa.nl/woocommerce/marktplaats/v2/proxy';
   }



   /**
    * The proxy URL for development.
    *
    * @return string
    */
   public function proxy_url_dev(){

      if(defined('\WOOSA_MIDLAYER_STA') && \WOOSA_MIDLAYER_STA) {
         return 'https://midlayer-sta.woosa.nl/woocommerce/marktplaats/v2/proxy';
      }

      return 'https://midlayer-dev.woosa.nl/woocommerce/marktplaats/v2/proxy';
   }



   /**
    * The list of headers.
    *
    * @param array $headers
    * @return array
    */
   public function headers(array $items = []) {

      $defaults = [
         'Authorization' => 'Bearer ' . $this->get_credentials('access_token'),
      ];

      if(!empty($this->resource)){
         $defaults = array_merge($defaults, [
            'Accept'        => "application/sellside.{$this->resource}-{$this->version()}+json",
            'Content-Type'  => "application/sellside.{$this->resource}-{$this->version()}+json",
         ]);
      }

      $items = array_merge($defaults, $items);

      return $items;
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

      return trailingslashit($base_url) . trailingslashit('api/sellside') . ltrim($endpoint, '/');
   }



   /**
    * Grants access.
    *
    * @return void
    */
   public function authorize(){
      Module_Midlayer_OAuth::get_access_token();
   }



   /**
    * The list of price types.
    *
    * @return array
    */
   public static function price_types(){
      return [
         'fixed_price'     => __('Fixed', 'integration-marktplaats-for-woocommerce'),
         'free'            => __('Free', 'integration-marktplaats-for-woocommerce'),
         'bidding'         => __('Bidding', 'integration-marktplaats-for-woocommerce'),
         'bidding_from'    => __('Bidding from', 'integration-marktplaats-for-woocommerce'),
         'see_description' => __('See description', 'integration-marktplaats-for-woocommerce'),
         'credible_bid'    => __('Credible bid', 'integration-marktplaats-for-woocommerce'),
      ];
   }



   /**
    * The list of salutation forms.
    *
    * @return array
    */
   public static function salutation_forms(){
      return [
         'male' => __('Male', 'integration-marktplaats-for-woocommerce'),
         'female' => __('Female', 'integration-marktplaats-for-woocommerce'),
         'family' => __('Family', 'integration-marktplaats-for-woocommerce'),
         'company' => __('Company', 'integration-marktplaats-for-woocommerce'),
      ];
   }



   /**
    * The list of shipping types.
    *
    * @return array
    */
   public static function shipping_types(){
      return [
         'ship' => __('Ship', 'integration-marktplaats-for-woocommerce'),
         'pickup' => __('Pickup', 'integration-marktplaats-for-woocommerce'),
      ];
   }



   /**
    * The list of shipping time frames.
    *
    * @return array
    */
   public static function shipping_timeframes(){
      return [
         '2d-5d' => __('2-5 working days', 'integration-marktplaats-for-woocommerce'),
         '6d-10d' => __('6-10 working days', 'integration-marktplaats-for-woocommerce'),
      ];
   }

}