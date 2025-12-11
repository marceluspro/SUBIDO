<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


$instance_changed = Option::get('midlayer:shop_instance_changed');
?>

<tr class="<?php echo PREFIX;?>-style">
   <td class="p-0">
      <div class="mb-10">
         <?php if('yes' === $instance_changed):?>
            <p><?php _e('We detected some changes in the shop which requires a new registration.', 'integration-marktplaats-for-woocommerce');?></p>
         <?php else:?>
            <p><?php _e('Register your shop in our system is required to be able to communicate with it.', 'integration-marktplaats-for-woocommerce');?></p>
         <?php endif;?>
      </div>

      <p><button type="button" class="button-primary" data-<?php echo PREFIX;?>-registration><?php _e( 'Click to register', 'integration-marktplaats-for-woocommerce' );?></button></p>
   </td>
</tr>