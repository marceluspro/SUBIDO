<?php
/**
 * Module Marketplace Action Bulker
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Action_Bulker{


   /**
    * The list of actions.
    *
    * @return array
    */
   public static function get_list(){

      return apply_filters(PREFIX . '\module\marketplace\action_bulker\list', [
         [
            'id' => PREFIX . '_create_or_update_product',
            'label'     => sprintf(__('%s: Publish / Update', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
            'post_type' => ['product'],
            'task'      => [
               'source' => 'shop',
               'target' => 'service'
            ]
         ],
         [
            'id' => PREFIX . '_pause_or_unpause_product',
            'label'     => sprintf(__('%s: Pause / Unpause', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
            'post_type' => ['product'],
            'task'      => [
               'source' => 'shop',
               'target' => 'service'
            ]
         ],
         [
            'id' => PREFIX . '_delete_or_trash_product',
            'label'     => sprintf(__('%s: Delete', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')),
            'post_type' => ['product'],
            'task'      => [
               'source' => 'shop',
               'target' => 'service'
            ]
         ],
      ]);

   }



   /**
    * Retrieves a specific action.
    *
    * @param string $id
    * @return array|null
    */
   public static function get_action($id){

      $list = self::get_list();
      $key  = array_search($id, array_column($list, 'id'));

      return Util::array($list)->get($key);
   }



   /**
    * Processes the action for the given product.
    *
    * @param string|int $product_id
    * @param array $action
    * @throws \Exception
    * @return void
    */
   public static function run_for_product($product_id, $action){

      $post_type = Util::array($action)->get('post_type', [get_post_type($product_id)]);

      if( array_intersect($post_type, ['product', 'product_variation']) ){

         $product = wc_get_product($product_id);

         if('variable' === $product->get_type()){

            foreach($product->get_available_variations() as $variation){

               $meta       = new Module_Meta($variation['variation_id']);
               $attributes = array_filter((array) Util::array($variation)->get('attributes'));

               if(empty($attributes)){

                  $meta->delete_errors();
                  $meta->set_status('error');
                  $meta->set_error(sprintf(__('It seems this variation (#%s) does not have an attribute value assigned, therefore we cannot process it. Make sure an attribute value is assigned correctly to this variation!', 'integration-marktplaats-for-woocommerce'), $variation['variation_id']));
                  $meta->save();

               }else{
                  $meta->set('marketplace:create_task_via_bulk_action', 'yes');//set a flag to know that a bulk action has been applied
                  $meta->save();

                  Module_Product_Task_Util::create_task($variation['variation_id'], Util::unprefix($action['id']));

                  $meta->delete('marketplace:create_task_via_bulk_action', 'yes');//remove the flag
                  $meta->save();
               }

            }

         }else{

            $meta = new Module_Meta($product_id);
            $meta->set('marketplace:create_task_via_bulk_action', 'yes');//set a flag to know that a bulk action has been applied
            $meta->save();

            Module_Product_Task_Util::create_task($product_id, Util::unprefix($action['id']));

            $meta->delete('marketplace:create_task_via_bulk_action', 'yes');//remove the flag
            $meta->save();
         }
      }
   }
}