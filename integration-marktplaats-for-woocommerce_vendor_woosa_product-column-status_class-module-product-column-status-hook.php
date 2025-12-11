<?php
/**
 * Module Product Column Status Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Column_Status_Hook implements Interface_Hook{


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

      $accounts = apply_filters(PREFIX . '\module\product_column_status\service_accounts', []);

      if( empty($accounts) ){

         if( ! isset($items[PREFIX . '_product_status']) ){

            $items[PREFIX . '_product_status'] = [
               'label'        => Module_Core::config('service.name'),
               'post_type'    => ['product'],
               'after_column' => 'product_cat',
               'callback'     => [__CLASS__, 'handle_table_column_status'],
            ];
         }

      }else{

         $index = 1;

         foreach($accounts as $account){

            $col_key = PREFIX . "_account_{$account['account_id']}";

            if( ! isset($items[$col_key]) ){

               $items[$col_key] = [
                  'label'        => $account['account_label'],
                  'post_type'    => ['product'],
                  'after_column' => 'product_cat',
                  'callback'     => [Module_Product_Column_Status_Hook::class, 'handle_table_column_status'],
               ];
            }

            $index++;
         }
      }

      return apply_filters(PREFIX . '\product_column_status\table_columns', $items);
   }



   /**
    * Renders the content of table column `{prefix}_product_status`.
    *
    * @param int $object_id
    * @param string $column
    * @return string
    */
   public static function handle_table_column_status($object_id, $column){

      $data = [
         'product_id'  => $object_id,
         'column_name' => $column,
      ];

      $hidden_columns = array_filter((array) get_user_option('manageedit-productcolumnshidden'));

      if(in_array($column, $hidden_columns)):?>
         -
      <?php else:?>
         <div class="<?php echo PREFIX;?>-style" data-<?php echo PREFIX;?>-product-table-column-status='<?php echo json_encode($data);?>'>
            <img src="<?php echo untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/images/loading.svg';?>">
         </div>
      <?php endif;
   }
}