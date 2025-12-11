<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


$fields = [];

$mfg = new Module_Field_Generator;
$mfg->set_fields($fields);
$mfg->render();
?>