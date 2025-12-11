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
Module_Change_Tracker_Hook_Order::init();
Module_Change_Tracker_Hook_Product::init();
Module_Change_Tracker_Hook_User::init();