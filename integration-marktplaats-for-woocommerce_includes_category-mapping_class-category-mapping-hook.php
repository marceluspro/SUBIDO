<?php
/**
 * Category Mapping Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Category_Mapping_Hook implements Interface_Hook {


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\category_selection\service_items', [__CLASS__, 'define_service_categories']);
      add_filter(PREFIX . '\module\category_seletion\level', [__CLASS__, 'define_category_selection_level'], 10, 2);

      add_filter(PREFIX . '\category-mapping\category-config\display-configure-button', [__CLASS__, 'display_category_config_button'], 10, 2);
      add_filter(PREFIX . '\category-mapping\category-config\get-category', [__CLASS__, 'define_category_fields'], 10, 2);

      add_action(PREFIX . '\field_generator\render\cpc_slider', [__CLASS__, 'render_cpc_slider_field']);
   }



   /**
    * Defines the list of service categories.
    *
    * @return array
    */
   public static function define_service_categories(){

      $service_category = new Service_API_Category;

      return $service_category->get_list();
   }



   /**
    * Defines the level of the category selection.
    * We need `leaf` here to avoid the selection of parent categories.
    *
    * @param string $level
    * @param Module_Category_Selection $class
    * @return string
    */
   public static function define_category_selection_level($level, $class){

      if('service' === $class->source){
         return 'leaf';
      }

      return $level;
   }



   /**
    * Display the config category fields button.
    *
    * @param mixed $has_category_config
    * @param mixed $category_id
    * @return bool
    */
   public static function display_category_config_button($has_category_config, $category_id){
      return true;
   }



   /**
    * Define the category with its fields for mapping their source values.
    *
    * @param mixed $category
    * @param mixed $category_id
    * @return array
    */
   public static function define_category_fields($category, $category_id){

      $api_category = new Service_API_Category;
      $category = $api_category->get($category_id);

      if($category){

         $fields[] = [
            'id' => Util::prefix('cpc'),
            'type' => 'cpc_slider',
            'name' => __('Cost per click', 'integration-marktplaats-for-woocommerce'),
            'required' => true,
            'config' => [
               'cpc' => $api_category->format_cpc(Util::array($category)->get('config/bidMicros')),
               'cpc_total_budget' => $api_category->format_cpc(Util::array($category)->get('config/totalBudgetMicros')),
            ],
         ];

         $category = [
            'name' => Util::array($category)->get('label/nl_NL'),
            'fields' => $fields
         ];
      }

      return $category;
   }



   /**
    * Displayes the CPC field.
    *
    * @param array $field
    * @return void
    */
   public static function render_cpc_slider_field($field){

      $total_budget_enabled = 'on' === Util::array($field)->get('config_fields/' . Util::prefix($field['id'] . '_automatic'));
      ?>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="<?php echo $field['id']; ?>"><?php echo esc_html( $field['title'] ); ?></label>
         </th>
         <td data-<?php echo PREFIX;?>-cpc-field class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
            <div class="p-15 bt-1 br-1 bb-1 bl-1 mb-20" style="max-width:400px;">
               <div class="pb-10"><?php _e('Cost:', 'integration-marktplaats-for-woocommerce');?></div>
               <input type="hidden" name="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo $field['value'];?>">
               <input
                  type="text"
                  id="<?php echo Util::prefix('category_cpc');?>"
                  class="<?php echo Util::prefix('cpc_field');?>"
                  name="<?php echo esc_attr( $field['id'] ); ?>"
                  data-type="single"
                  data-min="<?php echo $field['config']['cpc']['min'];?>"
                  data-max="<?php echo $field['config']['cpc']['max'];?>"
                  data-from="<?php echo $field['value'];?>"
                  data-grid="true"
                  data-step="0.01"
                  data-prefix="<?php echo get_woocommerce_currency_symbol('EUR');?>"
                  data-skin="round"
                  data-disable="<?php echo $total_budget_enabled ? 1 : 0;?>"
                  data-<?php echo PREFIX;?>-cpc
               />

               <div class="pt-20">
                  <label>
                     <input
                        type="checkbox"
                        name="<?php echo esc_attr( $field['id'] . '_automatic' ); ?>"
                        <?php checked($total_budget_enabled);?>
                        data-<?php echo PREFIX;?>-cpc-automatic
                     />
                     <?php _e('Set as automatic', 'integration-marktplaats-for-woocommerce');?>
                  </label>
               </div>
            </div>
            <div class="p-15 bt-1 br-1 bb-1 bl-1" data-<?php echo PREFIX;?>-cpc-total-budget-wrapper style="max-width:400px;">
               <div class="pb-10"><?php _e('Total budget:', 'integration-marktplaats-for-woocommerce');?></div>
               <input
                  type="text"
                  class="<?php echo Util::prefix('cpc_field');?>"
                  name="<?php echo esc_attr( $field['id'] . '_total_budget' ); ?>"
                  data-type="single"
                  data-min="<?php echo $field['config']['cpc_total_budget']['min'];?>"
                  data-max="<?php echo '500';//$field['config']['cpc_total_budget']['max'];?>"
                  data-from="<?php echo Util::array($field)->get('config_fields/' . $field['id'] . '_total_budget', $field['config']['cpc_total_budget']['min']);?>"
                  data-grid="true"
                  data-step="1"
                  data-prefix="<?php echo get_woocommerce_currency_symbol('EUR');?>"
                  data-skin="round"
                  data-disable="0"
               />
            </div>
         </td>
      </tr>
      <?php
   }

}