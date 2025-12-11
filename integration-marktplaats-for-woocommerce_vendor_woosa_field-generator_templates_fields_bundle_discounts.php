<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


$custom_attributes = $instance->get_field_custom_attributes($field['custom_attributes']);
$enabled           = Util::array($field)->get('value/enabled');
$rules             = Util::array($field)->get('value/rules');
$max_rules         = Util::array($field)->get('max_rules', 2);
$display_rules     = 'yes' === $enabled ? 'display:block;' : 'display:none;';
?>

<tr valign="top" class="<?php echo esc_attr( $is_disabled );?>" <?php $instance->render_visibility($field);?>>
   <td colspan="2" class="pl-0 forminp forminp-toggle">
      <fieldset>
         <div class="toggle-wrap">
            <?php if(strpos($custom_attributes, 'disabled') === false):?>
               <input type="hidden" name="<?php echo esc_attr( $instance->get_field_name($field['id']) ); ?>[enabled]" value="no">
            <?php else:?>
               <input type="hidden"
                  name="<?php echo esc_attr( $instance->get_field_name($field['id']) ); ?>[enabled]"
                  value="<?php echo esc_attr( $enabled );?>">
            <?php endif;?>
            <input
               name="<?php echo esc_attr( $instance->get_field_name($field['id']) ); ?>[enabled]"
               id="<?php echo esc_attr( $instance->get_field_id($field['id']) ); ?>"
               type="checkbox"
               class="checkbox <?php echo esc_attr( isset( $field['class'] ) ? $field['class'] : '' ); ?>"
               value="yes"
               <?php echo $instance->get_field_custom_attributes($field['custom_attributes']); ?>
               <?php checked( $enabled, 'yes' ); ?>
               data-<?php echo PREFIX;?>-has-extra-field="<?php echo esc_attr( $instance->get_field_id($field['id']) );?>"
            />

            <label class="switch" for="<?php echo esc_attr( $instance->get_field_id( $field['id'] ) ); ?>">
               <span class="slider"></span>
            </label>
         </div>
         <div class="toggle-info">
            <label for="<?php echo esc_attr( $instance->get_field_id( $field['id'] ) ); ?>">
               <div class="titledesc"><?php echo esc_html( $field['title'] ); ?></div>
            </label>
            <?php echo $instance->get_field_description($field);?>
         </div>
         <div class="pt-10 pl-35" data-<?php echo PREFIX;?>-extra-field-<?php echo esc_attr( $instance->get_field_id($field['id']) );?>="yes" style="<?php echo esc_attr( $display_rules );?>">
            <table class="widefat fixed striped">
               <thead>
                  <tr>
                     <td></td>
                     <td><?php _e('Quantity', 'integration-marktplaats-for-woocommerce');?></td>
                     <td><?php _e('Price discount (%)', 'integration-marktplaats-for-woocommerce');?></td>
                  </tr>
               </thead>
               <tbody>
                  <?php for($rule_index = 0; $rule_index <= $max_rules; $rule_index++):?>
                     <tr>
                        <td><?php printf(__('Bundle #%s', 'integration-marktplaats-for-woocommerce'), $rule_index+1);?></td>
                        <td>
                           <input type="number" min="1" name="<?php echo $instance->get_field_name($field['id']);?>[rules][<?php echo $rule_index;?>][quantity]" value="<?php echo Util::array($rules)->get($rule_index . '/quantity');?>" <?php echo $instance->get_field_custom_attributes($field['custom_attributes']); ?>>
                        </td>
                        <td>
                           <input type="number" min="1" name="<?php echo $instance->get_field_name($field['id']);?>[rules][<?php echo $rule_index;?>][discount]');?>" value="<?php echo Util::array($rules)->get($rule_index . '/discount');?>" <?php echo $instance->get_field_custom_attributes($field['custom_attributes']); ?>>
                        </td>
                     </tr>
                  <?php endfor;?>
               </tbody>
               <tfoot>
                  <tr>
                     <td></td>
                     <td><?php _e('Quantity', 'integration-marktplaats-for-woocommerce');?></td>
                     <td><?php _e('Price discount (%)', 'integration-marktplaats-for-woocommerce');?></td>
                  </tr>
               </tfoot>
            </table>
         </div>
      </fieldset>
   </td>
</tr>
