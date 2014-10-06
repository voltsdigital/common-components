<?php


if( !defined( 'WPINC' ) )
  die();


class Products {

    public static $instance              = null;


    private static $widthRelatedProduct  = 190;
    private static $heightRelatedProduct = 190;
    private static $widthListTaxonomy    = 288;
    private static $heightListTaxonomy   = 369;

    public static $maxPostsPerPage       = 6;
    public static $currentWidth          = null;
    public static $currentHeight         = null;
    public static $currentCrop           = false;
    public static $currentQuery          = false;
    public static $allResultsArgs        = false;
    public static $allProductsFiltered   = false;
    public static $currentProducts       = null;

    public static function getInstance( ) {
        if (null === self::$instance ) {
            self::$instance  = new Products();
        }
        return self::$instance;
    }

    protected function __construct() { }

    // -----------------------------------------------------------------------------

    private static function getArgumentsToQuery( ) {
        global $wp_query;

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => self::$maxPostsPerPage,
            'paged'          => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' )  : 1
        );

        if( get_query_var( 'product_cat' ) )
            $args['product_cat'] = get_query_var( 'product_cat' );

        if ( get_query_var( 'product_tag' )  )
            $args['product_tag'] = get_query_var( 'product_tag' );

        if ( get_query_var( 's' )  )
            $args['s'] = get_query_var( 's');

        $allResultsArgs = $args;
        $allResultsArgs['posts_per_page'] = -1;
        self::$allResultsArgs = $allResultsArgs;

        if ( get_query_var( Store::getTaxonomySizeName() )  )
            $args[Store::getTaxonomySizeName()] = get_query_var( Store::getTaxonomySizeName() );

        if ( get_query_var( Store::getTaxonomyColorName() )  )
            $args[Store::getTaxonomyColorName()] = get_query_var( Store::getTaxonomyColorName() );


