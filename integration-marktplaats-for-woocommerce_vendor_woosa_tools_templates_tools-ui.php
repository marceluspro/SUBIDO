<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<tr class="<?php echo PREFIX;?>-style">
   <td class="pt-0 pl-0 pr-0">
      <h3><?php _e('Woosa IP list', 'integration-marktplaats-for-woocommerce');?></h3>
      <p class="description mb-10"><?php _e('In case your shop has some restrictions for inbound requests please whitelist our IPs:', 'integration-marktplaats-for-woocommerce');?></p>
      <div><?php echo implode(', ', $ip_whitelist);?></div>
   </td>
</tr>

<?php foreach($tools as $tool):
   $btn_class = Util::array($tool)->get('btn_class');
   $btn_label = Util::array($tool)->get('btn_label', __('Click to run', 'integration-marktplaats-for-woocommerce'));
   $hidden    = Util::array($tool)->get('hidden');

   if(empty($tool['id']) || empty($tool['name']) || empty($tool['description']) || $hidden){
      continue;
   }
   ?>
   <tr class="<?php echo PREFIX;?>-style">
      <td class="pl-0 pr-0">
         <h3><?php echo $tool['name'];?></h3>
         <div class="mb-10">
            <p class="description"><?php echo $tool['description'];?></p>
         </div>
         <?php if( ! empty($tool['warning']) ):?>
            <div class="mb-10 alertbox alertbox--yellow"><b><?php _e('Warning:', 'integration-marktplaats-for-woocommerce');?></b> <?php echo $tool['warning'];?></div>
         <?php endif;?>
         <div>
            <button type="button" class="button <?php echo $btn_class;?>" data-<?php echo PREFIX;?>-run-tool="<?php echo $tool['id'];?>"><?php echo $btn_label;?></button>
         </div>
   </td>
</tr>
<?php endforeach;?>