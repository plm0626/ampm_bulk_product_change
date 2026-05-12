<?php
/**
 * Production-safe debug helpers.
 */

namespace AMPM\OrderSplit;

defined( 'ABSPATH' ) || exit;

/**
 * Log a debug message only when explicitly enabled.
 *
 * @param string $message Message to log.
 * @param array  $context Optional context.
 */
function debug_log( string $message, array $context = array() ): void {
	$enabled = (bool) get_option( 'ampm_order_split_debug', false );

	if ( ! $enabled || ! function_exists( 'wc_get_logger' ) ) {
		return;
	}

	wc_get_logger()->debug(
		$message,
		array_merge(
			array( 'source' => 'ampm-order-split' ),
			$context
		)
	);
}

