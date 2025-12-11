<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>

<div class="<?php echo PREFIX;?>-panel <?php echo PREFIX;?>-panel--active">

   <?php do_action(PREFIX . '\module\product_data_tab\panel\general_tab\top', $product, $meta); ?>

   <div class="options_group">

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_ean_' . $product->get_id());?>"><?php _e('Manage EAN?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[variable][variations]['.$product->get_id().'][use_local_ean]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_ean_' . $product->get_id());?>"
            name="<?php echo Util::prefix('fields[variable][variations]['.$product->get_id().'][use_local_ean]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('ean_' . $product->get_id());?>"
            <?php checked($meta->get('use_local_ean'), 'yes');?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different EAN code than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('ean_' . $product->get_id());?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_ean') );?>">
         <label for="<?php echo Util::prefix('ean_' . $product->get_id());?>">
            <?php _e('EAN code', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('ean_' . $product->get_id());?>"
            name="<?php echo Util::prefix('fields[variable][variations]['.$product->get_id().'][ean]');?>"
            value="<?php echo $meta->get('ean');?>"
         />
         <?php echo wc_help_tip(__('The EAN code associated with this product.', 'integration-marktplaats-for-woocommerce'));?>
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_price_' . $product->get_id());?>"><?php _e('Manage price?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[variable][variations]['.$product->get_id().'][use_local_price]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_price_' . $product->get_id());?>"
            name="<?php echo Util::prefix('fields[variable][variations]['.$product->get_id().'][use_local_price]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('price_' . $product->get_id());?>"
            <?php checked( 'yes', $meta->get('use_local_price') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different price than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('price_' . $product->get_id());?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_price') );?>">
         <label for="<?php echo Util::prefix('price_' . $product->get_id());?>">
            <?php _e('Price', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('price_' . $product->get_id());?>"
            name="<?php echo Util::prefix('fields[variable][variations]['.$product->get_id().'][price]');?>"
            value="<?php echo $meta->get('price');?>"
         />
         <?php echo wc_help_tip(__('The price you would like to sell the product at. Notice that this value has to be greater than zero.', 'integration-marktplaats-for-woocommerce'));?>
      </p>

   </div>

   <?php do_action(PREFIX . '\module\product_data_tab\panel\general_tab\bottom', $product, $meta); ?>

</div>
