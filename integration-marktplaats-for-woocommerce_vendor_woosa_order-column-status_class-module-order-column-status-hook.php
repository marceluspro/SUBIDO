<?php
/**
 * Module Order Column Status Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Column_Status_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\table_column\columns', [__CLASS__, 'table_columns']);

   }



   /**
    * Define the table columns.
    *
    * @param array $items
    * @return array
    */
   public static function table_columns($items){

      if( ! isset($items[PREFIX . '_order_status']) ){

         $items[PREFIX . '_order_status'] = [
            'label'        => Module_Core::config('service.name'),
            'post_type'    => ['shop_order'],
            'after_column' => 'order_status',
            'callback'     => [__CLASS__, 'handle_order_table_column_status'],
         ];
      }

      return $items;
   }



   /**
    * Renders the content of table column `{prefix}_order_status`.
    *
    * @param int $object_id
    * @param string $column
    * @return string
    */
   public static function handle_order_table_column_status($object_id, $column){

      $data = [
         'order_id'    => $object_id,
         'column_name' => $column,
      ];

      $hidden_columns1 = array_filter((array) get_user_option('manageedit-shop_ordercolumnshidden'));
      $hidden_columns2 = array_filter((array) get_user_option('managewoocommerce_page_wc-orderscolumnshidden'));
      $hidden_columns = array_merge($hidden_columns1, $hidden_columns2);

      if(in_array($column, $hidden_columns)):?>
         -
      <?php else:?>
         <div class="<?php echo PREFIX;?>-style" data-<?php echo PREFIX;?>-order-table-column-status='<?php echo json_encode($data);?>'>
            <img src="<?php echo untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/images/loading.svg';?>">
         </div>
      <?php endif;
   }
}