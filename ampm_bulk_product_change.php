<?php

/*
Name: AMPM Bulk Product Change
Plugin Name: AMPM Bulk Product Change
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
//include( plugin_dir_path( __FILE__ ) . 'product_change_classes.php');

function bulk_product_change_task() 
{
    global $debugOBJ,$total,$proc_success,$proc_fail,$total_parents,$total_variations;
    $categories = array('Frameless','Closets');
    $total = 0;
    $proc_success = 0;
    $proc_fail = 0;
    $total_parents = 0;
    $total_variations = 0;
    foreach ($categories as $category) {
        $query = new WC_Product_Query( array(
            'type'  => 'variable',
            'category'  => array($category),
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
        ) );
        $variables = $query->get_query_vars();
        $products = $query->get_products();
        $total_parents = $total_parents + count($products);
        foreach ($products as $product_id) 
        {
            $debugOBJ = new stdClass();
            $debugOBJ->category  =  $category;
            $debugOBJ->product_id = $product_id;
            get_variations($product_id,$category);
        }
    }
    $total = $total_parents + $total_variations;
    echo 'Total records: '.$total.' (Parents = '.$total_parents.', Variations = '.$total_variations.'). Success = '.$proc_success.', Fail = '.$proc_fail.'<br>';
}

function get_variations($product_id,$category)
{
    global $debugOBJ,$total,$proc_success,$proc_fail,$total_parents,$total_variations;
    $product = wc_get_product( $product_id );//get the parent product object
    $parent_sku = $product->get_sku();//get the parent sku
    $debugOBJ->parent_sku = $parent_sku;
    $parsed_parent_sku = parse_parent_sku($parent_sku);//call parse_parent_sku() pass the $parent_sku as a variable
    $debugOBJ->parsed_parent_sku = $parsed_parent_sku;
    $args = array(
        'parent'    => $product_id, // variable product ID
        'type'      => 'variation',
        'limit'     => -1,
        );
    $debugOBJ->get_variations_query_args = $args;
    $variations = wc_get_products( $args );//execute the query to get all of the product variations objects using args array() for a particular parent_id
    $total_variations = $total_variations + count($variations);
    foreach ( $variations as $variation ) { //loop through the variations
        $variation_id = $variation->get_id(); // Replace with your actual variation ID

        // Get the WC_Product_Variation object
        $variation_product = wc_get_product( $variation_id );

        // Check if the product object is valid and is a variation
        if ( $variation_product && $variation_product->is_type( 'variation' ) ) {
            // Get the SKU of the variation
            $existing_sku = $variation_product->get_sku();
            //echo "Existing Variation SKU: " . $existing_sku.'<br>';
        } else {
            echo "Invalid variation ID (".$variation_id.")or not a product variation.<br>";
        }
        $attributes = $variation_product->get_attributes();
        $sku = create_new_sku($parsed_parent_sku,$attributes,$category,$variation_id);//create the new sku object
        $debugOBJ->new_sku_obj = $sku;
        new deBug('debug object -> '.json_encode($debugOBJ));
        $new_sku = $sku->NewSKU;
        if ($new_sku === $existing_sku) {
            echo 'No Action Needed for Variation ID = '.$variation_id.', New and Existing SKU = '.$new_sku.'<br>';
            continue;
        } else {
            //echo 'Action NEEDED!<br>';
            echo 'Setting Variation ID: '.$variation_id.' with Existing SKU: '.$existing_sku.' and Attributes: '.json_encode($attributes).' to -> NEW sku = '.$new_sku.'<br>';
            // Assume this is some WooCommerce operation that might throw a WC_Data_Exception
            try {
                $variation_product->set_sku( $new_sku );
                $result = $variation_product->save();
                echo 'Update of variation id:'.$variation_id.' was -> '.(bool) $result.' (1=success)<br>';
                $proc_success++;
            } catch (WC_Data_Exception $e) {
                // Handle the exception
                echo "An error occurred when attempting to set the sku: " . $e->getMessage() . "<br>";
                // You can also get more detailed error data:
                $errorData = $e->getErrorData();
                echo "Error details: " . print_r($errorData, true) ."<br>";
                echo "SKU creation details: ".json_encode($debugOBJ)."<br>";
                echo "*****************************************************<br>";
                $proc_fail++;
                // Log the error, display a message to the user, or take other actions
            }

        }
    }
}

function parse_parent_sku($sku)
{
    $parsed_sku = array(
        'input_sku'                     => $sku,//input sku value
        'prefix'                        => substr($sku,0,strpos($sku,'-')), //get the prefix of the parent sku
        'parentsku'                     => substr($sku,strpos($sku,'-')+1), //parentsku w/o prefix
        'parsedparentsku'               => explode('|',substr($sku,strpos($sku,'-')+1)), //parse the parent sku at | (variation options should be marked with a '|" before each bracketed '(' ')' )
    ); 
    $parsed_sku['parentcount'] = count($parsed_sku['parsedparentsku']);
    
    $last = $parsed_sku['parsedparentsku'][$parsed_sku['parentcount'] -1];
    if (strpos($last,')')) {
        $parsed_sku['postfix'] = substr($last,strpos($last,')')+1);
    } else { $parsed_sku['postfix'] = ""; }
    return $parsed_sku;
}

function create_new_sku($parsed_parent_sku,$attributes,$category,$variation_id)
{
    $new_sku = new stdClass();
    $sku = $parsed_parent_sku;
    $new_sku->attributevalues = array_values((array)$attributes);
    $new_sku->options = array_values((array)$attributes);
    $new_sku->variation_id = $variation_id;
    if($attributes['pa_color']) //check if the passed in attributes contains the pa_color element 
        {
            $new_sku->prefix = get_color_prefix($attributes['pa_color']); //if it does replace the 
            array_shift($new_sku->options);
        } else 
        { 
            $new_sku->prefix = null; 
        }
    $new_sku->parsedparentsku = $sku['parsedparentsku'];
    $new_sku->postfix = $sku['postfix'];
    $j = 0;
    $newsku = array();
    for ($i=0; $i < count($new_sku->parsedparentsku); $i++) { 
        if ($i == 0) {
            $newsku[$i] = $new_sku->parsedparentsku[$i];
        } else 
        {
            if (str_contains($new_sku->parsedparentsku[$i],'(')) 
            {
                if (str_contains($new_sku->options[$j],'wdc') ) { 
                    $newsku[$i] = '-'.strtoupper($new_sku->options[$j]);
                } elseif (str_contains($new_sku->options[$j],'dc')) {
                    $newsku[$i] = strtoupper($new_sku->options[$j]);
                } elseif (str_contains($new_sku->options[$j],'led')) {
                    $newsku[$i] = '_'.strtoupper($new_sku->options[$j]);
                } elseif (str_contains($new_sku->options[$j],'LED')) {
                    $newsku[$i] = '_'.strtoupper($new_sku->options[$j]);
                } elseif (str_contains($new_sku->options[$j],'none')) {
                } elseif (str_contains($new_sku->options[$j],'None')) {
                } elseif (str_contains($new_sku->options[$j],'F')) {
                    $newsku[$i] = '-'.strtoupper($new_sku->options[$j]).substr($new_sku->options[$j],strpos($new_sku-->options[$j],'F'));
                } else {
                    $newsku[$i] = $new_sku->options[$j];
                }
                $j++;
            } elseif (str_contains($new_sku->parsedparentsku[$i],'(')) {
                # code...
            }
            else {
                $newsku[$i] = $new_sku->parsedparentsku[$i];
            }
        } 
    }
    if (empty($new_sku->prefix)) {
        $new_sku->NewSKU = '['.get_category_prefix($category).']'.implode('',$newsku).$new_sku->postfix;
    } else {
        $new_sku->NewSKU = '['.get_category_prefix($category).']'.$new_sku->prefix.'-'.implode('',$newsku).$new_sku->postfix;
    }
    return $new_sku;
}

function get_category_prefix($category)
{
    $catprefix = strtoupper(substr($category,0,2));
    return $catprefix;
}

function update_existing_sku($variation_id,$parent_id,$parent_sku)
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

add_shortcode( 'bulk_product_change', 'bulk_product_change_task');

?>