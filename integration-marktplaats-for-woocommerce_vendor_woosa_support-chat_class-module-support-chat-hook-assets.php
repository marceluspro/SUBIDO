<?php
/**
 * Module Support Chat Hook Assets
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Support_Chat_Hook_Assets implements Interface_Hook_Assets{


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action('admin_enqueue_scripts', [__CLASS__, 'admin_assets']);

   }



   /**
    * Enqueues public CSS/JS files.
    *
    * @return void
    */
   public static function public_assets(){}



   /**
    * Enqueues admin CSS/JS files.
    *
    * @return void
    */
   public static function admin_assets(){

      $screen = get_current_screen();
      $settings_page_slug = Module_Settings::get_page_slug();

      if (empty($screen)) {
         return;
      }

      if(
         (
            in_array($screen->id, ['toplevel_page_' . $settings_page_slug, 'bol_invoice_page_bol-settings'])
         ) ||
         (//this is DEPRECATED since Settings v2
            $screen->id === "woocommerce_page_wc-settings"
            && Util::array($_GET)->get('tab') === $settings_page_slug
         )
      ) {

         Util::enqueue_scripts([
            [
               'name' => 'module-support-chat',
               'js' => [
                  'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/js/',
                  'dependency' => ['jquery'],
                  'localize' => [
                     'appId' => Module_Support_Chat::get_app_id(),
                     'userName' => Module_Support_Chat::get_user_name(),
                     'userEmail' => Module_Support_Chat::get_user_email(),
                  ],
               ],
            ],
         ]);

      }

   }
}
