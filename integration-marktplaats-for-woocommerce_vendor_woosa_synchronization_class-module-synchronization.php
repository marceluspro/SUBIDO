<?php
/**
 * Module Synchronization
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Synchronization{


   /**
    * Whether or not the setting option `enable_product_sync` is enabled.
    *
    * @return boolean
    */
   public static function is_product_sync_enabled(){
      return Util::string_to_bool( Option::get('enable_product_sync', 'no') );
   }



   /**
    * Whether or not to sync new products.
    *
    * @return boolean
    */
   public static function is_new_product_sync_enabled(){

      if(self::is_new_product_sync_disabled()){
         return false;
      }

      return self::is_product_sync_enabled();
   }



   /**
    * Whether or not the setting option `pause_trashed_product` is enabled.
    *
    * @return boolean
    */
   public static function is_pause_trashed_product_enabled(){

      if( ! self::is_product_sync_enabled() ){
         return false;
      }

      return Util::string_to_bool( Option::get('pause_trashed_product', 'no') );
   }



   /**
    * Whether or not the setting option `enable_order_sync` is enabled.
    *
    * @return boolean
    */
   public static function is_order_sync_enabled(){
      return Util::string_to_bool( Option::get('enable_order_sync', 'no') );
   }



   /**
    * Checks whether or not the setting option `disable_new_product_sync` is enabled.
    *
    * @return bool
    */
   public static function is_new_product_sync_disabled(){

      if( ! self::is_product_sync_enabled() ){
         return true;
      }

      return Util::string_to_bool( Option::get('disable_new_product_sync', 'no') );
   }



   /**
    * Checks whether or not the setting option `disable_product_price_sync` is enabled
    *
    * @return bool
    */
   public static function is_product_price_sync_disabled(){

      if( ! self::is_product_sync_enabled() ){
         return true;
      }

      return Util::string_to_bool( Option::get('disable_product_price_sync', 'no') );
   }



   /**
    * Add sync setting sections
    *
    * @param $sections
    * @return array
    */
   public static function synchronization_sections($sections) {

      $sections = array_merge($sections, [
         'products' => [
            'name' => __('Products', 'integration-marktplaats-for-woocommerce'),
            'fields' => [
               [
                  'name'  => __('Synchronize products', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => sprintf(__('Enable this if you want to automatically synchronize your shop\'s products with %s whenever a product is created, updated, trashed or deleted.', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
                  'id'    => 'enable_product_sync',
                  'type'  => 'toggle',
                  'value' => Option::get('enable_product_sync', 'no')
               ],
               [
                  'name'  => __('Do not synchronize new products', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'disable_new_product_sync',
                  'type'  => 'toggle',
                  'show_if' => 'enable_product_sync',
                  'desc'  => __('Enable this if you want to prevent newly created products in your shop from being automatically synchronized.', 'integration-marktplaats-for-woocommerce'),
                  'value' => Option::get('disable_new_product_sync', 'no'),
               ],
               [
                  'name'  => __('Do not synchronize prices', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'disable_product_price_sync',
                  'type'  => 'toggle',
                  'show_if' => 'enable_product_sync',
                  'desc'  => __('Enable this if you want to prevent product prices from being automatically synchronized when updated in your shop.', 'integration-marktplaats-for-woocommerce'),
                  'value' => Option::get('disable_product_price_sync', 'no'),
               ],
               [
                  'name'  => __('Pause offers for trashed products', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'pause_trashed_product',
                  'type'  => 'toggle',
                  'show_if' => 'enable_product_sync',
                  'desc'  => sprintf(__('Enable this if you want to pause the %s offer when its corresponding product is moved to the trash in your shop.', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
                  'value' => Option::get('pause_trashed_product', 'no'),
               ],
               [
                  'name'  => __('Preserve stock offset', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => __('Define a value that will be subtracted from the product stock. This will help to avoid selling out of stock products.', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'preserve_stock_offset',
                  'type'  => 'number',
                  'value' => Option::get('preserve_stock_offset', '0')
               ],
               [
                  'name'  => __('Internal reference source', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => __('Choose the source of value to be used as internal product reference.', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'reference_source',
                  'type'  => 'field_value_source',
                  'options' => [
                     'default'      => __('Default', 'integration-marktplaats-for-woocommerce'),
                     'product_id'   => __('Product id', 'integration-marktplaats-for-woocommerce'),
                     'custom_field' => __('Product custom field', 'integration-marktplaats-for-woocommerce'),
                     'sku'          => __('Product SKU', 'integration-marktplaats-for-woocommerce'),
                  ],
                  'value' => Option::get('reference_source', 'default'),
               ],
               [
                  'name'  => __('EAN source', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => __('Choose the source of value to be used as product EAN.', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'ean_source',
                  'type'  => 'field_value_source',
                  'value' => Option::get('ean_source', 'default'),
               ],
               [
                  'name'    => __('Condition', 'integration-marktplaats-for-woocommerce'),
                  'id'      => 'condition',
                  'desc'    => __('Choose which product condition to be used as default.', 'integration-marktplaats-for-woocommerce'),
                  'type'    => 'select',
                  'options' => ['' => __('Please select', 'integration-marktplaats-for-woocommerce')],
                  'value'   => Option::get('condition'),
               ],
            ]
         ],
         'orders' => [
            'name' => __('Orders', 'integration-marktplaats-for-woocommerce'),
            'fields' => [
               [
                  'name'  => __('Import orders', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => sprintf(__('Enable this if you to automatically import orders from %s into your shop.', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
                  'id'    => 'enable_import_order',
                  'type'  => 'toggle',
                  'value' => Option::get('enable_import_order', 'no')
               ],
               [
                  'name'  => __('Disable processing order email', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => __('Enable this if you want to disable the email that is received by customer when the imported order has status processing.', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'disable_processing_order_email',
                  'type'  => 'toggle',
                  'show_if' => 'enable_import_order',
                  'value' => Option::get('disable_processing_order_email'),
               ],
               [
                  'name'  => __('Disable completed order email', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => __('Enable this if you want to disable the email that is received by customer when the imported order has status completed.', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'disable_completed_order_email',
                  'type'  => 'toggle',
                  'show_if' => 'enable_import_order',
                  'value' => Option::get('disable_completed_order_email'),
               ],
               [
                  'name'    => __('Import frequency', 'integration-marktplaats-for-woocommerce'),
                  'desc'    => __('Choose how often to import orders.', 'integration-marktplaats-for-woocommerce'),
                  'id'      => 'import_order_frequency',
                  'type'    => 'select',
                  'show_if' => 'enable_import_order',
                  'options' => [
                     '' => __('Please select', 'integration-marktplaats-for-woocommerce'),
                     'every_10_minutes' => __('Every 10 minutes', 'integration-marktplaats-for-woocommerce'),
                     'every_30_minutes' => __('Every 30 minutes', 'integration-marktplaats-for-woocommerce'),
                     'hourly' => __('Hourly', 'integration-marktplaats-for-woocommerce'),
                  ],
                  'value'   => Option::get('import_order_frequency', 'hourly')
               ],
               [
                  'name'    => __('Address format', 'integration-marktplaats-for-woocommerce'),
                  'desc'    => __('Choose which address format to be used for the imported orders.', 'integration-marktplaats-for-woocommerce'),
                  'id'      => 'address_format',
                  'type'    => 'select',
                  'show_if' => 'enable_import_order',
                  'options' => [
                     'format_1' => '123AB Hillside Avenue',
                     'format_2' => 'Hillside Avenue 123AB',
                  ],
               ],
            ],
         ],
         'shipping' => [
            'name' => __('Shipping', 'integration-marktplaats-for-woocommerce'),
            'fields' => [
               [
                  'name'  => __('Use my shipping carrier', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => __('Enable this if you want to use your own shipping carrier by default.', 'integration-marktplaats-for-woocommerce'),
                  'id'    => 'use_my_shipping_carrier',
                  'type'  => 'toggle',
                  'value' => Option::get('use_my_shipping_carrier', 'no')
               ],
               [
                  'name'    => __('My shipping carrier', 'integration-marktplaats-for-woocommerce'),
                  'desc'    => __('Choose which shipping carrier to be used as default.', 'integration-marktplaats-for-woocommerce'),
                  'id'      => 'shipping_carrier',
                  'type'    => 'select',
                  'show_if' => 'use_my_shipping_carrier',
                  'options' => ['' => __('Please select', 'integration-marktplaats-for-woocommerce')],
                  'value'   => Option::get('shipping_carrier'),
               ],
               [
                  'name'  => __('My tracking code source', 'integration-marktplaats-for-woocommerce'),
                  'desc'  => sprintf(
                     __('Define the order meta key where the tracking code is added by your 3rd-party shipping plugin. If the meta key value contains more than just the tracking code (e.g., an array/object with multiple values), you must specify the path to the tracking code within the array/object. For example, if the meta key is %s and its value is an array like: %s then you should provide the meta key as: %s', 'integration-marktplaats-for-woocommerce'),
                     '<code>shipping_data</code>',
                     '<code>["shipping_id" => 1234, "available_types" => [1,2,3], "tracking_code" => "3I5URI532U55U"]</code>',
                     '<code>shipping_data.tracking_code</code>'
                  ),
                  'id'    => 'tracking_code_meta_key',
                  'type'  => 'text',
                  'show_if' => 'use_my_shipping_carrier',
                  'value' => Option::get('tracking_code_meta_key'),
               ],
            ]
         ],
      ]);

      return $sections;
   }



   /**
    * Retrieves a given template file.
    *
    * @param string $template_name
    * @param array $args
    * @return string
    */
   public static function get_template($template_name, $args = []){
      return Util::get_template($template_name, $args, dirname(__FILE__), Util_File::build_path(['templates']));
   }

}