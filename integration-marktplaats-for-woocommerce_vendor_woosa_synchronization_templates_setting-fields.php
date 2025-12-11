<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


$available = true;
$field_generator = new Module_Field_Generator;
?>

<?php if( $available ):?>

   <?php
      $sections = apply_filters(PREFIX . '\module\synchronization\sections', []);
      $is_first = true;
   ?>

   <?php foreach ($sections as $key => $section): ?>
      <?php
         if (isset($section['active']) && false === $section['active']) {
            continue;
         }
      ?>
      <div class="collapsible-wrap <?php echo $is_first ? 'active' : 'closed';?>">
         <div class="collapsible-header" data-<?php echo PREFIX;?>-collapsible-state="<?php echo $is_first ? 'active' : 'closed'; ?>">
            <div class="collapsible-header-title"><?php echo $section['name']; ?></div>
            <div class="collapsible-header-handle">
               <div class="wch-collapse" style="display: <?php echo $is_first ? 'none' : 'block';?>">
                  <?php echo Util::get_svg_icon('bars'); ?>
                  <span><?php _e('Collapse', 'integration-marktplaats-for-woocommerce');?></span>
               </div>
               <div class="wch-minimize" style="display: <?php echo $is_first ? 'block' : 'none';?>">
                  <?php echo Util::get_svg_icon('xmark'); ?>
                  <span><?php _e('Minimize', 'integration-marktplaats-for-woocommerce');?></span>
               </div>
            </div>
         </div>
         <div class="collapsible-content" style="display: <?php echo $is_first ? 'block' : 'none'; ?>">

            <?php if( ! empty($section['desc']) ):?>
               <p class="mt-15 description"><em><?php echo $section['desc'];?></em></p>
            <?php endif;?>

            <table>
               <?php
               $field_generator->set_array_name(PREFIX . '_fields');
               $field_generator->set_fields($section['fields'], $key);
               $field_generator->render();
               ?>
            </table>
         </div>
      </div>
      <?php $is_first = false; ?>
   <?php endforeach; ?>

<?php endif;?>