<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


$order_status = $order->get_meta(PREFIX . '_order_status');
$account_id   = $order->get_meta(PREFIX . '_account_id');
?>

<div class="<?php echo PREFIX;?>-style" data-<?php echo PREFIX;?>-popup="module_order_details">

   <?php if(empty($order->get_items())):?>

      <p><?php _e('No results found.', 'integration-marktplaats-for-woocommerce');?></p>

   <?php else:?>

      <h3><?php _e('Order items', 'integration-marktplaats-for-woocommerce');?></h3>

      <table class="widefat fixed striped">
         <thead>
            <tr>
               <td class="check-column"><input type="checkbox"></td>
               <th style="width: 30%;"><?php _e('Name', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Shipping status', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Fulfill by', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Shipping carrier', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Tracking code', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Cancellation Reason', 'integration-marktplaats-for-woocommerce');?></th>
               <th style="width: 60px;"><?php _e('Cost', 'integration-marktplaats-for-woocommerce');?></th>
               <th style="width: 60px;"><?php _e('Qty', 'integration-marktplaats-for-woocommerce');?></th>
               <th style="width: 60px;"><?php _e('Total', 'integration-marktplaats-for-woocommerce');?></th>
            </tr>
         </thead>
         <tbody>

            <?php
            $available = [];
            foreach($order->get_items() as $item):

               $item_status           = $item->get_meta(PREFIX . '_order_line_status');
               $item_id               = $item->get_meta(PREFIX . '_order_line_id');
               $fulfilment_method     = $item->get_meta(PREFIX . '_fulfilment_method');
               $render_item_status    = apply_filters(PREFIX . '\module\order_details\render_order_item_status', $item_status, $item);
               $render_item_fulfil_by = apply_filters(PREFIX . '\module\order_details\render_item_fulfil_by', Module_Order_Details::is_fulfiled_by_marketplace($fulfilment_method) ? 'Kaufland' : 'Retailer', $item);

               if(
                  in_array($item_status, ['', 'open', 'need_to_be_sent', 'error']) &&
                  ! Module_Order_Details::is_fulfiled_by_marketplace($fulfilment_method) &&
                  'processed' !== $order_status
               ){
                  $available[] = $item_id;
               }
               ?>
               <tr>
                  <td class="check-column pl-10">
                     <?php if( in_array($item_id, $available) ):?>
                        <input type="hidden" name="items[<?php echo $item->get_id();?>][selected]" value="no" />
                        <input type="hidden" name="items[<?php echo $item->get_id();?>][wc_item_id]" value="<?php echo $item->get_id();?>" />
                        <input type="hidden" name="items[<?php echo $item->get_id();?>][wc_item_name]" value="<?php echo $item->get_name();?>" />
                        <input type="hidden" name="items[<?php echo $item->get_id();?>][wc_item_total]" value="<?php echo $order->get_item_total($item);?>" />
                        <input type="hidden" name="items[<?php echo $item->get_id();?>][wc_item_quantity]" value="<?php echo $item->get_quantity();?>" />
                        <input type="hidden" name="items[<?php echo $item->get_id();?>][order_line_id]" value="<?php echo $item_id;?>" />
                        <input type="checkbox" name="items[<?php echo $item->get_id();?>][selected]" value="yes" />
                     <?php endif;?>
                  </td>
                  <td><?php echo $item->get_name();?></td>
                  <td><?php echo $render_item_status;?></td>
                  <td><?php echo $render_item_fulfil_by;?></td>

                  <?php if( in_array($item_id, $available) ): ?>
                     <td>
                        <select name="items[<?php echo $item->get_id();?>][carrier_code]">
                           <option value=""><?php _e('Please select', 'integration-marktplaats-for-woocommerce');?></option>
                           <?php foreach(Module_Order_Details::get_dropdown_options('ship', $order) as $otp_key => $otp_val):?>
                              <option value="<?php echo $otp_key;?>" <?php selected(Module_Order_Details::get_order_item_shipping_carrier($item, $account_id), $otp_key);?>><?php echo $otp_val;?></option>
                           <?php endforeach;?>
                        </select>
                     </td>
                     <td>
                        <input type="text" name="items[<?php echo $item->get_id();?>][tracking_numbers]" value="<?php echo Module_Order_Details::get_order_item_tracking_number($item);?>" />
                     </td>
                  <?php else:?>
                     <td><?php echo empty($item->get_meta(Util::prefix('carrier_code'))) ? '-' : $item->get_meta(Util::prefix('carrier_code'));?></td>
                     <td><?php echo empty($item->get_meta(Util::prefix('tracking_numbers'))) ? '-' : $item->get_meta(Util::prefix('tracking_numbers'));?></td>
                  <?php endif;?>

                  <?php if( in_array($item_id, $available) ):?>
                     <td>
                        <select name="items[<?php echo $item->get_id();?>][reason]">
                           <option value=""><?php _e('Please select', 'integration-marktplaats-for-woocommerce');?></option>
                           <?php foreach(Module_Order_Details::get_dropdown_options('cancel', $order) as $otp_key => $otp_val):?>
                              <option value="<?php echo $otp_key;?>" <?php selected($item->get_meta(Util::prefix('cancel_reason')), $otp_key);?>><?php echo $otp_val;?></option>
                           <?php endforeach;?>
                        </select>
                     </td>
                  <?php else:?>
                     <td><?php echo empty($item->get_meta(Util::prefix('cancel_reason'))) ? '-' : $item->get_meta(Util::prefix('cancel_reason'));?></td>
                  <?php endif;?>

                  <td><?php echo wc_price( $order->get_item_total($item), ['currency' => $order->get_currency()] );?></td>
                  <td><?php echo $item->get_quantity();?></td>
                  <td><?php echo wc_price( $item->get_total(), ['currency' => $order->get_currency()] );?></td>

               </tr>
            <?php endforeach;?>

         </tbody>

         <tfoot>
            <tr>
               <td class="check-column"><input type="checkbox"></td>
               <th style="width: 30%;"><?php _e('Name', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Shipping status', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Fulfill by', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Shipping carrier', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Tracking code', 'integration-marktplaats-for-woocommerce');?></th>
               <th><?php _e('Cancellation Reason', 'integration-marktplaats-for-woocommerce');?></th>
               <th style="width: 60px;"><?php _e('Cost', 'integration-marktplaats-for-woocommerce');?></th>
               <th style="width: 60px;"><?php _e('Qty', 'integration-marktplaats-for-woocommerce');?></th>
               <th style="width: 60px;"><?php _e('Total', 'integration-marktplaats-for-woocommerce');?></th>
            </tr>
         </tfoot>
      </table>

      <?php if( ! empty($available) ):?>
         <p>
            <input type="hidden" name="order_id" value="<?php echo $order->get_id();?>">
            <button type="button" class="button button-primary" data-<?php echo PREFIX;?>-submit-popup="ship_items"><?php _e('Ship selected items', 'integration-marktplaats-for-woocommerce');?></button>
            <button type="button" class="button" data-<?php echo PREFIX;?>-submit-popup="cancel_items"><?php _e('Cancel selected items', 'integration-marktplaats-for-woocommerce');?></button>
         </p>
      <?php endif;?>

   <?php endif;?>

</div>