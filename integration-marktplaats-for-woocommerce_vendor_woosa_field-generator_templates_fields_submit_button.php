<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<tr valign="top" class="<?php echo $is_disabled;?>" <?php $instance->render_visibility($field);?>>
   <td colspan="2" class="forminp forminp-<?php echo $instance->get_field_type($field['type']); ?>">
      <div>
         <button type="button" class="button button-primary"
            data-submit-button="<?php echo $instance->get_field_id($field['id']);?>"
            <?php echo $instance->get_field_custom_attributes($field['custom_attributes']); ?>
         ><?php _e('Save changes', 'integration-marktplaats-for-woocommerce');?></button>
      </div>
   </td>
</tr>