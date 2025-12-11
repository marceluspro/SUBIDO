<?php
/**
 * Module Heartbeat
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Heartbeat{

   /**
    * This runs whatever is hooked in.
    *
    * @return void
    */
   public static function perform(){

      do_action_deprecated(PREFIX . '\heartbeat\perform', [], '3.1.0', 'woosa\heartbeat\perform');

      do_action('woosa\heartbeat\perform', PREFIX);

   }



   /**
    * Schedules the action responsible to perform.
    *
    * @param bool $run_hook
    * @return void
    */
   public static function schedule_action($run_hook = false){

      $skip = apply_filters('woosa\module\heartbeat\schedule_action\skip', false, PREFIX);

      if($skip){
         return;
      }

      $as = new Module_Action_Scheduler();
      $as->set_group('heartbeat');
      $as->set_hook('perform');
      $as->set_callback([Module_Heartbeat::class, 'perform']);
      $as->set_recurring(\MINUTE_IN_SECONDS);
      $as->save();

      if($run_hook){
         do_action('woosa\module\heartbeat\schedule_all_actions', PREFIX);
      }
   }



   /**
    * Cancel the action responsible to perform.
    *
    * @param bool $run_hook
    * @return void
    */
   public static function unschedule_action($run_hook = false){

      $skip = apply_filters('woosa\module\heartbeat\unschedule_action\skip', false, PREFIX);

      if($skip){
         return;
      }

      $as = new Module_Action_Scheduler();
      $as->set_group('heartbeat');
      $as->set_hook('perform');
      $as->unschedule();

      if($run_hook){
         do_action('woosa\module\heartbeat\unschedule_all_actions', PREFIX);
      }
   }



   /**
    * Enables or disables the external cron job.
    *
    * @param string $action
    * @param bool $schedule_action
    * @return array
    */
   public static function toggle_cron_job_status(string $action, bool $schedule_action = true){

      $perform = apply_filters('woosa\module\heartbeat\toggle_cron_job_status', ['success' => true], $action, PREFIX);

      if( ! Util::array($perform)->get('success') ){
         return $perform;
      }

      if('enable' === $action){

         Option::set('use_external_cronjob', 'yes');

         Module_Heartbeat::unschedule_action(true);

      }else{

         Option::set('use_external_cronjob', 'no');

         if($schedule_action){
            Module_Heartbeat::schedule_action(true);
         }
      }

      return $perform;
   }



   /**
    * Checks whether or not the cron job is enabled by any of our plugins.
    *
    * @param bool $use_exclude
    * @param bool $exclude_itself
    * @return boolean
    */
   public static function is_cron_job_enabled($use_exclude = false, $exclude_itself = false){

      global $wpdb;

      $result  = false;
      $exclude = self::get_excluded_cron_job_option_names();

      if($exclude_itself){
         $exclude[] = Util::prefix('use_external_cronjob');
      }

      $opt_keys = implode(',', array_fill(0, count($exclude), '%s'));

      if( $use_exclude && ! empty($exclude)){

         $query = $wpdb->get_row(
            $wpdb->prepare(
               "SELECT * FROM $wpdb->options
               WHERE option_name LIKE '%_use_external_cronjob'
                  AND option_value = 'yes'
                  AND option_name NOT IN ($opt_keys)",
               ...$exclude
            )
         );

      }else{

         $query = $wpdb->get_row("SELECT * FROM $wpdb->options
            WHERE option_name LIKE '%_use_external_cronjob'
               AND option_value = 'yes'"
         );
      }

      if(isset($query->option_id)){
         $result = true;
      }

      return $result;
   }



   /**
    * Gets the list of option names that can be used an excluded list.
    * This must be a general hook (not a prefixed one) to be easily controlled by any of our plugins.
    *
    * @return array
    */
   public static function get_excluded_cron_job_option_names(){
      return apply_filters('woosa\module\heartbeat\exclude_cronjob_option_names', [], PREFIX);
   }



   /**
    * Displays the section content.
    *
    * @param array $values
    * @return void
    */
   public static function render($values = []){

      $is_enabled = self::is_cron_job_enabled(true);
      $status     = __('Disabled', 'integration-marktplaats-for-woocommerce');
      $sa_status  = __('Enabled', 'integration-marktplaats-for-woocommerce');

      if($is_enabled){
         $status    = __('Enabled', 'integration-marktplaats-for-woocommerce');
         $sa_status = __('Disabled', 'integration-marktplaats-for-woocommerce');
      }

      $data = json_encode([
         'action' => $is_enabled ? 'disable' : 'enable'
      ]);

      $color     = $is_enabled ? 'green' : '#cc0000';
      $sa_color  = ! $is_enabled ? 'green' : '#cc0000';
      $status    = '<span style="color: '.$color.';">'.$status.'</span>';
      $sa_status = '<span style="color: '.$sa_color.';">'.$sa_status.'</span>';
      $btn_attr  = "data-" . PREFIX . "-heartbeat='{$data}'";
      $btn_label = $is_enabled ? __( 'Click to disable', 'integration-marktplaats-for-woocommerce' ) : __( 'Click to enable', 'integration-marktplaats-for-woocommerce' );

      echo Util::get_template('heartbeat-ui.php', [
         'status'      => $status,
         'sa_status'   => $sa_status,
         'description' => $values['desc'],
         'is_enabled'  => $is_enabled,
         'button'      => [
            'label' => $btn_label,
            'data-attr' => $btn_attr,
         ]
      ], dirname(dirname(__FILE__)), untrailingslashit(basename(dirname(__FILE__))) . '/templates');

   }


}
