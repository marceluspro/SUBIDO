<?php
/**
 * Ad Task
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Ad_Task extends Module_Abstract_Product_Task_Marketplace {


   /**
    * The ad title.
    *
    * @var string
    */
   protected $title;


   /**
    * The ad description.
    *
    * @var string
    */
   protected $description;


   /**
    * The price type.
    *
    * @var string
    */
   protected $price_type;


   /**
    * The category.
    *
    * @var string
    */
   protected $category;


   /**
    * The cost per click.
    *
    * @var string
    */
   protected $cpc;


   /**
    * The total budget of cpc.
    *
    * @var string
    */
   protected $cpc_total_budget;


   /**
    * Whether or not the cpc is automatic.
    *
    * @var bool
    */
   protected $cpc_automatic;


   /**
    * The shipping type.
    *
    * @var string
    */
   protected $shipping_type;


   /**
    * The shipping cost.
    *
    * @var string
    */
   protected $shipping_cost;


   /**
    * The shipping time.
    *
    * @var string
    */
   protected $shipping_time;


   /**
    * The shipping pickup location.
    *
    * @var string
    */
   protected $shipping_pickup_location;


   /**
    * Whether or not to display the email in the ad.
    *
    * @var bool
    */
   protected $allow_contact_by_email;


   /**
    * The salutation.
    *
    * @var string
    */
   protected $salutation;


   /**
    * The seller name.
    *
    * @var string
    */
   protected $seller_name;


   /**
    * The postcode.
    *
    * @var string
    */
   protected $postcode;


   /**
    * The phone.
    *
    * @var string
    */
   protected $phone;


   /**
    * The website URL.
    *
    * @var string
    */
   protected $website_url;


   /**
    * The footer_description.
    *
    * @var string
    */
   protected $footer_description;



   /**
    * Construct of this class
    *
    * @param array $data
    * @throws \Exception
    */
   public function __construct(array $data) {

      parent::__construct($data);

      $this->offer_id                 = Util::array($data)->get('meta_data/' . Util::prefix('product_id'));
      $this->on_hold                  = Util::array($data)->get('meta_data/' . Util::prefix('on_hold'));

      $this->title                    = Util::array($data)->get('meta_data/' . Util::prefix('title'));
      $this->description              = Util::array($data)->get('description');
      $this->price_type               = strtoupper(Util::array($data)->get('meta_data/' . Util::prefix('price_type')));
      $this->price                    = Util::array($data)->get('meta_data/' . Util::prefix('price'));
      $this->category                 = (int) Util::array($data)->get('meta_data/' . Util::prefix('category'));
      $this->cpc                      = (string) (((float) Util::array($data)->get('meta_data/' . Util::prefix('cpc'))) * 1000000); //micro
      $this->cpc_total_budget         = (string) (((float) Util::array($data)->get('meta_data/' . Util::prefix('cpc_total_budget'))) * 1000000); //micro
      $this->cpc_automatic            = Util::string_to_bool(Util::array($data)->get('meta_data/' . Util::prefix('cpc_automatic')));
      $this->shipping_type            = strtoupper(Util::array($data)->get('meta_data/' . Util::prefix('shipping_type')));
      $this->shipping_cost            = Util::array($data)->get('meta_data/' . Util::prefix('shipping_cost'));
      $this->shipping_time            = Util::array($data)->get('meta_data/' . Util::prefix('shipping_time'));
      $this->shipping_pickup_location = Util::array($data)->get('meta_data/' . Util::prefix('shipping_pickup_location'));
      $this->allow_contact_by_email   = Util::string_to_bool( Util::array($data)->get('meta_data/' . Util::prefix('allow_contact_by_email')) );
      $this->salutation               = strtoupper(Util::array($data)->get('meta_data/' . Util::prefix('salutation')));
      $this->seller_name              = Util::array($data)->get('meta_data/' . Util::prefix('seller_name'));
      $this->postcode                 = preg_replace('/\s+/', '', get_option('woocommerce_store_postcode'));
      $this->phone                    = Util::array($data)->get('meta_data/' . Util::prefix('phone'));
      $this->website_url              = Util::array($data)->get('meta_data/' . Util::prefix('website_url'));
      $this->footer_description       = Util::array($data)->get('meta_data/' . Util::prefix('footer_description'));
   }



   /**
    * Checks whether or not the given ad already exists.
    *
    * @return void
    */
   public function is_product_in_catalog(){}



   /**
    * Retrieves the price.
    *
    * @return string
    */
   public function get_price(){
      return $this->format_cost_amount($this->price);
   }



   /**
    * Retrieves the ad status.
    *
    * @return string
    */
   public function get_status(bool $on_hold = false) {

      if($on_hold){
         return 'PAUSED';
      }

      return 'ACTIVE';
   }



   /**
    * Retrieves the title.
    *
    * @return string
    */
   public function get_title(){

      if(strlen($this->title) > 60){
         return trim(substr($this->title, 0, 57)) . '...';
      }

      return $this->title;
   }



   /**
    * Retrieves the description.
    *
    * @return string
    */
   public function get_description(){
      return $this->description . $this->footer_description;
   }



   /**
    * Retrieves the bid;
    *
    * @return string
    */
   public function get_bid_micros(){

      if($this->cpc_automatic){
         $this->cpc = 'AUTOMATIC';
      }

      return $this->cpc;
   }



   /**
    * Retrieves the images.
    *
    * @return array
    */
   public function get_images(){

      $results = [];
      $product = $this->meta->get_product();

      if($product instanceof \WC_Product){

         $urls = Module_Marketplace_Product::get_product_image_urls($product);

         foreach($urls as $url){
            $results[] = [
               'src' => $url
            ];
         }
      }

      return $results;
   }



   /**
    * Retrieves the website URL.
    *
    * @return string
    */
   public function get_website_url(){

      if(Util::is_valid_url($this->website_url)){
         return $this->website_url;
      }

      return $this->meta->get_product()->get_permalink();
   }



   /**
    * Creates the ad.
    *
    * @return void
    */
   public function create(){

      try{

         if( ! $this->is_valid() ){
            return;
         }

         $payload  = $this->build_payload();
         $api_ad   = new Service_API_Ad();
         $response = $api_ad->create($payload);

         $this->meta->set_status('published');
         $this->meta->set("product_id", $response['id']);
         $this->meta->save();

      } catch (\Exception $e) {
         $this->handle_errors($e);
      }
   }



   /**
    * Updates the ad.
    *
    * @param bool $on_hold
    * @return void
    */
   public function update($on_hold = null){

      try{

         if( ! $this->is_valid() ){
            return;
         }

         $payload = $this->build_payload(true);

         if($this->is_pause_forced() || 'outofstock' === $this->stock_status){
            $on_hold = true;
         }

         if(is_null($on_hold)){
            $on_hold = $this->is_on_hold();
         }

         if('outofstock' !== $this->stock_status && 'yes' === $this->meta->get('on_hold_by_stock')){
            $on_hold = false;
         }

         $payload['status'] = $this->get_status($on_hold);

         $api_ad   = new Service_API_Ad();
         $api_ad->update($this->get_remote_id(), $payload);

         $this->meta->set('on_hold', $on_hold ? 'yes' : 'no');

         if($on_hold){
            $this->meta->set_status('paused');
         }else{
            $this->meta->set_status('published');
         }

         if( ! $this->is_on_hold() && 'outofstock' === $this->stock_status ){
            $this->meta->set('on_hold_by_stock', 'yes');
         }else{
            $this->meta->delete('on_hold_by_stock');
         }

         $this->meta->save();

      } catch (\Exception $e) {
         $this->handle_errors($e);
      }
   }



   /**
    * Deletes the ad.
    *
    * @return void
    */
   public function delete(){

      try{

         $api_ad = new Service_API_Ad();
         $api_ad->delete($this->get_remote_id());

      } catch (\Exception $e) {
         $this->handle_errors($e);
      }

      //always delete reference data
      $this->meta->delete('on_hold');
      $this->meta->delete('on_hold_by_stock');
      $this->meta->delete('product_id');
      $this->meta->delete_status();
      $this->meta->save();
   }



   /**
    * Builds the request payload.
    *
    * @param bool $update
    * @return array
    */
   private function build_payload(bool $update = false){

      $payload = [
         'title' => $this->get_title(),
         'description' => $this->get_description(),
         'categoryId' => $this->category,
         'status' => $this->get_status(),
         'price' => [
            'priceType' => $this->price_type,
            'amountCents' => $this->get_price()
         ],
         'salutation' => $this->salutation,
         'sellerName' => $this->seller_name,
         'phoneNumber' => $this->phone,
         'allowContactByEmail' => $this->allow_contact_by_email,
         'postcode' => $this->postcode,
         'bidMicros' => $this->get_bid_micros(),
         'budgets' => [
            'daily' => [
               'limitMicros' => 'UNLIMITED'
            ],
            'total' => [
               'limitMicros' => $this->cpc_total_budget
            ]
         ],
         'shippingOptions' => [
            [
               'type' => $this->shipping_type,
               'costCents' => $this->format_cost_amount($this->shipping_cost),
               'time' => $this->shipping_time,
               'pickupLocation' => $this->shipping_time,
            ]
         ],
         'images' => $this->get_images(),
         'links' => [
            'url' => $this->get_website_url()
         ],
      ];

      if($update){
         $payload['id'] = (int) $this->get_remote_id();
      }

      //remove unnecessary price data
      if( ! in_array($this->price_type, ['FIXED_PRICE', 'BIDDING_FROM']) ){
         unset($payload['price']['amountCents']);
      }

      //remove unnecessary shipping data
      if('PICKUP' === $this->shipping_type){
         unset($payload['shippingOptions'][0]['costCents'], $payload['shippingOptions'][0]['time']);
      }else{
         unset($payload['shippingOptions'][0]['pickupLocation']);
      }

      return $payload;
   }



   /**
    * Formats the given cost amount to the expected format.
    *
    * @param string $cost
    * @return int
    */
   private function format_cost_amount($cost){

      if (empty($cost)) {
         return 0;
      }

      $cost = str_replace(',', '.', $cost);
      $cost = preg_replace('/\.(?=.*\.)/', '', $cost);

      return intval($cost * 100); //cents
   }

}