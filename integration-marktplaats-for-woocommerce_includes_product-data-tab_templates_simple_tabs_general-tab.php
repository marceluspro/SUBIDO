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
         <label for="<?php echo Util::prefix('use_local_title');?>"><?php _e('Manage title?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_title]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_title');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_title]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_title');?>"
            <?php checked( 'yes', $meta->get('use_local_title') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different title than the product title.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_title');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_title') );?>">
         <label for="<?php echo Util::prefix('title');?>">
            <?php _e('Title', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('title');?>"
            name="<?php echo Util::prefix('fields[simple][title]');?>"
            value="<?php echo $meta->get('title');?>"
         />
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_price_type');?>"><?php _e('Manage price type?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_price_type]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_price_type');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_price_type]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_price_type');?>"
            <?php checked( 'yes', $meta->get('use_local_price_type') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different price type than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_price_type');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_price_type') );?>">
         <label for="<?php echo Util::prefix('price_type');?>">
            <?php _e('Price type', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <select
            id="<?php echo Util::prefix('price_type');?>"
            name="<?php echo Util::prefix('fields[simple][price_type]');?>"
         >
            <?php foreach(Service_API::price_types() as $key => $label):?>
               <option <?php selected($key, $meta->get('price_type'));?> value="<?php echo $key;?>"><?php echo $label;?></option>
            <?php endforeach;?>
         </select>
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_price');?>"><?php _e('Manage price?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_price]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_price');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_price]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_price');?>"
            <?php checked( 'yes', $meta->get('use_local_price') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different price than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_price');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_price') );?>">
         <label for="<?php echo Util::prefix('price');?>">
            <?php _e('Price', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('price');?>"
            name="<?php echo Util::prefix('fields[simple][price]');?>"
            value="<?php echo $meta->get('price');?>"
         />
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_category');?>"><?php _e('Manage category?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_category]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_category');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_category]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_category');?>"
            <?php checked( 'yes', $meta->get('use_local_category') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different category than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <div data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_category');?>="yes" style="padding: 5px 20px 5px 162px; margin: 9px 0px; <?php echo Util::css_display( 'yes', $meta->get('use_local_category') );?>">
         <label>
            <?php _e('Category', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <?php Module_Category_Selection::render_on_product('service', 'tree', $meta); ?>
      </div>

      <div data-<?php echo PREFIX;?>-cpc-field>
         <p class="form-field">
            <label for="<?php echo Util::prefix('use_local_cpc');?>"><?php _e('Manage cost per click?', 'integration-marktplaats-for-woocommerce');?></label>
            <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_cpc]');?>" value="no" />
            <input
               type="checkbox"
               id="<?php echo Util::prefix('use_local_cpc');?>"
               name="<?php echo Util::prefix('fields[simple][use_local_cpc]');?>"
               data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_cpc');?>"
               <?php checked( 'yes', $meta->get('use_local_cpc') );?>
               value="yes"
            />
            <span class="description"><?php _e('Use a different cost per click than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
         </p>
         <div data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_cpc');?>="yes" style="padding: 5px 20px 5px 162px; margin: 9px 0px; <?php echo Util::css_display( 'yes', $meta->get('use_local_cpc') );?>">
            <label>
               <?php _e('Cost per click', 'integration-marktplaats-for-woocommerce');?>
            </label>
            <div class="p-15 bt-1 br-1 bb-1 bl-1 mb-20" style="max-width:400px;">
               <?php
               $api_category = new Service_API_Category;
               $config = $api_category->get_config($meta->get('category'));
               $currency = get_woocommerce_currency_symbol('EUR');
               $as_automatic = 'yes' === $meta->get('cpc_automatic');
               ?>
               <div class="pb-10"><?php _e('Cost per click:', 'integration-marktplaats-for-woocommerce');?></div>
               <input
                  type="text"
                  id="<?php echo Util::prefix('cpc_slider');?>"
                  class="<?php echo Util::prefix('product_cpc_field');?>"
                  name="<?php echo esc_attr( Util::prefix('fields[simple][cpc]') ); ?>"
                  data-type="single"
                  data-min="<?php echo $config['cpc']['min'];?>"
                  data-max="<?php echo $config['cpc']['max'];?>"
                  data-from="<?php echo $meta->get('cpc');?>"
                  data-grid="true"
                  data-step="0.01"
                  data-prefix="<?php echo $currency;?>"
                  data-skin="round"
                  data-disable="<?php echo $as_automatic ? 1 : 0;?>"
                  data-<?php echo PREFIX;?>-cpc
               />

               <div class="pt-20">
                  <label class="m-0" style="float: none;">
                     <input type="hidden" name="<?php echo Util::prefix('fields[simple][cpc_automatic]');?>" value="no" />
                     <input
                        type="checkbox"
                        name="<?php echo esc_attr( Util::prefix('fields[simple][cpc_automatic]') ); ?>"
                        <?php checked( 'yes', $meta->get('cpc_automatic') );?>
                        value="yes"
                        data-<?php echo PREFIX;?>-cpc-automatic
                     />
                     <?php _e('Set as automatic', 'integration-marktplaats-for-woocommerce');?>
                  </label>
               </div>
            </div>
            <div class="p-15 bt-1 br-1 bb-1 bl-1" data-<?php echo PREFIX;?>-cpc-total-budget-wrapper style="max-width:400px;">
               <div class="pb-10"><?php _e('Total budget:', 'integration-marktplaats-for-woocommerce');?></div>
               <input
                  type="text"
                  id="<?php echo Util::prefix('cpc_total_budget_slider');?>"
                  class="<?php echo Util::prefix('product_cpc_field');?>"
                  name="<?php echo esc_attr( Util::prefix('fields[simple][cpc_total_budget]') ); ?>"
                  data-type="single"
                  data-min="<?php echo $config['cpc_total_budget']['min'];?>"
                  data-max="<?php echo '500';//$config['cpc_total_budget']['max'];?>"
                  data-from="<?php echo $meta->get('cpc_total_budget');?>"
                  data-grid="true"
                  data-step="1.00"
                  data-prefix="<?php echo $currency;?>"
                  data-skin="round"
                  data-disable="0"
               />
            </div>
         </div>
      </div>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_shipping_type');?>"><?php _e('Manage shipping type?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_shipping_type]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_shipping_type');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_shipping_type]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_shipping_type');?>"
            <?php checked( 'yes', $meta->get('use_local_shipping_type') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different shipping type than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <div data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_shipping_type');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_shipping_type') );?>">
         <p class="form-field" >
            <label for="<?php echo Util::prefix('shipping_type');?>">
               <?php _e('Shipping type', 'integration-marktplaats-for-woocommerce');?>
            </label>
            <select
               id="<?php echo Util::prefix('shipping_type');?>"
               name="<?php echo Util::prefix('fields[simple][shipping_type]');?>"
               data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('shipping_type_extra');?>"
            >
               <?php foreach(Service_API::shipping_types() as $key => $label):?>
                  <option <?php selected($key, $meta->get('shipping_type'));?> value="<?php echo $key;?>"><?php echo $label;?></option>
               <?php endforeach;?>
            </select>
         </p>
         <div data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('shipping_type_extra');?>="ship" style="<?php echo Util::css_display( 'ship', $meta->get('shipping_type') );?>">
            <p class="form-field" >
               <label for="<?php echo Util::prefix('shipping_cost');?>">
                  <?php _e('Shipping cost', 'integration-marktplaats-for-woocommerce');?>
               </label>
               <input
                  type="text"
                  id="<?php echo Util::prefix('shipping_cost');?>"
                  name="<?php echo Util::prefix('fields[simple][shipping_cost]');?>"
                  value="<?php echo $meta->get('shipping_cost');?>"
               />
            </p>
            <p class="form-field" >
               <label for="<?php echo Util::prefix('shipping_time');?>">
                  <?php _e('Shipping time', 'integration-marktplaats-for-woocommerce');?>
               </label>
               <select
                  id="<?php echo Util::prefix('shipping_time');?>"
                  name="<?php echo Util::prefix('fields[simple][shipping_time]');?>"
               >
                  <?php foreach(Service_API::shipping_timeframes() as $key => $label):?>
                     <option <?php selected($key, $meta->get('shipping_time'));?> value="<?php echo $key;?>"><?php echo $label;?></option>
                  <?php endforeach;?>
               </select>
            </p>
         </div>
         <div data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('shipping_type_extra');?>="pickup" style="<?php echo Util::css_display( 'pickup', $meta->get('shipping_type') );?>">
            <p class="form-field" >
               <label for="<?php echo Util::prefix('shipping_pickup_location');?>">
                  <?php _e('Shipping pickup location', 'integration-marktplaats-for-woocommerce');?>
               </label>
               <input
                  type="text"
                  id="<?php echo Util::prefix('shipping_pickup_location');?>"
                  name="<?php echo Util::prefix('fields[simple][shipping_pickup_location]');?>"
                  value="<?php echo $meta->get('shipping_pickup_location');?>"
                  placeholder="e.g. 1097DN"
               />
            </p>
         </div>
      </div>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_allow_contact_by_email');?>"><?php _e('Manage contact by email?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_allow_contact_by_email]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_allow_contact_by_email');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_allow_contact_by_email]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_allow_contact_by_email');?>"
            <?php checked( 'yes', $meta->get('use_local_allow_contact_by_email') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different value for the general setting "Allow contact by email".', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_allow_contact_by_email');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_allow_contact_by_email') );?>">
         <label for="<?php echo Util::prefix('allow_contact_by_email');?>">
            <?php _e('Allow contact by email', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <select
            id="<?php echo Util::prefix('allow_contact_by_email');?>"
            name="<?php echo Util::prefix('fields[simple][allow_contact_by_email]');?>"
         >
            <option <?php selected('no', $meta->get('allow_contact_by_email'));?> value="no"><?php _e('No', 'integration-marktplaats-for-woocommerce');?></option>
            <option <?php selected('yes', $meta->get('allow_contact_by_email'));?> value="yes"><?php _e('Yes', 'integration-marktplaats-for-woocommerce');?></option>
         </select>
         <?php echo wc_help_tip(__('Whether or not to show the email address on the ad.', 'integration-marktplaats-for-woocommerce'));?>
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_salutation');?>"><?php _e('Manage salutation?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_salutation]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_salutation');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_salutation]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_salutation');?>"
            <?php checked( 'yes', $meta->get('use_local_salutation') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different salutation than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_salutation');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_salutation') );?>">
         <label for="<?php echo Util::prefix('salutation');?>">
            <?php _e('Salutation', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <select
            id="<?php echo Util::prefix('salutation');?>"
            name="<?php echo Util::prefix('fields[simple][salutation]');?>"
         >
            <?php foreach(Service_API::salutation_forms() as $key => $label):?>
               <option <?php selected($key, $meta->get('salutation'));?> value="<?php echo $key;?>"><?php echo $label;?></option>
            <?php endforeach;?>
         </select>
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_seller_name');?>"><?php _e('Manage seller name?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_seller_name]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_seller_name');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_seller_name]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_seller_name');?>"
            <?php checked( 'yes', $meta->get('use_local_seller_name') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different seller name than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_seller_name');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_seller_name') );?>">
         <label for="<?php echo Util::prefix('seller_name');?>">
            <?php _e('Seller name', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('seller_name');?>"
            name="<?php echo Util::prefix('fields[simple][seller_name]');?>"
            value="<?php echo $meta->get('seller_name');?>"
         />
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_phone');?>"><?php _e('Manage phone number?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_phone]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_phone');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_phone]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_phone');?>"
            <?php checked( 'yes', $meta->get('use_local_phone') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different phone number than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_phone');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_phone') );?>">
         <label for="<?php echo Util::prefix('phone');?>">
            <?php _e('Phone number', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('phone');?>"
            name="<?php echo Util::prefix('fields[simple][phone]');?>"
            value="<?php echo $meta->get('phone');?>"
         />
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_website_url');?>"><?php _e('Manage website URL?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_website_url]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_website_url');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_website_url]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_website_url');?>"
            <?php checked( 'yes', $meta->get('use_local_website_url') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different website URL than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <p class="form-field" data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_website_url');?>="yes" style="<?php echo Util::css_display( 'yes', $meta->get('use_local_website_url') );?>">
         <label for="<?php echo Util::prefix('website_url');?>">
            <?php _e('Website URL', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <input
            type="text"
            id="<?php echo Util::prefix('website_url');?>"
            name="<?php echo Util::prefix('fields[simple][website_url]');?>"
            value="<?php echo $meta->get('website_url');?>"
         />
      </p>

      <p class="form-field">
         <label for="<?php echo Util::prefix('use_local_footer_description');?>"><?php _e('Manage footer description?', 'integration-marktplaats-for-woocommerce');?></label>
         <input type="hidden" name="<?php echo Util::prefix('fields[simple][use_local_footer_description]');?>" value="no" />
         <input
            type="checkbox"
            id="<?php echo Util::prefix('use_local_footer_description');?>"
            name="<?php echo Util::prefix('fields[simple][use_local_footer_description]');?>"
            data-<?php echo PREFIX;?>-has-extra-field="<?php echo Util::prefix('use_local_footer_description');?>"
            <?php checked( 'yes', $meta->get('use_local_footer_description') );?>
            value="yes"
         />
         <span class="description"><?php _e('Use a different footer description than the general settings.', 'integration-marktplaats-for-woocommerce');?></span>
      </p>
      <div data-<?php echo PREFIX;?>-extra-field-<?php echo Util::prefix('use_local_footer_description');?>="yes"
         style="padding: 5px 20px 5px 162px; margin: 9px 0px; <?php echo Util::css_display( 'yes', $meta->get('use_local_footer_description') );?>"
      >
         <label for="<?php echo Util::prefix('footer_description');?>">
            <?php _e('Footer description', 'integration-marktplaats-for-woocommerce');?>
         </label>
         <div data-<?php echo PREFIX;?>-editor-input="<?php echo esc_attr( Util::prefix('footer_description') ); ?>"><?php echo $meta->get('footer_description');?></div>
         <textarea
            style="display:none;"
            id="<?php echo Util::prefix('footer_description');?>"
            name="<?php echo Util::prefix('fields[simple][footer_description]');?>"
            data-<?php echo PREFIX;?>-editor-value="<?php echo esc_attr( Util::prefix('footer_description') ); ?>"
         ><?php echo esc_textarea( $meta->get('footer_description') ); // WPCS: XSS ok. ?></textarea>
      </div>

   </div>

   <?php do_action(PREFIX . '\module\product_data_tab\panel\general_tab\bottom', $product, $meta); ?>

</div>
