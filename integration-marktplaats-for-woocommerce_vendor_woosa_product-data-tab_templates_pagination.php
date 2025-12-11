<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>

<div class="<?php echo PREFIX;?>-toolbar">
   <button type="button" class="button button-secondary button-small" id="<?php echo PREFIX;?>-refresh-action"><?php _e('Refresh', 'integration-marktplaats-for-woocommerce');?></button>

   <div class="<?php echo PREFIX;?>-variations-nav">
      <?php if($pages > 1):?>
         <span><?php printf(__('Pagination: %s'), $page.'/'.$pages);?></span>
         <?php if($page > 1):?>
            <button type="button" class="button button-secondary button-small <?php echo PREFIX;?>-load-variations-page" data-load-page="prev"><?php _e('Prev page', 'integration-marktplaats-for-woocommerce');?></button>
         <?php else:?>
            <button type="button" class="button button-secondary button-small" disabled="disabled"><?php _e('Prev page', 'integration-marktplaats-for-woocommerce');?></button>
         <?php endif;?>

         <?php if($page < $pages):?>
            <button type="button" class="button button-secondary button-small <?php echo PREFIX;?>-load-variations-page" data-load-page="next"><?php _e('Next page', 'integration-marktplaats-for-woocommerce');?></button>
         <?php else:?>
            <button type="button" class="button button-secondary button-small" disabled="disabled"><?php _e('Next page', 'integration-marktplaats-for-woocommerce');?></button>
         <?php endif;?>
      <?php endif; ?>
      <input type="hidden" data-<?php echo PREFIX;?>-current-page value="<?php echo $page;?>" />
      <div class="clear"></div>
   </div>
</div>