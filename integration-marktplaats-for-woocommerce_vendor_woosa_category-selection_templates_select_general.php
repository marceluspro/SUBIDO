<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>

<div class="<?php echo PREFIX;?>-cs-box" data-<?php echo PREFIX;?>-cs-box="<?php echo $mcs->source;?>" data-<?php echo PREFIX;?>-cs-level="<?php echo $mcs->level;?>">
   <button type="button" class="button" data-<?php echo PREFIX;?>-cs-load-items="0"><?php _e('Please select', 'integration-marktplaats-for-woocommerce');?></button>
   <div class="cs-trail" data-<?php echo PREFIX;?>-cs-trail></div>
   <?php if('service' === $mcs->source):?>
      <div class="cs-search" data-<?php echo PREFIX;?>-cs-search>
         <input type="text" id="<?php echo PREFIX;?>-search-service" placeholder="Type to search..."/>
      </div>
   <?php endif;?>
   <?php if('shop' === $mcs->source):?>
      <div class="cs-search" data-<?php echo PREFIX;?>-cs-search>
         <input type="text" id="<?php echo PREFIX;?>-search-shop" placeholder="Type to search..."/>
      </div>
   <?php endif;?>
   <div class="cs-list" data-<?php echo PREFIX;?>-cs-list></div>
   <input type="hidden" data-<?php echo PREFIX;?>-cs-input value=""/>
</div>