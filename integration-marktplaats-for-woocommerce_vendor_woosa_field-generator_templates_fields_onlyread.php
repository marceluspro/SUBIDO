<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<tr valign="top" class="<?php echo esc_attr( $is_disabled );?>" <?php $instance->render_visibility($field);?>>
   <th>
      <label for="<?php echo $instance->get_field_id($field['id']); ?>"><?php echo esc_html( $field['title'] ); ?> <?php echo $instance->get_field_tooltip($field['desc_tip']);?></label>
   </th>
   <td class="forminp">
      <span>
         <?php if(empty($field['value'])){
            echo '<em>'.__('No value available', 'integration-marktplaats-for-woocommerce').'</em>';
         }else{
            echo $field['value'];
         }
         ?>
      </span>
   </td>
</tr>