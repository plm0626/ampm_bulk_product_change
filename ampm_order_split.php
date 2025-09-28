<?php

/*
Name: AMPM Order Split
Plugin Name: AMPM Order Split
Plugin URI: https://ampmllc.co
Description: Fixed Shipping Calculation after Order Split
Author: AMPM LLC
Version: 0.0.3
Author URI: https://ampmllc.co
Version History:
* Version 0.0.1 Baseline
* Version 0.0.2 Added Meta Data for shipping class
* Version 0.0.3 Fixed Shipping Calculation after Order Split
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
            'customer_note' => 'This Order is split from order: '.$order_id.' to assist in processing by Blue Valley Cabinets at '.$values[1],
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
         
         $new_order = copy_shipping_method_to_new_order($order,$new_order,$values[1]);

         $new_order = copy_meta($order,$new_order);
         $new_order->update_meta_data('_shipping_class',$values[1], true);      
         $new_order->calculate_totals();  
         $new_order->set_payment_method( $order->get_payment_method() );
         $new_order->set_payment_method_title( $order->get_payment_method_title() );         
         $new_order->update_status( $order->get_status() );
         //$new_order = remove_existing_shipping_lines($new_order);
         $new_order->calculate_shipping();
         //$new_order = recalculate_shipping($new_order);
         $new_order->calculate_totals();  
         $new_order->save();
         
         $order->calculate_totals();
         $order->set_customer_note('This is the original Order: '.$order_id.' for Class ID: '.$values[0].' Part of this order was split to another Sales Order to be processed at: '.$values[1]);
         $order->update_meta_data( '_order_split', true );
         $order->update_meta_data( '_shipping_class', $orig_ship_class, true);
         //$order = remove_existing_shipping_lines($order);
         $order->calculate_shipping();
         //$order = recalculate_shipping($order);
         $order->calculate_totals($order);  
         $order->save();
      }
 
    }
    
}

function copy_meta($order,$new_order)
{
   $order_meta = $order->get_meta_data();
   foreach ( $order_meta as $id => $values ) {
      $new_order->update_meta_data( $values->key, $values->value );
   }

   return $new_order;
}

function copy_shipping_method_to_new_order($order,$new_order,$ships_from)
{
      // Assuming $order is a WC_Order object
   if ( $order ) {
      // Get all shipping items from the order
      $shipping_items = $order->get_items( 'shipping' );

      // Check if there are any shipping items
      if ( ! empty( $shipping_items ) ) {
         display_shipping_meta_data_in_order_details( $order,$ships_from );
         // Loop through each shipping item
         foreach ( $shipping_items as $item_id => $item ) {
               //$all_formatted_meta_data = $item->get_formatted_meta_data(); //retrieve the formatted meta data
               $shipping_item_check = check_for_ships_from($item,$ships_from); //Check if method contains $ships_from
               if ( $shipping_item_check ) { 
                  // Get the shipping method name (e.g., Flat Rate, Free Shipping)
                  $method_title = $item->get_name();
                  // Get the shipping method ID (e.g., 'flat_rate', 'free_shipping')
                  $method_id = $item->get_method_id();
                  // Get the instance ID if applicable (for multiple instances of the same method)
                  $instance_id = $item->get_instance_id();
                  // Get the shipping cost
                  $cost = $item->get_total();
                  // Get the shipping tax
                  $tax_cost = $item->get_total_tax();

                  $new_shipping_item = new WC_Order_Item_Shipping();
                  $new_shipping_item->set_method_title( $method_title );
                  $new_shipping_item->set_method_id( $method_id );
                  $new_shipping_item->set_total( $cost ); // Set the cost as the total for the shipping item
                  $new_order->add_item( $new_shipping_item );
                  
                  remove_shipping_line_item_from_order( $order, $method_id );

               } else {
                  continue;
               }
            }
      } else {
      }
   }

   return $new_order;
}

function remove_shipping_line_item_from_order( $order, $method_id ) {
    // Get the WC_Order object
    // $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return; // Order not found
    }

    // Get all shipping items from the order
    $shipping_items = $order->get_items( 'shipping' );

    // Loop through shipping items and remove them
    foreach ( $shipping_items as $item_id => $item ) {
      //echo "Checking item_id(".$item->get_method_id().") vs method_id(".$method_id;
      if ( $item->get_method_id() == $method_id ) {
        //echo ") = TRUE"."<br>";
        $order->remove_item( $item_id );
      } else {
        //echo ") = FALSE"."<br>";
      }
    }

    // Recalculate order totals after removing shipping
    $order->calculate_totals();

    // Save the modified order
    $order->save();
}


/**
 * Function to display shipping meta data for a given order.
 *
 * @param WC_Order $order The WooCommerce order object.
 * @param string   $meta_key The meta key to retrieve.
 */
