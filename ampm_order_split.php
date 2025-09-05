<?php

/*
Name: AMPM Order Split
Plugin Name: AMPM Order Split
Plugin URI: https://ampmllc.co
Description: Release 0.0.1
Author: AMPM LLC
Version: 0.0.1
Author URI: https://ampmllc.co
Version History:
* Version 0.0.1 Baseline
*/

defined( 'ABSPATH' ) || exit; // block direct access to plugin PHP files by adding this line at the top of each of them

include( plugin_dir_path( __FILE__ ) . './includes/debug_class.php');
include( plugin_dir_path( __FILE__ ) . 'orderSplitClass.php');

function bulk_product_change_task() 
{
    global $debugOBJ;
    $debugOBJ = new stdClass();
}

?>