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
Module_Product_Column_Status_Hook::init();
Module_Product_Column_Status_Hook_AJAX::init();
Module_Product_Column_Status_Hook_Assets::init();