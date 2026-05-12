<?php

defined( 'ABSPATH' ) || define( 'ABSPATH', __DIR__ . '/' );

if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $hook_name, callable|string $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		$GLOBALS['wp_stub_actions'][] = compact( 'hook_name', 'callback', 'priority', 'accepted_args' );
		return true;
	}
}

if ( ! function_exists( 'add_options_page' ) ) {
	function add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable|string $callback = '', ?int $position = null ): string {
		$GLOBALS['wp_stub_options_pages'][] = compact( 'page_title', 'menu_title', 'capability', 'menu_slug', 'callback', 'position' );
		return $menu_slug;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( string $capability ): bool {
		return in_array( $capability, $GLOBALS['wp_stub_current_user_capabilities'] ?? array(), true );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( string $text, string $domain = 'default' ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $option, mixed $default = false ): mixed {
		return $GLOBALS['wp_stub_options'][ $option ] ?? $default;
	}
}

if ( ! function_exists( 'wc_get_logger' ) ) {
	function wc_get_logger(): object {
		return new class() {
			public function debug( string $message, array $context = array() ): void {
				$GLOBALS['wp_stub_debug_log'][] = compact( 'message', 'context' );
			}
		};
	}
}

