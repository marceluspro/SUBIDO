<?php
/**
 * Migration Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Migration_Hook implements Interface_Hook {


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init() {

      add_action('admin_init', [__CLASS__, 'process_settings']);

      add_filter(PREFIX . '\meta\get', [__CLASS__, 'process_product_meta'], 10, 3);
   }



   public static function process_settings(){

      $api_credentials = Option::get('api_credentials');

      if(empty($api_credentials)){
         Option::delete('is_authorized_live');

         Option::set('shipping_type', strtolower(Option::get('shipping_type', 'ship')));

         Option::set('salutation', Option::get('salution_type', 'male'));
         Option::delete('salution_type');

         Option::set('shipping_pickup_location', Option::get('shipping_pickupLocation'));
         Option::delete('shipping_pickupLocation');

         Option::delete('shrink_title');
         Option::delete('logs');
         Option::delete('updated_products');
         Option::delete('pause_outofstock');


         //process category mapping
         self::process_category_mapping();


         //delete ML webhooks
         $data_store = \WC_Data_Store::load('webhook');
         $webhooks   = $data_store->search_webhooks([]);

         foreach ( $webhooks as $id ) {
            $webhook = new \WC_Webhook( $id );
            if ( strpos($webhook->get_delivery_url(), '.woosa.nl/webhooks/') !== false ) {
               $webhook->delete( true );
            }
         }
      }
   }



   public static function process_category_mapping(){

      $terms = get_terms([
         'taxonomy'   => 'product_cat',
         'hide_empty' => false,
         'fields' => 'ids',
         'meta_query' => [
            'relation' => 'AND',
            [
               'key'     => 'mkt_root_category',
               'compare' => 'EXISTS',
            ],
            [
               'key'     => 'mkt_sub_category',
               'compare' => 'EXISTS',
            ],
         ],
      ]);

      if(!is_array($terms)){
         return;
      }

      foreach ($terms as $id) {

         $config_fields    = array_filter((array) get_term_meta($id, 'mkt_config_fields', true));
         $cpc_automatic    = get_term_meta($id, 'mkt_automatic_budget', true);
         $mapped_cat_id    = get_term_meta($id, 'mkt_sub_category', true);

         $api_category = new Service_API_Category;
         $category = $api_category->get($mapped_cat_id);

         if($category){
            $config_fields['mkt_cpc'] = $api_category->format_cpc(Util::array($category)->get('config/bidMicros'))['min'];
            $config_fields['mkt_cpc_total_budget'] = $api_category->format_cpc(Util::array($category)->get('config/totalBudgetMicros'))['min'];
         }

         $config_fields['mkt_cpc_automatic'] = $cpc_automatic;

         update_term_meta($id, Util::prefix('mapped_category_id'), $mapped_cat_id);
         update_term_meta($id, Util::prefix('config_fields'), $config_fields);

         delete_term_meta($id, 'mkt_root_category');
         delete_term_meta($id, 'mkt_sub_category');
         delete_term_meta($id, 'mkt_automatic_budget');
         delete_term_meta($id, 'mkt_total_budget');
         delete_term_meta($id, 'mkt_cost_per_click');
      }
   }



   public static function process_product_meta($value, $key, $meta){

      //remove hook to avoid infinite loop
      remove_filter(PREFIX . '\meta\get', [__CLASS__, 'process_product_meta'], 10, 3);

      $product = $meta->get_product();

      if($product instanceof \WC_Product){
         $product_id = $product->get_id();

         if(
            in_array($key, [Util::prefix('product_status'), Util::prefix('seller_name')]) &&
            (
               ! metadata_exists('post', $product_id, Util::prefix('seller_name')) &&
               metadata_exists('post', $product_id, '_mkt_sellerName')
            )
         ){

            $ad_id = get_post_meta($product_id, '_mkt_advertisement_id', true);
            $status = get_post_meta($product_id, '_mkt_status', true);
            $shipping = get_post_meta($product_id, '_mkt_shipping', true);

            if($ad_id){
               $meta->set('product_id', $ad_id);
               $meta->set_status('published');
            }

            if('PAUSED' === $status){
               $meta->set('on_hold', 'yes');
               $meta->set_status('paused');
            }

            $meta->set('title', get_post_meta($product_id, '_mkt_title', true));
            $meta->set('category', get_post_meta($product_id, '_mkt_categoryId', true));
            $meta->set('website_url', get_post_meta($product_id, '_mkt_website_url', true));
            $meta->set('shipping_type', strtolower(Util::array($shipping)->get('type')));
            $meta->set('shipping_cost', Util::array($shipping)->get('cost'));
            $meta->set('shipping_time', Util::array($shipping)->get('time'));
            $meta->set('shipping_pickup_location', Util::array($shipping)->get('pickupLocation'));
            $meta->set('phone', get_post_meta($product_id, '_mkt_phone', true));
            $meta->set('seller_name', get_post_meta($product_id, '_mkt_sellerName', true));
            $meta->set('allow_contact_by_email', get_post_meta($product_id, '_mkt_allowContactByEmail', true));
            $meta->set('salutation', get_post_meta($product_id, '_mkt_salutation', true));
            $meta->set('cpc', absint(get_post_meta($product_id, '_mkt_cpc', true)));
            $meta->set('cpc_total_budget', get_post_meta($product_id, '_mkt_total_budget', true));
            $meta->set('cpc_automatic', get_post_meta($product_id, '_mkt_automatic_budget', true));
            $meta->set('price', get_post_meta($product_id, '_mkt_price', true));
            $meta->set('price_type', get_post_meta($product_id, '_mkt_priceType', true));

            $meta->save();

            delete_post_meta($product_id, '_mkt_advertisement_id');
            delete_post_meta($product_id, '_mkt_title');
            delete_post_meta($product_id, '_mkt_categoryId');
            delete_post_meta($product_id, '_mkt_website_url');
            delete_post_meta($product_id, '_mkt_shipping');
            delete_post_meta($product_id, '_mkt_sellerName');
            delete_post_meta($product_id, '_mkt_allowContactByEmail');
            delete_post_meta($product_id, '_mkt_salutation');
            delete_post_meta($product_id, '_mkt_cpc');
            delete_post_meta($product_id, '_mkt_total_budget');
            delete_post_meta($product_id, '_mkt_automatic_budget');
            delete_post_meta($product_id, '_mkt_price');
            delete_post_meta($product_id, '_mkt_priceType');

            delete_post_meta($product_id, '_mkt_category_root_id');
            delete_post_meta($product_id, '_mkt_status');
            delete_post_meta($product_id, '_mkt_currency');
            delete_post_meta($product_id, '_mkt_pause_outofstock');
            delete_post_meta($product_id, '_mkt_desc_length');
            delete_post_meta($product_id, '_mkt_publish_product_success');
            delete_post_meta($product_id, '_mkt_title_length');
            delete_post_meta($product_id, '_mkt_action');
            delete_post_meta($product_id, '_mkt_postcode');
            delete_post_meta($product_id, '_mkt_phone');
         }
      }

      //add hook back
      add_filter(PREFIX . '\meta\get', [__CLASS__, 'process_product_meta'], 10, 3);

      return $value;
   }

}
