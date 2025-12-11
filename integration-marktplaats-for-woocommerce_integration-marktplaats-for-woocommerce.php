<?php
/**
 * Plugin Name: Integration Marktplaats for WooCommerce
 * Plugin URI: https://www.woosa.com/woocommerce-marketplace-plugin/marktplaats/
 * Description: Connects WooCommerce with Marktplaats platform.
 * Version: 2.0.2
 * Author: WSA Team
 * Author URI: https://marktplaats.nl/
 * Text Domain: integration-marktplaats-for-woocommerce
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * WC requires at least: 3.5.0
 * WC tested up to: 5.6.0
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


define(__NAMESPACE__ . '\PREFIX', 'mkt');

define(__NAMESPACE__ . '\VERSION', '2.0.2');

define(__NAMESPACE__ . '\DIR_URL', untrailingslashit(plugin_dir_url(__FILE__)));

define(__NAMESPACE__ . '\DIR_PATH', untrailingslashit(plugin_dir_path(__FILE__)));

define(__NAMESPACE__ . '\DIR_NAME', plugin_basename(DIR_PATH));

define(__NAMESPACE__ . '\DIR_BASENAME', DIR_NAME . '/'.basename(__FILE__));

define(__NAMESPACE__ . '\DEBUG', get_option(PREFIX . '_debug') === 'yes' ? true:false);

define(__NAMESPACE__ . '\DEBUG_FILE', DIR_PATH . '/debug.log');


//include files
require_once DIR_PATH . '/vendor/autoload.php';

//init
Module_Core_Hook::init();