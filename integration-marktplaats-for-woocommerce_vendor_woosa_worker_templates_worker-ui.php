<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>

<table class="form-table striped">
   <thead>
      <tr>
         <td style="width: 20px;"><b>#</b></td>
         <td><b><?php _e('Status', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td style="width: 250px;"><b><?php _e('Action', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td><b><?php _e('Tasks', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td style="width: 150px;"><b><?php _e('Recurrence', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td style="width: 150px;"><b><?php _e('Next Run', 'integration-marktplaats-for-woocommerce');?></b></td>
      </tr>
   </thead>
   <tbody>
      <?php
      if(empty($actions)):?>

         <tr>
            <td colspan="7"><?php _e('There are no actions yet.', 'integration-marktplaats-for-woocommerce');?></td>
         </tr>

      <?php else:

         $number = 1;
         foreach($actions as $item):
            $action     = Module_Worker::action($item);
            $is_current = $action->is_active() && Module_Worker::get_current_action() === $action->get_id();
            $run_style  = $is_current ? 'background: #d0ffd0;font-style: italic;' : '';
            $opacity    = $action->is_inactive() ? 'opacity: .5;' : '';
            ?>
            <tr style="<?php echo $run_style.$opacity;?>">
               <td><?php echo $number;?></td>
               <td><?php echo $is_current ? 'running' : $action->get_status();?></td>
               <td><?php echo $action->get_id();?></td>
               <td>
                  <?php if(empty($action->get_callback())):
                     ?>

                     <table>
                        <thead>
                           <tr>
                              <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><b>Total</b></td>
                              <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><b>Active</b></td>
                              <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><b>Rescheduled</b></td>
                           </tr>
                        </thead>
                        <tbody>
                           <tr data-<?php echo PREFIX;?>-worker-action='<?php echo json_encode($item);?>'>
                              <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><div><img src="<?php echo untrailingslashit(plugin_dir_url(dirname(__FILE__))) . '/assets/images/icons/loading.svg';?>"></div></td>
                              <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><div><img src="<?php echo untrailingslashit(plugin_dir_url(dirname(__FILE__))) . '/assets/images/icons/loading.svg';?>"></div></td>
                              <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><div><img src="<?php echo untrailingslashit(plugin_dir_url(dirname(__FILE__))) . '/assets/images/icons/loading.svg';?>"></div></td>
                           </tr>
                        </tbody>
                     </table>

                  <?php else:?>
                     -
                  <?php endif;?>
               </td>
               <td><?php $action->render_recurrence_time();?></td>
               <td><?php $action->render_next_run_time();?></td>
            </tr>
         <?php $number++;
         endforeach;?>

      <?php endif;?>

   </tbody>
   <tfoot>
      <tr>
         <td style="width: 20px;"><b>#</b></td>
         <td><b><?php _e('Status', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td style="width: 250px;"><b><?php _e('Action', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td><b><?php _e('Tasks', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td style="width: 150px;"><b><?php _e('Recurrence', 'integration-marktplaats-for-woocommerce');?></b></td>
         <td style="width: 150px;"><b><?php _e('Next Run', 'integration-marktplaats-for-woocommerce');?></b></td>
      </tr>
   </tfoot>
</table>