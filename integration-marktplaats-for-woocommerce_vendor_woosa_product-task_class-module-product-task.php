<?php
/**
 * Module Product Task
 *
 * Payload structure:
 * [
 *    'id'                => 0,
 *    'type'              => 'simple', //`variation`, `variable`
 *    'name'              => 'Dummy title',
 *    'status'            => 'publish',
 *    'description'       => 'Long and with HTML description',
 *    'short_description' => 'Short description',
 *    'parent_id'         => 0, //the shop parent id
 *    'meta_data'         => [
 *       '{prefix}_sku'        => '34343RR',
 *       '{prefix}_stock'      => 11,
 *       '{prefix}_ean'        => '3434343454545',
 *       '{prefix}_mkt_price'  => 21,
 *       '{prefix}_b2b_price'  => 19,
 *       '{prefix}_rrp_price'  => 22,
 *       '{prefix}_parent_id'  => 3232, //the service parent id
 *       '{prefix}_weight'     => 13,
 *       '{prefix}_vat'        => 21,
 *       '{prefix}_backorder'  => false,
 *       '{prefix}_categories' => [
 *          [
 *             'id'        => 123,
 *             'parent_id' => 0,
 *             'name'      => 'Furniture',
 *          ],
 *       ],
 *       '{prefix}_attributes' => [
 *          [
 *             'name'               => 'Brand',
 *             'value'              => ['sony', 'apple'],
 *             'used_for_variation' => false
 *          ],
 *       ],
 *       '{prefix}_dimensions' => [
 *          'length' => 43,
 *          'width'  => null,
 *          'height' => 12
 *       ],
 *       '{prefix}_images' => [
 *          'https://example.com/wp-content/uploads/2017/03/T_1.jpg',
 *          'https://example.com/wp-content/uploads/2017/03/T_2.jpg',
 *       ]
 *    ],
 * ]
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Task extends Module_Abstract_Entity_Post implements Interface_Entity_Task{


   /**
    * The list of supported product types.
    *
    * @var array
    */
   protected $supported_types = [
      'simple',
      'variable',
   ];



   /**
    * List of supported actions.
    *
    * @param string $prop - the array item propriety to be return
    * @return array
    */
   public static function action_list($prop = ''){

      $list = [
         [
            'id'       => 'create_or_update_product',
            'priority' => 5,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'assign_product_attribute',
            'priority' => 6,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'assign_product_category',
            'priority' => 7,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'update_product_stock',
            'priority' => 10,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'update_product_price',
            'priority' => 11,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'delete_or_trash_product',
            'priority' => 50,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'delete_shop_category',
            'priority' => 51,
            'context'  => 'product_task',
         ],
         [
            'id'       => 'update_product_lookup_table',
            'priority' => 80,
            'callback' => [Module_Product_Task_Util::class, 'update_lookup_table'],
            'context'  => 'product_task',
         ],
         [
            'id'       => 'download_product_image',
            'priority' => 100,
            'context'  => 'product_task',
         ],
      ];

      if( ! empty($prop) ){
         return array_column($list, $prop);
      }

      return $list;
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
      return apply_filters(PREFIX . '\product_task\meta_key_identifiers', [
         '_sku',
         PREFIX . '_sku',
         PREFIX . '_product_id',
      ]);
   }



   /**
    * Updates the product stock.
    *
    * $this->data = [
    *    'meta_data' => [
    *       '{prefix}_sku'   => '34343RR',
    *       '{prefix}_stock' => 53,
    *    ]
    * ]
    *
    * @return bool
    */
   public function update_stock(){

      if($this->id && $this->data){

         $meta_data = Util::array($this->data)->get('meta_data', []);
         $price     = Module_Product_Task_Util::process_price('not_available', $this->id);
         $stock     = Module_Product_Task_Util::process_stock(Util::array($meta_data)->get(Util::prefix('stock')));

         $this->set_data([
            'meta_data'        => array_merge($meta_data, [
               '_manage_stock' => '' == $stock ? 'no' : 'yes',
               '_stock'        => $stock,
               '_stock_status' => empty($stock) ? 'outofstock' : 'instock',
               Util::prefix('last_stock_update') => time()
            ])
         ]);

         $this->update_metadata();

         if($this->exclude_unavailable_product($stock)){

            Module_Task::update_entries([
               [
                  'action'      => 'delete_or_trash_product',
                  'source'      => 'shop',
                  'target'      => 'shop',
                  'payload'     => ['id' => $this->id],
                  'resource_id' => $this->id,
                  'priority'    => 10,
               ]
            ]);

         }else{

            $this->maybe_publish($price, $stock);
         }

         return true;

      }

      return false;
   }



   /**
    * Updates the product price.
    *
    * $this->data = [
    *    'meta_data' => [
    *       '{prefix}_sku'       => '34343RR',
    *       '{prefix}_mkt_price' => 22,
    *       '{prefix}_b2b_price' => 19,
    *       '{prefix}_rrp_price' => 23,
    *    ]
    * ]
    *
    * @return bool
    */
   public function update_price(){

      if($this->id && $this->data){

         $meta_data = Util::array($this->data)->get('meta_data', []);
         $raw_price = apply_filters(PREFIX . '\product_task\update_price\raw_price', Util::array($meta_data)->get(Util::prefix('mkt_price'), 0), $meta_data );
         $price     = Module_Product_Task_Util::process_price($raw_price);
         $stock     = Module_Product_Task_Util::process_stock('not_available', $this->id);

         $this->set_data([
            'meta_data' => array_merge($meta_data, [
               '_price'         => $price,
               '_regular_price' => $price,
               Util::prefix('last_price_update') => time()
            ])
         ]);

         $parent_id = get_post_meta($this->id, Util::prefix('parent_id'), true);

         if( ! empty($parent_id) ){

            $parent = Module_Product_Task_Util::get_parent_by_meta($parent_id);

            if(isset($parent['id'])){
               //remove parent transients to force WC to re-create them
               Transient::delete("wc_var_prices_{$parent['id']}", false);
            }
         }

         $this->update_metadata();

         if($this->exclude_unavailable_product($stock)){

            Module_Task::update_entries([
               [
                  'action'      => 'delete_or_trash_product',
                  'source'      => 'shop',
                  'target'      => 'shop',
                  'payload'     => ['id' => $this->id],
                  'resource_id' => $this->id,
                  'priority'    => 10,
               ]
            ]);

         }else{

            $this->maybe_publish($price, $stock);
         }

         return true;
      }

      return false;
   }



   /**
    * Creates the taxonomies for the product attributes.
    *
    * $this->data = [
    *    'id'        => 123,
    *    'type'      => 'simple|variable',
    *    'meta_data' => [
    *       '{prefix}_sku'        => '34343RR',
    *       '{prefix}_attributes' => [
    *          [
    *             'name'               => 'Brand',
    *             'value'              => ['sony', 'apple'],
    *             'used_for_variation' => false
    *          ],
    *       ]
    *    ]
    * ]
    *
    * @return void
    */
   public function create_attributes(){

      global $wpdb;

      $attributes = apply_filters(PREFIX . '\product_task\create_attributes\attributes', Util::array($this->data)->get('meta_data/' . Util::prefix('attributes'), []), $this);

      if($this->id && ! empty($attributes) && $this->is_supported_type()){

         $payload = [];

         foreach($attributes as $attribute){

            if(array_key_exists('name', $attribute) && array_key_exists('value', $attribute) && array_key_exists('used_for_variation', $attribute) ){

               $slug = wc_sanitize_taxonomy_name( $attribute['name'] );

               //check if already exists
               $attribute_taxonomies = $wpdb->get_row("SELECT *
                  FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
                  WHERE attribute_name = '$slug'
               ");

               //create a new one
               if(empty($attribute_taxonomies)){

                  $attribute_taxonomies = [
                     'attribute_name'    => $slug,
                     'attribute_label'   => $attribute['name'],
                     'attribute_type'    => 'select',
                     'attribute_orderby' => 'menu_order',
                     'attribute_public'  => 0,
                  ];

                  $result = $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute_taxonomies);

                  if($result){

                     $attribute_taxonomies['attribute_id'] = $wpdb->insert_id;

                     //add it to the WC list
                     $raw_attribute_taxonomies = array_merge( array_filter((array) get_transient( 'wc_attribute_taxonomies' )), [(object) $attribute_taxonomies] );

                     set_transient( 'wc_attribute_taxonomies', $raw_attribute_taxonomies );

                     wp_cache_flush();

                  }else{

                     Util::log()->error([
                        'error' => [
                           'message' => 'This attribute could not be created.',
                        ],
                        'detail' => [
                           'query' => $wpdb->last_query,
                        ]
                     ], __FILE__, __LINE__);

                  }

               }

               if( ! empty($attribute_taxonomies) ){

                  if(isset($payload['attributes'])){

                     $payload['attributes'] = array_merge($payload['attributes'], [
                        [
                           'taxonomy'     => wc_attribute_taxonomy_name($slug),
                           'terms'        => array_filter((array) $attribute['value']),
                           'is_variation' => $attribute['used_for_variation']
                        ]
                     ]);

                  }else{

                     $payload = [
                        'id'         => $this->id,
                        'type'       => $this->data['type'],
                        'attributes' => [
                           [
                              'taxonomy'     => wc_attribute_taxonomy_name($slug),
                              'terms'        => array_filter((array) $attribute['value']),
                              'is_variation' => $attribute['used_for_variation']
                           ]
                        ]
                     ];
                  }

               }

            }else{

               Util::log()->error([
                  'error' => [
                     'message' => 'Creating attribute failed. The attribute payload is invalid. It should be an array of: `name`, `value` and `used_for_variation`.',
                  ],
                  'detail' => [
                     'attribute' => $attribute,
                  ]
               ], __FILE__, __LINE__);
            }
         }

         if( ! empty($payload) ){

            //create task to assign the attributes
            Module_Task::update_entries([
               [
                  'action'      => 'assign_product_attribute',
                  'source'      => 'shop',
                  'target'      => 'shop',
                  'payload'     => $payload,
                  'resource_id' => $this->id,
                  'priority'    => 10,
               ]
            ]);
         }
      }

   }



   /**
    * Assigns the product attributes. This means the terms will be created for the attribute and then the attribute will be assign to the product.
    *
    * $this->data = [
    *    'id'        => 123,
    *    'type'      => 'simple|variable',
    *    'attributes' => [
    *       [
    *          'taxonomy'     => pa_brand,
    *          'terms'        => ['sony', 'apple'],
    *          'is_variation' => false
    *       ],
    *    ]
    * ]
    *
    * @return bool
    */
   public function assign_attributes(){

      $attributes = apply_filters(PREFIX . '\product_task\assign_attributes\attributes', Util::array($this->data)->get('attributes', []), $this);

      if($this->id && ! empty($attributes) && $this->is_supported_type()){

         $terms      = [];
         $taxonomies = [];
         $results    = array_filter((array) get_post_meta($this->id, '_product_attributes', true));

         foreach($attributes as $attribute){

            if(array_key_exists('taxonomy', $attribute) && array_key_exists('terms', $attribute) && array_key_exists('is_variation', $attribute) ){

               foreach($attribute['terms'] as $term_name){

                  //check if taxonomy exists - because the taxonomy could have been created earlier in the same request therefor it might not be available yet
                  if(taxonomy_exists($attribute['taxonomy'])){

                     $term_id = Module_Term::create($term_name, 0, $attribute['taxonomy']);

                     if($term_id){

                        $terms[]      = $term_id;
                        $taxonomies[] = $attribute['taxonomy'];

                        $results[$attribute['taxonomy']] = [
                           'name'         => $attribute['taxonomy'],
                           'value'        => '',//no need value here
                           'position'     => 0,
                           'is_visible'   => empty($attribute['is_variation']) ? 1 : 0,
                           'is_variation' => empty($attribute['is_variation']) ? 0 : 1,
                           'is_taxonomy'  => true,
                        ];

                     }

                  }else{

                     return false;
                  }
               }

            }else{

               Util::log()->error([
                  'error' => [
                     'message' => 'Assingning attribute failed. The attribute payload is invalid. It should be an array of: `taxonomy`, `terms` and `is_variation`.',
                  ],
                  'detail' => [
                     'attribute' => $attribute,
                  ]
               ], __FILE__, __LINE__);
            }
         }

         if( ! empty($results) ){

            //set as attributes
            update_post_meta($this->id, '_product_attributes', $results);

            //set the term relationships
            foreach($taxonomies as $taxonomy){

               $result = wp_set_object_terms($this->id, $terms, $taxonomy, $append = true);

               if(is_wp_error($result)){

                  Util::log()->error([
                     'error' => [
                        'code' => $result->get_error_code(),
                        'message' => $result->get_error_message(),
                     ],
                     'detail' => [
                        'id'    => $this->id,
                        'terms' => $terms,
                     ]
                  ], __FILE__, __LINE__);

               }
            }

         }
      }

      return true;

   }



   /**
    * Updates the product attributes. Removes what is no long availalbe and creates the new attribute taxonomies.
    *
    * $this->data = [
    *    'id'        => 123,
    *    'type'      => 'simple|variable',
    *    'meta_data' => [
    *       '{prefix}_sku'        => '34343RR',
    *       '{prefix}_attributes' => [
    *          [
    *             'name'               => 'Brand',
    *             'value'              => ['sony', 'apple'],
    *             'used_for_variation' => false
    *          ],
    *       ]
    *    ]
    * ]
    *
    * @return void
    */
   public function update_attributes(){

      $attributes = apply_filters(PREFIX . '\product_task\update_attributes\attributes', Util::array($this->data)->get('meta_data/' . Util::prefix('attributes'), []), $this);

      if($this->id && ! empty($attributes) && $this->is_supported_type()){

         $force_update       = get_post_meta($this->id, Util::prefix('force_update_attributes'), true);
         $current_attributes = array_filter((array) get_post_meta($this->id, Util::prefix('attributes'), true));
         $has_diff           = strcmp(json_encode($current_attributes), json_encode($attributes));

         if($has_diff !== 0 || 'yes' === $force_update){

            $taxonomies         = [];
            $product_attributes = get_post_meta($this->id, '_product_attributes', true);

            foreach($current_attributes as $attribute){
               $taxonomies[] = 'pa_' . wc_sanitize_taxonomy_name($attribute['name']);
            }

            foreach($taxonomies as $taxonomy){

               $terms = wp_get_object_terms($this->id, $taxonomy);

               if( ! is_wp_error($terms) ){

                  $current = array_column($terms, 'term_id');

                  wp_remove_object_terms($this->id, $current, $taxonomy);
               }

               if( isset( $product_attributes[$taxonomy] ) ) {
                  unset($product_attributes[$taxonomy]);
               }
            }

            //update attribute list
            update_post_meta($this->id, '_product_attributes', $product_attributes);

            //delete force update flag
            delete_post_meta($this->id, Util::prefix('force_update_attributes'));

            //maybe create attributes
            $this->create_attributes();

         }

      }

   }



   /**
    * Creates the product categories. The list of categories must contain the full path of categories.
    * In case a category has a parent which does not exist in the list then it will be ignored.
    *
    * $this->data = [
    *    'id'        => 123,
    *    'type'      => 'simple|variable',
    *    'meta_data' => [
    *       '{prefix}_sku'        => '34343RR',
    *       '{prefix}_categories' => [
    *          [
    *             'id'        => 123,
    *             'parent_id' => 0,
    *             'name'      => 'Furniture',
    *          ],
    *       ]
    *    ]
    * ]
    *
    * @return void
    */
   public function create_categories(){

      $categories = apply_filters(PREFIX . '\product_task\create_categories\categories', Util::array($this->data)->get('meta_data/' . Util::prefix('categories'), []), $this);

      if($this->id && ! empty($categories) && $this->is_supported_type()){

         $list      = [];
         $processed = [];
         $payload   = [];

         foreach($categories as $category){
            if( ! in_array($category['id'], $list) ){
               $list[] = $category['id'];
            }
         }

         do{

            foreach($categories as $index => $category){

               if(array_key_exists('id', $category) && array_key_exists('parent_id', $category) && array_key_exists('name', $category) ){

                  //skip if it's a child and its parent was not processed yet
                  if ( ! empty($category['parent_id']) && ! in_array( $category['parent_id'], $processed ) ) {

                     //remove if parent is not present in the list
                     if( ! in_array( $category['parent_id'], $list ) ){

                        unset($categories[$index]);

                        //remove parent from de list as well
                        unset($list[array_search($category['id'], $list)]);

                     }

                     continue;

                  }else{

                     $processed[] = $category['id'];

                     unset($categories[$index]);

                     $term_ids = Module_Term::get_mapped_terms($category['id'], $category['parent_id']);

                     if(empty($term_ids)){

                        $term_id = Module_Term::create($category['name'], 0, 'product_cat', [
                           'category_id'        => $category['id'],
                           'category_parent_id' => $category['parent_id'],
                        ]);

                        if($term_id){
                           $term_ids = [$term_id];
                        }
                     }

                     if($term_ids){

                        if(isset($payload['categories'])){

                           $payload['categories'] = array_merge($payload['categories'], $term_ids);

                        }else{

                           $payload = [
                              'id'         => $this->id,
                              'type'       => $this->data['type'],
                              'categories' => $term_ids
                           ];
                        };
                     }

                  }

               }else{

                  //remove because of invalid payload
                  unset($categories[$index]);

                  Util::log()->error([
                     'error' => [
                        'message' => 'Creating category failed. The category payload is invalid. It should be an array of: `id`, `parent_id` and `name`.',
                     ],
                     'detail' => [
                        'category' => $category,
                     ]
                  ], __FILE__, __LINE__);
               }
            }

         }while( ! empty($categories) );

         if( ! empty($payload) ){

            //create task to assign the categories
            Module_Task::update_entries([
               [
                  'action'      => 'assign_product_category',
                  'source'      => 'shop',
                  'target'      => 'shop',
                  'payload'     => $payload,
                  'resource_id' => $this->id,
                  'priority'    => 10,
               ]
            ]);
         }

      }
   }



   /**
    * Assigns the product categories.
    *
    * $this->data = [
    *    'id'         => 123,
    *    'type'       => 'simple|variable',
    *    'categories' => [44,55,66]
    * ]
    *
    * @return void
    */
   public function assign_categories(){

      $categories = apply_filters(PREFIX . '\product_task\assign_categories\categories', Util::array($this->data)->get('categories', []), $this);

      if($this->id && ! empty($categories) && $this->is_supported_type()){

         $result = wp_set_object_terms($this->id, $categories, 'product_cat', $append = true);

         if(is_wp_error($result)){

            Util::log()->error([
               'error' => [
                  'code' => $result->get_error_code(),
                  'message' => $result->get_error_message(),
               ],
               'detail' => [
                  'id'         => $this->id,
                  'categories' => $categories,
               ]
            ], __FILE__, __LINE__);

         }
      }

   }



   /**
    * Updates the product categories.
    *
    * $this->data = [
    *    'id'        => 123,
    *    'type'      => 'simple|variable',
    *    'meta_data' => [
    *       '{prefix}_sku'        => '34343RR',
    *       '{prefix}_categories' => [
    *          [
    *             'id'        => 123,
    *             'parent_id' => 0,
    *             'name'      => 'Furniture',
    *          ],
    *       ]
    *    ]
    * ]
    *
    * @return void
    */
   public function update_categories(){

      $categories = apply_filters(PREFIX . '\product_task\update_categories\categories', Util::array($this->data)->get('meta_data/' . Util::prefix('categories'), []), $this);

      if($this->id && ! empty($categories) && $this->is_supported_type()){

         $current_categories = array_filter((array) get_post_meta($this->id, Util::prefix('categories'), true));
         $has_diff           = strcmp(json_encode($current_categories), json_encode($categories));

         if($has_diff !== 0){

            $cat_list = [];

            foreach($current_categories as $category){
               $cat_list[] = is_array($category) ? $category['id'] : $category;
            }

            $term_ids = Module_Term::get_results([
               'taxonomy'   => 'product_cat',
               'hide_empty' => false,
               'fields'     => 'ids',
               'meta_query' => [
                  [
                     'key'   => Util::prefix('category_id'),
                     'value' => $cat_list,
                     'compare' => 'IN'
                  ]
               ]
            ]);

            wp_remove_object_terms($this->id, $term_ids, 'product_cat');

            $this->create_categories();
         }

      }
   }



   /**
    * Triggers the download product images process.
    *
    * $this->data = [
    *    'id'        => 123,
    *    'type'      => 'simple|variable',
    *    'meta_data' => [
    *       '{prefix}_sku'    => '34343RR',
    *       '{prefix}_images' => [
    *          'https://example.com/wp-content/uploads/2017/03/T_1.jpg',
    *       ]
    *    ]
    * ]
    *
    * @return void
    */
   public function process_images(){

      $images = apply_filters(PREFIX . '\product_task\process_images\images', Util::array($this->data)->get('meta_data/' . Util::prefix('images'), []), $this);

      if($this->id && ! empty($images) && $this->is_supported_type()){

         $current_images = array_filter((array) get_post_meta($this->id, Util::prefix('images'), true));
         $has_diff       = strcmp(json_encode($current_images), json_encode($images));

         if($has_diff !== 0){

            //create task to download the images
            Module_Task::update_entries([
               [
                  'action'      => 'download_product_image',
                  'source'      => 'shop',
                  'target'      => 'shop',
                  'payload'     => [
                     'id'     => $this->id,
                     'type'   => $this->data['type'],
                     'images' => $images
                  ],
                  'resource_id' => $this->id,
                  'priority'    => 10,
               ]
            ]);
         }
      }

   }



   /**
    * Downloads and attaches the product images.
    *
    * $this->data = [
    *    'id'     => 123,
    *    'type'   => 'simple|variable',
    *    'images' => [
    *       'https://example.com/wp-content/uploads/2017/03/T_1.jpg',
    *    ]
    * ]
    *
    * @return void
    */
   public function download_images(){

      $images = apply_filters(PREFIX . '\product_task\download_images\images', Util::array($this->data)->get('images', []), $this);

      if($this->id && ! empty($images)){

         $first_image  = array_shift($images);
         $thumbnail_id = get_post_thumbnail_id($this->id);
         $image_id     = Util_File::download_image_from_url($first_image, $this->id);

         if ( is_wp_error($image_id) ) {

            Util::log()->error([
               'error' => [
                  'code' => $image_id->get_error_code(),
                  'message' => $image_id->get_error_message(),
               ],
               'detail' => [
                  'id'     => $this->id,
                  'images' => $images,
               ]
            ], __FILE__, __LINE__);

         }else{

            set_post_thumbnail($this->id, $image_id);

            if (Util_File::has_downloaded_attachment($thumbnail_id)) {
               wp_delete_attachment($thumbnail_id);
            }

            $gallery_images = [];

            foreach ($images as $product_gallery) {

               $image_id = Util_File::download_image_from_url($product_gallery, $this->id);

               if (is_wp_error($image_id)) {

                  Util::log()->error([
                     'error' => [
                        'code' => $image_id->get_error_code(),
                        'message' => $image_id->get_error_message(),
                     ],
                     'detail' => [
                        'id'     => $this->id,
                        'images' => $images,
                     ]
                  ], __FILE__, __LINE__);

               }else{
                  $gallery_images[] = $image_id;
               }
            }

            if (!empty($gallery_images)) {

               $product_image_gallery = get_post_meta($this->id, '_product_image_gallery', true);

               if (!empty($product_image_gallery)) {
                  foreach (explode(',', $product_image_gallery) as $product_image_gallery_id) {
                     if (Util_File::has_downloaded_attachment($product_image_gallery_id)) {
                        wp_delete_attachment($product_image_gallery_id);
                     }
                  }
               }

               update_post_meta($this->id, '_product_image_gallery', implode(',', $gallery_images));
            }
         }

      }

   }



   /**
    * Deletes the product images.
    *
    * @return void
    */
   public function delete_images(){

      if($this->id){

         $images = get_attached_media('image', $this->id);

         foreach($images as $image){
            wp_delete_attachment($image->ID, true);
         }

      }

   }



   /**
    * Deletes the shop categories in case there are no products.
    * Mark the existing products for deleting.
    *
    * @return bool
    */
   public function delete_shop_category(){

      Module_Product_Task_Util::update_term_count();

      return Module_Term::delete($this->id, 'product_cat', ['is_empty' => true]);
   }



   /**
    * Whether or not to mark an unavailable product for deleting.
    *
    * @param string|null $stock
    * @return bool
    */
   public function exclude_unavailable_product($stock){

      $result  = false;
      $exclude = apply_filters(PREFIX . '\product_task\exclude_unavailable_product', false);

      if($exclude && is_null($stock)){
         $result = true;
      }

      return $result;
   }



   /**
    * Sets the product visibility relation.
    *
    * @param string $visibility - visible|hidden
    * @return void
    */
   public function set_visibility(string $visibility){

      if($this->id && $this->is_supported_type() && in_array($visibility, ['visible', 'hidden'])){

         $terms        = [];
         $excl_catalog = get_term_by( 'slug', 'exclude-from-catalog', 'product_visibility');
         $excl_search  = get_term_by( 'slug', 'exclude-from-search', 'product_visibility');

         if(isset($excl_catalog->term_id)){
            $terms[] = $excl_catalog->term_id;
         }

         if(isset($excl_search->term_id)){
            $terms[] = $excl_search->term_id;
         }

         if('hidden' === $visibility){

            $result = wp_set_object_terms($this->id, $terms, 'product_visibility');

         }else{

            $result = wp_remove_object_terms($this->id, $terms, 'product_visibility');

            if($result){
               update_post_meta($this->id, Util::prefix('import_state'), 'finished');
            }
         }

         if(is_wp_error($result)){

            Util::log()->error([
               'error' => [
                  'code' => $result->get_error_code(),
                  'message' => $result->get_error_message(),
               ],
               'detail' => [
                  'id'    => $this->id,
                  'terms' => $terms,
               ]
            ], __FILE__, __LINE__);

         }
      }
   }



   /**
    * Sets the product type relation.
    *
    * @return void
    */
   public function set_type(){

      if($this->id && $this->is_supported_type()){

         $terms     = [];
         $type      = Util::array($this->data)->get('type');
         $type_term = get_term_by( 'slug', $type, 'product_type');

         if(isset($type_term->term_id)){
            $terms[] = $type_term->term_id;
         }else{
            $type_term = wp_insert_term($type, 'product_type');

            if(is_wp_error($type_term)){
               Util::log()->error([
                  'error' => [
                     'code' => $type_term->get_error_code(),
                     'message' => $type_term->get_error_message(),
                  ],
                  'detail' => [
                     'id'   => $this->id,
                     'type' => $type,
                  ]
               ]);

            }else{
               $terms[] = $type_term->term_id;
            }
         }

         $result = wp_set_object_terms($this->id, $terms, 'product_type');

         if(is_wp_error($result)){

            Util::log()->error([
               'error' => [
                  'code' => $result->get_error_code(),
                  'message' => $result->get_error_message(),
               ],
               'detail' => [
                  'id'    => $this->id,
                  'terms' => $terms,
               ]
            ]);

         }
      }
   }



   /**
    * Retrieves the product type.
    *
    * @return string
    */
   public function get_type(){

      global $wpdb;

      $type = '';

      if($this->id){

         $terms = wp_get_object_terms($this->id, 'product_type');

         foreach($terms as $term){

            if(in_array($term->slug, $this->supported_types)){

               $type = $term->slug;

               break;

            }
         }


         //add fallback for variation products
         if(empty($type)){

            $post_type = $wpdb->get_var("SELECT post_type FROM {$wpdb->posts} WHERE ID = '{$this->id}' LIMIT 1");

            if('product_variation' === $post_type){
               $type = 'variation';
            }
         }

      }

      return $type;
   }



   /**
    * In case there is a parent id found in the metadata then will process the parent accordingly.
    *
    * @return bool
    */
   public function maybe_process_parent(){

      $parent_id = Util::array($this->data)->get('meta_data/' . Util::prefix('parent_id'));
      $type      = Util::array($this->data)->get('type');

      //in case this is a variation product
      if( 'variation' === $type && ! empty( $parent_id ) ){

         $parent = Module_Product_Task_Util::get_parent_by_meta($parent_id);

         if(empty($parent)){

            $variation_sku = Util::array($this->data)->get('meta_data/' . Util::prefix('sku'));

            //add task to create the variable product first then the variations
            Module_Task::update_entries([
               [
                  'action'      => 'create_or_update_product',
                  'source'      => 'shop',
                  'target'      => 'shop',
                  'payload'     => Module_Product_Task_Util::build_variable_payload($this->data, [
                     PREFIX . '_product_id' => $parent_id,
                     PREFIX . '_source_variation_sku' => $variation_sku,
                  ]),
                  'resource_id' => $parent_id,
                  'priority'    => 5, //make it high priority
               ]
            ]);

            return false;

         }else{

            $extra_meta    = [];
            $v_attributes  = Util::array($this->data)->get('meta_data/' . Util::prefix('attributes', []));
            $current_attributes = array_filter((array) get_post_meta($parent['id'], Util::prefix('attributes'), true));
            $p_attributes  = Module_Product_Task_Util::combine_variation_with_parent_attribute($current_attributes, $v_attributes);
            $has_diff      = strcmp(json_encode($current_attributes), json_encode($p_attributes));

            //-------- Parent

            if($has_diff !== 0){

               //update product
               $p_product =  new self();
               $p_product->set_data([
                  'id'        => $parent['id'],
                  'type'      => 'variable',
                  'meta_data' => [
                     PREFIX . '_force_update_attributes' => 'yes',
                     PREFIX . '_attributes' => $p_attributes
                  ],
               ]);
               $p_product->set_id( $p_product->get_id_from_data() );
               $p_product->update_metadata();
               $p_product->update_attributes();

            }

            //remove parent transients to force WC to re-create them
            Transient::delete("wc_product_children_{$parent['id']}", false);


            //-------- Variation

            //build extra meta_data from attributes
            foreach($v_attributes as $attribute){
               $extra_meta['attribute_pa_' . sanitize_title($attribute['name'])] = sanitize_title($attribute['value']);
            }

            //set parent id
            $this->data['parent_id'] = $parent['id'];

            //set extra meta_data
            $this->data['meta_data'] = array_merge($this->data['meta_data'], $extra_meta);

            //update data
            $this->set_data($this->data);

         }
      }

      return true;
   }



   /**
    * In case there is a parent id found in the metadata then will update the parent according to the variation source updates.
    *
    * @return bool
    */
   public function maybe_update_parent(){

      $parent_id = Util::array($this->data)->get('meta_data/' . Util::prefix('parent_id'));
      $type      = Util::array($this->data)->get('type');

      //in case this is a variation product
      if( 'variation' === $type && ! empty( $parent_id ) ){

         $parent = Module_Product_Task_Util::get_parent_by_meta($parent_id);

         if( ! empty($parent) ){

            $variation_sku = Util::array($this->data)->get('meta_data/' . Util::prefix('sku'));
            $source_variation_sku = Util::array($parent)->get('meta_data/' . Util::prefix('source_variation_sku'));

            //in case no variation source (backward compatibility) or only if variation source matches (to avoid multiple updates from each variation)
            if( empty($source_variation_sku) || $source_variation_sku == $variation_sku){

               Module_Task::update_entries([
                  [
                     'action'      => 'create_or_update_product',
                     'source'      => 'shop',
                     'target'      => 'shop',
                     'payload'     => Module_Product_Task_Util::build_variable_payload($this->data, [
                        PREFIX . '_product_id' => $parent_id,
                        PREFIX . '_source_variation_sku' => $variation_sku,
                     ]),
                     'resource_id' => $parent_id,
                  ]
               ]);
            }

         }
      }

      return true;
   }



   /**
    * Checks whether or not the product should be published.
    *
    * @param string $price
    * @param int $stock
    * @return bool
    */
   protected function needs_to_be_published($price, $stock){

      global $wpdb;

      $result = false;

      //set the type if it's missing
      if( empty($this->data['type']) ){
         $this->data['type'] = $this->get_type();
      }

      //for all supported types
      if($this->is_supported_type()){

         $import_state = get_post_meta($this->id, Util::prefix('import_state'), true);

         if('finished' !== $import_state){

            if('variable' === $this->data['type']){

               //check if there is at least one variation with price and stock
               foreach($this->get_children() as $child_id){

                  $v_price = Module_Product_Task_Util::process_price('not_available', $child_id);
                  $v_stock = Module_Product_Task_Util::process_stock('not_available', $child_id);

                  //check if stock is manageable
                  if(empty($v_stock)){
                     $manage_stock = get_post_meta($child_id, '_manage_stock', true);
                     $v_stock      = 'no' === $manage_stock ? '1' : $v_stock;
                  }

                  if( '' != $v_price && '' != $v_stock){

                     $price = $v_price;
                     $stock = $v_stock;

                     break;

                  }
               }

            }else{

               //check if stock is manageable
               if(empty($stock)){
                  $manage_stock = get_post_meta($this->id, '_manage_stock', true);
                  $stock        = 'no' === $manage_stock ? '1' : $stock;
               }
            }

            $result = '' != $price && '' != $stock;

         }

      //for `variation` type - let's try to publish its parent
      }elseif('variation' === $this->data['type']){

         $parent_id = $wpdb->get_var("SELECT post_parent FROM {$wpdb->posts} WHERE ID = '{$this->id}' LIMIT 1");

         if($parent_id){

            $p_data = [
               'id'   => $parent_id,
               'type' => 'variable',
            ];

            $p_product = new self;
            $p_product->set_data($p_data);
            $p_product->set_id($p_product->get_id_from_data());
            $p_product->maybe_publish(null, null);

         }

      }

      return $result;
   }



   /**
    * In case the product has stock and price set it as publish.
    *
    * @param string $price
    * @param int $stock
    * @return void
    */
   protected function maybe_publish($price, $stock){

      if($this->needs_to_be_published($price, $stock)){

         $this->set_data(array_merge($this->data, [
            'status' => apply_filters(PREFIX . '\product_task\maybe_publish\status', 'publish', $this->data),
         ]));

         if('variable' === $this->data['type']){

            //set default attributes
            $this->set_data(array_merge($this->data, [
               'meta_data' => array_merge(Util::array($this->data)->get('meta_data', []), [
                  '_default_attributes' => Module_Product_Task_Util::get_default_attributes($this->id),
               ]),
            ]));
         }

         $this->update();
         $this->update_metadata();
         $this->set_visibility('visible');
      }
   }

}