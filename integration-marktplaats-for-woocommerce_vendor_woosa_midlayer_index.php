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
Module_Midlayer_Hook::init();
Module_Midlayer_Hook_AJAX::init();
Module_Midlayer_Hook_Assets::init();
Module_Midlayer_Hook_Authorization::init();
Module_Midlayer_Hook_Core::init();