<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>

<?php do_action(PREFIX . '\module\product_data_tab\panel\top', $product, $meta); ?>

<div class="nav-tab-wrapper <?php echo PREFIX;?>-tab-wrapper" data-<?php echo PREFIX;?>-tablist>
   <div class="nav-tab nav-tab-active"><?php _e('General', 'integration-marktplaats-for-woocommerce');?></div>
   <?php
      do_action(PREFIX . '\module\product_data_tab\panel\tab_nav', $product, $meta);
   ?>
</div>

<div class="<?php echo PREFIX;?>-panel-wrapper" data-<?php echo PREFIX;?>-tabpanel>

   <?php
   echo Util::get_template('general-tab.php', [
      'product' => $product,
      'meta' => $meta,
   ], dirname(dirname(dirname(dirname(__FILE__)))), untrailingslashit(basename(dirname(dirname(dirname(__FILE__))))) . '/templates/simple/tabs');

   do_action(PREFIX . '\module\product_data_tab\panel\tab_content', $product, $meta);
   ?>

</div>

<?php do_action(PREFIX . '\module\product_data_tab\panel\bottom', $product, $meta); ?>