function display_order_shipping_meta_data( $order, $meta_key ) {
    if ( ! is_a( $order, 'WC_Order' ) ) {
        return; // Ensure it's a valid order object
    }

    // Get all shipping items from the order
    $shipping_items = $order->get_items( 'shipping' );

    if ( ! empty( $shipping_items ) ) {
        echo '<h3>Shipping Details:</h3>';
        echo '<ul>';
        foreach ( $shipping_items as $item_id => $shipping_item ) {
            // Get the meta data for the specific key
            $meta_value = $shipping_item->get_meta( $meta_key );

            if ( ! empty( $meta_value ) ) {
                echo '<li><strong>' . esc_html( $meta_key ) . ':</strong> ' . esc_html( $meta_value ) . '</li>';
            } else {
                echo '<li>No value found for "' . esc_html( $meta_key ) . '" on shipping item #' . esc_html( $item_id ) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<p>No shipping items found for this order.</p>';
    }
}

// Example usage:
// Assuming you have an order object, for instance, in the 'woocommerce_thankyou' hook or on the order details page.
// Replace 'your_meta_key' with the actual meta key you want to retrieve.
// For example, if you're using a plugin that stores a tracking number in '_tracking_number', use that.

/*
// Example within a hook (e.g., on the Thank You page)
add_action( 'woocommerce_thankyou', 'my_custom_display_shipping_meta', 10, 1 );
function my_custom_display_shipping_meta( $order_id ) {
    $order = wc_get_order( $order_id );
    display_order_shipping_meta_data( $order, '_tracking_number' ); // Replace '_tracking_number' with your actual meta key
}
*/

function display_shipping_meta_data_in_order_details( $order ) {

    // Get all items in the "shipping" group for the order.
    $shipping_items = $order->get_items( 'shipping' );

    // Loop through each shipping item.
    foreach ( $shipping_items as $item_id => $shipping_item ) {

        // Get the formatted meta data.
        $meta_data = $shipping_item->get_formatted_meta_data();

        // Check if there is any meta data to display.
        if ( ! empty( $meta_data ) ) {
            echo '<h3>' . esc_html( $shipping_item->get_name() ) . ' Details</h3>';
            echo '<table class="woocommerce-table woocommerce-table--order-shipping-meta shop_table order_details">';
            foreach ( $meta_data as $meta ) {
                // $meta is an object with properties:
                // ->key (meta_key from database)
                // ->value (meta_value from database)
                // ->display_key (key formatted for display)
                // ->display_value (value formatted for display)
                ?>
                <tr>
                    <th><?php echo wp_kses_post( $meta->display_key ); ?></th>
                    <td><?php echo wp_kses_post( $meta->display_value ); ?></td>
                </tr>
                <?php
            }
            echo '</table>';
        }
    }
}


function check_for_ships_from( $shipping_item, $ships_from )
{

    // Get all items in the "shipping" group for the order.
    //$shipping_items = $order->get_items( 'shipping' );

    // Loop through each shipping item.
    //foreach ( $shipping_items as $item_id => $shipping_item ) {

        // Get the formatted meta data.
        $meta_data = $shipping_item->get_formatted_meta_data();

        // Check if there is any meta data to display.
        if ( ! empty( $meta_data ) ) {
            foreach ( $meta_data as $meta ) {
                // $meta is an object with properties:
                // ->key (meta_key from database)
                // ->value (meta_value from database)
                // ->display_key (key formatted for display)
                // ->display_value (value formatted for display)
               if ( strpos($meta->display_value, $ships_from) !== false ) {
                  //echo "Substring ".$ships_from." found in ".$meta->display_value;
                  return true;
               } else {
                  //echo "Substring ".$ships_from." NOT found in ".$meta->display_value;
               }
            }
        }
    //}
    //echo "Substring ".$ships_from." NOT found in ".$meta->display_value;
    return false;
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

function recalculate_shipping($order)
{
    foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
        $order->remove_item( $shipping_item_id );
    }
    $order->calculate_shipping();
    return $order;
}

function remove_existing_shipping_lines($order)
{
   foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
      $order->remove_item( $shipping_item_id );
   }
   return $order;
}

?>