<?php
/**
 * Synchronization Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Synchronization_Hook implements Interface_Hook {


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init() {

      add_filter(PREFIX . '\module\synchronization\sections', [__CLASS__, 'synchronization_sections']);
   }



   /**
    * Add setting sections for the sync
    *
    * @param $sections
    * @return array
    */
   public static function synchronization_sections($sections) {

      //PRODUCTS SECTION

      foreach($sections['products']['fields'] as $index => $field){
         if(in_array($field['id'], [
            'preserve_stock_offset',
            'reference_source',
            'ean_source',
            'condition',
         ])){
            unset($sections['products']['fields'][$index]);
         }
      }

      $sections['products']['fields'] = array_merge($sections['products']['fields'], [
         [
            'id'    => 'use_wc_price',
            'name'  => __('Use WooCommerce price', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Enable this if you want to use the default WooCommerce price.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'use_wc_price',
            'value' => Option::get('use_wc_price'),
            'price_addition'    => '',
         ],
         [
            'id'    => 'allow_contact_by_email',
            'name'  => __('Allow contact by email', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Enable this if you want to show the email address on the ad.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'toggle',
            'value' => Option::get('allow_contact_by_email', 'no'),
         ],
         [
            'id'    => 'price_type',
            'name'  => __('Price type', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Choose the source of value to be used as price type.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'select',
            'value' => Option::get('price_type'),
            'options' => Service_API::price_types()
         ],
         [
            'id'    => 'salutation',
            'name'  => __('Salutation', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Choose the default salutation form.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'select',
            'value' => Option::get('salutation'),
            'options' => Service_API::salutation_forms()
         ],
         [
            'id'    => 'seller_name',
            'name'  => __('Seller name', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Define the default seller name.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'text',
            'value' => Option::get('seller_name'),
         ],
         [
            'id'    => 'phone',
            'name'  => __('Phone number', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Define the default seller phone number.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'text',
            'value' => Option::get('phone'),
         ],
         [
            'id'    => 'website_url',
            'name'  => __('Website URL', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('An external URL that is shown on the ad page. If nothing is provided then it will use the product page URL.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'url',
            'value' => Option::get('website_url'),
         ],
         [
            'id'    => 'footer_description',
            'name'  => __('Footer description', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Define the description that will be added to the end of each ad.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'editor',
            'value' => Option::get('footer_description'),
         ],
      ]);

      //ORDERS SECTION

      unset($sections['orders']);

      //SHIPPING SECTION

      $sections['shipping']['fields'] = [
         [
            'id'    => 'shipping_type',
            'name'  => __('Type', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Choose the default shipping type.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'select',
            'value' => Option::get('shipping_type', 'ship'),
            'options' => Service_API::shipping_types()
         ],
         [
            'id'    => 'shipping_cost',
            'name'  => __('Cost', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Define the default shipping cost.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'number',
            'show_if' => ['id' => 'shipping_type', 'value' => 'ship'],
            'value' => Option::get('shipping_cost', '0.0'),
         ],
         [
            'id'    => 'shipping_time',
            'name'  => __('Time', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Choose the default shipping time.', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'select',
            'show_if' => ['id' => 'shipping_type', 'value' => 'ship'],
            'value' => Option::get('shipping_time', '2d-5d'),
            'options' => Service_API::shipping_timeframes(),
         ],
         [
            'id'    => 'shipping_pickup_location',
            'name'  => __('Pickup location', 'integration-marktplaats-for-woocommerce'),
            'desc'  => __('Define the default shipping pickup location (zipcode).', 'integration-marktplaats-for-woocommerce'),
            'type'  => 'text',
            'show_if' => ['id' => 'shipping_type', 'value' => 'pickup'],
            'value' => Option::get('shipping_pickup_location', ''),
            'placeholder' => 'e.g. 1097DN',
         ],
      ];

      return $sections;
   }
}
