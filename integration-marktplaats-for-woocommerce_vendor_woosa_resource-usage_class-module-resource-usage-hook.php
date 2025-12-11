<?php
/**
 * Module Resource Usage Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Resource_Usage_Hook implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_filter(PREFIX . '\module\settings\page\content\fields\dashboard', [__CLASS__, 'add_setting_option'], 99);

      add_action(PREFIX . '\module\settings\page\top', [__CLASS__, 'show_server_config_warning'], 3);

      add_filter(PREFIX . '\util\memory_limit\allocated_percentage', [__CLASS__, 'define_memory_usage']);
      add_filter(PREFIX . '\util\max_execution_time\allocated_seconds', [__CLASS__, 'define_exec_time_usage']);

      add_filter(PREFIX . '\task\query_limit', [__CLASS__, 'define_query_task_limit']);
      add_filter(PREFIX . '\task\calculate_reschedule_time\extra_time', [__CLASS__, 'define_reschedule_extra_time'], 10, 2);

      add_filter(PREFIX . '\midlayer_feed\check_feed_recurrence', [__CLASS__, 'define_check_feed_recurrence']);
   }



   /**
    * Adds extra setting options on Dashboard tab.
    *
    * @param array $fields
    * @return array
    */
   public static function add_setting_option($fields){

      $results = [];

      foreach ($fields as $field) {

         if (PREFIX .'_settings_end' === $field['id']) {

            $allow = apply_filters(PREFIX . '\module\resource_usage\add_setting_option\allow', true);

            if($allow){

               $results[] = [
                  'name'    => __( 'Resource usage', 'integration-marktplaats-for-woocommerce' ),
                  'desc'    => __( 'Define the usage of the server resources to get the best performance from the plugin.', 'integration-marktplaats-for-woocommerce' ),
                  'id'      => Util::prefix('resource_usage'),
                  'type'    => 'select',
                  'value'   => Option::get('resource_usage', 'medium'),
                  'options' => [
                     'low'    => __('Low', 'integration-marktplaats-for-woocommerce'),
                     'medium' => __('Medium (recommended)', 'integration-marktplaats-for-woocommerce'),
                     'high'   => __('High', 'integration-marktplaats-for-woocommerce'),
                  ]
               ];
            }
         }

         $results[] = $field;
      }

      return $results;
   }



   /**
    * Displays warning about server configuration updates.
    *
    * @param array $current_tab
    * @return string
    */
   public static function show_server_config_warning($current_tab){

      $memory_limit                = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
      $memory_limit_required       = 536870912; //512MB
      $max_execution_time          = (int) ini_get('max_execution_time');
      $max_execution_time_required = Util::get_allocated_max_execution_time(false) / (1 - 0.5);
      $max_input_vars              = (int) ini_get('max_input_vars');
      $max_input_vars_required     = apply_filters(PREFIX . '\module\core\max_input_vars_required', 1000);

      $resource_usage = Option::get('resource_usage', 'medium');

      if('medium' === $resource_usage){
         $memory_limit_required = 1073741824; //1GB
      }

      if('high' === $resource_usage){
         $memory_limit_required = 2147483648; //2GB
      }

      $rows    = [];
      $title   = __('Server Configuration Update Recommended!', 'integration-marktplaats-for-woocommerce');
      $content = __('We strongly recommend increasing the following PHP directive(s) for optimal performance:', 'integration-marktplaats-for-woocommerce');

      if($max_execution_time < $max_execution_time_required){
         $rows[] = [
            'directive' => 'max_execution_time',
            'current' => $max_execution_time,
            'recommended' => $max_execution_time_required,
         ];
      }

      if($memory_limit < $memory_limit_required){
         $rows[] = [
            'directive' => 'memory_limit',
            'current' => size_format($memory_limit),
            'recommended' => size_format($memory_limit_required),
         ];
      }

      if($max_input_vars < $max_input_vars_required){
         $rows[] = [
            'directive' => 'max_input_vars',
            'current' => $max_input_vars,
            'recommended' => $max_input_vars_required,
         ];
      }

      if( ! empty($rows) ){

         $content .= '<table class="mt-10">';
         $content .= '<tr><th>'.__('Directive', 'integration-marktplaats-for-woocommerce').'</th><th>'.__('Current value', 'integration-marktplaats-for-woocommerce').'</th><th>'.__('Recommended value', 'integration-marktplaats-for-woocommerce').'</th></tr>';

         foreach($rows as $row){
            $content .= '<tr><td>'.$row['directive'].'</td><td>'.$row['current'].'</td><td>'.$row['recommended'].'</td></tr>';
         }

         $content .= '</table>';

         Util::alertbox()->content($content)->title($title)->error();
      }

   }



   /**
    * Defines what percentage of the memory RAM to be used.
    *
    * @param float $result
    * @return float
    */
   public static function define_memory_usage($result){

      $usage = Option::get('resource_usage', 'medium');

      switch($usage){

         case 'low': $result = 0.40; break;

         case 'medium': $result = 0.50; break;

         case 'high': $result = 0.65; break;

      }

      return $result;
   }



   /**
    * Defines what execution time (in seconds) to be used.
    *
    * @param float $result
    * @return float
    */
   public static function define_exec_time_usage($result){

      $usage = Option::get('resource_usage', 'medium');

      switch($usage){

         case 'low': $result = 20; break;

         case 'medium': $result = 35; break;

         case 'high': $result = 50; break;

      }

      return $result;
   }



   /**
    * Defines the query limit for Module Task.
    *
    * @param int $result
    * @return int
    */
   public static function define_query_task_limit($result){

      $usage = Option::get('resource_usage', 'medium');

      switch($usage){

         case 'low': $result = 500; break;

         case 'medium': $result = 1000; break;

         case 'high': $result = 1500; break;

      }

      return (int) $result;
   }



   /**
    * Defines the extra time to be added to the result of the reschedule time calculation.
    *
    * @param int $result
    * @param string $action
    * @return int
    */
   public static function define_reschedule_extra_time($result, $action){

      $usage = Option::get('resource_usage', 'medium');

      switch($usage){

         case 'low': $result = 10800; break;

         case 'medium': $result = 7200; break;

         case 'high': $result = 3600; break;

      }

      return (int) $result;
   }



   /**
    * Defines the recurrence time for checking the feed (dropshipping).
    *
    * @param int $result
    * @return int
    */
   public static function define_check_feed_recurrence($result){

      $usage = Option::get('resource_usage', 'medium');

      switch($usage){

         case 'low': $result = \DAY_IN_SECONDS * 10; break;

         case 'medium': $result = \DAY_IN_SECONDS * 5; break;

         case 'high': $result = \DAY_IN_SECONDS * 3; break;

      }

      return (int) $result;
   }
}