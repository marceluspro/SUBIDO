<?php
/**
 * Module Marketplace Product
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Product{


   /**
    * Retrieves product by the given EAN code.
    *
    * @param string $ean
    * @return \WC_Product|null
    */
   public static function get_product_by_ean(string $ean){

      if(empty($ean)){
         return null;
      }

      $product_id = 0;
      $source     = Option::get('ean_source', 'default');

      switch($source){

         case 'custom_field':

            $meta_key = Option::get('ean_custom_field_name');
            $product_id = self::find_product_id_by_ean($ean, $meta_key);

            break;


         case 'sku':

            $product_id = wc_get_product_id_by_sku($ean);

            break;


         case 'attribute':

            $attr_name = sanitize_title(Option::get('ean_source__attribute_name'));
            $query     = new \WP_Query([
               'posts_per_page' => 1,
               'post_type'      => ['product'],
               'post_status'    => 'any',
               'tax_query' => [
                  [
                     'taxonomy' => "pa_{$attr_name}",
                     'field'    => 'slug',
                     'terms'    => [$ean],
                     'operator' => 'IN',
                  ],
               ]
            ]);

            if(isset($query->posts[0])){
               $product_id = $query->posts[0]->ID;
            }

            break;

         default:

            $product_id = self::find_product_id_by_ean($ean);

      }

      //fallback, use EAN code from our meta field
      if(empty($product_id) && 'default' !== $source){
         $product_id = self::find_product_id_by_ean($ean);
      }

      return wc_get_product($product_id);
   }



   /**
    * Retrieves product ID found by EAN code.
    *
    * @param string $ean
    * @param string $meta_key
    * @return int
    */
   private static function find_product_id_by_ean($ean, $meta_key = PREFIX.'_ean'){

      $product_id = 0;

      if(empty($ean)){
         return $product_id;
      }

      $query = new \WP_Query([
         'posts_per_page' => 1,
         'post_type'      => ['product', 'product_variation'],
         'post_status'    => 'any',
         'fields'         => 'ids',
         'meta_query'     => [
            [
               'key'     => $meta_key,
               'value'   => $ean,
               'compare' => '=',
            ]
         ]
      ]);

      if(isset($query->posts[0])){
         $product_id = (int) $query->posts[0];
      }

      return $product_id;
   }



   /**
    * Retrieves the product image urls.
    *
    * @param \WC_Product $product
    * @return array
    */
   public static function get_product_image_urls(\WC_Product $product, $size = [1024, 1024]){

      $results     = [];
      $post_id     = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
      $gallery_ids = $product->get_gallery_image_ids();
      $thumbnail   = get_the_post_thumbnail_url($post_id, $size);

      if( ! empty($thumbnail) ){
         $results[] = $thumbnail;
      }

      if($product->is_type('variation')){

         //get the gallery set by using `Variation Images Gallery for WooCommerce` plugin
         if(empty($gallery_ids)){
            $gallery_ids = $product->get_meta('rtwpvg_images');
         }

         //get the gallery set by using `Additional Variation Images Gallery for WooCommerce` plugin
         if(empty($gallery_ids)){
            $gallery_ids = $product->get_meta('woo_variation_gallery_images');
         }

         //get the gallery of the parent product
         if(empty($gallery_ids)){
            $p_product = wc_get_product( $product->get_parent_id() );
            $gallery_ids = $p_product->get_gallery_image_ids();
         }
      }

      foreach(array_filter((array) $gallery_ids) as $img_id){
         $results[] = wp_get_attachment_image_url($img_id, $size);
      }

      return $results;
   }
}