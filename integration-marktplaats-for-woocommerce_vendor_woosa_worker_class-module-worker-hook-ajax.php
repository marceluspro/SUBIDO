<?php
/**
 * Module Worker Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Worker_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_render_total_worker_action_tasks', [__CLASS__, 'handle_total_action_tasks']);

   }



   /**
    * Processes the request to render total action tasks.
    *
    * @return string
    */
   public static function handle_total_action_tasks(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $action            = Module_Worker::action(Util::array($_POST)->get('worker_action'));
      $total             = $action->count_tasks();
      $active_tasks      = $action->count_tasks('active');
      $rescheduled_tasks = $action->count_tasks('rescheduled');

      ob_start()
      ?>

      <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><?php echo $total; ?></td>
      <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><?php echo $active_tasks;?></td>
      <td class="pt-0 pb-0 pl-0" style="font-size: 12px;"><?php echo $rescheduled_tasks;?></td>

      <?php
      $output = ob_get_clean();

      wp_send_json_success([
         'template' => $output,
      ]);
   }
}