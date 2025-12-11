<?php
/**
 * Meta Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Meta_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\product_task\util\get_payload', [__CLASS__, 'set_product_task_payload'], 91, 2);

      add_filter(PREFIX . '\meta\get', [__CLASS__, 'set_general_meta'], 10, 3);

   }



   /**
    * This ensures we add fallbacks for the product payload retrieved via `Product Task` module.
    * This is required to solve the case when the product meta fields are not defined yet or when global settings have been changed.
    *
    * @param array $payload
    * @param \WC_Product $product
    * @return array
    */
   public static function set_product_task_payload($payload, $product){

      $meta = new Module_Meta($product->get_id());

      if( ! $meta->is_product_type(['simple', 'variation']) ){
         return $payload;
      }

      $payload['meta_data'][Util::prefix('title')] = $meta->get('title');
      $payload['meta_data'][Util::prefix('price_type')] = $meta->get('price_type');
      $payload['meta_data'][Util::prefix('price')] = $meta->get('price');
      $payload['meta_data'][Util::prefix('category')] = $meta->get('category');
      $payload['meta_data'][Util::prefix('cpc')] = $meta->get('cpc');
      $payload['meta_data'][Util::prefix('cpc_automatic')] = $meta->get('cpc_automatic');
      $payload['meta_data'][Util::prefix('cpc_total_budget')] = $meta->get('cpc_total_budget');
      $payload['meta_data'][Util::prefix('shipping_type')] = $meta->get('shipping_type');
      $payload['meta_data'][Util::prefix('shipping_cost')] = $meta->get('shipping_cost');
      $payload['meta_data'][Util::prefix('shipping_time')] = $meta->get('shipping_time');
      $payload['meta_data'][Util::prefix('shipping_pickup_location')] = $meta->get('shipping_pickup_location');
      $payload['meta_data'][Util::prefix('allow_contact_by_email')] = $meta->get('allow_contact_by_email');
      $payload['meta_data'][Util::prefix('salutation')] = $meta->get('salutation');
      $payload['meta_data'][Util::prefix('seller_name')] = $meta->get('seller_name');
      $payload['meta_data'][Util::prefix('phone')] = $meta->get('phone');
      $payload['meta_data'][Util::prefix('website_url')] = $meta->get('website_url');
      $payload['meta_data'][Util::prefix('footer_description')] = $meta->get('footer_description');

      return $payload;
   }



   /**
    * Sets general meta value by using fallback.
    *
    * @param mixed $value
    * @param string $key
    * @param Module_Meta $meta
    * @return mixed
    */
   public static function set_general_meta($value, $key, $meta) {

      //remove hook to avoid infinite loop
      remove_filter(PREFIX . '\meta\get', [__CLASS__, 'set_general_meta'], 10, 3);

      if($meta->is_product_type(['simple', 'variation'])){

         switch($key){

            //title
            case Util::prefix('title'):

               if( ! $meta->is_checked('use_local_title') ){

                  $value = $meta->get_product()->get_title();

                  $meta->set('title', $value)->save();
               }

               break;

            //price_type
            case Util::prefix('price_type'):

               if( ! $meta->is_checked('use_local_price_type') ){

                  $value = Option::get('price_type', 'fixed_price');

                  $meta->set('price_type', $value)->save();
               }

               break;

            //price
            case Util::prefix('price'):

               $use_wc_price  = Option::get('use_wc_price');

               if( ! $meta->is_checked('use_local_price') && 'yes' === $use_wc_price ){

                  if( empty($meta->get('price')) || ! Module_Synchronization::is_product_price_sync_disabled()){

                     $price    = $meta->get_product()->get_price();
                     $addition = Option::get('price_addition', '0');
                     $value    = Util::price($price)->addition($addition);

                     $meta->set('price', $value)->save();
                  }
               }

               break;

            //category
            case Util::prefix('category'):

               if( ! $meta->is_checked('use_local_category') ){

                  $product = $meta->get_product();

                  if($meta->is_product_type('variation')){
                     $product = wc_get_product($product->get_parent_id());
                  }

                  $categories = $product->get_category_ids();

                  foreach($categories as $cat_id){
                     $value = get_term_meta($cat_id, Util::prefix('mapped_category_id'), true);

                     if(!empty($value)){
                        break;
                     }
                  }

                  $meta->set('category', $value)->save();
               }

               break;

            //cpc
            case Util::prefix('cpc'):

               if( ! $meta->is_checked('use_local_cpc') ){

                  $product = $meta->get_product();

                  if($meta->is_product_type('variation')){
                     $product = wc_get_product($product->get_parent_id());
                  }

                  $value = Util::array( self::get_category_configs($product) )->get(Util::prefix('cpc'), 0.01);

                  $meta->set('cpc', $value)->save();
               }

               break;

            //cpc_automatic
            case Util::prefix('cpc_automatic'):

               if( ! $meta->is_checked('use_local_cpc') ){

                  $product = $meta->get_product();

                  if($meta->is_product_type('variation')){
                     $product = wc_get_product($product->get_parent_id());
                  }

                  $value = 'on' === Util::array( self::get_category_configs($product) )->get(Util::prefix('cpc_automatic')) ? 'yes' : 'no';

                  $meta->set('cpc_automatic', $value)->save();
               }

               break;

            //cpc_total_budget
            case Util::prefix('cpc_total_budget'):

               if( ! $meta->is_checked('use_local_cpc') ){

                  $product = $meta->get_product();

                  if($meta->is_product_type('variation')){
                     $product = wc_get_product($product->get_parent_id());
                  }

                  $value = Util::array( self::get_category_configs($product) )->get(Util::prefix('cpc_total_budget'), 2);

                  $meta->set('cpc_total_budget', $value)->save();
               }

               break;

            //shipping_type
            case Util::prefix('shipping_type'):

               if( ! $meta->is_checked('use_local_shipping_type') ){

                  $value = Option::get('shipping_type', 'ship');

                  $meta->set('shipping_type', $value)->save();
               }

               break;

            //shipping_cost
            case Util::prefix('shipping_cost'):

               if( ! $meta->is_checked('use_local_shipping_cost') ){

                  $value = Option::get('shipping_cost', '0.0');

                  $meta->set('shipping_cost', $value)->save();
               }

               break;

            //shipping_time
            case Util::prefix('shipping_time'):

               if( ! $meta->is_checked('use_local_shipping_time') ){

                  $value = Option::get('shipping_time', '2d-5d');

                  $meta->set('shipping_time', $value)->save();
               }

               break;

            //shipping_pickup_location
            case Util::prefix('shipping_pickup_location'):

               if( ! $meta->is_checked('use_local_shipping_pickup_location') ){

                  $value = Option::get('shipping_pickup_location');

                  $meta->set('shipping_pickup_location', $value)->save();
               }

               break;

            //allow_contact_by_email
            case Util::prefix('allow_contact_by_email'):

               if( ! $meta->is_checked('use_local_allow_contact_by_email') ){

                  $value = Option::get('allow_contact_by_email', 'no');

                  $meta->set('allow_contact_by_email', $value)->save();
               }

               break;

            //salutation
            case Util::prefix('salutation'):

               if( ! $meta->is_checked('use_local_salutation') ){

                  $value = Option::get('salutation', 'male');

                  $meta->set('salutation', $value)->save();
               }

               break;

            //seller_name
            case Util::prefix('seller_name'):

               if( ! $meta->is_checked('use_local_seller_name') ){

                  $value = Option::get('seller_name');

                  $meta->set('seller_name', $value)->save();
               }

               break;

            //phone
            case Util::prefix('phone'):

               if( ! $meta->is_checked('use_local_phone') ){

                  $value = Option::get('phone');

                  $meta->set('phone', $value)->save();
               }

               break;

            //website_url
            case Util::prefix('website_url'):

               if( ! $meta->is_checked('use_local_website_url') ){

                  $value = Option::get('website_url');

                  if(empty($value)){
                     $value = $meta->get_product()->get_permalink();
                  }

                  $meta->set('website_url', $value)->save();
               }

               break;

            //footer_description
            case Util::prefix('footer_description'):

               if( ! $meta->is_checked('use_local_footer_description') ){

                  $value = Option::get('footer_description');

                  $meta->set('footer_description', $value)->save();
               }

               break;
         }

      }

      //add hook back
      add_filter(PREFIX . '\meta\get', [__CLASS__, 'set_general_meta'], 10, 3);

      return $value;
   }



   /**
    * Retrieves the category fields for the given product.
    *
    * @param \WC_Product $product
    * @return array
    */
   private static function get_category_configs(\WC_Product $product){

      $results = [];

      //use local product category if any
      if('yes' === $product->get_meta(Util::prefix('use_local_category'))){
         $cat_id = $product->get_meta(Util::prefix('category'));
         $api_category = new Service_API_Category;
         $config = $api_category->get_config($cat_id);

         return [
            Util::prefix('cpc') => $config['cpc']['min'],
            Util::prefix('cpc_total_budget') => $config['cpc_total_budget']['min'],
         ];
      }

      $categories = $product->get_category_ids();

      foreach($categories as $cat_id){
         $results = get_term_meta($cat_id, Util::prefix('config_fields'), true);

         if(!empty($results)){
            break;
         }
      }

      return $results;
   }


}
