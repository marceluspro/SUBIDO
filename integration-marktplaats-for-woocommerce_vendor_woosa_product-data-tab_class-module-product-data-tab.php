<?php
/**
 * Module Product Data Tab
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Data_Tab{


   /**
    * List of available tabs.
    *
    * @return array
    * [
    *    'unique_id' => [
    *       'label'        => __('My label', 'integration-marktplaats-for-woocommerce'),
    *       'product_type' => ['simple'],
    *       'visibility'   => ['show_if_simple', 'hide_if_subscription', 'show_if_variable],
    *       'priority'     => 10,
    *       'path_file'    => 'simple/data-panel.php'
    *    ]
    * ]
    */
   public static function get_tabs(){

      $items = [
         PREFIX . '_simple' => [
            'label'        => Module_Core::config('service.name'),
            'product_type' => 'simple',
            'visibility'   => ['show_if_simple', 'hide_if_subscription'],
            'priority'     => 20,
            'path_file'    => 'simple/panel.php'
         ],
         PREFIX . '_variable' => [
            'label'        => Module_Core::config('service.name'),
            'product_type' => 'variable',
            'visibility'   => ['show_if_variable'],
            'priority'     => 20,
            'path_file'    => 'variable/panel.php'
         ],
      ];

      return apply_filters(PREFIX . '\module\product_data_tab\tabs', $items);
   }



   /**
    * Retrieves the content of the tab panel.
    *
    * @param \WC_Product $product
    * @param string $tab_key
    * @param integer $page
    * @return string
    */
   public static function get_tab_panel($product, $tab_key, $page = 1){

      $output = '<p>'.__('Please save the changes first!', 'integration-marktplaats-for-woocommerce').'</p>';
      $tab    = Util::array(self::get_tabs())->get($tab_key);
      $type   = array_filter((array) Util::array($tab)->get('product_type'));

      if( array_intersect($type, ['variable']) ){

         $query = wc_get_products([
            'status'   => ['private', 'publish'],
            'type'     => 'variation',
            'parent'   => $product->get_id(),
            'limit'    => 10,
            'page'     => $page,
            'paginate' => true,
            'orderby'  => [
               'menu_order' => 'ASC',
               'ID'         => 'DESC',
            ],
            'return'  => 'ids',
         ]);

         $variations = $query->products;
         $pages      = $query->max_num_pages;
         $meta       = new Module_Meta($product->get_id());

         $output = Util::get_template(trim($tab['path_file'], '/'), [
            'product'    => $product,
            'meta'       => $meta,
            'tab_key'    => $tab_key,
            'variations' => $variations,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');

         /**
          * Let 3rd-party to filter
          */
         $output = apply_filters(PREFIX . '\module\product_data_tab\get_tab_panel\variable\output', $output, [
            'product'    => $product,
            'meta'       => $meta,
            'tab_key'    => $tab_key,
            'variations' => $variations,
         ]);

         //add pagination
         $output .= Util::get_template('pagination.php', [
            'page'  => $page,
            'pages' => $pages,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');

      }else if( array_intersect($type, ['simple', 'grouped']) ){

         $meta  = new Module_Meta($product->get_id());

         $output = Util::get_template(trim($tab['path_file'], '/'), [
            'product' => $product,
            'meta'    => $meta,
            'tab_key' => $tab_key,
         ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');

         /**
          * Let 3rd-party to filter
          */
         $output = apply_filters(PREFIX . '\module\product_data_tab\get_tab_panel\default\output', $output, [
            'product' => $product,
            'meta'    => $meta,
            'tab_key' => $tab_key,
         ]);
      }

      return $output;

   }

}