<?php
/**
 * Module Order Column Status Hook AJAX
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Order_Column_Status_Hook_AJAX implements Interface_Hook{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('wp_ajax_' . PREFIX . '_render_order_table_column_status', [__CLASS__, 'handle_table_column_status']);

   }



   /**
    * Processes the request to render the status on order table column.
    *
    * @return string
    */
   public static function handle_table_column_status(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $wc_order_id = Util::array($_POST)->get('args/order_id');
      $column_name = Util::array($_POST)->get('args/column_name');

      $order_id      = '';
      $meta          = new Module_Meta($wc_order_id);
      $shipment      = $meta->get('shipment');
      $tracking_url  = Util::array($shipment)->get('tracking_url');
      $tracking_code = Util::array($shipment)->get('tracking_code');

      foreach(Module_Meta_Util::order_id_meta() as $key){
         if( ! $meta->is_empty($key) ){
            $order_id = $meta->get($key);
            break;
         }
      }

      ob_start();

      if( empty($order_id) ){

         $meta->display_status();

      }else{

         $fulfill_icon = '';
         $fulfill_by   = $meta->get('fulfill_by');

         //backward compatibility - Bol
         $lvb = $meta->get('_' . PREFIX . '_LVB', true, false);

         if('yes' === $lvb){
            $fulfill_by = Module_Core::config('service.name');
         }

         if($meta->get_status() !== 'not_available'){
            $meta->display_status();
         }

         if( $fulfill_by == Module_Core::config('service.name') ){
            $fulfill_icon = '<span class="woocommerce-help-tip icon-tip-info" data-tip="' . sprintf(__('This order contains items fulfilled by %s', 'integration-marktplaats-for-woocommerce'), $fulfill_by) . '"></span>';
         }

         echo '<div style="line-height: 16px;"><b>' . __('Order id:', 'integration-marktplaats-for-woocommerce') . '</b> ' . $order_id . ' '. $fulfill_icon . '</div>';
      }

      if( ! empty($tracking_code) ){

         if(Util::is_valid_url($tracking_url)){
            echo '<div style="line-height: 16px;"><b>' . __('Tracking code:', 'integration-marktplaats-for-woocommerce') . '</b> <a href="'.$tracking_url.'" target="_blank">'.$tracking_code.'</a></div>';
         }else{
            echo '<div style="line-height: 16px;"><b>' . __('Tracking code:', 'integration-marktplaats-for-woocommerce') . '</b> '.$tracking_code.'</div>';
         }
      }

      /**
       * Let 3rd-parties to display extra info
       */
      do_action(PREFIX . '\module\order_column_status\column_output', $meta, $column_name);

      $output = ob_get_clean();

      wp_send_json_success([
         'template' => $output
      ]);
   }
}