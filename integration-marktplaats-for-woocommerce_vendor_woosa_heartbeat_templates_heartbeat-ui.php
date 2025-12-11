<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>

<tr class="<?php echo PREFIX;?>-style">
   <td class="p-0">

      <h2><?php _e('Scheduled action', 'integration-marktplaats-for-woocommerce');?></h2>

      <div class="pb-15">
         <span class="tb"><?php _e('Status ', 'integration-marktplaats-for-woocommerce');?></span>
         <?php echo $sa_status; ?>
      </div>

      <div class="pb-20">
         <p><?php
            printf(
               __('By default, the plugin uses a scheduled action called %s, which runs every minute to trigger the processing of plugin tasks. This action relies on WordPress\'s default cron system, meaning it only runs when someone visits your website. If your website experiences a period of inactivity, the scheduled action won\'t run until the next visit. As a result, the plugin may not execute tasks at the intended intervals, potentially causing delays.', 'integration-marktplaats-for-woocommerce'),
               '<b>' . Util::prefix('perform') . '</b>'
            );
         ?></p>
      </div>

      <h2><?php _e('External cron job', 'integration-marktplaats-for-woocommerce');?></h2>

      <div class="pb-15">
         <span class="tb"><?php _e('Status ', 'integration-marktplaats-for-woocommerce');?></span>
         <?php echo $status; ?>
      </div>

      <div class="pb-20">
         <p><?php
         printf(__(
            'By enabling this, our cron job system will ping your website every minute (%sat this URL%s) to trigger the processing of plugin tasks. Unlike the scheduled action, this will reliably run every minute, regardless of website traffic.', 'integration-marktplaats-for-woocommerce'),
            '<a href="'.rest_url('woosa-heartbeat/perform').'" target="_blank">',
            '</a>'
         );?></p>
      </div>

      <div>
         <button type="button" class="button button-primary" <?php echo $button['data-attr'];?>><?php echo $button['label'];?></button>
      </div>
   </td>
</tr>