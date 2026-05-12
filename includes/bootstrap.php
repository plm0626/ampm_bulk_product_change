<?php
/**
 * Plugin bootstrap.
 */

namespace AMPM\OrderSplit;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/admin-page.php';

/**
 * Register plugin hooks.
 */
function bootstrap(): void {
	add_action( 'admin_menu', __NAMESPACE__ . '\\register_admin_page' );
}

