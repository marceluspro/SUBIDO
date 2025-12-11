<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<div class="<?php echo PREFIX;?>-style">
   <form method="post" id="mainform" action="" enctype="multipart/form-data">
      <div class="container pt-20">
         <div class="row">
            <div class="sidebar col-md-4 col-xl-3">
               <div class="sidebar-top">
                  <div class="mobile-menu p-10">
                     <span class="dashicons dashicons-menu-alt3"></span>
                     <span class="menu-text"><?php _e('Menu', 'integration-marktplaats-for-woocommerce');?></span>
                  </div>
                  <div class="sidebar-logo" style="background-image: url(<?php echo $logo_url;?>)">
                     <div class="plugin-meta">v<?php echo VERSION;?> for <?php echo Module_Core::config('service.name');?></div>
                  </div>
               </div>
               <div class="sidebar-menu">
                  <?php foreach ($tabs as $tab): ?>
                     <div id="<?php echo sanitize_title($tab['name']); ?>-item" class="menu-item-wrap">
                        <a href="<?php echo Module_Settings::get_tab_url($tab); ?>">
                           <div class="menu-item <?php echo ($tab['slug'] === $current_tab['slug']) ? 'active': '';?>">
                              <div class="menu-item-icon"><?php echo Module_Settings::get_tab_icon($tab); ?></div>
                              <div class="menu-item-description-wrap">
                                 <div class="menu-item-name tt-u"><?php echo $tab['name']; ?></div>
                                 <div class="menu-item-description"><?php echo $tab['description']; ?></div>
                              </div>
                           </div>
                        </a>
                     </div>
                  <?php endforeach; ?>
               </div>
               <?php do_action(PREFIX . '\module\settings\page\sidebar\bottom', $current_tab);?>
            </div>
            <div id="<?php echo sanitize_title($current_tab['name']); ?>-content" class="content col-md-8 col-xl-9">
               <div class="content-header">
                  <div class="content-header-icon"><?php echo Module_Settings::get_tab_icon($current_tab); ?></div>
                  <div class="content-header-name"><?php echo $current_tab['name']; ?></div>
               </div>
               <div class="content-body">
                  <div class="page-top">
                     <?php do_action(PREFIX . '\module\settings\page\top', $current_tab); ?>
                  </div>
                  <div class="page-content">
                     <?php do_action(PREFIX . '\module\settings\page\content', $current_tab); ?>
                  </div>
                  <div class="page-bottom">
                     <?php do_action(PREFIX . '\module\settings\page\bottom', $current_tab); ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>
