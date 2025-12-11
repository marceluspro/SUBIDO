<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

/**
 * @var Module_Authorization $authorization
 */
?>
<tr class="<?php echo PREFIX;?>-style">
   <td class="p-0">
      <div>
         <span class="tb"><?php _e('Status ', 'integration-marktplaats-for-woocommerce');?></span>
         <?php echo $status; ?>
      </div>

      <?php if (!empty($authorization->get_wiki_article_url())): ?>
         <p class="pt-20"><?php
            printf(
               __('Questions about the authorization of your %s account? Read our %sHelp Center article%s, we will guide you step-by-step through the process.', 'integration-marktplaats-for-woocommerce'),
               Module_Core::config('service.name'),
               sprintf(
                  '<a href="%s" target="_blank" class="tb">',
                  $authorization->get_wiki_article_url()
               ),
               '</a>'
            );
         ?></p>
      <?php endif; ?>

      <div class="field-section">
         <?php do_action(PREFIX . '\authorization\output_section\fields', $authorization);?>

         <?php
            $auth_fields = Util::get_template('authorization-fields.php', [
               'is_authorized' => $authorization->is_authorized(),
            ], dirname(dirname(dirname(__FILE__))), untrailingslashit(basename(dirname(dirname(__FILE__)))) . '/templates');
         ?>

         <?php if( ! empty($auth_fields) ):?>
            <table style="width:100%;">
               <?php echo $auth_fields;?>
            </table>
         <?php endif;?>

         <div class="pt-10 mb-30">
            <?php if($authorization->is_authorized()):?>
               <button type="button" class="button button-primary" data-<?php echo PREFIX;?>-authorization-action="revoke"><?php _e('Click to revoke', 'integration-marktplaats-for-woocommerce');?></button>
            <?php else:?>
               <button type="button" class="button button-primary" data-<?php echo PREFIX;?>-authorization-action="authorize"><?php _e('Click to authorize', 'integration-marktplaats-for-woocommerce');?></button>
            <?php endif;?>
         </div>

         <?php
            if( ! empty($authorization->get_error()) ):?>
               <p class="ajax-response error"><?php echo $authorization->get_formatted_error($authorization->get_error());?></p>
            <?php
            endif;
         ?>
      </div>

      <?php if($authorization->is_authorized()):
         $setting_fields = Util::get_template('authorization-setting-fields.php', [
            'is_authorized' => $authorization->is_authorized(),
         ], dirname(dirname(dirname(__FILE__))), untrailingslashit(basename(dirname(dirname(__FILE__)))) . '/templates');
         ?>
         <?php if( ! empty($setting_fields) ):?>
            <div class="field-section">
               <h2 class="mb-5"><?php _e('Settings', 'integration-marktplaats-for-woocommerce');?></h2>
               <table style="width:100%;">
                  <?php echo $setting_fields;?>
               </table>
               <div class="pt-10">
                  <button type="button" class="button button-primary" data-<?php echo PREFIX;?>-authorization-action="save"><?php _e('Save settings', 'integration-marktplaats-for-woocommerce');?></button>
               </div>
            </div>
         <?php endif;?>
      <?php endif;?>
   </td>
</tr>