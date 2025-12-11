<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<tr valign="top" class="<?php echo $is_disabled;?>" <?php $instance->render_visibility($field);?>>
   <th scope="row" class="titledesc">
      <label for="<?php echo $instance->get_field_id($field['id']); ?>"><?php echo esc_html( $field['title'] ); ?> <?php echo $instance->get_field_tooltip($field['desc_tip']);?></label>
   </th>
   <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
      <div id="<?php echo $instance->get_field_id($field['id']);?>_preview">
         <?php if ( $field['value'] && preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $field['value']) ) : ?>
            <img src="<?php echo esc_url($field['value']); ?>" style="max-width:100px; height:auto;" />
         <?php endif; ?>
      </div>
      <input
         type="text"
         name="<?php echo $instance->get_field_name( $field['id'] ); ?>"
         id="<?php echo $instance->get_field_id($field['id']); ?>"
         value="<?php echo esc_attr( $field['value'] ); ?>"
      />
      <input data-<?php echo PREFIX;?>-media-file-selector='<?php echo $instance->get_field_id($field['id']);?>' type="button" class="button" value="<?php _e('Select file');?>" />
      <?php echo $instance->get_field_description($field); ?>
   </td>
</tr>