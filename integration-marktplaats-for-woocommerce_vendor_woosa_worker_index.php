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
Module_Worker_Hook::init();
Module_Worker_Hook_AJAX::init();
Module_Worker_Hook_Assets::init();
Module_Worker_Hook_Settings::init();