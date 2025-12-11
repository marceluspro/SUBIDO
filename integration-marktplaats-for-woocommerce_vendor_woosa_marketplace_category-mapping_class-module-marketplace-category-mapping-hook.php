<?php
/**
 * Module Marketplace Category Mapping Hook
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace_Category_Mapping_Hook implements Interface_Hook {


   /**
    * Initiates the hooks.
    *
    * @return void
    */
   public static function init(){

      add_action(PREFIX . '\category-mapping\template\top', [__CLASS__, 'render_explanation_text']);

   }



   /**
    * Displays the explanation text.
    *
    * @return void
    */
   public static function render_explanation_text() {

      ?>
      <div class="mb-20">
         <h2><?php _e('How does it work?', 'integration-marktplaats-for-woocommerce');?></h2>
         <p><?php printf(__('By connecting %1$s categories with your shop categories it means that the products from your shop categories will be published to the connected %1$s categories.', 'integration-marktplaats-for-woocommerce'), Module_Core::config('service.name')); ?></p>
      </div>
      <?php

   }


}
