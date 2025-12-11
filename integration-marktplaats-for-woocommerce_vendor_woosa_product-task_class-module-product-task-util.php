<?php
/**
 * Module Product Task Util
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Task_Util{


   /**
    * Updates the product lookup tables.
    *
    * @return void
    */
   public static function update_lookup_table(){

      if(! wc_update_product_lookup_tables_is_running() ) {

         self::update_term_count();

         wc_update_product_lookup_tables();
      }
   }



   /**
    * Retrieves the parent product (variable) by meta key.
    *
    * @param string $parent_id
    * @return array
    */
   public static function get_parent_by_meta($parent_id){

      global $wpdb;

      $result = [];

      if( ! empty($parent_id) ){

         $sql = sprintf("SELECT
               p.ID AS id,
               pm1.meta_value AS product_id,
               pm2.meta_value AS source_variation_sku
            FROM $wpdb->posts AS p
            LEFT JOIN $wpdb->postmeta AS pm1
               ON pm1.post_id = p.ID AND pm1.meta_key = '%1\$s_product_id'
            LEFT JOIN $wpdb->postmeta AS pm2
               ON pm2.post_id = p.ID AND pm2.meta_key = '%1\$s_source_variation_sku'
            WHERE p.post_type = 'product'
               AND p.post_parent = '0'
               AND pm1.meta_value = '$parent_id'
         ", PREFIX);

         $query = $wpdb->get_row($sql, 'ARRAY_A');

         if(isset($query['id'])){
            $result = [
               'id'        => $query['id'],
               'meta_data' => [
                  PREFIX . '_product_id' => $query['product_id'],
                  PREFIX . '_source_variation_sku' => $query['source_variation_sku'],
               ],
            ];
         }

      }

      return $result;
   }



   /**
    * Builds the required payload schema for variable product.
    *
    * @param array $data
    * @param array $extra_meta
    * @return array
    */
   public static function build_variable_payload(array $data, array $extra_meta = []){

      $default = [
         'type'              => 'variable',
         'name'              => Util::array($data)->get('name'),
         'description'       => Util::array($data)->get('description'),
         'short_description' => Util::array($data)->get('short_description'),
      ];

      $default_meta = [
         '_manage_stock'        => 'no',
         PREFIX . '_parent_id'  => 0,
         PREFIX . '_attributes' => Util::array($data)->get('meta_data/' . Util::prefix('attributes'), []),
         PREFIX . '_images'     => Util::array($data)->get('meta_data/' . Util::prefix('images'), []),
         PREFIX . '_categories' => Util::array($data)->get('meta_data/' . Util::prefix('categories'), []),
      ];

      $meta_data = array_merge($default_meta, $extra_meta);
      $payload   = array_merge($default, ['meta_data' => $meta_data]);

      return apply_filters(PREFIX . '\product_task\build_variable_payload', $payload, $data, $extra_meta);
   }



   /**
    * Retrieves product categories.
    *
    * @param \WC_Product $product
    * @return array
    */
   public static function get_categories(\WC_Product $product){

      $results = [];
      $terms   = get_the_terms($product->get_id(), 'product_cat');

      if(is_array($terms)){

         foreach($terms as $term){
            $results[] = [
               'id'        => $term->term_id,
               'name'      => $term->name,
               'parent_id' => $term->parent,
            ];
         }
      }

      return $results;
   }



   /**
    * Retrieves product images.
    *
    * @param \WC_Product $product
    * @return array
    */
   public static function get_images(\WC_Product $product){

      $results = [];
      $img_ids = array_filter( array_merge([$product->get_image_id()], $product->get_gallery_image_ids()) );

      foreach($img_ids as $id){
         $results[] = wp_get_attachment_url($id);
      }

      return $results;
   }



   /**
    * Retrieves product attributes. It supports custom attributes as well.
    *
    * @param \WC_Product $product
    * @return array
    */
   public static function get_attributes(\WC_Product $product){

      $results = [];

      if('variation' === $product->get_type()){

         foreach($product->get_variation_attributes(false) as $key => $value){

            $taxonomy = $key;
            $term     = get_term_by('slug', $value, $taxonomy);

            if(isset($term->term_id)){
               $value = $term->name;
            }

            $results[] = [
               'name'               => ucfirst( str_replace('pa_', '', $taxonomy) ),
               'value'              => $value,
               'used_for_variation' => true,
            ];
         }

      }else{

         foreach($product->get_attributes() as $attribute){

            if($attribute instanceof \WC_Product_Attribute){
               $data     = $attribute->get_data();
               $taxonomy = $data['name'];
               $options  = $data['options'];
               $value    = [];

               foreach($options as $option){
                  $term = get_term($option, $taxonomy);

                  if(isset($term->term_id)){
                     $value[] = $term->name;
                  }
               }

               $results[] = [
                  'name'               => ucfirst( str_replace('pa_', '', $taxonomy) ),
                  'value'              => empty($value) ? $options : $value,
                  'used_for_variation' => $data['variation'],
               ];
            }
         }
      }

      return $results;
   }



   /**
    * Adds values from the variation attributes to the parent attributes.
    *
    * @param array $parent_attributes
    * @param array $variation_attributes
    * @return array
    */
   public static function combine_variation_with_parent_attribute(array $parent_attributes, array $variation_attributes){

      $values = [];

      foreach($variation_attributes as $v_attribute){
         if($v_attribute['used_for_variation']){
            $values[$v_attribute['name']] = $v_attribute['value'];
         }
      }

      foreach($parent_attributes as $index => $p_attribute){
         if(isset($values[$p_attribute['name']])){
            $parent_attributes[$index]['value'] = array_unique( array_merge((array) $p_attribute['value'], (array) $values[$p_attribute['name']]) );
         }
      }

      return $parent_attributes;
   }



   /**
    * In case the price is not available will be retrieved from DB.
    *
    * @param string $price
    * @param int $product_id
    * @return string
    */
   public static function process_price($price = 'not_available', $product_id = 0){

      if('not_available' === $price){
         $price = get_post_meta($product_id, '_regular_price', true);
      }

      return apply_filters(PREFIX . '\product_task\process_price\price', $price, $product_id);
   }



   /**
    * In case the stock is not available will be retrieved from DB.
    *
    * @param string $stock
    * @param int $product_id
    * @return string|null
    */
   public static function process_stock($stock = 'not_available', $product_id = 0){

      if('not_available' === $stock){

         $stock = get_post_meta($product_id, Util::prefix('stock'), true);

         //in case the meta exists but the stock is empty string, we consider it `null`
         if('' == $stock && metadata_exists( 'post', $product_id, Util::prefix('stock') )){
            $stock = null;
         }

      }

      return apply_filters(PREFIX . '\product_task\process_stock\stock', $stock, $product_id);
   }



   /**
    * Retrieves the payload schema for the given product.
    *
    * @param int|\WC_Product $product
    * @return array
    */
   public static function get_payload($product){

      $payload = [];
      $product = $product instanceof \WC_Product ? $product : wc_get_product($product);

      if($product instanceof \WC_Product){

         $meta_data = array_merge(
            //default WC metadata
            [
               '_sku'           => $product->get_sku('edit'),//use `edit` to avoid the fallback to parent for variations
               '_regular_price' => $product->get_regular_price(),
               '_sale_price'    => $product->get_sale_price(),
               '_stock'         => $product->get_stock_quantity(),
               '_stock_status'  => $product->get_stock_status(),
               '_manage_stock'  => $product->get_manage_stock(),
               '_weight'        => $product->get_weight(),
               '_length'        => $product->get_length(),
               '_width'         => $product->get_width(),
               '_height'        => $product->get_height(),
            ],
            //retrieve all our metadata
            Util::get_prefixed_meta_data($product->get_id()),
            //update some of our metadata with values from WC
            [
               PREFIX . '_categories' => self::get_categories($product),
               PREFIX . '_attributes' => self::get_attributes($product),
               PREFIX . '_images'     => self::get_images($product),
               PREFIX . '_dimensions' => [
                  'length' => $product->get_length(),
                  'width'  => $product->get_width(),
                  'height' => $product->get_height()
               ],
            ]
         );

         $payload = [
            'id'                => $product->get_id(),
            'name'              => $product->get_name(),
            'type'              => $product->get_type(),
            'status'            => $product->get_status(),
            'description'       => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'parent_id'         => $product->get_parent_id(),
            'meta_data'         => $meta_data
         ];
      }

      return apply_filters(PREFIX . '\product_task\util\get_payload', $payload, $product);
   }



   /**
    * Updates the term count with the products total number found.
    *
    * @return void
    */
   public static function update_term_count(){

      global $wpdb;

      //make sure the term count is updated
      $wpdb->query("UPDATE $wpdb->term_taxonomy tt SET count = (
         SELECT count(p.ID) FROM $wpdb->term_relationships tr
            LEFT JOIN $wpdb->posts p
         ON p.ID = tr.object_id AND p.post_status IN ('publish', 'draft', 'trash')
            WHERE tr.term_taxonomy_id = tt.term_taxonomy_id)");
   }



   /**
    * Gets the attributes that can be set as default.
    *
    * @param string|int $product_id
    * @return array
    */
   public static function get_default_attributes($product_id) {

      $results    = [];
      $attributes = get_post_meta($product_id, '_product_attributes', true);

      if( ! is_array($attributes) ) {
         return $results;
      }

      foreach ($attributes as $name => $attribute) {

         if ( ! $attribute['is_variation'] ) {
            continue;
         }

         if ($attribute['is_taxonomy']) {

            $terms = wp_get_post_terms($product_id, $name);

            if(is_wp_error($terms)){
               Util::log()->error([
                  'error' => [
                     'code' => $terms->get_error_code(),
                     'message' => $terms->get_error_message(),
                  ],
                  'detail' => [
                     'attribute' => $attribute,
                     'name' => $name,
                     'product_id' => $product_id
                  ]
               ]);

               continue;
            }

            if(isset($terms[0])){
               $results[$name] = $terms[0]->slug;
            }

         } else {

            $values = explode('|', $attribute['value']);

            if (!empty($values)) {
               $results[$name] = $values[0];
            }
         }
      }

      return $results;
   }



   /**
    * Creates task for the given product and action.
    *
    * @param string|int $product_id
    * @param string $action_id
    * @param array $custom_data
    * @return void
    */
   public static function create_task($product_id, string $action_id, array $custom_data = []){

      $tasks    = [];
      $accounts = apply_filters(PREFIX . '\module\product_task\service_accounts', [], $action_id);

      if(empty($accounts)){

         $meta    = new Module_Meta($product_id);
         $exclude = apply_filters(PREFIX . '\module\product_task\create_task\exclude', false, $meta, $action_id);

         if($exclude){
            return;
         }

         $meta->delete_errors();
         $meta->set_status('in_progress');
         $meta->save();

         $payload = self::get_payload($product_id);

         if( ! empty($custom_data) ){
            $payload['custom_data'] = $custom_data;
         }

         $tasks[] = [
            'action'      => $action_id,
            'source'      => 'shop',
            'target'      => 'service',
            'payload'     => $payload,
            'resource_id' => $product_id,
         ];

      }else{

         foreach($accounts as $account){

            $meta            = new Module_Meta($product_id);
            $exclude_account = Util::string_to_bool($meta->get(Util::prefix($account['account_id'] . '_exclude_account')));
            $exclude         = apply_filters(PREFIX . '\module\product_task\create_account_task\exclude', $exclude_account, $meta, $account['account_id'], $action_id);

            if($exclude){
               continue;
            }

            $meta->delete_errors();
            $meta->set_account_id($account['account_id']);
            $meta->delete_errors();
            $meta->set_status('in_progress');
            $meta->save();

            $payload = self::get_payload($product_id);

            $custom_data['account_id'] = $account['account_id'];
            $payload['custom_data']    = $custom_data;

            $tasks[] = [
               'action'      => $action_id,
               'source'      => 'shop',
               'target'      => 'service',
               'payload'     => $payload,
               'resource_id' => $product_id . '-' . $account['account_id'],
            ];
         }
      }

      Module_Task::update_entries($tasks);
   }

}