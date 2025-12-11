<?php
/**
 * Module Order Task
 *
 * Payload structure:
 * [
 *    'id'             => 0,
 *    'status'         => 'processing',
 *    'payment_method' => '',
 *    'created_via'    => '',
 *    'parent_id'      => 0,
 *    'transaction_id' => '',
 *    'customer_id'    => 0,
 *    'customer_note'  => '',
 *    'billing'        => [
 *       'email'      => '',
 *       'first_name' => '',
 *       'last_name'  => '',
 *       'company'    => '',
 *       'address_1'  => '',
 *       'address_2'  => '',
 *       'city'       => '',
 *       'state'      => '',
 *       'postcode'   => '',
 *       'country'    => '',
 *       'phone'      => '',
 *    ],
 *    'shipping' => [
 *       'first_name' => '',
 *       'last_name'  => '',
 *       'company'    => '',
 *       'address_1'  => '',
 *       'address_2'  => '',
 *       'city'       => '',
 *       'state'      => '',
 *       'postcode'   => '',
 *       'country'    => '',
 *       'phone'      => '',
 *    ],
 *    'tax_lines' => [
 *       [
 *          'name'         => 'Tax',
 *          'percentage'   => 21,
 *          'amount'       => 1.47,
 *          'total'        => 3.99,
 *          'shipping_tax' => 2.52,
 *          'meta_data'    => []
 *       ]
 *    ],
 *    'shipping_lines' => [
 *       [
 *          'name'      => 'Shipping',
 *          'total'     => 12,
 *          'total_tax' => 3.83,
 *          'meta_data' => []
 *       ]
 *    ],
 *    'coupon_lines' => [
 *       [
 *          'name'         => 'test10',
 *          'discount'     => 20,
 *          'discount_tax' => 6.38,
 *          'meta_data'    => []
 *       ]
 *    ],
 *    'fee_lines' => [
 *       [
 *          'name'      => '$3.00 fee',
 *          'total'     => 3,
 *          'total_tax' => 0.96,
 *          'meta_data' => []
 *       ],
 *    ],
 *    'line_items' => [
 *       [
 *          'name'      => 'Dummy product',
 *          'quantity'  => 2,
 *          'price'     => 12.00,
 *          'meta_data' => [
 *             '{prefix}_product_id'        => '323232',
 *             '{prefix}_sku'               => 'GG55',
 *             '{prefix}_ean'               => '877878787',
 *             '{prefix}_order_line_id'     => '5545555',
 *             '{prefix}_order_line_status' =>  'open',
 *          ]
 *       ]
 *    ],
 *    'meta_data' => [
 *       [
 *          '{prefix}_order_id' => '',
 *          '{prefix}_user_id'  => '',
 *       ],
 *    ],
 * ]
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Task extends Module_Abstract_Entity_Post implements Interface_Entity_Task{


   /**
    * Order isntance.
    *
    * @var \WC_Order|null
    */
   protected $order = null;


   /**
    * Whether or not to import order.
    *
    * @var boolean
    */
   protected $import = false;


   public function __construct()
   {
      $this->import = Util::string_to_bool(Option::get('enable_import_order', 'no'));
   }



   /**
    * List of supported actions.
    *
    * @param string $prop - the array item propriety to be return
    * @return array
    */
   public static function action_list($prop = ''){

      $list = [
         [
            'id'       => 'create_or_update_order',
            'priority' => 9,
            'context'  => 'order_task',
         ],
         [
            'id'       => 'delete_order',
            'priority' => 41,
            'context'  => 'order_task',
         ],
      ];

      if( ! empty($prop) ){
         return array_column($list, $prop);
      }

      return $list;
   }



   /**
    * Retrieves entity id either by a metadata key or `id` property.
    *
    * $this->data = [
    *    'id' => 123
    * ]
    * --- OR ---
    * $this->data = [
    *    'meta_data' => [
    *       'meta_key' => '123',
    *    ]
    * ]
    *
    * @return int
    */
   public function get_id_from_data(){

      global $wpdb;

      $id = empty($this->id) ? Util::array($this->data)->get('id', 0) : $this->id;

      if(empty($id) && $this->data){

         $where = [];

         foreach($this->meta_key_identifiers() as $key){

            $value = Util::array($this->data)->get("meta_data/{$key}");

            if( ! empty($value) ){
               $where[] = "(meta_key = '{$key}' AND meta_value = '{$value}')";
            }
         }

         $where = implode(' OR ', $where);

         if( ! empty($where) ){

            $is_hpos    = Module_Core::is_HPOS_enabled();
            $table_name = $is_hpos ? "{$wpdb->prefix}wc_orders_meta" : $wpdb->postmeta;

            $sql = apply_filters(PREFIX . '\module\order_task\get_id_from_data\sql', "SELECT post_id FROM {$table_name} WHERE {$where} LIMIT 1", $table_name, $is_hpos, $this);
            $id  = $wpdb->get_var($sql);
         }

      }

      return (int) $id;
   }



   /**
    * Retrieves the entity id from the service.
    *
    * @return string|int
    */
   public function get_remote_id(){}



   /**
    * Checks whether or not the entity is valid.
    *
    * @return boolean
    */
   public function is_valid(){}



   /**
    * Retrieves the entity type.
    *
    * @return string
    */
   public function get_type(){}



   /**
    * Sets the entity errors.
    *
    * @param string $message
    * @param string $key - the key which the error message will be set in the list of errors.
    * @return void
    */
   public function set_error($message, $key = ''){}



   /**
    * Deletes the entity errors.
    *
    * @param string $key - the key which the error message will be set in the list of errors.
    * @return void
    */
   public function delete_error($key = ''){}



   /**
    * Sets the entity status.
    *
    * @param string $status
    * @return void
    */
   public function set_status($status){}



   /**
    * Deletes the entity status.
    *
    * @return void
    */
   public function delete_status(){}



   /**
    * Sets the references between service & shop entity.
    *
    * @return void
    */
   public function set_reference(){}



   /**
    * Deletes the references between service & shop entity.
    *
    * @return void
    */
   public function delete_reference(){}



   /**
    * List of metadata keys to be used when identify the entity id.
    *
    * @return array
    */
   public function meta_key_identifiers(){
      return apply_filters(PREFIX . '\order_task\meta_key_identifiers', [Util::prefix('order_id')]);
   }



   /**
    * Retrieves the product id.
    *
    * @param array $meta_data The order item meta data.
    * @return int
    */
   private function get_product_id(array $meta_data){

      global $wpdb;

      $account_id = Util::array($meta_data)->get(Util::prefix('account_id'));

      $meta_conditions = [
         Util::prefix('product_id') => [
            Util::array($meta_data)->get(Util::prefix('product_id'))
         ],
         //support for multi-account
         Util::prefix($account_id . '_product_id') => [
            Util::array($meta_data)->get(Util::prefix('product_id'))
         ],
         Util::prefix('sku') => [
            Util::array($meta_data)->get(Util::prefix('sku')),
         ],
         Util::prefix('ean') => [
            Util::array($meta_data)->get(Util::prefix('ean'))
         ],
         //this is for EAN source: product custom field
         Option::get('ean_source__custom_field_name') => [
            Util::array($meta_data)->get(Util::prefix('ean'))
         ],
         //this is for EAN source: product SKU
         '_sku' => [
            Util::array($meta_data)->get(Util::prefix('ean')),
            Util::array($meta_data)->get(Util::prefix('sku')),
         ],
         //this is for EAN source: product global identifier
         '_global_unique_id' => [
            Util::array($meta_data)->get(Util::prefix('ean')),
         ],
      ];

      $meta_conditions = array_map(function($values) {
         return array_filter((array)$values, function($value) {
            return !empty($value);
         });
      }, $meta_conditions);

      $meta_conditions = array_filter($meta_conditions);

      if (empty($meta_conditions)) {
         return 0;
      }

      $query = "SELECT pm.post_id
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE p.post_type IN ('product', 'product_variation')
         AND (";

      $conditions = [];
      $values = [];

      foreach ($meta_conditions as $key => $key_values) {
         foreach ($key_values as $value) {
            $conditions[] = "(pm.meta_key = %s AND pm.meta_value = %s)";
            $values[] = $key;
            $values[] = $value;
         }
      }

      $query .= implode(' OR ', $conditions) . ")
         LIMIT 1";

      $result = $wpdb->get_var(
         $wpdb->prepare($query, $values)
      );

      return (int) $result;
   }



   /**
    * Retrieves the order item id.
    *
    * @param array $meta_data The order item meta data.
    * @return int
    */
   private function get_order_item_id(array $meta_data){

      global $wpdb;

      $order_id = $this->order ? $this->order->get_id() : '';
      $order_line_id = Util::array($meta_data)->get(Util::prefix('order_line_id'));
      $result = $wpdb->get_var(
         $wpdb->prepare("SELECT oim.order_item_id
            FROM {$wpdb->prefix}woocommerce_order_itemmeta AS oim
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi
               ON oim.order_item_id = oi.order_item_id AND oi.order_id = %d
            WHERE oim.meta_key = %s AND oim.meta_value = %s
               LIMIT 1",
            $order_id,
            Util::prefix('order_line_id'),
            $order_line_id
         )
      );

      return (int) $result;
   }



   /**
    * Retrieves the user id. In case the user does not exist the will be created.
    *
    * @return int
    */
   protected function get_user_id(){

      $user_id = 0;
      $email   = Util::array($this->data)->get('billing/email');

      if( ! empty($email) ){

         $user = new Module_User_Task();
         $user->set_data([
            'email'     => $email,
            'username'  => $email,
            'password'  => wp_generate_password(),
            'meta_data' => [
               PREFIX . '_role'      => 'customer',
               'first_name'          => Util::array($this->data)->get('billing/first_name'),
               'last_name'           => Util::array($this->data)->get('billing/last_name'),
               'billing_email'       => Util::array($this->data)->get('billing/email'),
               'billing_first_name'  => Util::array($this->data)->get('billing/first_name'),
               'billing_last_name'   => Util::array($this->data)->get('billing/last_name'),
               'billing_company'     => Util::array($this->data)->get('billing/company'),
               'billing_address_1'   => Util::array($this->data)->get('billing/address_1'),
               'billing_address_2'   => Util::array($this->data)->get('billing/address_2'),
               'billing_city'        => Util::array($this->data)->get('billing/city'),
               'billing_state'       => Util::array($this->data)->get('billing/state'),
               'billing_postcode'    => Util::array($this->data)->get('billing/postcode'),
               'billing_country'     => Util::array($this->data)->get('billing/country'),
               'billing_phone'       => Util::array($this->data)->get('billing/phone'),
               'shipping_first_name' => Util::array($this->data)->get('shipping/first_name'),
               'shipping_last_name'  => Util::array($this->data)->get('shipping/last_name'),
               'shipping_company'    => Util::array($this->data)->get('shipping/company'),
               'shipping_address_1'  => Util::array($this->data)->get('shipping/address_1'),
               'shipping_address_2'  => Util::array($this->data)->get('shipping/address_2'),
               'shipping_city'       => Util::array($this->data)->get('shipping/city'),
               'shipping_state'      => Util::array($this->data)->get('shipping/state'),
               'shipping_postcode'   => Util::array($this->data)->get('shipping/postcode'),
               'shipping_country'    => Util::array($this->data)->get('shipping/country'),
               'shipping_phone'      => Util::array($this->data)->get('shipping/phone'),
            ],
         ]);
         $user->set_id( $user->get_id_from_data() );

         if( empty( $user->get_id() ) ){
            $user->create();
            $user->create_metadata();

            $user_id = $user->get_id();
         }

         $user_id = $user->get_id();
      }

      return (int) $user_id;
   }



   /**
    * Creates the order.
    *
    * $this->data = [
    *    'status'         => 'processing',
    *    'payment_method' => '',
    *    'created_via'    => '',
    *    'parent_id'      => 0,
    *    'transaction_id' => '',
    *    'customer_id'    => 0,
    *    'customer_note'  => '',
    *    'billing'        => [],
    *    'shipping'       => [],
    * ]
    *
    * @return void
    */
   public function create(){

      if( ! $this->import ){
         return;
      }

      $line_items = Util::array($this->data)->get('line_items', []);

      if(empty($line_items)){
         return;
      }

      $this->order = wc_create_order();
      $this->order->set_payment_method( Util::array($this->data)->get('payment_method', self::default_payment_method_id()) );
      $this->order->set_created_via( Util::array($this->data)->get('created_via', PREFIX . '_external_service') );
      $this->order->set_parent_id( Util::array($this->data)->get('parent_id', 0) );
      $this->order->set_transaction_id( Util::array($this->data)->get('transaction_id', '') );
      $this->order->set_customer_note( Util::array($this->data)->get('customer_note', '') );
      $this->order->set_address( Util::array($this->data)->get('billing', []), 'billing' );
      $this->order->set_address( Util::array($this->data)->get('shipping', []), 'shipping' );
      $this->order->set_customer_id( $this->get_user_id() );

      $this->id = $this->order->get_id();

   }



   /**
    * Creates the order metadata.
    *
    * $this->data = [
    *    'meta_data' => []
    * ]
    *
    * @return void
    */
   public function create_metadata(){
      $this->update_metadata();
   }



   /**
    * Creates/Updates/Deletes the order line items.
    *
    * $this->data = [
    *    'line_items' => [
    *       [
    *          'name'      => 'Dummy product',
    *          'quantity'  => 2,
    *          'price'     => 12.00,
    *          'meta_data' => [
    *             '{prefix}_product_id'        => '323232',
    *             '{prefix}_sku'               => 'GG55',
    *             '{prefix}_ean'               => '877878787',
    *             '{prefix}_order_line_id'     => '5545555',
    *             '{prefix}_order_line_status' =>  'open',
    *       ]
    *    ]
    * ]
    *
    * @return void
    */
   public function process_line_items(){

      if($this->import && ! $this->order instanceof \WC_Order ){
         return;
      }

      $account_id = Util::array($this->data)->get('meta_data/account_id');
      $order_id   = Util::array($this->data)->get('meta_data/order_id');
      $line_items = Util::array($this->data)->get('line_items', []);

      foreach($line_items as $data){

         $meta_data  = Util::array($data)->get('meta_data', []);
         $order_item = $this->order ? $this->order->get_item( $this->get_order_item_id($meta_data) ) : '';

         //UPDATE
         if($order_item){

            //update the metadata of the item
            foreach($meta_data as $key => $value){
               $order_item->update_meta_data($key, $value);
            }

         //CREATE
         }else{

            $quantity = (int) Util::array($data)->get('quantity', 1);
            $product  = wc_get_product( $this->get_product_id($meta_data) );

            //process stock update when import is disabled
            if(!$this->import){

               if( $product instanceof \WC_Product && $product->get_manage_stock() ) {

                  $reduced_stock_by_orders = array_filter((array) $product->get_meta(Util::prefix('reduced_stock_by_orders')));

                  //skip if this order already reduced product's stock
                  if(in_array($order_id, $reduced_stock_by_orders)){
                     continue;
                  }

                  $reduced_stock_by_orders[] = $order_id;
                  $current_stock       = (int) $product->get_stock_quantity();
                  $new_stock           = $current_stock < $quantity ? 0 : $current_stock - $quantity;

                  $product->set_stock_quantity($new_stock);

                  if(Module_Marketplace::is_stock_already_reduced($meta_data)){
                     $product->update_meta_data(Util::prefix('reduced_stock_by_unimported_order'), 'yes');
                  }else{

                     if(empty($account_id)){
                        $product->update_meta_data(Util::prefix('sync_stock'), 'no');
                     }else{
                        $product->update_meta_data(Util::prefix($account_id . '_sync_stock'), 'no');
                     }
                  }

                  $product->update_meta_data(Util::prefix('reduced_stock_by_orders'), $reduced_stock_by_orders);
                  $product->save();
               }

               //move to the next item
               continue;
            }

            $price    = (float) Util::array($data)->get('price', 0);
            $tax_rate = $this->get_tax_rate($product);

            //exclude tax - to avoid double taxation by the shop
            if($tax_rate > 0 && apply_filters(PREFIX . '\module\order_task\process_line_items\extract_tax_rate', true, $this->order, $product, $this->data)){
               $price = $price - ( $price / ( $tax_rate + 100 ) * $tax_rate );
            }

            $subtotal = $price;
            $total    = $price * $quantity;

            if( $product instanceof \WC_Product ){

               $order_item = $this->order->get_item(
                  $this->order->add_product($product, $quantity, [
                     'subtotal' => $subtotal,
                     'total'    => $total,
                  ])
               );

               $reduced_stock_by_orders = array_filter((array) $product->get_meta(Util::prefix('reduced_stock_by_orders')));

               if(Module_Marketplace::is_stock_already_reduced($meta_data) || in_array($order_id, $reduced_stock_by_orders)){
                  $order_item->update_meta_data('_reduced_stock', $quantity); //use default WooCommerce meta flag
               }else{

                  if(empty($account_id)){
                     $product->update_meta_data(Util::prefix('sync_stock'), 'no');
                  }else{
                     $product->update_meta_data(Util::prefix($account_id . '_sync_stock'), 'no');
                  }

                  $product->save_meta_data();
               }

            }else{

               $order_item = new \WC_Order_Item_Product();
               $order_item->set_name( Util::array($data)->get('name', 'Unknown product') );
               $order_item->set_order_id( $this->order->get_id() );
               $order_item->set_subtotal($subtotal);
               $order_item->set_total($total);
               $order_item->set_quantity($quantity);
               $order_item->save();

               $this->order->add_item($order_item);
            }

            //add metadata of the order item
            foreach($meta_data as $key => $value){
               $order_item->add_meta_data($key, $value, $unique = true);
            }
         }

         $order_item->save_meta_data();
      }
   }



   /**
    * Creates the order fee lines.
    *
    * $this->data = [
    *    'fee_lines' => [
    *       [
    *          'name'      => '',
    *          'ammount'   => 0,
    *          'meta_data' => []
    *       ],
    *    ],
    * ]
    *
    * @return void
    */
   public function create_fee_lines(){

      if( ! $this->order instanceof \WC_Order ){
         return;
      }

      $fee_lines = Util::array($this->data)->get('fee_lines', []);

      foreach($fee_lines as $data){

         $name      = Util::array($data)->get('name', 'Unknown fee');
         $amount    = Util::array($data)->get('amount', 0);
         $meta_data = Util::array($data)->get('meta_data', []);

         $order_item_fee = new \WC_Order_Item_Fee();
         $order_item_fee->set_name($name);
         $order_item_fee->set_amount($amount);
         $order_item_fee->set_total($amount);
         $order_item_fee->save();

         $this->order->add_item( $order_item_fee );

         //add metadata of the order item fee
         foreach($meta_data as $key => $value){
            $order_item_fee->add_meta_data($key, $value, $unique = true);
         }

         $order_item_fee->save_meta_data();
      }
   }



   /**
    * Creates the order shipping lines.
    *
    * $this->data = [
    *    'shipping_lines' => [
    *       [
    *          'name'      => 'Shipping',
    *          'total'     => 12,
    *          'total_tax' => 3.83,
    *          'meta_data' => []
    *       ],
    *    ],
    * ]
    *
    * @return void
    */
   public function create_shipping_lines(){

      if( ! $this->order instanceof \WC_Order ){
         return;
      }

      $shipping_lines = Util::array($this->data)->get('shipping_lines', []);

      foreach($shipping_lines as $data){

         $name      = Util::array($data)->get('name', 'Unknown shipping method');
         $total     = Util::array($data)->get('total', 0);
         $meta_data = Util::array($data)->get('meta_data', []);

         $order_item_shipping = new \WC_Order_Item_Shipping();
         $order_item_shipping->set_method_title($name);
         $order_item_shipping->set_total($total);
         $order_item_shipping->save();

         $this->order->add_item( $order_item_shipping );

         //add metadata of the order item fee
         foreach($meta_data as $key => $value){
            $order_item_shipping->add_meta_data($key, $value, $unique = true);
         }

         $order_item_shipping->save_meta_data();
      }
   }



   /**
    * Updates the order.
    *
    * $this->data = [
    *    'status'   => 'processing',
    *    'billing'  => [],
    *    'shipping' => [],
    * ]
    *
    * @return void
    */
   public function update(){

      $this->order = wc_get_order($this->id);

      if( ! $this->order instanceof \WC_Order ){
         return;
      }

      $email    = Util::array($this->data)->get('billing/email');
      $billing  = Util::array($this->data)->get('billing', []);
      $shipping = Util::array($this->data)->get('shipping', []);

      if( ! empty($billing) ){
         $this->order->set_address( $billing, 'billing' );
      }

      if( ! empty($shipping) ){
         $this->order->set_address( $shipping, 'shipping' );
      }

      if( ! empty($email) ){
         $this->order->set_customer_id( $this->get_user_id() );
      }
   }



   /**
    * Updates the order metadata.
    *
    * $this->data = [
    *    'meta_data' => []
    * ]
    *
    * @return void
    */
   public function update_metadata(){

      if( ! $this->order instanceof \WC_Order ){
         return;
      }

      $meta_data = Util::array($this->data)->get('meta_data', []);

      foreach($meta_data as $key => $value){
         $this->order->update_meta_data($key, $value);
      }
   }



   /**
    * Saves the order updates.
    *
    * @return void
    */
   public function save(){

      if( ! $this->order instanceof \WC_Order ){
         return;
      }

      $this->order->calculate_totals();
      $this->order->save();

      $status = Util::array($this->data)->get('status', 'pending');
      $allow_status_update = apply_filters(PREFIX . '\module\order_task\allow_status_update', in_array($this->order->get_status(), ['pending', 'processing']), $this);

      //set order status AFTER calculating totals
      if($allow_status_update){
         $this->order->update_status($status);
      }
   }



   /**
    * Retrieves the default payment method id.
    *
    * @return string
    */
   private function default_payment_method_id(){
      return Module_Core::config('service.slug', null, false);
   }



   /**
    * Retrieves the VAT rate.
    *
    * @param \WC_Product $product
    * @return float
    */
   private function get_tax_rate(\WC_Product $product){

      $location = $this->order->get_taxable_location();

      if( ! isset($location['tax_class']) ){
         $location['tax_class'] = ( $product && method_exists( $product, 'get_tax_class' ) ) ? $product->get_tax_class() : '';
      }

      $rates = \WC_Tax::find_rates($location);
      $tax   = reset( $rates ); //get first one

      if(isset($tax['rate'])){
         return $tax['rate'];
      }

      return 0.00;
   }
}