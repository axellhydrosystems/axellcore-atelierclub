<?php
/**
 * Plugin Name:       Axellcore — Atelier Club
 * Plugin URI:        https://axell.com.br/
 * Description:       Self-contained landing page (FSE template + core blocks + a custom application-form block) for the Atelier Axell Club invite program.
 * Version:           0.1.1
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Axell
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       axellcore-atelierclub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AXELLCORE_ATELIERCLUB_VERSION', '0.1.1' );
define( 'AXELLCORE_ATELIERCLUB_FILE', __FILE__ );
define( 'AXELLCORE_ATELIERCLUB_PATH', plugin_dir_path( __FILE__ ) );
define( 'AXELLCORE_ATELIERCLUB_URL', plugin_dir_url( __FILE__ ) );

require_once AXELLCORE_ATELIERCLUB_PATH . 'includes/class-plugin.php';
require_once AXELLCORE_ATELIERCLUB_PATH . 'includes/class-template-loader.php';
require_once AXELLCORE_ATELIERCLUB_PATH . 'includes/class-assets.php';
require_once AXELLCORE_ATELIERCLUB_PATH . 'includes/class-blocks.php';
require_once AXELLCORE_ATELIERCLUB_PATH . 'includes/class-icons.php';
require_once AXELLCORE_ATELIERCLUB_PATH . 'includes/class-activator.php';

register_activation_hook( AXELLCORE_ATELIERCLUB_FILE, array( 'Axellcore_Atelierclub\\Activator', 'activate' ) );

Axellcore_Atelierclub\Plugin::instance()->boot();
