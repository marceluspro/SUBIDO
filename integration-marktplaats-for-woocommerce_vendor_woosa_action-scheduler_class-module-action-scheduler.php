<?php
/**
 * Module Action Scheduler
 *
 * @author Woosa Team
 * @link https://actionscheduler.org/
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Action_Scheduler{


   /**
    * The type of action - `single`, `async` or `recurring`.
    *
    * @var string
    */
   protected $type = '';


   /**
    * The interval of time in seconds.
    *
    * @var integer
    */
   protected $interval = 60;


   /**
    * The recurring interval of time in seconds.
    *
    * @var integer
    */
   protected $recurring_int = 0;


   /**
    * Whether or not to perform a full check if the action exists.
    *
    * @var bool
    */
   protected $full_check = false;


   /**
    * Whether or not to allow same action multiple times.
    *
    * @var bool
    */
   protected $multiple = false;


   /**
    * Group name.
    *
    * @var string
    */
   protected $group = PREFIX . '-action';


   /**
    * Hook name.
    *
    * @var string
    */
   protected $hook = PREFIX . '_hook';


   /**
    * The calback function for the hook.
    *
    * @var array
    */
   protected $callback = [];


   /**
    * A lis of arguments that will be passed to the callback function.
    *
    * @var array
    */
   protected $args = [];



   /**
    * Sets whether or not to perform a full check if the action exists.
    *
    * @param string $bool
    * @return void
    */
   public function set_full_check(bool $bool){
      $this->full_check = $bool;
   }



   /**
    * Sets whether or not to allow same action multiple times.
    *
    * @param string $bool
    * @return void
    */
   public function set_multiple(bool $bool){
      $this->multiple = $bool;
   }



   /**
    * Sets the group name.
    *
    * @param string $value
    * @return void
    */
   public function set_group(string $value){
      $this->group = empty($value) ? $this->group : Util::prefix($value, true);
   }



   /**
    * Sets the hook name.
    *
    * @param string $value
    * @return void
    */
   public function set_hook(string $value){
      $this->hook = empty($value) ? $this->hook : Util::prefix($value);
   }




   /**
    * Sets the callback function.
    *
    * @param array $value
    * @return void
    */
   public function set_callback(array $value){
      $this->callback = $value;
   }



   /**
    * Sets the args for the callback.
    *
    * @param array $value
    * @return void
    */
   public function set_args(array $value){
      $this->args = $value;
   }



   /**
    * Saves the action in a list.
    *
    * @return void|InvalidArgumentException
    */
   public function save(){

      //we force to have only the name of the class (which should be a string) but not the instance of it
      if(is_string($this->callback[0])){

         //schedule the action per type
         switch($this->type){

            case 'single':
               as_schedule_single_action( time() + $this->interval, $this->hook, $this->args, $this->group );
               break;

            case 'async':
               as_enqueue_async_action( $this->hook, $this->args, $this->group );
               break;

            case 'recurring':

               if($this->recurring_int > 0){
                  as_schedule_recurring_action( time() + $this->recurring_int, $this->recurring_int, $this->hook, $this->args, $this->group );
               }

               break;
         }

         $list = self::get_actions();

         $list[$this->hook] = $this->callback;

         Option::set('action_scheduler', $list);

      }else{

         throw new \InvalidArgumentException('The first parameter or the callback must be the name of the class but not the instance of it!');
      }

   }



   /**
    * Schedules an action to run one time at some defined point in the future.
    *
    * @param integer $interval
    * @return void
    */
   public function set_single($interval = ''){

      if( $this->is_scheduled() ){

         if( $this->multiple ){

            $this->type     = 'single';
            $this->interval = '' === $interval ? $this->interval : $interval;

            $result = $this->find($this->hook, null, $this->group);

            //cancel found action and merge its args in a new list
            if(is_array($result)){
               \ActionScheduler::store()->cancel_action($result['id']);
               $this->args = array_merge_recursive($this->args, $result['args']);
            }

         }

      }else{

         $this->type     = 'single';
         $this->interval = '' === $interval ? $this->interval : $interval;
      }


   }



   /**
    * Schedules an action to run one time, as soon as possible.
    *
    * @return void
    */
   public function set_async(){

      if( ! $this->is_scheduled() ){
         $this->type = 'async';
      }
   }



   /**
    * Schedules an action to run repeatedly with a specified interval in seconds.
    *
    * @param integer $interval
    * @return void
    */
   public function set_recurring(int $interval){

      if( ! $this->is_scheduled() ){
         $this->type          = 'recurring';
         $this->recurring_int = $interval;
      }
   }



   /**
    * Checks whether or not an action is already scheduled.
    *
    * @return boolean
    */
   protected function is_scheduled(){
      return $this->full_check ? as_next_scheduled_action( $this->hook, $this->args, $this->group ) : as_next_scheduled_action( $this->hook );
   }



   /**
    * Check if there is an existing action in the queue with a given hook, args and group combination.
    *
    * An action in the queue could be pending, in-progress or async. If it is pending for a time in
    * future, an array with data will be returned. If it is currently being run, or an
    * async action sitting in the queue waiting to be processed, in which case boolean true will be
    * returned. Or there may be no async, in-progress or pending action for this hook, in which case,
    * boolean false will be the return value.
    *
    * @param string $hook
    * @param array $args
    * @param string $group
    *
    * @return int|array
    */
   protected function find( $hook, $args = NULL, $group = '' ) {

      if ( ! \ActionScheduler::is_initialized( __FUNCTION__ ) ) {
         return false;
      }

      $params = array();

      if ( is_array($args) ) {
         $params['args'] = $args;
      }

      if ( !empty($group) ) {
         $params['group'] = $group;
      }

      $params['status'] = \ActionScheduler_Store::STATUS_RUNNING;
      $job_id = \ActionScheduler::store()->find_action( $hook, $params );

      if ( ! empty( $job_id ) ) {
         return true;
      }

      $params['status'] = \ActionScheduler_Store::STATUS_PENDING;
      $job_id = \ActionScheduler::store()->find_action( $hook, $params );

      if ( empty($job_id) ) {
         return false;
      }

      $job = \ActionScheduler::store()->fetch_action( $job_id );
      $scheduled_date = $job->get_schedule()->get_date();

      if ( $scheduled_date ) {

         return [
            'id'    => $job_id,
            'hook'  => $job->get_hook(),
            'args'  => $job->get_args(),
            'group' => $job->get_group(),
         ];

      } elseif ( NULL === $scheduled_date ) { // pending async action with NullSchedule
         return true;
      }

      return false;
   }



   /**
    * Gets the list of actions.
    *
    * @return array
    */
   public static function get_actions(){
      return array_filter((array) Option::get('action_scheduler'));
   }



   /**
    * Unschedule all saved actions.
    *
    * @return void
    */
   public static function unschedule_actions() {

      foreach ( self::get_actions() as $hook => $callback ) {
         as_unschedule_all_actions( $hook );
      }

      //remove the list of actions as well
      Option::delete('action_scheduler');
   }



   /**
    * Cancel all occurrences of a scheduled action.
    *
    * @return void
    */
   public function unschedule(){

      as_unschedule_all_actions($this->hook, $this->args, $this->group);

      $list = self::get_actions();

      if(isset($list[$this->hook])){

         unset($list[$this->hook]);

         Option::set('action_scheduler', $list);

      }
   }

}