<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

?>
<div class="<?php echo PREFIX;?>-style">
   <div class="mb-10 alertbox <?php echo $box_class;?>">

      <h3 class="mb-0"><?php echo Module_Core::config('service.name');?></h3>

      <?php echo $output;?>

      <?php if( ! empty($button_label) ):
         $data = json_encode([
            'popup_title' => sprintf(__('Order #%s', 'integration-marktplaats-for-woocommerce'), $number),
            'order_id' => $order->get_id(),
         ]);
         ?>
         <p>
            <button type="button" class="thickbox button button-hero <?php echo $button_class;?>" data-<?php echo PREFIX;?>-open-popup-order='<?php echo $data;?>'><?php echo $button_label;?></button>
         </p>
      <?php endif;?>

   </div>
</div>