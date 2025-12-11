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
Module_Order_Details_Hook::init();
Module_Order_Details_Hook_AJAX::init();
Module_Order_Details_Hook_Assets::init();
