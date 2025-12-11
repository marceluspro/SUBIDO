<?php
/**
 * Module Task
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Task  implements Interface_DB_Table{


   /**
    * Retrieves the database table name.
    *
    * @return string
    */
   public static function get_table_name() {

      global $wpdb;

      return $wpdb->prefix . Util::prefix('tasks');
   }



   /**
    * Creates the database table if not exists.
    *
    * @return void
    */
   public static function create_table(){

      $table_name = self::get_table_name();

      if( ! Util_DB_Table::is_created($table_name) ){

         Util_DB_Table::create($table_name, "
            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            action varchar(191) NULL,
            source varchar(191) NULL,
            target varchar(191) NULL,
            resource_id varchar(191) NULL,
            payload longtext NULL,
            priority int(4) NOT NULL DEFAULT '10',
            next_process_at timestamp NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            serial_key varchar(191) NOT NULL UNIQUE
         ");
      }
   }



   /**
    * Deletes the database table.
    *
    * @return void
    */
   public static function delete_table(){
      Util_DB_Table::delete( self::get_table_name() );
   }



   /**
    * Retrieves multiple table entries.
    *
    * @param array $args
    * [
    *    'ids'          => [11,344],
    *    'actions'      => ['create_product', 'update_product'],
    *    'sources'      => ['shop', 'shop'],
    *    'targets'      => ['service', 'shop'],
    *    'resource_ids' => [1234, 56565],
    * ]
    * @return array
    */
   public static function get_entries(array $args){

      global $wpdb;

      $offset  = 0;
      $where   = '';
      $results = [];

      if(! empty($args['ids'])){

         $ids = implode("','", $args['ids']);
         $where .= " id IN ('{$ids}')";
      }

      if( ! empty($args['actions'])){

         $actions = implode("','", $args['actions']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} action IN ('{$actions}')";
      }

      if( ! empty($args['sources'])){

         $sources = implode("','", $args['sources']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} source IN ('{$sources}')";
      }

      if( ! empty($args['targets'])){

         $targets = implode("','", $args['targets']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} target IN ('{$targets}')";
      }

      if( ! empty($args['resource_ids'])){

         $resource_ids = implode("','", $args['resource_ids']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} resource_id IN ('{$resource_ids}')";
      }

      $date_now = date('Y-m-d H:i:s');
      $and      = empty($where) ? '' : ' AND';

      $where .= "{$and} (next_process_at IS NULL OR next_process_at < '{$date_now}')";

      if( ! empty($where) ){

         $sql     = sprintf("SELECT * FROM %s WHERE %s ORDER BY `priority` ASC , `created_at` ASC LIMIT %s, %s", self::get_table_name(), $where, $offset, self::get_query_limit());
         $results = $wpdb->get_results($sql, 'ARRAY_A');
      }

      return $results;

   }



   /**
    * Retrieves a single table entry.
    *
    * @param int $id
    * @return array|null
    */
   public static function get_entry($id){

      global $wpdb;

      $sql = sprintf("SELECT * FROM %s WHERE id = $id", self::get_table_name());
      $result = $wpdb->get_row($sql, 'ARRAY_A');

      return $result;
   }



   /**
    * Creates multiple new table entries.
    *
    * @param array $args
    * @return void
    */
   public static function create_entries(array $args){}



   /**
    * Creates a single table entry.
    *
    * @param array $args
    * @return void
    */
   public static function create_entry(array $args){}



   /**
    * Updates multiple table entries. If they do not exist then it will create them.
    *
    * @param array $args
    * [
    *    [
    *       'action'          => 'test_action',
    *       'source'          => 'shop',
    *       'target'          => 'service',
    *       'payload'         => '',
    *       'resource_id'     => '123',
    *       'priority'        => '10',
    *       'next_process_at' => null, //or date format: `Y-m-d H:i:s` or integer values: 3600 (1hour), 1800 (30min), etc
    *    ]
    * ]
    * @return void
    */
   public static function update_entries(array $args){

      global $wpdb;

      $action_list   = [];
      $prep_values   = [];
      $column_values = [];
      $slice         = self::get_query_limit();
      $chunks        = array_chunk(array_filter($args), $slice);

      foreach($chunks as $tasks){

         foreach($tasks as $task){

            $action          = Util::array($task)->get('action');
            $source          = Util::array($task)->get('source', null);
            $target          = Util::array($task)->get('target', null);
            $resource_id     = Util::array($task)->get('resource_id', null);
            $payload         = self::encode_payload(Util::array($task)->get('payload'));
            $priority        = Util::array($task)->get('priority', 10);
            $next_process_at = null;
            $created_at      = date('Y-m-d H:i:s', time());
            $serial_key      = self::generate_serial_key($task);
            $action_list[]   = $action;

            if(array_key_exists('next_process_at', $task)){
               $next_process_at = is_int($task['next_process_at']) ? self::calculate_next_process_at($task['next_process_at'], $task) : $next_process_at;
            }

            array_push($column_values, $action, $source, $target, $resource_id, $payload, $priority, $next_process_at, $created_at, $serial_key);

            $prep_values[] = '(%s, %s, %s, %s, %s, %s, %s, %s, %s)';
         }

         $sql = sprintf("INSERT INTO %s (action, source, target, resource_id, payload, priority, next_process_at, created_at, serial_key)
            VALUES %s
            ON DUPLICATE KEY UPDATE
               action          = VALUES (action),
               source          = VALUES (source),
               target          = VALUES (target),
               resource_id     = VALUES (resource_id),
               payload         = VALUES (payload),
               priority        = VALUES (priority),
               next_process_at = VALUES (next_process_at),
               serial_key      = VALUES (serial_key)
         ", self::get_table_name(), implode(',', $prep_values));

         $result = $wpdb->query($wpdb->prepare($sql, $column_values));

         if($result === false){

            Util::wc_error_log([
               'title' => '==== TASK - UPDATE/CREATE ENTRIES ====',
               'message' => 'The query was not performed successfully.',
               'detail' => [
                  'error' => $wpdb->last_error,
                  'query' => $wpdb->last_query,
               ]
            ], __FILE__, __LINE__);

         }else{

            /**
             * Let 3rd-party to hook on the query result
             * @since 1.0.0
             */
            do_action(PREFIX . '\task\update_entries\processed', $result, $tasks);

            self::delete_counted_action_tasks($action_list);

         }
      }

   }



   /**
    * Updates a single table entry.
    *
    * @param int $id
    * @param array $args
    * [
    *    'action'          => 'test_action',
    *    'source'          => 'shop',
    *    'target'          => 'service',
    *    'payload'         => '',
    *    'resource_id'     => '123',
    *    'priority'        => '10',
    *    'next_process_at' => null, //or date format: `Y-m-d H:i:s` or integer values: 3600 (1hour), 1800 (30min), etc
    * ]
    * @return void
    */
   public static function update_entry($id, array $args){

      global $wpdb;

      $columns = [];

      if(array_key_exists('action', $args)){
         $columns['action'] = $args['action'];
      }

      if(array_key_exists('source', $args)){
         $columns['source'] = $args['source'];
      }

      if(array_key_exists('target', $args)){
         $columns['target'] = $args['target'];
      }

      if(array_key_exists('resource_id', $args)){
         $columns['resource_id'] = $args['resource_id'];
      }

      if(array_key_exists('payload', $args)){
         $columns['payload'] = self::encode_payload($args['payload']);
      }

      if(array_key_exists('priority', $args)){
         $columns['priority'] = $args['priority'];
      }

      if(array_key_exists('next_process_at', $args)){
         $columns['next_process_at'] = strtotime($args['next_process_at']) ? $args['next_process_at'] : date('Y-m-d H:i:s', time() + (int) $args['next_process_at']);
      }

      if( ! empty($columns) ){

         $result = $wpdb->update(
            self::get_table_name(),
            $columns,
            [
               'id' => $id
            ]
         );

         if($result === false){

            Util::wc_error_log([
               'title' => '==== TASK - UPDATE ENTRY ====',
               'message' => 'The query was not performed successfully.',
               'detail' => [
                  'error' => $wpdb->last_error,
                  'query' => $wpdb->last_query,
               ]
            ], __FILE__, __LINE__);
         }
      }
   }



   /**
    * Deletes multiple table entries.
    *
    * @param array $args
    * [
    *    'ids'          => [11,344],
    *    'actions'      => ['create_product', 'update_product'],
    *    'sources'      => ['shop', 'shop'],
    *    'targets'      => ['service', 'shop'],
    *    'resource_ids' => [1234, 56565],
    * ]
    * @return int
    */
   public static function delete_entries(array $args){

      global $wpdb;

      $result = 0;
      $where  = '';

      if(! empty($args['ids'])){

         $ids = implode("','", $args['ids']);
         $where .= " id IN ('{$ids}')";
      }

      if( ! empty($args['actions'])){

         $actions = implode("','", $args['actions']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} action IN ('{$actions}')";
      }

      if( ! empty($args['sources'])){

         $sources = implode("','", $args['sources']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} source IN ('{$sources}')";
      }

      if( ! empty($args['targets'])){

         $targets = implode("','", $args['targets']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} target IN ('{$targets}')";
      }

      if(! empty($args['resource_ids'])){

         $resource_ids = implode("','", $args['resource_ids']);
         $and = empty($where) ? '' : ' AND';

         $where .= "{$and} resource_id IN ('{$resource_ids}')";
      }

      if( ! empty($where) ){

         $result = $wpdb->query(sprintf("DELETE FROM %s WHERE %s LIMIT %s", self::get_table_name(), $where, self::get_query_limit()));

         if($result === false){

            Util::wc_error_log([
               'title' => '==== TASK - DELETE TABLE ENTRIES ====',
               'message' => 'The query was not performed successfully.',
               'detail' => [
                  'error' => $wpdb->last_error,
                  'query' => $wpdb->last_query,
               ]
            ], __FILE__, __LINE__);

         }

      }

      return $result;
   }



   /**
    * Deletes entries that are old.
    *
    * @return int
    */
   public static function delete_old_entries(){

      global $wpdb;

      $result = 0;
      $days   = apply_filters(PREFIX . '\task\delete_old_entries\days', 30);
      $result = $wpdb->query( sprintf("DELETE FROM %s WHERE created_at < (NOW() - INTERVAL %s DAY) LIMIT %s", self::get_table_name(), $days, self::get_query_limit()) );

      if($result === false){

         Util::wc_error_log([
            'title' => '==== TASK - CLEAR OLD ENTRIES ====',
            'message' => 'The query was not performed successfully.',
            'detail' => [
               'error' => $wpdb->last_error,
               'query' => $wpdb->last_query,
            ]
         ], __FILE__, __LINE__);

      }

      return $result;
   }



   /**
    * Deletes a single table entry.
    *
    * @param int $id
    * @return void
    */
   public static function delete_entry($id){

      global $wpdb;

      $result = $wpdb->delete(
         self::get_table_name(),
         [
            'id' => $id
         ]
      );

      if($result === false){

         Util::wc_error_log([
            'title' => '==== TASK - DELETE TASK ====',
            'message' => 'The query was not performed successfully.',
            'detail' => [
               'error' => $wpdb->last_error,
               'query' => $wpdb->last_query,
            ]
         ], __FILE__, __LINE__);
      }
   }



   /**
    * Gets the query limit.
    */
   private static function get_query_limit(){
      return apply_filters(PREFIX . '\task\query_limit', 1000);
   }



   /**
    * Generates a unique key based on the given data.
    *
    * @param array $args
    * @return string
    */
   private static function generate_serial_key(array $args){

      $data = [
         'action'      => Util::array($args)->get('action'),
         'source'      => Util::array($args)->get('source'),
         'target'      => Util::array($args)->get('target'),
         'resource_id' => (string) Util::array($args)->get('resource_id'),//make sure this is always a string
      ];

      return base64_encode(hash_hmac('sha256', json_encode($data), Util::prefix('woosa'), true));
   }



   /**
    * Calculates the next process time and rounds it to the closest interval based on the delay value.
    * Example: delay = 10 minutes - current time = 12:13 - next process will be 12:30
    *
    * @param int $delay - the amount in seconds to retry a task
    * @param array $task
    * @return string
    */
   public static function calculate_next_process_at(int $delay, array $task){

      $seconds = apply_filters(PREFIX . '\task\calculate_next_process_at\seconds', $delay, $task);

      if($seconds <= 0){
         $seconds = 60;
      }

      $result = date("Y-m-d H:i:s", ceil( (time() + $seconds) / $seconds) * $seconds);

      return $result;
   }



   /**
    * Calculates the reschedule interval of time based on the action total tasks.
    *
    * @param string $action
    * @return int
    */
   public static function calculate_reschedule_time($action){

      $process_single_task = 0.1;
      $current_tasks       = self::count_action_tasks($action);
      $extra_time          = apply_filters(PREFIX . '\task\calculate_reschedule_time\extra_time', 7200, $action);

      $interval = ceil(($current_tasks * $process_single_task) / 1 + $extra_time);

      return (int) $interval;
   }



   /**
    * Counts the tasks of the given action.
    *
    * @param string $action
    * @return int
    */
   public static function count_action_tasks(string $action){

      global $wpdb;

      $result = Transient::get("action:{$action}:total_tasks");

      if( $result == ''){

         $sql    = sprintf("SELECT COUNT(id) FROM %s WHERE action = '{$action}'", Module_Task::get_table_name());
         $result = $wpdb->get_var($sql);

         Transient::set("action:{$action}:total_tasks", $result, HOUR_IN_SECONDS);
      }

      return (int) $result;

   }



   /**
    * Deletes the counted action tasks.
    *
    * @param string|array $action
    * @return void
    */
   public static function delete_counted_action_tasks($action){

      global $wpdb;

      $keys    = [];
      $actions = array_filter( (array) $action);

      foreach($actions as $action){
         $keys = array_merge($keys, [
            '_transient_' . Util::prefix("action:{$action}:total_tasks"),
            '_transient_timeout_' . Util::prefix("action:{$action}:total_tasks"),
         ]);
      }

      $keys = implode("','", array_unique($keys));

      $result = $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE `option_name` IN ('{$keys}')");

      if($result === false){

         Util::wc_error_log([
            'title' => '==== TASK - DELETE TOTAL COUNTED ACTION TASKS ====',
            'message' => 'The query was not performed successfully.',
            'detail' => [
               'error' => $wpdb->last_error,
               'query' => $wpdb->last_query,
            ]
         ], __FILE__, __LINE__);
      }

   }



   /**
    * Encodes the valid payload as JSON.
    *
    * @param mixed $payload
    * @return string
    */
   public static function encode_payload($payload){
      return is_array($payload) || is_object($payload) ? wp_json_encode($payload) : $payload;
   }



   /**
    * Transforms the payload to an array.
    *
    * @param string $payload
    * @return array
    */
   public static function decode_payload($payload){
      return array_filter( (array) Util::obj_to_arr(
         Util::maybe_decode_json(
            maybe_unserialize($payload)
         )
      ));
   }

}