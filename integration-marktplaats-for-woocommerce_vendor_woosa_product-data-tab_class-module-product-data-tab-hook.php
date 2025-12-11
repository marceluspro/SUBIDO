<?php
/**
 * Module Product Data Tab Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Product_Data_Tab_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('admin_init', [__CLASS__, 'maybe_init']);

   }



   /**
    * Initiates the logic under a condition.
    *
    * @return void
    */
   public static function maybe_init(){

      add_filter('woocommerce_product_data_tabs', [__CLASS__, 'add_tab']);
      add_action('woocommerce_product_data_panels', [__CLASS__, 'output_tab_panel']);
      add_action('woocommerce_process_product_meta', [__CLASS__, 'save_tab'], 30);

      add_action(PREFIX . '\module\product_data_tab\panel\general_tab\top', [__CLASS__ , 'display_general_errors'], 1, 2);
      add_action(PREFIX . '\module\product_data_tab\panel\account_tab\top', [__CLASS__ , 'display_account_errors'], 1, 3);

      //backward compatibility for templates with old hook names - START
      add_action(PREFIX . '\product_data_tab\panel\general_tab\top', [__CLASS__ , 'display_general_errors'], 1, 2);
      add_action(PREFIX . '\product_data_tab\panel\account_tab\top', [__CLASS__ , 'display_account_errors'], 1, 3);
      //backward compatibility for templates with old hook names - END
   }



   /**
    * Whether or not the given product type has a tab.
    *
    * @param string $product_type
    * @return boolean
    */
   public static function is_valid_type($product_type){

      $result = false;

      foreach(Module_Product_Data_Tab::get_tabs() as $tab){

         $type = array_filter((array) $tab['product_type']);

         if(in_array($product_type, $type)){
            $result = true;
            break;
         }
      }

      return $result;
   }



   /**
    * Adds the tab.
    *
    * @param array $tabs
    * @return void
    */
   public static function add_tab(array $tabs){

      foreach(Module_Product_Data_Tab::get_tabs() as $key => $tab){

         $tabs[$key] = array(
            'target'   => "{$key}_data",
            'label'    => Util::array($tab)->get('label', 'Unknown'),
            'class'    => Util::array($tab)->get('visibility'),
            'priority' => Util::array($tab)->get('priority', 10)
         );

      }

      return $tabs;
   }



   /**
    * Renders the content of tab panel.
    *
    * @return string
    */
   public static function output_tab_panel(){

      global $product_object;

      $product = $product_object;

      if(self::is_valid_type($product->get_type())){

         foreach(Module_Product_Data_Tab::get_tabs() as $tab_key => $tab){

            $output = Module_Product_Data_Tab::get_tab_panel($product, $tab_key);

            echo '<div id="'.$tab_key.'_data" class="panel woocommerce_options_panel hidden '.PREFIX.'-style" data-'.PREFIX.'-tabkey="'.$tab_key.'">'.$output.'</div>';
         }

      }

   }



   /**
    * Saves data tab.
    *
    * @param int $post_id
    * @return void
    */
   public static function save_tab( $post_id ) {

      if(isset($_POST[Util::prefix('fields')])){

         $meta = new Module_Meta($post_id);

         if( $meta->is_product_type('simple') ){

            $fields = apply_filters(PREFIX . '\module\product_data_tab\save', Util::array($_POST)->get(Util::prefix('fields/simple')), $post_id, $meta);

            foreach($fields as $key => $value){
               $meta->delete_error($key);
               $meta->set($key, $value);
            }

            $meta->save();

         }elseif($meta->is_product_type('variable')){

            $variable   = Util::array($_POST)->get(Util::prefix('fields/variable'));
            $variations = Util::array($variable)->get('variations');

            if(isset($variable['parent'])){

               $p_fields = apply_filters(PREFIX . '\module\product_data_tab\save', $variable['parent'], $post_id, $meta);

               foreach($p_fields as $key => $value){
                  $meta->delete_error($key);
                  $meta->set($key, $value);
               }

               $meta->save();
            }

            foreach($variations as $var_id => $v_fields){

               $meta     = new Module_Meta($var_id);
               $v_fields = apply_filters(PREFIX . '\module\product_data_tab\save', $v_fields, $var_id, $meta);

               foreach($v_fields as $key => $value){
                  $meta->delete_error($key);
                  $meta->set($key, $value);
               }

               $meta->save();
            }
         }
      }
   }



   /**
    * Displayes the general errors.
    *
    * @param \WC_Product $product
    * @param Module_Meta $meta
    * @return string
    */
   public static function display_general_errors($product, $meta){

      $data = [
         'product_id' => $product->get_id(),
      ];
      ?>
      <div data-<?php echo PREFIX;?>-error-tab="<?php echo $product->get_id();?>" data-<?php echo PREFIX;?>-error-args='<?php echo json_encode($data);?>'></div>
      <?php
   }



   /**
    * Displayes the account errors.
    *
    * @param \WC_Product $product
    * @param Module_Meta $meta
    * @param string $account_id
    * @return string
    */
   public static function display_account_errors($product, $meta, $account_id){

      $data = [
         'product_id' => $product->get_id(),
         'account_id' => $account_id,
      ];
      ?>
      <div data-<?php echo PREFIX;?>-error-tab="<?php echo $product->get_id() . '_' . $account_id;?>" data-<?php echo PREFIX;?>-error-args='<?php echo json_encode($data);?>'></div>
      <?php
   }

}