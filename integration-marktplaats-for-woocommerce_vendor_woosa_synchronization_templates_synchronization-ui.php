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

      <div data-<?php echo Util::prefix('setting-fields');?> class="<?php echo PREFIX;?>-panel">
         <?php echo Module_Synchronization::get_template('setting-fields.php');?>
      </div>

   </td>
</tr>