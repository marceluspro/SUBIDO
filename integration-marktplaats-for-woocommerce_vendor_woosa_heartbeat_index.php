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
Module_Heartbeat_Hook::init();
Module_Heartbeat_Hook_AJAX::init();
Module_Heartbeat_Hook_Assets::init();
Module_Heartbeat_Hook_Settings::init();