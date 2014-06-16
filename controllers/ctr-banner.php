<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *
 * All the functions used in the front end are used by this controller
 */
class CTR_Banner
{

    // Redimensionamento
    private static $_width  = 960;
    private static $_height = 353;

    // -----------------------------------------------------------------------------

    function __construct() {
        add_filter( "ctr_get_banners_home", array(&$this, 'get_banners_home'), 10, 2);
    }

    // -----------------------------------------------------------------------------

    /**
     * Retorna todos os banners da home publicados com o seus respectivos  atributos do metabox
     * @return  array Array de Objetos
     */


    public function get_banners_home() {

        $args = array(
            'post_type'      => 'banner',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order'
        );
        $banners = get_posts( $args );
        foreach($banners as $banner) {
            $id_image          = get_post_meta( $banner->ID,  "banner_foto" , "single" );
            $path_image        = wp_get_attachment_url( $id_image );
            $banner->image_src = $path_image;
            $banner->link      = get_post_meta( $banner->ID,  "banner_link" , "single" ) ;
            if(!strstr( $banner->link, "http://"))
                $banner->link = 'http://' . $banner->link;
            $banner->alt       = get_post_meta( $id_image, '_wp_attachment_image_alt', true);
            $banner->cor_fundo = get_post_meta( $banner->ID,  'banner_cor_fundo', true );
        }
        return $banners;
    }

    // -----------------------------------------------------------------------------
}

new CTR_Banner;