        return $args;

    }

    // -----------------------------------------------------------------------------

    public static function getProductsRelated( $product ) {

        $idsProductRelated = $product->get_upsells();
        $related           = array();

        self::$currentWidth  = self::$widthRelatedProduct;
        self::$currentHeight = self::$heightRelatedProduct;

        foreach( $idsProductRelated as $k  => $productId ) {
            $productRelated              = get_product( $productId );
            $productRelated->mainImage   = self::getImageByPosition( $productRelated , 0 );
            $productRelated->secondImage = self::getImageByPosition( $productRelated , 1 );
            $related[]                   = $productRelated;
        }

        return $related;
    }

    // -----------------------------------------------------------------------------

    public static function getFeaturedProducts() {


        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => self::$maxPostsPerPage,
            'paged'          => -1,
            'meta_query'     => array(
                    array(
                             'key'   => '_featured',
                             'value' => 'yes'
                        )
            )
         );


        $query = new WP_Query( $args ) ;
        self::$currentQuery  = $query;

        $posts    =  $query->posts;
        $products = array();

        self::$currentWidth  = self::$widthListTaxonomy;
        self::$currentHeight = self::$heightListTaxonomy;
        self::$currentCrop   = array( 'center', 'top' );

        foreach ( $posts as $key => $post ) {
            $product              = get_product( $post->ID );
            $product->mainImage   = self::getImageByPosition( $product , 0 );
            $product->secondImage = self::getImageByPosition( $product , 1 );
            $products[] = $product;
        }
        self::$currentProducts = $products;
        self::setAllProductsInQuery();

        return $products;

    }

    // -----------------------------------------------------------------------------

    public static function getProductsFromTaxonomy( ) {

        $query = new WP_Query( self::getArgumentsToQuery() ) ;
        self::$currentQuery  = $query;
        $posts    =  $query->posts;
        $products = array();

        self::$currentWidth  = self::$widthListTaxonomy;
        self::$currentHeight = self::$heightListTaxonomy;
        self::$currentCrop   = array( 'center', 'top' );

        foreach ( $posts as $key => $post ) {
            $product              = get_product( $post->ID );
            $product->mainImage   = self::getImageByPosition( $product , 0 );
            $product->secondImage = self::getImageByPosition( $product , 1 );
            $products[] = $product;
        }

        self::$currentProducts = $products;
        self::setAllProductsInQuery();

        return $products;
    }

    // -----------------------------------------------------------------------------

    public static function getImageByPosition( $product , $position = 0) {

        $color         = self::getDefaultColor( $product );

        $imagesId      = get_post_meta( $product->id, 'product_color_gallery_'. $color, true );
        $imagesId      = explode( ',' , $imagesId );
        $imageId = $imagesId[ $position ];

        $crop = 'best';

        if ( $position != 0 )
            $crop = array('center', 'top');

        return  ImageFactory::createProductImage( $imageId , self::$currentWidth, self::$currentHeight, $crop );
    }

    // -----------------------------------------------------------------------------

    public static function setAllProductsInQuery( ) {


        $allProductsQuery    = new WP_Query( self::$allResultsArgs ) ;
        $posts    = $allProductsQuery->posts;
        $products = array();
        foreach( $posts as $k => $post ) {
            $products[]  = get_product( $post );
        }

        self::$allProductsFiltered = $products;
    }

    // -----------------------------------------------------------------------------

    public static function getLinkWithArgs( $product ) {

        $link = $product->get_permalink();

        if (  get_query_var( Store::getTaxonomyColorName() ) )
            $link = add_query_arg( Store::getTaxonomyColorName(), self::getDefaultColor( $product) , $link );

        return $link;

    }

    // -----------------------------------------------------------------------------

    private function getDefaultColor( $product ) {


        if (  get_query_var( Store::getTaxonomyColorName() ) ) {
            $color = self::getColorSelectedFromProduct( $product );
            if($color)
                return $color;
        }

        if (  get_query_var( Store::getTaxonomySizeName() ) ) {
            $color =  self::getFirstColorWithSelectedSize( $product );
            if( $color )
                return $color;
        }


        $variationDefaultAttributes = $product->get_variation_default_attributes();

        if ( ! empty( $variationDefaultAttributes ) )
            return  $variationDefaultAttributes[ Store::getTaxonomyColorName() ];

        $firstVariationId    = array_shift( $product->get_children() );
        $firstVariation      = get_product($firstVariationId);
        $variationAttributes = $firstVariation->get_variation_attributes( );
        $defaultAttributes   = array();

        foreach( $variationAttributes as $attributeKey => $attributeValue ) {
            $attributeKey                        = str_replace( 'attribute_', '' , $attributeKey );
            $defaultAttributes [ $attributeKey ] = $attributeValue;
        }

        update_post_meta( $product->id, '_default_attributes', maybe_serialize( $defaultAttributes ) );
        return $defaultAttributes[ Store::getTaxonomyColorName() ];
    }

    // -----------------------------------------------------------------------------

    private function getColorSelectedFromProduct( $product ) {
        $selectedColors = explode( ",", get_query_var( Store::getTaxonomyColorName() ) );
        $variations     = $product->get_available_variations( );

        foreach($variations as $k => $variation ) {

            if( ! $variation['is_in_stock'] )
                continue;

            $attributes = $variation['attributes'];
            $color      = $attributes['attribute_'. Store::getTaxonomyColorName() ];

            if ( in_array( $color , $selectedColors ) )
                return $attributes['attribute_'. Store::getTaxonomyColorName() ];
        }

    }

    // -----------------------------------------------------------------------------

    private function getFirstColorWithSelectedSize( $product ) {

        $selectedSizes = explode( ",", get_query_var( Store::getTaxonomySizeName() ) );
        $variations    = $product->get_available_variations( );

        foreach($variations as $k => $variation ) {

            if( ! $variation['is_in_stock'] )
                continue;

            $attributes = $variation['attributes'];
            $size       = $attributes['attribute_'. Store::getTaxonomySizeName() ];

            if ( in_array( $size , $selectedSizes ) )
                return $attributes['attribute_'. Store::getTaxonomyColorName() ];
        }
    }

    // -----------------------------------------------------------------------------
}

