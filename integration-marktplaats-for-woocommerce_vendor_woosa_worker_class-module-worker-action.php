<?php
/**
 * Module Worker Action
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Worker_Action{


   /**
    * Id of the action
    *
    * @var string
    */
   protected $id = '';


   /**
    * Priority of the action
    *
    * @var string
    */
   protected $priority = '';


   /**
    * Recurrence of the action
    *
    * @var string
    */
   protected $recurrence = '';


   /**
    * Callback of the action
    *
    * @var array
    */
   protected $callback = [];


   /**
    * Context of the action
    *
    * @var string
    */
   protected $context = '';



   /**
    * Construct of this class
    *
    * @param array $action
    */
   public function __construct(array $action){

      $this->id         = Util::array($action)->get('id');
      $this->priority   = Util::array($action)->get('priority');
      $this->recurrence = Util::array($action)->get('recurrence');
      $this->callback   = Util::array($action)->get('callback');
      $this->context    = Util::array($action)->get('context');
   }



   /**
    * Gets action id.
    *
    * @return string
    */
   public function get_id(){
      return $this->id;
   }



   /**
    * Gets action priority.
    *
    * @return string
    */
   public function get_priority(){
      return $this->priority;
   }



   /**
    * Gets action recurrence.
    *
    * @return string
    */
   public function get_recurrence(){
      return $this->recurrence;
   }



   /**
    * Gets action callback.
    *
    * @return array
    */
   public function get_callback(){
      return $this->callback;
   }



   /**
    * Gets action context.
    *
    * @return string
    */
   public function get_context(){
      return $this->context;
   }



   /**
    * Gets action status.
    *
    * @return string
    */
   public function get_status(){

      $status = $this->get_prop('status', 'active');

      return $status;
   }



   /**
    * Retrieves an action property value.
    *
    * @param string $prop
    * @param mixed $default
    * @return mixed
    */
   public function get_prop(string $prop, $default = false){
      return Option::get("action:{$this->id}:{$prop}", $default);
   }



   /**
    * Sets an action property value.
    *
    * @param string $prop
    * @param mixed $value
    * @return void
    */
   public function set_prop(string $prop, $value){
      Option::set("action:{$this->id}:{$prop}", $value);
   }



   /**
    * Deletes an action property.
    *
    * @param string $prop
    * @return void
    */
   public function delete_prop(string $prop){
      Option::delete("action:{$this->id}:{$prop}");
   }



   /**
    * Marks an action as scheduled if it has recurrence.
    *
    * @return void
    */
   public function maybe_schedule(){

      if( ! empty($this->recurrence) ){

         $reschedule = Option::get("action:{$this->id}:reschedule");

         if($reschedule){

            Transient::set("action:{$this->id}:scheduled", true, $this->recurrence);

            // Only mimic _transient_timeout_* if object cache is active
            if ( wp_using_ext_object_cache() ) {
               update_option("_transient_timeout_" . Util::prefix("action:{$this->id}:scheduled"), time() + $this->recurrence);
            }

            //remove the flag
            Option::delete("action:{$this->id}:reschedule");
         }
      }
   }



   /**
    * Checks whether or not the action is cheduled.
    *
    * @return boolean
    */
   public function is_scheduled(){
      return Transient::get("action:{$this->id}:scheduled");
   }



   /**
    * Checks whether or not the action is active.
    *
    * @return bool
    */
   public function is_active(){
      return 'active' === $this->get_status();
   }



   /**
    * Checks whether or not the action is inactive.
    *
    * @return bool
    */
   public function is_inactive(){
      return ! $this->is_active();
   }



   /**
    * Retrieves the next time when the action is available to be performed.
    *
    * @return int
    */
   public function get_next_run(){
      return Transient::get_expire_time("action:{$this->id}:scheduled");
   }



   /**
    * Displays the next run time.
    *
    * @return void
    */
   public function render_next_run_time(){

      $next_run = $this->get_next_run();
      $next_run = $next_run > time() ? human_time_diff( time(), $next_run ) : __( 'queue...', 'integration-marktplaats-for-woocommerce' );
      $output = empty($this->recurrence) ? '-' : $next_run;

      if( ! empty($this->recurrence) && Module_Worker::get_current_action() === $this->get_id()){
         $output = __( 'now...', 'integration-marktplaats-for-woocommerce' );
      }

      echo $output;
   }



   /**
    * Displays the recurrence time.
    *
    * @return void
    */
   public function render_recurrence_time(){

      $minutes = empty($this->recurrence) ? 0 : $this->recurrence / 60;

      if ($minutes >= 1440) { // 1440 minutes = 24 hours
         $days = $minutes / 1440;
         $rec_text = sprintf(_n('Every %s day', 'Every %s days', $days, 'integration-marktplaats-for-woocommerce'), $days);
      } elseif ($minutes >= 60) {
         $hours = $minutes / 60;
         $rec_text = sprintf(_n('Every %s hour', 'Every %s hours', $hours, 'integration-marktplaats-for-woocommerce'), $hours);
      } else {
         $rec_text = sprintf(_n('Every %s minute', 'Every %s minutes', $minutes, 'integration-marktplaats-for-woocommerce'), $minutes);
      }

      echo empty($this->recurrence) ? '-' : $rec_text;
   }



   /**
    * Counts the tasks for the given action.
    *
    * @param string $mode - active|rescheduled
    * @return int
    */
   public function count_tasks($mode = ''){

      global $wpdb;

      $date_now = date('Y-m-d H:i:s');
      $where    = "action = '{$this->id}'";

      if('active' === $mode){

         $where = "action = '{$this->id}' AND (next_process_at IS NULL OR next_process_at < '{$date_now}')";

      }elseif('rescheduled' === $mode){

         $where = "action = '{$this->id}' AND next_process_at > '{$date_now}'";
      }

      $sql    = sprintf("SELECT COUNT(id) FROM %s WHERE %s ", Module_Task::get_table_name(), $where);
      $result = $wpdb->get_var($sql);

      return $result;

   }



   /**
    * Counts the tasks per target for the given action.
    *
    * @param string $target
    * @return int
    */
   public function count_target_tasks($target = ''){

      global $wpdb;

      $sql    = sprintf("SELECT COUNT(id) FROM %s WHERE action = '%s' AND target = '%s' ", Module_Task::get_table_name(), $this->id, $target);
      $result = $wpdb->get_var($sql);

      return $result;

   }

}