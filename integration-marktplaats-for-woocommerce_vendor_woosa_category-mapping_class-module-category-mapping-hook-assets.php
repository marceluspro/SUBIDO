<?php
/**
 * Category Mapping Hook Assets
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Category_Mapping_Hook_Assets implements Interface_Hook_Assets{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('admin_enqueue_scripts', [__CLASS__ , 'admin_assets']);

   }



   /**
    * Enqueues public CSS/JS files.
    *
    * @return void
    */
   public static function public_assets(){}



   /**
    * Enqueues admin CSS/JS files.
    *
    * @return void
    */
   public static function admin_assets(){

      Util::enqueue_scripts([
         [
            'name' => 'module-category-mapping',
            'css' => [
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/css/',
            ],
            'js' => [
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/js/',
               'dependency' => [PREFIX . '-module-core'],
               'localize' => [
                  'translation' => [
                     'btn' => [
                        'save' => __('Save', 'integration-marktplaats-for-woocommerce'),
                        'close' => __('Close', 'integration-marktplaats-for-woocommerce'),
                        'processing' => __('Processing...', 'integration-marktplaats-for-woocommerce'),
                        'connect' => __('Connect', 'integration-marktplaats-for-woocommerce'),
                     ],
                     'view_content_title' => __('Product content', 'integration-marktplaats-for-woocommerce'),
                     'unmap_category' => __('Are you sure you want to unmap this category?', 'integration-marktplaats-for-woocommerce'),
                     'upload_product_content' => __('Are you sure you you want to upload the content?', 'integration-marktplaats-for-woocommerce'),
                     'config_category_title' => __('Configure category', 'integration-marktplaats-for-woocommerce'),
                  ]
               ],
            ],
         ],
      ]);
   }
}
