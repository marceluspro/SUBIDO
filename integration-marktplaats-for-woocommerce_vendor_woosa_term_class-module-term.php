<?php
/**
 * Module Term
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Term{


   /**
    * Creates the given term.
    *
    * @param string $name
    * @param int $parent_id
    * @param string $taxonomy
    * @param array $meta
    * @return int|false term id or false
    */
   public static function create($name, $parent_id = 0, $taxonomy = 'product_cat', $meta = []){

      $term_id            = false;
      $name               = Util::remove_backslashes($name);
      $category_id        = Util::array($meta)->get('category_id', 0);
      $category_parent_id = Util::array($meta)->get('category_parent_id', 0);

      //search for parent by meta
      if( ! empty($category_parent_id) ){
         $parent_id = self::get_parent_id_by_meta($category_parent_id, Util::prefix('category_id'));
      }

      $args = [
         'parent' => $parent_id,
      ];
      $insert = wp_insert_term($name, $taxonomy, $args);

      if(is_wp_error($insert)){

         if('term_exists' === $insert->get_error_code()){

            $term_id = $insert->get_error_data();//extract the term id

         }else{

            Util::log()->error([
               'error' => [
                  'code' => $insert->get_error_code(),
                  'message' => $insert->get_error_message(),
               ],
               'detail' => [
                  'name'     => $name,
                  'taxonomy' => $taxonomy,
                  'args'     => $args,
               ]
            ], __FILE__, __LINE__);
         }

      }else{

         $term_id = $insert['term_id'];
      }

      if($term_id){

         if('product_cat' === $taxonomy){

            if( ! metadata_exists('term', $term_id, Util::prefix('mapped_category_id')) ){

               add_term_meta($term_id, Util::prefix('category_id'), $category_id, true);
               add_term_meta($term_id, Util::prefix('category_parent_id'), $category_parent_id, true);
               add_term_meta($term_id, Util::prefix('plugin_version'), VERSION, true);
            }

         }else{

            add_term_meta($term_id, Util::prefix('plugin_version'), VERSION, true);
         }
      }

      return $term_id;
   }



   /**
    * Updates the given term.
    *
    * @param int $term_id
    * @param string $taxonomy
    * @param array $args
    * @return int|false term id or false
    */
   public static function update($term_id, $taxonomy = 'product_cat', $args = []){

      $update = wp_update_term($term_id, $taxonomy, $args);

      if(is_wp_error($update)){

         Util::log()->error([
            'error' => [
               'code' => $update->get_error_code(),
               'message' => $update->get_error_message(),
            ],
            'detail' => [
               'term_id'  => $term_id,
               'taxonomy' => $taxonomy,
               'args'     => $args,
            ]
         ], __FILE__, __LINE__);

         return false;
      }

      return $term_id;
   }



   /**
    * Deletes the given term if it's empty.
    *
    * @param int $term_id
    * @param string $taxonomy
    * @param array $args
    * @return bool
    */
   public static function delete($term_id, $taxonomy = 'product_cat', $args = []){

      $term = get_term($term_id, $taxonomy);

      //term not found
      if( ! isset($term->term_id) ){
         return true;
      }

      //term not empty
      if(Util::array($args)->get('is_empty') === true && $term->count > 0){
         return false;
      }

      $delete = wp_delete_term($term_id, $taxonomy, $args);

      if(is_wp_error($delete)){

         Util::log()->error([
            'error' => [
               'code' => $delete->get_error_code(),
               'message' => $delete->get_error_message(),
            ],
            'detail' => [
               'term_id'  => $term_id,
               'taxonomy' => $taxonomy,
               'args'     => $args,
            ]
         ], __FILE__, __LINE__);

         return false;
      }

      return true;

   }



   /**
    * Retrieves the results by the given arguments. For the list of args see https://developer.wordpress.org/reference/functions/get_terms/
    *
    * @param array $args
    * @return array
    */
   public static function get_results($args){

      $terms = get_terms($args);

      if(is_wp_error($terms)){

         Util::log()->error([
            'error' => [
               'code' => $terms->get_error_code(),
               'message' => $terms->get_error_message(),
            ],
            'detail' => [
               'args' => $args,
            ]
         ], __FILE__, __LINE__);

         return [];
      }

      return $terms;
   }



   /**
    * Retrieves the mapped terms for the given category.
    *
    * @param string $category_id
    * @param string $category_parent_id
    * @param string $taxonomy
    * @return array
    */
   public static function get_mapped_terms($category_id, $category_parent_id, $taxonomy = 'product_cat'){

      $args  = [
         'taxonomy'   => $taxonomy,
         'hide_empty' => false,
         'fields' => 'ids',
         'meta_query' => []
      ];

      $args['meta_query'] = [
         'relation' => 'OR',
         [
            'key'   => Util::prefix('mapped_category_id'),
            'value' => $category_id
         ],
         [
            'key'   => Util::prefix('mapped_category_id'),
            'value' => $category_parent_id
         ]
      ];

      //search by mapped category
      $terms = get_terms($args);

      if(empty($terms)){

         $args['meta_query'] = [
            [
               'key'   => Util::prefix('category_id'),
               'value' => $category_id
            ],
            [
               'key'   => Util::prefix('category_parent_id'),
               'value' => $category_parent_id
            ],
         ];

         //search by category id
         $terms = get_terms($args);
      }

      if(is_wp_error($terms)){

         Util::log()->error([
            'error' => [
               'code' => $terms->get_error_code(),
               'message' => $terms->get_error_message(),
            ],
            'detail' => [
               'args' => $args,
            ]
         ], __FILE__, __LINE__);

         return [];
      }

      return $terms;
   }



   /**
    * Retrieves the parent by a given meta key.
    *
    * @param int|string $parent_id
    * @param string $meta_key
    * @param string $taxonomy
    * @return int
    */
   protected static function get_parent_id_by_meta($parent_id, $meta_key, $taxonomy = 'product_cat'){

      $result = 0;
      $terms = get_terms([
         'taxonomy'   => $taxonomy,
         'hide_empty' => false,
         'meta_query' => [
            [
               'key'   => $meta_key,
               'value' => $parent_id
            ]
         ]
      ]);

      if(isset($terms[0])){
         $result = $terms[0]->term_id;
      }

      return $result;
   }
}