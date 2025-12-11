<?php
/**
 * Module Meta
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Meta extends Module_Meta_Abstract{


   /**
    * Account id.
    *
    * @var string
    */
   protected $account_id = '';



   /**
    * Sets the account id.
    *
    * @param string $account_id
    * @return void
    */
   public function set_account_id(string $account_id){
      $this->account_id = $account_id;
   }



   /**
    * Removes the account id.
    *
    * @return void
    */
   public function remove_account_id(){
      $this->account_id = '';
   }



   /**
    * Whether or not has an account id.
    *
    * @return bool
    */
   public function has_account_id(){
      return ! empty($this->account_id);
   }



   /**
    * Gets the list of service accounts.
    *
    * @return array
    */
   public static function get_accounts(){
      return apply_filters(PREFIX . '\module\meta\service_accounts', []);
   }



   /*
   |--------------------------------------------------------------------------
   | STATUS
   |--------------------------------------------------------------------------
   */

   /**
    * Retrieves the status.
    *
    * @return string
    */
   public function get_status(){

      $default_keys = [
         'product'           => $this->has_account_id() ? $this->account_id . '_product_status' : 'product_status',
         'product_variation' => $this->has_account_id() ? $this->account_id . '_product_status' : 'product_status',
         'shop_order'        => 'order_status',
      ];

      $default = $this->is_post_type(['product', 'product_variation']) ? 'not_published' : 'not_available';
      $key     = Util::array($default_keys)->get($this->get_post_type());

      if('yes' === $this->get($this->account_id . '_exclude_account')){
         return 'excluded';
      }

      if($this->get_errors()){
         return 'error';
      }

      return empty($this->get($key)) ? $default : $this->get($key);
   }



   /**
    * Sets the given status.
    *
    * @param string $value
    * @return self
    */
   public function set_status($value){

      $key = $this->is_post_type('shop_order') ? 'order_status' : 'product_status';

      if('product_status' === $key && $this->has_account_id()){
         $key = $this->account_id . '_product_status';
      }

      $this->set($key, $value);

      return $this;

   }



   /**
    * Deletes the status.
    *
    * @return self
    */
   public function delete_status(){

      $key = $this->is_post_type('shop_order') ? 'order_status' : 'product_status';

      if('product_status' === $key && $this->has_account_id()){
         $key = $this->account_id . '_product_status';
      }

      $this->delete($key);

      return $this;

   }



   /**
    * Displays the product status.
    *
    * @return string
    */
   public function display_product_status(){

      $statuses = Util_Status::get_list();

      if ( $this->is_product_type( 'variable' ) ) {

         $status_list = [];
         $meta = new Module_Meta( $this->get_product()->get_id() );

         if(in_array($meta->get_status(), ['error', 'in_progress'])){

            $this->display_status();

         }else{

            $variations = wc_get_products([
               'status'   => ['private', 'publish', 'trash'],
               'type'     => 'variation',
               'parent'   => $this->get_product()->get_id(),
               'limit'    => -1,
               'return'  => 'ids',
            ]);

            foreach ( $variations as $var_id ) {

               $_meta = new Module_Meta( $var_id );
               $_meta->set_account_id($this->account_id);

               $status_list[] = $_meta->get_status();
            }

            $status_list = array_count_values(array_map(function($value) {
               return $value == "" ? 'not_published' : $value;
            }, $status_list));

            if(empty($status_list)){

               Util::status('not_published')->render();

            }else{

               foreach( $status_list as $status => $status_total ) {

                  $title = Util::array($statuses)->get("{$status}/title" );
                  $color = Util::array($statuses)->get("{$status}/color" );
                  $count = sprintf( _n( '%s variation', '%s variations', $status_total, 'integration-marktplaats-for-woocommerce' ), $status_total );
                  echo '<div class="' . PREFIX . '-product-status"><span style="color: ' . $color . '">' . $title . '</span><em>' . $count . '</em></div>';

               }
            }
         }

      } else {

         $this->display_status();
      }

   }



   /**
    * Displays the status (simple way).
    *
    * @return string
    */
   public function display_status(){
      Util::status($this->get_status())->render();
   }



   /*
   |--------------------------------------------------------------------------
   | ERRORS
   |--------------------------------------------------------------------------
   */

   /**
    * Retrieves the errors (general or account).
    *
    * @return array
    */
   public function get_errors(){

      $key    = $this->is_post_type('shop_order') ? 'order_errors' : 'product_errors';
      $errors = array_filter((array) $this->get($key));

      if( 'product_errors' === $key && $this->has_account_id() ){
         $errors = array_filter((array) $this->get($this->account_id . '_' .$key));
      }

      return $errors;
   }



   /**
    * Retrieves all the errors (general and account).
    *
    * @return array
    */
   public function get_all_errors(){

      $key            = $this->is_post_type('shop_order') ? 'order_errors' : 'product_errors';
      $general_errors = array_filter((array) $this->get($key));
      $account_errors = [];

      foreach($this->get_accounts() as $account_id => $account){
         $account_errors[] = array_filter((array) $this->get($account_id . '_' .$key));
      }

      return array_merge($general_errors, array_filter($account_errors));
   }



   /**
    * Sets the given error message.
    *
    * @param string $value
    * @param string $index_key
    * @return self
    */
   public function set_error($value, $index_key = ''){

      $errors = $this->get_errors();

      if( ! in_array($value, $errors) ){

         $key = $this->is_post_type('shop_order') ? 'order_errors' : 'product_errors';

         if( 'product_errors' === $key && $this->has_account_id() ){
            $key = $this->account_id . '_' . $key;
         }

         if(empty($index_key)){
            $errors[] = $value;
         }else{
            $errors[$index_key] = $value;
         }

         $this->set($key, $errors);
      }

      return $this;

   }



   /**
    * Deletes an error by the given index key.
    *
    * @param string $index_key
    * @return self
    */
   public function delete_error($index_key){

      $errors = $this->get_errors();

      if(isset($errors[$index_key])){

         $key = $this->is_post_type('shop_order') ? 'order_errors' : 'product_errors';

         unset($errors[$index_key]);

         $this->set($key, $errors);
      }

      return $this;

   }



   /**
    * Deletes the errors (general or account).
    *
    * @return self
    */
   public function delete_errors(){

      $key = $this->is_post_type('shop_order') ? 'order_errors' : 'product_errors';

      if( 'product_errors' === $key && $this->has_account_id() ){
         $key = $this->account_id . '_' . $key;
      }

      $this->delete($key);

      return $this;

   }



   /**
    * Deletes all the errors (general and account).
    *
    * @return array
    */
   public function delete_all_errors(){

      $key = $this->is_post_type('shop_order') ? 'order_errors' : 'product_errors';

      $this->delete($key);

      foreach($this->get_accounts() as $account_id => $account){
         $this->delete($account_id . '_' .$key);
      }

      return $this;
   }



   /**
    * Displayes the errors.
    *
    * @return string|void
    */
   public function display_errors(){

      $errors = $this->get_errors();

      if ($errors) {

         echo Util::get_template('errors.php', [
            'errors' => $errors,
         ], dirname(__FILE__), 'templates');
      }

   }



   /*
   |--------------------------------------------------------------------------
   | MISC
   |--------------------------------------------------------------------------
   */

   /**
    * Retrieves the service category connected with the assigned WooCommerce category.
    *
    * @return string
    */
   public function get_connected_category(){

      $value = '';
      $terms = get_the_terms($this->object_id, 'product_cat');

      if( ! is_array($terms) ){

         $product = wc_get_product($this->object_id);

         if($product->is_type('variation')){
            $terms = get_the_terms($product->get_parent_id(), 'product_cat');
         }
      }

      if(is_array($terms)){

         foreach($terms as $term){

            $term_meta = new self($term->term_id, 'term');
            $value     = $term_meta->get('category');

            if( ! empty($value) ){
               break;
            }
         }

         if(empty($value)){

            foreach($terms as $term){
               $term_ancs = get_ancestors($term->term_id, 'product_cat');

               foreach($term_ancs as $term_anc_id){
                  $term_meta = new self($term_anc_id, 'term');
                  $value     = $term_meta->get('category');

                  if( ! empty($value) ){
                     break;
                  }
               }
               if( ! empty($value) ){
                  break;
               }
            }

         }

      }

      return $value;
   }



   /**
    * Gets the value of International Article Number based on the global setting.
    *
    * @param string $source_option_key - this is the setting option key which defines what source to use
    * @param string $default_meta - this is the default meta key where to get the value from
    * @param string $source_custom_field_option_key - this is the custom meta key where to get the value from
    * @param string $source_attribute_option_key - this is the name/slug of an attribute where to get the value from
    * @return string
    */
   public function get_ian_value($source_option_key = 'ean_source', $default_meta = 'ean', $source_custom_field_option_key = 'ian_custom_field_name', $source_attribute_option_key = 'ian_attribute_name'){
      wc_deprecated_function( 'Module_Meta::get_ian_value', '1.0.1', 'Module_Meta::get_value_by_source' );
      $this->get_value_by_source($source_option_key, $default_meta, $source_custom_field_option_key, $source_attribute_option_key);
   }



   /**
    * Retrieves a value based on the source.
    *
    * @param string $source_option_key - this is the setting option key which defines what source to use
    * @param string $default_meta - this is the default meta key where to get the value from
    * @param string $source_custom_field_option_key - this is the custom meta key where to get the value from
    * @param string $source_attribute_option_key - this is the name/slug of an attribute where to get the value from
    * @return string
    */
   public function get_value_by_source($source_option_key, $default_meta, $source_custom_field_option_key, $source_attribute_option_key){

      $source  = Option::get($source_option_key, 'default');
      $value   = get_post_meta($this->object_id, Util::prefix($default_meta), true);
      $product = wc_get_product($this->object_id);

      if( ! $product instanceof \WC_Product ){
         return $value;
      }

      switch($source){

         case 'custom_field':

            $field_name = Option::get($source_custom_field_option_key);

            if( !empty($field_name) ){

               $value = $product->get_meta($field_name);

               if(is_array($value)){
                  $value = $value[0];
               }
            }

            break;

         case 'product_id':

            $value = $product->get_id();

            break;


         case 'sku':

            $value = $product->get_sku();

            break;

         case 'global_unique_id':

            $value = $product->get_global_unique_id();

            break;


         case 'attribute':

            if( $product->is_type('variable') || $product->is_type('variation') ){
               return $value;
            }

            $name  = sanitize_title( Option::get($source_attribute_option_key) );
            $attrs = $product->get_attributes();
            $data  = [];

            foreach($attrs as $item){

               if("pa_{$name}" === $item->get_name()){
                  $data = $item->get_data();
                  break;
               }
            }

            if(isset($data['id'])){

               if($data['id'] > 0){

                  $options = $data['options'];
                  $term_id = $options[0];
                  $term = get_term($term_id, "pa_{$name}");

                  if(is_wp_error( $term )){
                     Utility::wc_error_log($term, __FILE__, __LINE__);
                  }elseif(isset($term->name)){
                     $value = $term->name;
                  }else{
                     Utility::wc_error_log("Term no found for attribute taxonomy: pa_{$name}", __FILE__, __LINE__);
                  }

               }

            }

            break;

      }

      return $value;
   }

}
