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
//include( plugin_dir_path( __FILE__ ) . 'product_change_classes.php');

function bulk_product_change_task() 
{
    $categories = array('Frameless','Closets');
    foreach ($categories as $category) {
        $query = new WC_Product_Query( array(
            'type'  => 'variable',
            'category'  => array($category),
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
        ) );
        $variables = $query->get_query_vars();
        $products = $query->get_products();
        foreach ($products as $product_id) 
        {
            get_variations($product_id);
        }
    }
}

function get_variations($product_id)
{
    $product = wc_get_product( $product_id );
    $parent_sku = $product->get_sku();
    //echo 'Parent SKU: '.$parent_sku.'<br>';
    $parsed_parent_sku = parse_sku($parent_sku);
    //echo 'Parsed Parent SKU: '.json_encode($parsed_parent_sku).'<br>';
    $args = array(
        'parent'    => $product_id, // variable product ID
        'type'      => 'variation',
        'limit'     => -1,
        );
    $variations = wc_get_products( $args );
    foreach ( $variations as $variation ) {
        $id = $variation->get_id();
        $orig_sku = $variation->get_sku();
        $attributes = $variation->get_attributes();
        $sku = new_sku($parsed_parent_sku,$attributes);
        //echo 'Variation ID: '.$id.', Variation Original SKU: '.$orig_sku.', Attributes: '.json_encode($attributes).', NewSKU: '.json_encode($sku).'<br>';
        echo 'New SKU = '.$sku['NewSKU'].', Original SKU = '.$orig_sku.'<br>';
        //$variation->set_sku($sku['NewSKU']);
        //$variation->save();
    }  
}

function parse_sku($sku)
{
    $parsed_sku = array(
        'prefix'                        => substr($sku,0,strpos($sku,'-')),
        'parentsku'                     => substr($sku,strpos($sku,'-')+1),
        'parsedparentsku'               => explode('|',substr($sku,strpos($sku,'-')+1)),
    ); 
    return $parsed_sku;
}

function new_sku($parsed_parent_sku,$attributes)
{
    $sku = $parsed_parent_sku;
    //echo 'Attributes: '.json_encode($attributes).'<br>';
    $attributevalues = array_values((array)$attributes);
    //echo 'Attribute Values: '.implode(',',$attributevalues).'<br>';
    $options = array_values((array)$attributes);
    echo 'options: '.implode(',',$options).'<br>';
    if($attributes['pa_color']) 
        {
            //echo 'COLOR: '.$attributes['pa_color'].'<br>';
            $sku['prefix'] = get_color_prefix($attributes['pa_color']);
            array_shift($options);
            //echo 'options - 1: '.implode(',',$options).'<br>';
        } else 
        { 
            $sku['prefix'] = null; 
        }
    $parentsku = $sku['parsedparentsku'];
    //echo 'Parentsku: ('.count($parentsku).')'.implode(',',$parentsku).'<br>';
    //echo 'SKU Prefix: '.$sku['prefix'].'<br>';
    $j = 0;
    $newsku = array();
    for ($i=0; $i < count($parentsku); $i++) { 
        if ($i == 0) {
            $newsku[$i] = $parentsku[$i]; 
        } else 
        {
            if (str_contains($parentsku[$i],'(')) 
            {
                //echo 'Variable: '.$parentsku[$i].' Attributevalue: '.implode(',',$options).'<br>';
                if (str_contains($options[$j],'wdc') ) 
                    { 
                        $newsku[$i] = '-'.strtoupper($options[$j]);
                    } elseif (str_contains($options[$j],'dc')) {
                        $newsku[$i] = strtoupper($options[$j]);
                    } elseif (str_contains($options[$j],'led')) {
                        $newsku[$i] = '-'.strtoupper($options[$j]);
                    } elseif (str_contains($options[$j],'none')) {
                    } elseif (str_contains($options[$j],'None')) {
                        # code...
                    } else {
                        $newsku[$i] = $options[$j];
                    }
                $j++;
            } 
            else {
                $newsku[$i] = $parentsku[$i];
            }
        } 
    }
    if (empty($sku['prefix'])) {
        $sku['NewSKU'] = implode('',$newsku);
    } else {
        $sku['NewSKU'] = $sku['prefix'].'-'.implode('',$newsku);
    }
    //echo 'In Function New SKU: '.json_encode($sku).'<br>';
    return $sku;
}

function update_variation_sku($variation_id,$parent_id,$parent_sku)
{
    echo "Variation ID: $variation_id".", Parent ID: $parent_id".", Parent SKU: $parent_sku"."<br>";
    $args = array(
        'parent'    => $variation_id,
        'type'      => 'variation',
    );

}

function get_color_prefix($color)
{
    $colorprefixs = array(
        'glossy_white'  =>  'GW',
        'matte_grey'    =>  'MG',
        'matte_white'   =>  'MW',
        'oak'           =>  'OAK',
        'walnut'        =>  'WALNUT',
        'kansas_oak'    =>  'KO',
        'white'         =>  'WS',
    );
    return $colorprefixs[$color];
}

function schedule_bulk_product_change_task() {
    $hook_name = 'bulk_product_change_hook';
    // Check if the event is already scheduled to prevent multiple schedules
    if ( ! wp_next_scheduled( $hook_name ) ) {
        // Schedule the event to run in the near future (e.g., 5 minutes from now)
        // time() + (5 * MINUTE_IN_SECONDS) schedules it for 5 minutes from now
        wp_schedule_single_event( time() + (5 * MINUTE_IN_SECONDS), $hook_name );
    }
}
//enable this add_action to schedule task in the background
//add_action( 'wp', 'schedule_bulk_product_change_task' );
//enable this add_action to hook into the schedule
// Link the scheduled hook to your function
//add_action( 'bulk_product_change_hook', 'bulk_product_change_task' );

add_shortcode( 'bulk_product_change', 'bulk_product_change_task');

?>