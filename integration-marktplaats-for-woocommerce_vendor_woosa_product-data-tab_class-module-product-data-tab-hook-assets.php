<?php
/**
 * Module Product Data Tab Hook Assets
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Data_Tab_Hook_Assets implements Interface_Hook_Assets{


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
            'name' => 'module-product-data-tab',
            'css' => [
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/css/',
            ],
            'js' => [
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/js/',
               'dependency' => ['jquery', PREFIX . '-module-core'],
            ],
         ],
      ]);

      $icon_src = file_exists(DIR_PATH . '/includes/product-data-tab/assets/images/icon.png') ? DIR_URL . '/includes/product-data-tab/assets/images/icon.png' : untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/images/icon.png';

      $css = '
         #woocommerce-product-data ul.wc-tabs li.mkt_variable_options.mkt_variable_tab a:before,
         #woocommerce-product-data ul.wc-tabs li.mkt_simple_options.mkt_simple_tab a:before{
            content: " ";
            background: url('.$icon_src.');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: 0 0;
         }
      ';

      wp_add_inline_style(PREFIX . '-module-product-data-tab', $css);

   }

}