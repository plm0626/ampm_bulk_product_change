<?php
/**
 * Admin page helpers.
 */

namespace AMPM\OrderSplit;

defined( 'ABSPATH' ) || exit;

defined( 'AMPM_ORDER_SPLIT_ENABLED_OPTION' ) || define( 'AMPM_ORDER_SPLIT_ENABLED_OPTION', 'ampm_order_split_enabled' );
defined( 'AMPM_ORDER_SPLIT_DEBUG_OPTION' ) || define( 'AMPM_ORDER_SPLIT_DEBUG_OPTION', 'ampm_order_split_debug' );

const ADMIN_PARENT_SLUG       = 'ampm_plugin_admin';
const ADMIN_PARENT_TITLE      = 'AMPM Admin Page';
const ADMIN_PARENT_MENU_TITLE = 'AMPM Admin';
const ADMIN_CAPABILITY        = 'manage_options';
const ADMIN_PAGE_SLUG         = 'ampm_order_split_admin';

/**
 * Register this plugin's AMPM Admin submenu page.
 */
function register_admin_page(): void {
	ensure_admin_parent_page();

	add_submenu_page(
		ADMIN_PARENT_SLUG,
		'AMPM Order Split',
		'Order Split',
		ADMIN_CAPABILITY,
		ADMIN_PAGE_SLUG,
		__NAMESPACE__ . '\\render_admin_page',
		70
	);
}

/**
 * Register settings shown on the submenu.
 */
function register_admin_settings(): void {
	register_setting( ADMIN_PAGE_SLUG, AMPM_ORDER_SPLIT_ENABLED_OPTION );
	register_setting( ADMIN_PAGE_SLUG, AMPM_ORDER_SPLIT_DEBUG_OPTION );
}

/**
 * Render the settings page.
 */
function render_admin_page(): void {
	if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
		return;
	}

	$enabled = is_enabled();
	$debug   = ! empty( get_option( AMPM_ORDER_SPLIT_DEBUG_OPTION, false ) );
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'AMPM Order Split', 'ampm-order-split' ); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields( ADMIN_PAGE_SLUG ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php echo esc_html__( 'Plugin enabled', 'ampm-order-split' ); ?></th>
					<td>
						<input type="hidden" name="<?php echo esc_attr( AMPM_ORDER_SPLIT_ENABLED_OPTION ); ?>" value="0">
						<label for="<?php echo esc_attr( AMPM_ORDER_SPLIT_ENABLED_OPTION ); ?>">
							<input
								id="<?php echo esc_attr( AMPM_ORDER_SPLIT_ENABLED_OPTION ); ?>"
								name="<?php echo esc_attr( AMPM_ORDER_SPLIT_ENABLED_OPTION ); ?>"
								type="checkbox"
								value="1"
								<?php checked( $enabled, true ); ?>
							>
							<?php echo esc_html__( 'Enable shipping-class order splitting after checkout.', 'ampm-order-split' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Debug logging', 'ampm-order-split' ); ?></th>
					<td>
						<input type="hidden" name="<?php echo esc_attr( AMPM_ORDER_SPLIT_DEBUG_OPTION ); ?>" value="0">
						<label for="<?php echo esc_attr( AMPM_ORDER_SPLIT_DEBUG_OPTION ); ?>">
							<input
								id="<?php echo esc_attr( AMPM_ORDER_SPLIT_DEBUG_OPTION ); ?>"
								name="<?php echo esc_attr( AMPM_ORDER_SPLIT_DEBUG_OPTION ); ?>"
								type="checkbox"
								value="1"
								<?php checked( $debug, true ); ?>
							>
							<?php echo esc_html__( 'Enable AMPM Order Split debug logging where supported.', 'ampm-order-split' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Save Settings' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Check whether this plugin's behavior is enabled.
 */
function is_enabled(): bool {
	return in_array(
		strtolower( (string) get_option( AMPM_ORDER_SPLIT_ENABLED_OPTION, '1' ) ),
		array( '1', 'true', 'yes', 'on' ),
		true
	);
}

/**
 * Ensure the shared AMPM Admin parent menu exists.
 */
function ensure_admin_parent_page(): void {
	if ( function_exists( 'AMPM\\Common\\ensure_admin_parent_menu' ) ) {
		\AMPM\Common\ensure_admin_parent_menu();
		return;
	}

	if ( admin_parent_menu_exists( ADMIN_PARENT_SLUG ) ) {
		return;
	}

	add_menu_page(
		ADMIN_PARENT_TITLE,
		ADMIN_PARENT_MENU_TITLE,
		ADMIN_CAPABILITY,
		ADMIN_PARENT_SLUG,
		__NAMESPACE__ . '\\render_admin_parent_page',
		'dashicons-admin-generic',
		2
	);
}

/**
 * Render the fallback parent page when AMPM Common is inactive.
 */
function render_admin_parent_page(): void {
	if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'AMPM Admin Page', 'ampm-order-split' ); ?></h1>
		<p><?php echo esc_html__( 'Use the AMPM Admin submenu pages to manage AMPM family plugin settings.', 'ampm-order-split' ); ?></p>
	</div>
	<?php
}

/**
 * Check whether an admin parent menu slug has already been registered.
 */
function admin_parent_menu_exists( string $menu_slug ): bool {
	if ( function_exists( 'AMPM\\Common\\admin_parent_menu_exists' ) ) {
		return \AMPM\Common\admin_parent_menu_exists( $menu_slug );
	}

	global $menu;

	foreach ( (array) $menu as $menu_item ) {
		if ( isset( $menu_item[2] ) && $menu_slug === $menu_item[2] ) {
			return true;
		}
	}

	return false;
}
