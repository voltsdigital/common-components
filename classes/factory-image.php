<?php


if( !defined( 'WPINC' ) )
  die();

/**
 *  Factory used to create images
 *
 *  Case you need change
 *  @description define the attributes and methos to be used in all parts of one product
 */
class ImageFactory extends Odin_Thumbnail_Resizer
{


    public static function create( $imageId, $widthResize, $heightResize, $crop = false )
    {
        return new Image( $imageId, $widthResize, $heightResize, $crop );
    }


    public static function createProductImage( $imageId, $widthResize, $heightResize,  $crop = false )
    {

        $maxWidth  =  $widthResize;
        $maxHeight =  $heightResize;

        $imageInfo = wp_get_attachment_metadata( $imageId );

        if( $crop == 'height' )
            $widthResize = $heightResize * ( $imageInfo[ 'width'] / $imageInfo[ 'height'] );

        if( $crop == 'width' )
            $heightResize = $widthResize * ( $imageInfo[ 'height'] / $imageInfo[ 'width'] );

        if( $crop == 'best' && $imageInfo ){


            if( $imageInfo['height'] >= $imageInfo['width'] ) {
                $heightResize = $widthResize * ( $imageInfo[ 'height'] / $imageInfo[ 'width'] );
            }
            else {
                $heightResize = $widthResize * ( $imageInfo[ 'height'] / $imageInfo[ 'width'] );
                $widthResize  = $heightResize * ( $imageInfo[ 'width'] / $imageInfo[ 'height'] );
            }

            $crop = false;
        }

        return new Image( $imageId, $widthResize, $heightResize, $crop );
    }

}