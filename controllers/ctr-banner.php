<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *
 * All the functions used in the front end are used by this controller
 */
class CTRBanner
{

    // Redimensionamento
    public $width  = 960;
    public $height = 353;

    // -----------------------------------------------------------------------------

    function __construct() { }

    // -----------------------------------------------------------------------------

    /**
     * Retorna todos os banners da home publicados com o seus respectivos  atributos do metabox
     * @return  array Array de Objetos
     */

    public function getImages() {

        $args = array(
            'post_type'      => 'banner_home',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order'
        );

        $banners = get_posts( $args );

        foreach($banners as $banner) {

            $id_image                = get_post_meta( $banner->ID,  "banner_foto" , "single" );
            $banner->imageSrc        = wp_get_attachment_url( $id_image );
            $banner->link            = get_post_meta( $banner->ID,  "banner_link" , "single" ) ;
            $banner->alt             = get_post_meta( $id_image, '_wp_attachment_image_alt', true);
            $banner->backgroundColor = get_post_meta( $banner->ID,  'banner_cor_fundo', true );

            if(!strstr( $banner->link, "http://") && $banner->link)
                $banner->link = 'http://' . $banner->link;
        }

        return $banners;
    }

    // -----------------------------------------------------------------------------
}
