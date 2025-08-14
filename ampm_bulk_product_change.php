<?php

/*
Name: AMPM Bulk Product Change
Plugin Name: AMPM Bulk Product Change
Plugin URI: https://ampmllc.co
Description: Release 0.0.1
Author: AMPM LLC
Version: 0.1.1
Author URI: https://ampmllc.co
Version History:
* Version 0.0.1 Baseline
*/

defined( 'ABSPATH' ) || exit; // block direct access to plugin PHP files by adding this line at the top of each of them

include( plugin_dir_path( __FILE__ ) . './includes/debug_class.php');
include( plugin_dir_path( __FILE__ ) . 'product_change_class.php');

function my_one_time_task() {
    // Your code to be executed goes here
    // For example:
    // error_log('This one-time task has been executed!');
    // update_option('my_one_time_task_status', 'completed');
    echo "I am here!";
    error_log( print_r('MY NEW FUNCTION JUST RAN!', true) );
}

function schedule_my_one_time_event() {
    // Define a unique hook name for your event
    $hook_name = 'my_one_time_cron_hook';

    // Check if the event is already scheduled to prevent multiple schedules
    if ( ! wp_next_scheduled( $hook_name ) ) {
        // Schedule the event to run in the near future (e.g., 5 minutes from now)
        // time() + (5 * MINUTE_IN_SECONDS) schedules it for 5 minutes from now
        wp_schedule_single_event( time() + (5 * MINUTE_IN_SECONDS), $hook_name );
    }
}
add_action( 'wp', 'schedule_my_one_time_event' );

// Link the scheduled hook to your function
add_action( 'my_one_time_cron_hook', 'my_one_time_task' );

?>