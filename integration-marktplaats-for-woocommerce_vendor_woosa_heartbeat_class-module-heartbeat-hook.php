<?php
/**
 * Module Heartbeat Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Heartbeat_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action(PREFIX . '\core\state\activated', [__CLASS__, 'enable_on_activation']);
      add_action(PREFIX . '\core\state\deactivated', [__CLASS__, 'disable_on_deactivation']);

      //re-schedule
      add_action('action_scheduler_failed_action', [__CLASS__, 'reschedule_action']);
      add_action('action_scheduler_canceled_action', [__CLASS__, 'reschedule_cancelled_action']);

      add_action('woosa\module\heartbeat\schedule_all_actions', [__CLASS__, 'schedule_action']);
      add_action('woosa\module\heartbeat\unschedule_all_actions', [__CLASS__, 'unschedule_action']);

      add_action('added_option', [__CLASS__, 'check_added_cron_job_option'], 10, 2);
      add_action('updated_option', [__CLASS__, 'check_updated_cron_job_option'], 10, 3);

   }



   /**
    * Enables the heartbeat on plugin activation.
    *
    * @return void
    */
   public static function enable_on_activation(){

      //in case the Heartbeat is enabled by any of our plugins
      if(Module_Heartbeat::is_cron_job_enabled(true, true)){

         Option::set('use_external_cronjob', 'yes');

         return;
      }

      Module_Heartbeat::schedule_action();
   }



   /**
    * Disables the heartbeat on plugin deactivation.
    *
    * @return void
    */
   public static function disable_on_deactivation(){

      global $wpdb;

      //use custom query to avoid triggering added/updated_option hooks
      $wpdb->query(
         $wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = 'no' WHERE option_name = %s",
            Util::prefix('use_external_cronjob')
         )
      );

      //in case the Heartbeat is NOT enabled by any of our plugins
      if(! Module_Heartbeat::is_cron_job_enabled(true, true)){
         Module_Heartbeat::toggle_cron_job_status('disable', false);
      }
   }



   /**
    * Reschedules the action if the condition matches.
    *
    * @param int $action_id
    * @return void
    */
   public static function reschedule_action($action_id){

      $action = \ActionScheduler::store()->fetch_action( $action_id );

      if($action->get_hook() === Util::prefix('perform') && $action->get_group() === Util::prefix('heartbeat', true) ){
         Module_Heartbeat::schedule_action();
      }
   }



   /**
    * Reschedules the cancelled action.
    *
    * @param int $action_id
    * @return void
    */
   public static function reschedule_cancelled_action($action_id){

      if(isset($_GET['row_action']) && Util::array($_GET)->get('row_id') == $action_id){
         self::reschedule_action($action_id);
      }
   }



   /**
    * Schedules the action responsible to perform.
    *
    * When any of our plugins scheduled the action will trigger the hook of this callback so
    * that each plugin will schedule their actions.
    *
    * @param string $plugin_prefix
    * @return void
    */
   public static function schedule_action($plugin_prefix){

      //remove action to avoid inifinte loop
      remove_action('woosa\module\heartbeat\schedule_all_actions', [self::class, 'schedule_action']);

      Module_Heartbeat::schedule_action(true);

      //add action back
      add_action('woosa\module\heartbeat\schedule_all_actions', [self::class, 'schedule_action']);
   }



   /**
    * Cancel the action responsible to perform.
    *
    * When any of our plugins unscheduled the action will trigger the hook of this callback so
    * that each plugin will unschedule their actions.
    *
    * @param string $plugin_prefix
    * @return void
    */
   public static function unschedule_action($plugin_prefix){

      //remove action to avoid inifinte loop
      remove_action('woosa\module\heartbeat\unschedule_all_actions', [self::class, 'unschedule_action']);

      Module_Heartbeat::unschedule_action(true);

      //add action back
      add_action('woosa\module\heartbeat\unschedule_all_actions', [self::class, 'unschedule_action']);
   }



   /**
    * Check if any of our plugins added option `use_external_cronjob`.
    *
    * If so then set the current option as well.
    *
    * @param string $option
    * @param mixed $value
    * @return void
    */
   public static function check_added_cron_job_option($option, $value){

      $skip = apply_filters('woosa\module\heartbeat\check_processed_cron_job_option\skip', false, $option, PREFIX);

      if($skip){
         return;
      }

      if(strpos($option, '_use_external_cronjob') !== false){
         Option::set('use_external_cronjob', $value);
      }
   }



   /**
    * Check if any of our plugins updated option `use_external_cronjob`.
    *
    * If so then set the current option as well.
    *
    * @param string $option
    * @param mixed $old_value
    * @param mixed $value
    * @return void
    */
   public static function check_updated_cron_job_option($option, $old_value, $value){
      self::check_added_cron_job_option($option, $value);
   }

}