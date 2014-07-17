<?php
if( !defined( 'WPINC' ) )
  die();

/**
 * Create the metaboxes and the fields
 */
class MTB_Banner {

    // -----------------------------------------------------------------------------

    public function __construct() {

        $this->cria_metabox_geral();
    }

    // -----------------------------------------------------------------------------

    public function cria_metabox_geral() {

        $geral = new Odin_Metabox(
            'banner', 
            'Banner', 
            'banner',
            'normal', 
            'high' 
        );

        $geral->set_fields(
            array(
                array(
                    'id'          => 'banner_link',
                    'name'        => 'Link',
                    'label'       => 'Link',
                    'type'        => 'text'
                ),
                array(
                    'id'          => 'banner_foto',
                    'label'       => 'Foto',
                    'type'        => 'image'
                ),
                array(
                    'id'          => 'banner_cor_fundo',
                    'label'       => 'Cor de Fundo',
                    'type'        => 'color', 
                    'default'     => '#ffffff', 
                    'description' => 'Cor de Fundo do Banner, utilizada para manter o padrão da imagem' 
                )
            )
        );
    }

    // -----------------------------------------------------------------------------

}

new MTB_Banner;

?>
