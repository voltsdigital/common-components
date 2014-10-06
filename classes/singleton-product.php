<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *  Singleton Product
 *  @description define the attributes and methos to be used in all parts of one product
 */
class Product
{

    public static $widthMainImage  = 440;
    public static $heightMainImage = 660;
    public static $cropMainImage   = array( 'center', 'top' );

    public static $instance = null;

    public static function getInstance( $postId = null ) {

        if ( (null === self::$instance) || $postId  ) {
             new Product( $postId );
        }
        return self::$instance;
    }

    protected function __construct($postId) {
        $product = get_product( $postId );
        self::$instance = $product;
    }

    public static function getValuesAvailableFromAttribute( $attribute ) {

        $product             = self::$instance;
        $variations          = $product->get_available_variations( );
        $attributesAvailable = array();

        foreach ( $variations as $variation ) {

            if( ! $variation['is_in_stock'] )
                continue;

            $attributesVariation = $variation['attributes'];
            $value               = $attributesVariation['attribute_'. $attribute  ];

            if ( !$value )
                continue;

            if ( in_array( $value , $attributesAvailable ) )
                continue;

            $term                          = get_term_by( 'slug', $value , $attribute );
            $attributesAvailable[ $value ] = $term->name;

        }

        return $attributesAvailable;
    }

    public static function getAttributeValuesFilteredFromVariation(  ) {

        $product                           = self::$instance;
        $variations                        = $product->get_available_variations();
        $attributesAvailable               = $product->get_variation_attributes();
        $attributesFiltered                = array();

        foreach ( $attributesAvailable  as $attribute => $values ) {
            $attributesFiltered[ $attribute ] = array();
            $selectedValue                    = self::getAttributeValueSelected($attribute);

            if( $attribute == Store::getTaxonomyColorName() )
                continue;

            if ( ! $selectedValue  )
                continue;
            $variations = self::unsetVariationsWithoutSelectedAttribute( $selectedValue,  $variations );
        }


        return self::getAttributesFilteredFromVariations( $variations, $attributesFiltered );
    }

    public static function getAttributeValueSelected( $attribute ) {
        if ( isset( $_REQUEST[ $attribute ] ) )
            $selectedValue = $_REQUEST[ $attribute  ];
        else
            $selectedValue = '';
        return $selectedValue;
    }

    public static function unsetVariationsWithoutSelectedAttribute( $selectedValue, $variations ) {

        foreach ( $variations as  $k => $variation )  {
            $variationAttributes = $variation[ 'attributes' ];
            if( ! in_array( $selectedValue , $variationAttributes ) ||  ! $variation['is_in_stock'] )
                unset($variations[$k]);
        }
        return $variations;
    }

    public static function getAttributesFilteredFromVariations( $variations, $attributesFiltered ) {

        foreach ( $variations as  $k => $variation )  {
            $variationAttributes = $variation[ 'attributes' ];

            foreach( $variationAttributes as $k => $attribute ) {
                $attributeTaxonomyName = str_replace( 'attribute_', '', $k );

                if( !is_array($attributesFiltered[$attributeTaxonomyName]) )
                    continue;

                if( in_array( $attribute, $attributesFiltered[$attributeTaxonomyName] ) )
                    continue;
                $attributesFiltered[$attributeTaxonomyName][] = $attribute;
            }
        }



        return  $attributesFiltered;
    }

    public static function getActualVariationBasedOnAttributesSelected() {
        $product             = self::$instance;
        $variations          = $product->get_available_variations();
        $attributesAvailable = $product->get_variation_attributes();

        foreach ( $attributesAvailable  as $attribute => $values ) {
            $selectedValue                    = self::getAttributeValueSelected($attribute);
            if ( ! $selectedValue )
                continue;
            $variations = self::unsetVariationsWithoutSelectedAttribute( $selectedValue,  $variations );
        }

        if ( count ( $variations )  != 1 )
            return false;

        return current( $variations ) ;
    }

    public static function getMaxQuantity(  )  {
        $variation = self::getActualVariationBasedOnAttributesSelected();
        return $variation['max_qty'];
    }

    public static function getMainImage( $width =  NULL , $height = NULL, $crop = NULL ) {


        $width  = ( is_null( $width ) ) ?  self::$widthMainImage  : $width ;
        $height = ( is_null( $height) ) ?  self::$heightMainImage : $height ;
        $crop   = ( is_null( $crop) ) ?    self::$cropMainImage   : $crop ;


        $product       = self::$instance;
        $colorSelected = self::getAttributeValueSelected( Store::getTaxonomyColorName() );

        if(  ! $colorSelected )
            $colorSelected = self::getDefaultColor();

        $imagesId    = self::getImagesIdByColor( $colorSelected );
        $mainImageId = array_shift( $imagesId );

        return ImageFactory::create( $mainImageId , $width, $height, $crop );

    }

    public static function getGallery( $width, $height, $crop = array('center', 'center') ) {

        $product       = self::$instance;
        $colorSelected = self::getAttributeValueSelected( Store::getTaxonomyColorName() );
        $color         = "";

        if ( $colorSelected )
            $color = $colorSelected;

        if( ! $color && ! $colorSelected ) {
            $variationDefaultAttributes = $product->get_variation_default_attributes();
            $color                      = $variationDefaultAttributes[ Store::getTaxonomyColorName() ];
        }

        $imagesId =  self::getImagesIdByColor( $color );
        $images   = array();

        foreach( $imagesId as $k => $imageId ) {
            $images[] = ImageFactory::createProductImage( $imageId, $width, $height, $crop );
        }

        return $images;
    }

    public function getAttributeNameFromVariation( $attribute ) {
        $product = self::$instance;
        $attributes = $product->get_variation_attributes();

        $attribute = get_term_by( 'slug', $attributes['attribute_'. $attribute ], $attribute );
        return $attribute->name;

    }

    public function getDefaultColor() {

        $product = self::$instance;

        if ( $product->product_type  != 'simple' && $product->product_type != 'variable' ) {
            $attributes =  $product->get_variation_attributes();
            return $attributes['attribute_'.Store::getTaxonomyColorName() ];
        }

        $variationDefaultAttributes = $product->get_variation_default_attributes();

        if ( ! empty( $variationDefaultAttributes ) )
            return  $variationDefaultAttributes[ Store::getTaxonomyColorName() ];

    }

    public static function getImagesIdByColor( $color ) {

        $product  = self::$instance;
        $imagesId = get_post_meta( $product->id, 'product_color_gallery_'. $color, true );
        $imagesId = explode( ',' , $imagesId );

        foreach( $imagesId as $k => $imageId ) {

            if( ! $imageId  )
                unset($imagesId[$k]);
        }

        return $imagesId;
    }


}