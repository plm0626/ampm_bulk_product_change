<?php

defined( 'ABSPATH' ) || exit; // block direct access to plugin PHP files by adding this line at the top of each of them

if ( ! class_exists( 'productChange' ) )
{
  class productChange 
    {
      function __construct() {
      }
    }
}

if ( ! class_exists( 'ProductsQuery' ) )
{
  class ProductsQuery
    {
        function __construct()
        {
            $this->status = 'publish';//('draft', 'pending', 'private', 'publish')
            $this->type = 'variable';//('external', 'grouped', 'simple', 'variable', or a custom type)
            $this->limit = -1;//Accepts an integer: Maximum number of results to retrieve or -1 for unlimited. If not specified, this will default to the value of the global posts_per_page option.
            $this->order = 'ASC';//Accepts a string: 'DESC' or 'ASC'. Use with 'orderby'.
            $this->orderby = 'ID';//Accepts a string: 'none', 'ID', 'name', 'type', 'rand', 'date', 'modified'
            $this->return = 'ids';//Accepts a string: 'ids' or 'objects'
            $this->query_args = array(
                'status'    => $this->status,
                'type'      => $this->type,
                'limit'     => $this->limit,
                'order'     => $this->order,
                'orderby'   => $this->orderby,
                'return'    => $this->return,
            );
            $this->query = new WC_Product_Query( $this->query_args );
        }

        function get_products_query_vars()
        {
          return $this->query_args;
        }

        function get_products_array()
        {
          $products_id_array = array();
          $query = new WC_Product_Query( $this->query_args );
  
          $product_ids = $query->get_products();
  
          if ( is_object($product_ids) ) {
              $products_id_array = array_merge($products_id_array,$product_ids->products);          
          } else {
              $products_id_array = array_merge($products_id_array,$product_ids);          
          }
          $query_args['page'] = $page;
          return array_unique($products_id_array,SORT_NUMERIC);
        }

    }
}
?>
