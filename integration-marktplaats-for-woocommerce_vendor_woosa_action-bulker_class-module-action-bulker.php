<?php
/**
 * Module Action Bulker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Action_Bulker{


   /**
    * Adds new bulk actions.
    *
    * @param array $items
    * @return $items
    */
   public static function add_bulk_actions($items) {

      foreach(self::get_actions() as $action_id => $action){
         $key = $action['id'] ?? $action_id;
         $items[$key] = $action['label'];
      }

      return $items;
   }



   /**
    * Handles the bulk actions.
    *
    * @param string $redirect_to
    * @param string $doaction
    * @param array $post_ids
    * @return string
    */
   public static function handle_bulk_actions($redirect_to, $doaction, $post_ids){

      self::perform($doaction, $post_ids);

      return $redirect_to;
   }



   /**
    * The list of post types where to add the bulk action.
    *
    * @param bool $check_post_type
    * @return array
    */
   public static function get_post_types($check_post_type = true){

      $result = [];

      foreach(self::get_actions($check_post_type) as $action_id => $action){
         $result = array_merge($result, self::get_action_post_types($action));
      }

      return array_unique($result);
   }



   /**
    * Retrieves the post types of an action.
    *
    * @param array $action
    * @return array
    */
   protected static function get_action_post_types($action){
      return array_filter((array) Util::array($action)->get('post_type'));
   }



   /**
    * List of available actions.
    *
    * [
    *    [
    *       'id'            => my_action_id,
    *       'label'         => __('Action name', 'integration-marktplaats-for-woocommerce'),
    *       'post_type'     => ['product'],
    *       'callback'      => [__CLASS__, 'action_callback'],
    *       'bulk_perform'  => true,
    *       'schedulable'   => true,
    *       'validate_item' => true,
    *    ]
    * ]
    *
    * @param bool $check_post_type
    * @return array
    */
   protected static function get_actions($check_post_type = true){

      global $post_type, $current_screen;

      $list = apply_filters(PREFIX . '\action_bulker\actions', []);

      if($check_post_type){

         if((isset($current_screen->id) && 'woocommerce_page_wc-orders' === $current_screen->id)){
            $post_type = 'shop_order';
         }

         if((isset($current_screen->id) && 'edit-category' === $current_screen->id)){
            $post_type = 'category';
         }

         if((isset($current_screen->id) && 'edit-product_cat' === $current_screen->id)){
            $post_type = 'product_cat';
         }

         foreach($list as $key => $item){
            if( ! in_array($post_type, Util::array($item)->get('post_type', [])) ){
               unset($list[$key]);
            }
         }
      }

      return $list;
   }



   /**
    * Gets an action.
    *
    * @param string $action_id
    * @return false|array
    */
   protected static function get_action($action_id){

      $list   = self::get_actions();
      $output = false;
      $key    = array_search($action_id, array_column($list, 'id'));

      if($key !== false){
         $keys        = array_keys($list);
         $originalKey = $keys[$key];
         $output      = $list[$originalKey];
      }elseif(isset($list[$action_id])){
         //backward compatibility for actions with no `id`
         $output = array_merge(['id' => $action_id], $list[$action_id]);
      }

      return $output;
   }



   /**
    * Performs a given action.
    *
    * @param string $action_id
    * @param array $items
    * @return void
    */
   protected static function perform($action_id, $items){

      $action        = self::get_action($action_id);
      $items         = array_filter((array) $items);
      $callback      = Util::array($action)->get('callback');
      $schedulable   = Util::array($action)->get('schedulable', false);
      $task          = Util::array($action)->get('task', false);
      $validate_item = Util::array($action)->get('validate_item', false);
      $bulk_perform  = Util::array($action)->get('bulk_perform', false);
      $allow         = apply_filters(PREFIX . '\action_bulker\allow_perform', true, $action);

      if($allow && $action){

         if($validate_item){

            foreach($items as $key => $item_id){

               $valid = apply_filters(PREFIX . '\action_bulker\validate_item', true, $item_id);

               if( $valid === false ) {
                  unset( $items[$key] );
               }
            }

         }

         $items = apply_filters(PREFIX . '\action_bulker\valid_items', $items, $action);

         if( empty($items) ){

            /**
             * Fires when the action is not performed due to no items available
             */
            do_action(PREFIX . '\action_bulker\no_items', $action, $items);

         }else{

            $items = array_values($items);//to reset the indexes

            if($bulk_perform){

               /**
                * Let 3rd-party to hook here before the action is actually performed
                */
               do_action(PREFIX . '\action_bulker\before_perform\items', $action, $items);

               if($task){

                  /**
                   * Let 3rd-party to create task for the action
                   */
                  do_action(PREFIX . '\action_bulker\perform_task', $action, $items);

               }elseif($schedulable){

                  /**
                   * Let 3rd-party to schedule the action
                   */
                  do_action(PREFIX . '\action_bulker\perform_schedulable', $action, $items);

               }elseif(is_callable($callback)){
                  call_user_func_array($callback, [$items, $action]);
               }

            }else{

               //loop through items
               foreach($items as $key => $item_id){

                  /**
                   * Let 3rd-party to hook here before the action is actually performed
                   */
                  do_action(PREFIX . '\action_bulker\before_perform\item', $action, $item_id);

                  if($task){

                     do_action(PREFIX . '\action_bulker\perform_task\item', $action, $item_id);

                  }elseif($schedulable){

                     do_action(PREFIX . '\action_bulker\perform_schedulable\item', $action, $item_id);

                  }elseif(is_callable($callback)){
                     call_user_func_array($callback, [$item_id, $action]);
                  }

               }
            }
         }

      }
   }

}
