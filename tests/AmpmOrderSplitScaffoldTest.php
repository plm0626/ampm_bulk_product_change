<?php

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/bootstrap.php';

final class AmpmOrderSplitScaffoldTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['wp_stub_options'] = array();
		$GLOBALS['wp_stub_actions'] = array();
		$GLOBALS['wp_stub_current_user_capabilities'] = array();
		$GLOBALS['wp_stub_debug_log'] = array();
		$GLOBALS['wp_stub_options_pages'] = array();
	}

	public function testBootstrapRegistersOrderSplitAdminPage(): void {
		\AMPM\OrderSplit\bootstrap();

		$this->assertSame( 'admin_menu', $GLOBALS['wp_stub_actions'][0]['hook_name'] );
		$this->assertSame( 'AMPM\\OrderSplit\\register_admin_page', $GLOBALS['wp_stub_actions'][0]['callback'] );
	}

	public function testRegisterAdminPageUsesOrderSplitLabels(): void {
		\AMPM\OrderSplit\register_admin_page();

		$this->assertSame( 'AMPM Order Split', $GLOBALS['wp_stub_options_pages'][0]['page_title'] );
		$this->assertSame( 'Order Split', $GLOBALS['wp_stub_options_pages'][0]['menu_title'] );
		$this->assertSame( 'manage_options', $GLOBALS['wp_stub_options_pages'][0]['capability'] );
		$this->assertSame( 'ampm-order-split', $GLOBALS['wp_stub_options_pages'][0]['menu_slug'] );
		$this->assertSame( 'AMPM\\OrderSplit\\render_admin_page', $GLOBALS['wp_stub_options_pages'][0]['callback'] );
	}

	public function testDebugLogUsesOrderSplitSourceWhenEnabled(): void {
		\AMPM\OrderSplit\debug_log( 'not logged' );
		$this->assertSame( array(), $GLOBALS['wp_stub_debug_log'] );

		$GLOBALS['wp_stub_options']['ampm_order_split_debug'] = true;

		\AMPM\OrderSplit\debug_log( 'order split', array( 'order_id' => 42 ) );

		$this->assertSame( 'order split', $GLOBALS['wp_stub_debug_log'][0]['message'] );
		$this->assertSame(
			array(
				'source'   => 'ampm-order-split',
				'order_id' => 42,
			),
			$GLOBALS['wp_stub_debug_log'][0]['context']
		);
	}
}
