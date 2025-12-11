<?php
/**
 * Module Settings Hook Assets
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Settings_Hook_Assets implements Interface_Hook_Assets{


   /**
    * Initiates.
    *
    * @return void
    */
   public static function init(){

      add_action('admin_enqueue_scripts', [__CLASS__ , 'admin_assets']);
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

      $enqueue = apply_filters(PREFIX . '\module\settings\enqueue_admin_assets', (Module_Settings::get_page_slug() === Util::array($_GET)->get('page') || Module_Settings::get_page_slug() === Util::array($_GET)->get('tab')), $_GET);

      if( ! $enqueue){
         return;
      }

      Util::enqueue_scripts([
         [
            'name' => 'module-settings',
            'css' => [
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/css/',
            ],
            'js' => [
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/js/',
               'dependency' => [PREFIX . '-module-core', PREFIX . '-jquery.tipTip.min']
            ],
         ],
         [
            'css' => [
               'name' => 'jquery.tipTip',
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/css/',
            ],
            'js' => [
               'name' => 'jquery.tipTip.min',
               'path' => untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/js/',
               'dependency' => ['jquery']
            ],
         ],
      ]);

      $primary = Module_Core::config('settings.page.colors.primary');
      $secondary = Module_Core::config('settings.page.colors.secondary');

      $primary_dark = Util::darken_color($primary, 1, 20);
      $secondary_dark = Util::darken_color($secondary, 1, 20);

      $btn = $primary;
      $btn_intercative = Util::darken_color($btn, 15);

      $btn_primary = $secondary;
      $btn_primary_intercative = Util::darken_color($btn_primary, 15);

      $style = "
         .mkt-style a{
            color: {$primary};
         }
         .mkt-style a:hover,
         .mkt-style a:focus{
            color: {$secondary};
         }

         .mkt-style .sidebar-logo .plugin-meta{
            color: {$primary};
         }

         .mkt-style .content-header-icon,
         .mkt-style .menu-item-icon:before {
            background-color: {$primary_dark}
         }

         .mkt-style .menu-item:hover .menu-item-icon:before,
         .mkt-style .menu-item.active .menu-item-icon:before {
            background-color: {$secondary_dark}
         }

         .mkt-style .content-header-icon svg,
         .mkt-style .menu-item-icon svg {
            fill: {$primary};
         }

         .mkt-style .menu-item:hover .menu-item-icon svg,
         .mkt-style .menu-item.active .menu-item-icon svg{
            fill: {$secondary};
         }

         .mkt-style .content-body h2 {
            color: {$primary};
         }

         .mkt-style .menu-item:hover,
         .mkt-style .menu-item.active {
            border-left-color: {$primary};
         }
         .mkt-style .menu-item.active {
            color: {$secondary};
         }

         .mkt-style .collapsible-header {
            background: {$primary};
         }

         .mkt-style .page-content input[type=text]:focus,
         .mkt-style .page-content input[type=number]:focus,
         .mkt-style .page-content input[type=password]:focus,
         .mkt-style .page-content input[type=url]:focus,
         .mkt-style .page-content textarea:focus,
         .mkt-style .page-content select:focus{
            box-shadow: 0 0 0 1px {$primary};
         }

         .mkt-style .checkbox:checked + .switch {
            background-color: {$primary};
         }

         .mkt-style .button {
            color: {$btn};
            border-color: {$btn};
         }
         .mkt-style .button:hover,
         .mkt-style .button:active,
         .mkt-style .button:focus {
            background: {$btn_intercative};
            border-color: {$btn_intercative};
         }

         .mkt-style .button-primary {
            background: {$btn_primary};
            border-color: {$btn_primary};
         }
         .mkt-style .button-primary:hover,
         .mkt-style .button-primary:active,
         .mkt-style .button-primary:focus {
            background: {$btn_primary_intercative};
            border-color: {$btn_primary_intercative};
         }

         .mkt-style .button-link:hover,
         .mkt-style .button-link:active,
         .mkt-style .button-link:focus {
            color: {$btn_primary_intercative};
         }

         .irs--round .irs-handle{
            border-color: {$primary} !important;
         }
         .irs--round .irs-single:before{
            border-top-color: {$primary} !important;
         }
         .irs--round .irs-bar,
         .irs--round .irs-from,
         .irs--round .irs-to,
         .irs--round .irs-single{
            background-color: {$primary} !important;
         }
      ";

      wp_add_inline_style(PREFIX . '-module-settings', Util::minify_css($style));
   }
}
