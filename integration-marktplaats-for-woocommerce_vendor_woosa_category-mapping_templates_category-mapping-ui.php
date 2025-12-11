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

      <?php do_action(PREFIX . '\category-mapping\template\top'); ?>

      <div data-<?php echo PREFIX;?>-cm-box>
         <table class="bc-c" style="width: 100%;">
            <tr>
               <td class="p-0 va-t" style="width: 40%;">
                  <h3 class="m-0"><?php printf(__('%s Category', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')); ?></h3>
               </td>
               <td class="p-0 va-t" style="width: 40%;">
                  <h3 class="m-0"><?php _e('WooCommerce Category', 'integration-marktplaats-for-woocommerce') ?></h3>
               </td>
               <td class="p-0 va-t ta-r">
                  <h3 class="m-0"><?php _e('Action', 'integration-marktplaats-for-woocommerce') ?></h3>
               </td>
            </tr>
            <tr>
               <td class="p-0 pt-15 va-t" style="width: 40%;">
                  <?php Module_Category_Selection::render('service', 'tree');?>
               </td>
               <td class="p-0 pt-15 va-t" style="width: 40%;">
                  <?php Module_Category_Selection::render('shop', 'tree');?>
               </td>
               <td class="p-0 pt-15 va-t ta-r">
                  <button type="button" class="button button-primary"  data-<?php echo PREFIX;?>-cm-action="connect"><?php _e('Connect', 'integration-marktplaats-for-woocommerce');?></button>
               </td>
            </tr>
            <tr>
               <td colspan="3" class="pt-5 pr-0 pb-5 pl- ta-r" data-<?php echo PREFIX;?>-cm-info>
                  <?php if( 'yes' === Util::array($_GET)->get('connected') ):?>
                     <p class="ajax-response success"><?php _e('The categories have been connected.', 'integration-marktplaats-for-woocommerce');?></p>
                  <?php elseif( 'yes' === Util::array($_GET)->get('removed') ):?>
                     <p class="ajax-response success"><?php _e('The connected categories have been removed.', 'integration-marktplaats-for-woocommerce');?></p>
                  <?php endif;?>
               </td>
            </tr>
            <tr>
               <td class="p-0" colspan="3">
                  <table class="bc-c striped bt-1" style="width: 100%;">
                     <?php
                        $mcs1 = new Module_Category_Selection('service', 'tree');
                        $mcs2 = new Module_Category_Selection('shop', 'tree');

                        foreach($results as $result):

                           $trail    = ltrim(trim(strip_tags($mcs1->get_trail_template($result['category_id']))), '»&nbsp;');
                           $wc_trail = ltrim(trim(strip_tags($mcs2->get_trail_template($result['term_id']))), '»&nbsp;');

                           $has_category_config = apply_filters(PREFIX . '\category-mapping\category-config\display-configure-button', false, $result['category_id']);
                           ?>
                           <tr data-<?php echo PREFIX;?>-cm-term-id="<?php echo $result['term_id'];?>" data-<?php echo PREFIX;?>-cm-category-id="<?php echo $result['category_id'];?>">
                              <td  style="width: 40%;"><?php echo $trail?></td>
                              <td  style="width: 40%;"><?php echo $wc_trail;?></td>
                              <td  style="width: 20%;" class="ta-r button-actions">
                                 <?php if($has_category_config): ?>
                                    <button type="button" class="button" data-<?php echo PREFIX;?>-config-category title="<?php _e('Configure category fields.', 'integration-marktplaats-for-woocommerce'); ?>">
                                       <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-adjustments-horizontal"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 6l8 0" /><path d="M16 6l4 0" /><path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 12l2 0" /><path d="M10 12l10 0" /><path d="M17 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 18l11 0" /><path d="M19 18l1 0" /></svg>
                                    </button>
                                 <?php endif; ?>
                                 <button type="button" class="button" data-<?php echo PREFIX;?>-cm-action="remove" title="<?php _e('Remove category connection.', 'integration-marktplaats-for-woocommerce'); ?>">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                 </button>
                              </td>
                           </tr>

                        <?php endforeach;?>
                  </table>
               </td>
            </tr>

            <?php if($pages > 1):?>
               <tr class="bt-1">
                  <td colspan="3">
                     <ul class="pagination">
                        <?php for($page = 1; $page <= $pages; $page++):
                           $current = Util::array($_GET)->get('term_page')
                           ?>
                           <li>
                              <?php if( (empty($current) && $page == 1) || $current == $page):?>
                                 <span class="button button-small disabled"><?php echo $page;?></span>
                              <?php else:?>
                                 <a class="button button-small" href="<?php echo add_query_arg(['tab' => 'category_mapping', 'term_page' => $page], Module_Settings::get_page_url());?>"><?php echo $page;?></a>
                              <?php endif;?>
                           </li>
                        <?php endfor;?>
                     </ul>
                  </td>
               </tr>
            <?php endif;?>
         </table>
      </div>
   </td>
</tr>