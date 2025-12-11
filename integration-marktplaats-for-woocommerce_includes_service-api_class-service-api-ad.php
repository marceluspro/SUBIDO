<?php
/**
 * Service API Ad
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;

use Rakit\Validation\Validator;

//prevent direct access data leaks
defined('ABSPATH') || exit;


class Service_API_Ad extends Service_API {


   /**
    * The API resource.
    *
    * @var string
    */
   protected $resource = 'ad';


   /**
    * The validation rules.
    *
    * @return array
    */
   protected function validation_rules(){
      return [
         'title'                          => 'required',
         'description'                    => 'required',
         'categoryId'                     => 'required|integer',
         'status'                         => 'required|in:ACTIVE,PAUSED,DELETED',
         'price.priceType'                => 'required',
         'price.amountCents'              => 'required|min:1|max:10000000000',
         'salutation'                     => 'required|in:COMPANY,MALE,FEMALE,FAMILY,UNKNOWN',
         'sellerName'                     => 'required',
         'postcode'                       => 'required',
         // 'bidMicros'                      => 'required',
         // 'budgets.total.limitMicros'      => 'required',
         'shippingOptions.*.type'           => 'required|in:SHIP,PICKUP',
         'shippingOptions.*.costCents'      => 'required|min:0|max:15000',
         'shippingOptions.*.time'           => 'required|in:1d,2d-5d,6d-10d',
         'shippingOptions.*.pickupLocation' => 'required',
      ];
   }



   /**
    * The validation messages.
    *
    * @return array
    */
   protected function validation_messages(){
      return [
         'title'                          => __('The ad title is required.', 'integration-marktplaats-for-woocommerce'),
         'description'                    => __('The ad description is required.', 'integration-marktplaats-for-woocommerce'),
         'categoryId'                     => __('The category is required.', 'integration-marktplaats-for-woocommerce'),
         'price.priceType'                => __('The price type is required.', 'integration-marktplaats-for-woocommerce'),
         'price.amountCents'              => __('The price amount is required and must be greater than 0.', 'integration-marktplaats-for-woocommerce'),
         'salutation'                     => __('The salutation is required.', 'integration-marktplaats-for-woocommerce'),
         'sellerName'                     => __('The seller name is required.', 'integration-marktplaats-for-woocommerce'),
         'postcode'                       => __('The postcode is required. Make sure it\'s defined in WooCommerce > Settigns > General > Store Address', 'integration-marktplaats-for-woocommerce'),
         // 'bidMicros'                      => __('The cost per click is required.', 'integration-marktplaats-for-woocommerce'),
         // 'budgets.total.limitMicros'      => __('The total budget for cost per click is required.', 'integration-marktplaats-for-woocommerce'),
         'shippingOptions.*.type'           => __('The shipping type is required and must be either "Ship" or "Pickup".', 'integration-marktplaats-for-woocommerce'),
         'shippingOptions.*.costCents'      => __('The shipping cost is required.', 'integration-marktplaats-for-woocommerce'),
         'shippingOptions.*.time'           => __('The shipping time is required.', 'integration-marktplaats-for-woocommerce'),
         'shippingOptions.*.pickupLocation' => __('The shipping pickup location is required.', 'integration-marktplaats-for-woocommerce'),
      ];
   }



   /**
    * Retrieves the ad.
    *
    * @param string $ad_id
    * @throws \Exception
    * @return array
    */
   public function get(string $ad_id){

      if(empty($ad_id)){
         throw new \Exception('No Ad ID provided.');
      }

      $response = $this->send_request("ad/{$ad_id}", [], 'GET', ['cache' => false]);

      if(200 == $response->status){
         return Util::obj_to_arr($response->body);
      }

      throw new \Exception( wp_json_encode($response->body) );

   }



   /**
    * Creates the ad.
    *
    * @param array $payload - the same payload structure as Marktplaats API has.
    * @throws \Exception
    * @return array
    */
   public function create(array $payload){

      $this->process_payload($payload);

      $response = $this->send_request('ad', $payload);

      if(201 == $response->status){
         return Util::obj_to_arr($response->body);
      }

      throw new \Exception( wp_json_encode($response->body) );
   }



   /**
    * Updates the ad.
    *
    * @param string $ad_id
    * @param array $payload - the same payload structure as BOL API has.
    * @throws \Exception
    * @return array
    */
   public function update(string $ad_id, array $payload){

      if(empty($ad_id)){
         throw new \Exception('No Ad ID provided.');
      }

      $this->process_payload($payload);

      $response = $this->send_request("ad/{$ad_id}", $payload, 'PUT');

      if(200 == $response->status){
         return Util::obj_to_arr($response->body);
      }

      throw new \Exception( wp_json_encode($response->body) );
   }



   /**
    * Deletes the ad.
    *
    * @param string $ad_id
    * @throws \Exception
    * @return array
    */
   public function delete(string $ad_id){

      if(empty($ad_id)){
         throw new \Exception('No Ad ID provided.');
      }

      $response = $this->send_request("ad/{$ad_id}/status/DELETED", [], 'PUT');

      if(200 == $response->status){
         return Util::obj_to_arr($response->body);
      }

      throw new \Exception( wp_json_encode($response->body) );
   }



   /**
    * Processes and validates the given payload.
    *
    * @param array $payload
    * @return void
    */
   private function process_payload(&$payload){

      $rules     = $this->validation_rules();
      $messages  = $this->validation_messages();
      $validator = new Validator;

      //remove unnecessary price validation
      if( ! in_array(Util::array($payload)->get('price/priceType'), ['FIXED_PRICE', 'BIDDING_FROM']) ){
         unset($rules['price.amountCents']);
      }

      //remove unnecessary shipping validation
      if( 'PICKUP' === Util::array($payload)->get('shippingOptions/0/type') ){
         unset($rules['shippingOptions.*.time'], $rules['shippingOptions.*.costCents']);
      }else{
         unset($rules['shippingOptions.*.pickupLocation']);
      }

      $validation = $validator->validate($payload, $rules, $messages);

      if ($validation->fails()){
         throw new \Exception( wp_json_encode($validation->errors()->all()) );
      }
   }

}