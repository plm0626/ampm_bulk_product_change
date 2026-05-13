<?php

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/bootstrap.php';
require_once dirname( __DIR__ ) . '/ampm_order_split.php';

final class AmpmOrderSplitScaffoldTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['wp_stub_options'] = array();
		$GLOBALS['wp_stub_actions'] = array();
		$GLOBALS['wp_stub_current_user_capabilities'] = array();
		$GLOBALS['wp_stub_debug_log'] = array();
		$GLOBALS['wp_stub_options_pages'] = array();
		$GLOBALS['wp_stub_added_menu_pages'] = array();
		$GLOBALS['wp_stub_added_submenu_pages'] = array();
		$GLOBALS['wp_stub_registered_settings'] = array();
		$GLOBALS['menu'] = array();
		$GLOBALS['wp_stub_orders'] = array();
		$GLOBALS['wp_stub_terms'] = array();
		$GLOBALS['wp_stub_order_notes'] = array();
		$GLOBALS['ordersplitlogArray'] = array();
	}

	public function testBootstrapRegistersOrderSplitAdminPage(): void {
		\AMPM\OrderSplit\bootstrap();

		$this->assertSame( 'admin_menu', $GLOBALS['wp_stub_actions'][0]['hook_name'] );
		$this->assertSame( 'AMPM\\OrderSplit\\register_admin_page', $GLOBALS['wp_stub_actions'][0]['callback'] );
		$this->assertSame( 'admin_init', $GLOBALS['wp_stub_actions'][1]['hook_name'] );
		$this->assertSame( 'AMPM\\OrderSplit\\register_admin_settings', $GLOBALS['wp_stub_actions'][1]['callback'] );
	}

	public function testRegisterAdminPageUsesOrderSplitLabels(): void {
		\AMPM\OrderSplit\register_admin_page();

		$this->assertSame( 'AMPM Admin Page', $GLOBALS['wp_stub_added_menu_pages'][0]['page_title'] );
		$this->assertSame( 'ampm_plugin_admin', $GLOBALS['wp_stub_added_submenu_pages'][0]['parent_slug'] );
		$this->assertSame( 'AMPM Order Split', $GLOBALS['wp_stub_added_submenu_pages'][0]['page_title'] );
		$this->assertSame( 'Order Split', $GLOBALS['wp_stub_added_submenu_pages'][0]['menu_title'] );
		$this->assertSame( 'manage_options', $GLOBALS['wp_stub_added_submenu_pages'][0]['capability'] );
		$this->assertSame( 'ampm_order_split_admin', $GLOBALS['wp_stub_added_submenu_pages'][0]['menu_slug'] );
		$this->assertSame( 'AMPM\\OrderSplit\\render_admin_page', $GLOBALS['wp_stub_added_submenu_pages'][0]['callback'] );
	}

	public function testRegisterAdminSettingsUsesOrderSplitOptions(): void {
		\AMPM\OrderSplit\register_admin_settings();

		$this->assertSame( 'ampm_order_split_admin', $GLOBALS['wp_stub_registered_settings'][0]['option_group'] );
		$this->assertSame( 'ampm_order_split_enabled', $GLOBALS['wp_stub_registered_settings'][0]['option_name'] );
		$this->assertSame( 'ampm_order_split_debug', $GLOBALS['wp_stub_registered_settings'][1]['option_name'] );
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

	public function testMainSplitterMovesSecondShippingClassIntoNewOrder(): void {
		$GLOBALS['wp_stub_terms']['product_shipping_class'][10] = new WP_Term(
			array(
				'term_id'  => 10,
				'name'     => 'Milburnie',
				'slug'     => 'milburnie',
				'taxonomy' => 'product_shipping_class',
			)
		);
		$GLOBALS['wp_stub_terms']['product_shipping_class'][20] = new WP_Term(
			array(
				'term_id'  => 20,
				'name'     => 'Spectrum',
				'slug'     => 'spectrum',
				'taxonomy' => 'product_shipping_class',
			)
		);

		$order = new WC_Order(
			array(
				'id'            => 4242,
				'customer_id'   => 7,
				'customer_note' => 'Call before delivery. ',
				'status'        => 'processing',
			)
		);

		$milburnie_item = new WC_Order_Item_Product();
		$milburnie_item->set_product( new WC_Product( array( 'shipping_class_id' => 10 ) ) );
		$milburnie_item->set_quantity( 1 );
		$milburnie_item->set_subtotal( 100 );
		$milburnie_item->set_total( 100 );
		$order->add_item( $milburnie_item );

		$spectrum_item = new WC_Order_Item_Product();
		$spectrum_item->set_product( new WC_Product( array( 'shipping_class_id' => 20 ) ) );
		$spectrum_item->set_quantity( 2 );
		$spectrum_item->set_subtotal( 200 );
		$spectrum_item->set_total( 200 );
		$spectrum_item->add_meta_data( 'finish', 'white', true );
		$order->add_item( $spectrum_item );

		$GLOBALS['wp_stub_orders'][4242] = $order;

		AMPM_split_order_after_checkout( 4242 );

		$this->assertTrue( $order->get_meta( '_order_split' ) );
		$this->assertSame( 'Milburnie', $order->get_meta( '_shipping_class' ) );
		$this->assertCount( 1, $order->get_items() );

		$new_order = $GLOBALS['wp_stub_orders'][2];
		$this->assertSame( 'Spectrum', $new_order->get_meta( '_shipping_class' ) );
		$this->assertStringContainsString( 'Spectrum warehouse.', $new_order->get_customer_note() );
		$this->assertCount( 1, $new_order->get_items() );
	}
}
