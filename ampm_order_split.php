<?php

/*
Name: AMPM Order Split
Plugin Name: AMPM Order Split
Plugin URI: https://ampmllc.co
Description: Release 0.0.2
Author: AMPM LLC
Version: 0.0.2
Author URI: https://ampmllc.co
Version History:
* Version 0.0.1 Baseline
* Version 0.0.2 Added Meta Data for shipping class
*/

defined( 'ABSPATH' ) || exit; // block direct access to plugin PHP files by adding this line at the top of each of them

include( plugin_dir_path( __FILE__ ) . './includes/debug_class.php');
include( plugin_dir_path( __FILE__ ) . 'orderSplitClass.php');

/**
 * @snippet       Split Woo Order Based on Shipping Class
 * @tutorial      https://businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 9
 * @community     https://businessbloomer.com/club/
 */
 
add_action( 'woocommerce_thankyou', 'AMPM_split_order_after_checkout', 9999 );
 
function AMPM_split_order_after_checkout( $order_id ) {
    
    $order = wc_get_order( $order_id );
    if ( ! $order || $order->get_meta( '_order_split' ) ) return;
    $items_by_shipping_class = array();
    $shipping_class_array = array();

    foreach ( $order->get_items() as $item_id => $item ) {
        $product = $item->get_product();     
        $class_id = $product->get_shipping_class_id();
        $shipping_class_array[$class_id] = get_shipping_class_name($class_id);
        $items_by_shipping_class[$class_id][$item_id] = $item;
    }

    $values = array_values($shipping_class_array);
    $orig_ship_class = $values[0];
    
    if ( count( $items_by_shipping_class ) > 1 ) {
      foreach ( array_slice( $items_by_shipping_class, 1 ) as $class_id => $items ) {
         $args = array(
            'status'      => 'pending', // Or 'processing', 'completed', etc.
            'customer_id' => $order->get_customer_id(), // Optional: Assign to a specific customer
            'customer_note' => 'This Order is split from order: '.$order_id.' to assist in processing by Blue Valley Cabinets.'.' for Class ID: '.$class_id,
         );
         $new_order = wc_create_order( $args );
         $new_order->set_address( $order->get_address( 'billing' ), 'billing' );
         if ( $order->needs_shipping_address() ) $new_order->set_address( $order->get_address( 'shipping' ) ?? $order->get_address( 'billing' ), 'shipping' );

         foreach ( $items as $item_id => $item ) {
            $new_item = new WC_Order_Item_Product();
            $new_item->set_product( $item->get_product() );
            $new_item->set_quantity( $item->get_quantity() );
            $new_item->set_total( $item->get_total() );
            $new_item->set_subtotal( $item->get_subtotal() );
            $new_item->set_tax_class( $item->get_tax_class() );
            $new_item->set_taxes( $item->get_taxes() );
            foreach ( $item->get_meta_data() as $meta ) {
               $new_item->add_meta_data( $meta->key, $meta->value, true );
            }
            $new_order->add_item( $new_item );
            $order->remove_item( $item_id );
         }
         $new_order = copy_meta($order,$new_order);
         $new_order->update_meta_data('_shipping_class',$values[1], true);      

         $new_order->calculate_totals();  
         $new_order->set_payment_method( $order->get_payment_method() );
         $new_order->set_payment_method_title( $order->get_payment_method_title() );         
         $new_order->update_status( $order->get_status() );
         
         $order->calculate_totals();
         $order->update_meta_data( '_order_split', true );
         $order->update_meta_data( '_shipping_class', $orig_ship_class, true);
         $order->save();
      }
 
    }
    
}

function copy_meta($order,$new_order)
{
   $meta_names = array("is_vat_exempt","Assembly","_wcb2b_group","_wc_avatax_tax_calculated","_wc_avatax_tax_date");
   foreach ( $meta_names as $key ) {
      $new_order->update_meta_data( $key, $order->get_meta($key,true) );
   }

   return $new_order;
}

function get_shipping_class_name($shipping_class_id)
{
   if ( $shipping_class_id > 0 ) { // Check if a shipping class is assigned
      $shipping_class_term = get_term( $shipping_class_id, 'product_shipping_class' );

      if ( ! is_wp_error( $shipping_class_term ) && is_a( $shipping_class_term, 'WP_Term' ) ) {
         $shipping_class_name = $shipping_class_term->name;
         return $shipping_class_name;
      }
   } else {
      return 'No shipping class assigned.';
   }
}
?>