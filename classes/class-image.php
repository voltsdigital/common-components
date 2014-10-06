<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *  Classe to used to manipulate images
 *
 *  Case you need change
 *  @description define the attributes and methos to be used in all parts of one product
 */
class Image extends Odin_Thumbnail_Resizer
{

    public $imageId;
    public $imageSrc;
    public $imageThumbnail;
    public $alt;

    public $widthResize;
    public $heightResize;
    public $crop;

    public function __construct( $imageId, $widthResize, $heightResize , $crop = false ) {

        $this->imageId      = $imageId;
        $this->imageSrc     = wp_get_attachment_url( $imageId) ;
        $this->widthResize  = $widthResize;
        $this->heightResize = $heightResize;
        $this->alt          = get_post_meta ( $imageId, '_wp_attachment_image_alt', true );
        $this->crop         = $crop;

        $this->resize();
    }

    public function resize( ) {

        $this->imageThumbnail = self::process(  $this->imageSrc , $this->widthResize, $this->heightResize, $this->crop );
    }

}
