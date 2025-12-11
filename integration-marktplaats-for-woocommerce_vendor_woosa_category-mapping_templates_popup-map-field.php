<?php

/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined('ABSPATH') || exit;

/**
 * @var int $term_id
 * @var int $category_id
 * @var string $category_name
 * @var array $config_fields
 * @var array $category_fields
 */

$total_required_fields = count(array_filter($category_fields, fn($field) => !empty($field['required'])));
$total_non_required_fields = count(array_filter($category_fields, fn($field) => empty($field['required'])));
?>
<form id="<?php echo PREFIX; ?>-ajax-view-content" class="<?php echo PREFIX; ?>-style">
   <table class="form-table">
      <?php if ($total_required_fields > 0): ?>
         <td style="vertical-align: top; width: 50%">
            <h2><?php echo sprintf(__('Mandatory fields for category "%s"', 'integration-marktplaats-for-woocommerce'), $category_name); ?></h2>
            <div>
               <table style="width: 100%">
                  <tbody>
                     <tr>
                        <th><?php _e('Field name', 'integration-marktplaats-for-woocommerce'); ?></th>
                        <td><b><?php _e('Source value', 'integration-marktplaats-for-woocommerce'); ?></b></td>
                     </tr>
                     <?php
                     Module_Category_Mapping::render_fields($term_id, $config_fields, $category_fields, true);
                     ?>
                  </tbody>
               </table>
            </div>
         </td>
      <?php endif; ?>
      <?php if ($total_non_required_fields > 0): ?>
         <td style="vertical-align: top; width: 50%">
            <h2><?php echo sprintf(__('Optional fields for category "%s"', 'integration-marktplaats-for-woocommerce'), $category_name); ?></h2>
            <div>
               <table style="width: 100%">
                  <tbody>
                     <tr>
                        <th><?php _e('Field name', 'integration-marktplaats-for-woocommerce'); ?></th>
                        <td><b><?php _e('Source value', 'integration-marktplaats-for-woocommerce'); ?></b></td>
                     </tr>
                     <?php
                     Module_Category_Mapping::render_fields($term_id, $config_fields, $category_fields, false);
                     ?>
                  </tbody>
               </table>
            </div>
         </td>
      <?php endif; ?>
   </table>

   <div class="<?php echo PREFIX; ?>-popup-footer">
      <table>
         <tr>
            <td class="va-t">
               <button type="button" class="button button-primary" data-<?php echo PREFIX; ?>-save-category-config="<?php echo $term_id; ?>">
                  <?php _e('Save config', 'integration-marktplaats-for-woocommerce'); ?>
               </button>
               <button type="button" class="button" data-<?php echo PREFIX; ?>-copy-category-config-toggle="<?php echo $term_id; ?>">
                  <?php _e('Copy config', 'integration-marktplaats-for-woocommerce'); ?>
               </button>
               <select data-<?php echo PREFIX; ?>-copy-term-config="<?php echo $term_id; ?>" style="display: none; width: auto !important;">
                  <?php foreach ($mapped_terms as $mapped_term_id => $name): ?>
                     <option value="<?php echo $mapped_term_id; ?>"><?php echo $name; ?></option>
                  <?php endforeach; ?>
               </select>
               <button type="button" class="button"
                  data-<?php echo PREFIX; ?>-copy-category-config="<?php echo $term_id; ?>"
                  data-<?php echo PREFIX; ?>-term-id="<?php echo $term_id; ?>"
                  data-<?php echo PREFIX; ?>-category="<?php echo $category_id; ?>"
                  style="display: none;">
                  <?php _e('Copy', 'integration-marktplaats-for-woocommerce'); ?>
               </button>
            </td>
            <td class="va-t pl-15" data-<?php echo PREFIX; ?>-ajax-response></td>
         </tr>
      </table>
   </div>
</form>