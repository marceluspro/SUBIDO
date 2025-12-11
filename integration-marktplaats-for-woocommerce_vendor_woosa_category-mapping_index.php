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
Module_Category_Mapping_Hook_AJAX::init();
Module_Category_Mapping_Hook_Assets::init();
Module_Category_Mapping_Hook_Settings::init();