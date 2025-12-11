<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


$field_id          = $field['id'];
$field_rules_id    = $field['id'] . '_rules';
$custom_attributes = $instance->get_field_custom_attributes($field['custom_attributes']);
$default_cost      = Util::array($field)->get('value/default_cost', '0');
$enabled           = Util::array($field)->get('value/rules/enabled');
$rules             = Util::array($field)->get('value/rules/rules', []);
$display_rules     = 'yes' === $enabled ? 'display:block;' : 'display:none;';
?>
<tr valign="top" class="<?php echo esc_attr( $is_disabled );?>" <?php $instance->render_visibility($field);?>>
   <th scope="row" class="titledesc">
      <label for="<?php echo $instance->get_field_id($field_id); ?>"><?php echo esc_html( $field['title'] ); ?> <?php echo $instance->get_field_tooltip($field['desc_tip']);?></label>
   </th>
   <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
      <input
         name="<?php echo $instance->get_field_name($field_id); ?>"
         id="<?php echo $instance->get_field_id($field_id); ?>"
         type="number"

         <?php if ( $field['css'] ) : ?>
            style="<?php echo esc_attr( $field['css'] ); ?>"
         <?php endif; ?>

         value="<?php echo esc_attr( $default_cost  ); ?>"

         <?php if ( $field['class'] ) : ?>
            class="<?php echo esc_attr( $field['class'] ); ?>"
         <?php endif; ?>

         <?php if ( $field['placeholder'] ) : ?>
            placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
         <?php endif; ?>

         <?php if ( $field['required'] ) : ?>
            data-required="<?php echo esc_attr( $field['required'] ); ?>"
         <?php endif; ?>

         <?php echo $instance->get_field_custom_attributes($field['custom_attributes']); ?>
      />

      <?php echo $instance->get_field_description($field); ?>

      <div class="mt-10">
         <fieldset>
            <div class="toggle-wrap">
               <?php if(strpos($custom_attributes, 'disabled') === false):?>
                  <input type="hidden" name="<?php echo esc_attr( $instance->get_field_name($field_rules_id) ); ?>[enabled]" value="no">
               <?php else:?>
                  <input type="hidden"
                     name="<?php echo esc_attr( $instance->get_field_name($field_rules_id) ); ?>[enabled]"
                     value="<?php echo esc_attr( $enabled );?>">
               <?php endif;?>
               <input
                  name="<?php echo esc_attr( $instance->get_field_name($field_rules_id) ); ?>[enabled]"
                  id="<?php echo esc_attr( $instance->get_field_id($field_rules_id .'_enabled') ); ?>"
                  type="checkbox"
                  class="checkbox <?php echo esc_attr( isset( $field['class'] ) ? $field['class'] : '' ); ?>"
                  value="yes"
                  <?php echo $instance->get_field_custom_attributes($field['custom_attributes']); ?>
                  <?php checked( $enabled, 'yes' ); ?>
                  data-<?php echo PREFIX;?>-has-extra-field="<?php echo esc_attr( $instance->get_field_id($field_rules_id .'_enabled') );?>"
               />

               <label class="switch" for="<?php echo esc_attr( $instance->get_field_id( $field_rules_id .'_enabled' ) ); ?>">
                  <span class="slider"></span>
               </label>
            </div>
            <div class="toggle-info">
               <label for="<?php echo esc_attr( $instance->get_field_id( $field_rules_id .'_enabled' ) ); ?>">
                  <div class="titledesc"><?php echo __('Enable shipping costs based on weight', 'integration-marktplaats-for-woocommerce'); ?></div>
               </label>
            </div>
         </fieldset>
         <div class="pt-10" data-<?php echo PREFIX;?>-extra-field-<?php echo esc_attr( $instance->get_field_id($field_rules_id .'_enabled') );?>="yes" style="<?php echo esc_attr( $display_rules );?>">
            <?php
            echo Util::get_template('shipping_cost_rules_table.php', [
               'rules' => $rules,
               'field_name' => $instance->get_field_name($field_rules_id),
               'custom_attributes' => $instance->get_field_custom_attributes($field['custom_attributes']),
            ], dirname(dirname(__FILE__)) . '/parts');?>
         </div>
      </div>
   </td>
</tr>