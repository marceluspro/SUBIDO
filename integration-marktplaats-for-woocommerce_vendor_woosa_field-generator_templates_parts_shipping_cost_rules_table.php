<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<div data-<?php echo PREFIX;?>-rule-list>
   <table class="widefat fixed striped mb-10">
      <thead>
         <tr>
            <td style="width: 20px;">#</td>
            <td><?php printf(__('Weight From (%s)', 'integration-marktplaats-for-woocommerce'), get_option('woocommerce_weight_unit'));?></td>
            <td><?php printf(__('Weight To (%s)', 'integration-marktplaats-for-woocommerce'), get_option('woocommerce_weight_unit'));?></td>
            <td><?php printf(__('Cost (%s)', 'integration-marktplaats-for-woocommerce'), get_woocommerce_currency_symbol());?></td>
            <td style="width: 25px;"></td>
         </tr>
      </thead>
      <tbody data-<?php echo PREFIX;?>-shipping-rules>
         <?php if(empty($rules)):?>
            <tr>
               <td colspan="5"><?php _e('No rules available.', 'integration-marktplaats-for-woocommerce');?></td>
            </tr>
         <?php else:?>
            <?php foreach($rules as $index => $rule):?>
               <tr data-<?php echo PREFIX;?>-shipping-rule>
                  <td style="width: 15px;"><?php echo $index+1;?></td>
                  <td>
                     <input type="number" min="0.1" step=".01" name="<?php echo $field_name;?>[rules][<?php echo $index;?>][min_weight]" value="<?php echo Util::array($rules)->get($index . '/min_weight');?>" <?php echo $custom_attributes; ?>>
                  </td>
                  <td>
                     <input type="number" min="0.1" step=".01" name="<?php echo $field_name;?>[rules][<?php echo $index;?>][max_weight]" value="<?php echo Util::array($rules)->get($index . '/max_weight');?>" <?php echo $custom_attributes; ?>>
                  </td>
                  <td>
                     <input type="number" min="0.1" step=".01" name="<?php echo $field_name;?>[rules][<?php echo $index;?>][cost]');?>" value="<?php echo Util::array($rules)->get($index . '/cost');?>" <?php echo $custom_attributes; ?>>
                  </td>
                  <td><span class="dashicons dashicons-trash delete-action-icon" data-<?php echo PREFIX;?>-remove-shipping-rule></span></td>
               </tr>
            <?php endforeach;?>
         <?php endif;?>
      </tbody>
      <tfoot>
         <tr>
            <td style="width: 20px;">#</td>
            <td><?php printf(__('Weight From (%s)', 'integration-marktplaats-for-woocommerce'), get_option('woocommerce_weight_unit'));?></td>
            <td><?php printf(__('Weight To (%s)', 'integration-marktplaats-for-woocommerce'), get_option('woocommerce_weight_unit'));?></td>
            <td><?php printf(__('Cost (%s)', 'integration-marktplaats-for-woocommerce'), get_woocommerce_currency_symbol());?></td>
            <td style="width: 25px;"></td>
         </tr>
      </tfoot>
   </table>
   <div>
      <?php $rule_template = '<tr data-' . PREFIX . '-shipping-rule>
         <td>__number__</td>
         <td>
            <input type="number" min="0.1" step=".01" '. $custom_attributes .' name="' . $field_name . '[rules][__index__][min_weight]">
         </td>
         <td>
            <input type="number" min="0.1" step=".01" '. $custom_attributes .' name="' . $field_name . '[rules][__index__][max_weight]">
         </td>
         <td>
            <input type="number" min="0.1" step=".01" '. $custom_attributes .' name="' . $field_name . '[rules][__index__][cost]">
         </td>
         <td><span class="dashicons dashicons-trash delete-action-icon" data-' . PREFIX . '-remove-shipping-rule></span></td>
      </tr>';
      ?>
      <?php
         $rule_data = wp_json_encode([
            'rule_template' => $rule_template,
         ]);
      ?>
      <button type="button" class="button button-small" data-<?php echo PREFIX;?>-add-shipping-rule='<?php echo $rule_data;?>'><?php _e('Add rule', 'integration-marktplaats-for-woocommerce');?></button>
   </div>
</div>