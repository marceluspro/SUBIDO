<?php
/**
 * Index
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


//init
Module_Product_Data_Tab_Hook::init();
Module_Product_Data_Tab_Hook_AJAX::init();
Module_Product_Data_Tab_Hook_Assets::init();