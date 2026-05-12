<?php
/**
 * Admin page helpers for the plugin template.
 */

namespace AMPM\OrderSplit;

defined( 'ABSPATH' ) || exit;

/**
 * Register a simple settings page under Settings.
 */
function register_admin_page(): void {
	add_options_page(
		'AMPM Order Split',
		'Order Split',
		'manage_options',
		'ampm-order-split',
		__NAMESPACE__ . '\\render_admin_page'
	);
}

/**
 * Render the settings page.
 */
function render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'AMPM Order Split', 'ampm-order-split' ); ?></h1>
	</div>
	<?php
}

