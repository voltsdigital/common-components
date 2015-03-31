<?php

if( !defined( 'WPINC' ) )
  die();

/*
 * Creates the CUSTOM POST TYPE of Banner
 */

class CPT_Banner {

    // -----------------------------------------------------------------------------

    public function __construct() {

        $banner = new Odin_Post_Type(
            'Banner', 
            'banner_home' 
        );

        $banner->set_arguments(
            array(
                'supports'            => array( 'title' ),
                'hierarchical'        => false,
                'menu_icon'           => 'dashicons-images-alt2',
                'exclude_from_search' => true
            )
        );

        $banner->set_labels(
            array(
                'menu_name'          => 'Banner ',
                'singular_name'      => 'Banner',
                'add_new'            => 'Adicionar Novo Banner',
                'add_new_item'       => 'Adicionar Novo Banner',
                'edit_item'          => 'Editar Banner',
                'new_item'           => 'Novo Banner',
                'all_items'          => 'Todos os Banners',
                'view_item'          => 'Ver Banner',
                'search_items'       => 'Procurar Banner',
                'not_found'          => 'Nenhum Banner Encontrado',
                'not_found_in_trash' => 'Nenhum Banner Encontrado na Lixeira',
                'parent_item_colon'  => '',
            )
        );

        add_filter( 'manage_edit-banner_columns',        array($this, 'colunas_exibicao_listagem' ));
        add_action( 'manage_banner_posts_custom_column', array($this, 'valores_exibicao_listagem'), 10,2);

    }

    // -----------------------------------------------------------------------------

    /**
     * Edita as colunas que serão exibigas na listagem do post
     * @param  array $columns   Colunas
     * @return array
     */
    public function colunas_exibicao_listagem( $columns ) {


        unset($columns["date"]);
        unset($columns["icl_translations"]);
        unset($columns["wpseo-metadesc"]);
        unset($columns["wpseo-title"]);
        unset($columns["wpseo-focuskw"]);
        unset($columns["wpseo-score"]);

        $new_columns = array(
            'banner_foto'      => 'Miniatura',
            'banner_link'      => 'Link',
            "date"             => "Data",

        );

        return array_merge($columns, $new_columns);

    }

    // -----------------------------------------------------------------------------

    /**
     * Pega os valores das colunas customizadas
     * @param  array $column  Colunas com os indíces a serem exibidas
     * @param  int $post_id ID
     * @return  string          Valor do campo
     */
    public function valores_exibicao_listagem( $column, $post_id ) {

        if( $column == "banner_foto" ) 
            $valor = wp_get_attachment_image( get_post_meta( $post_id, $column , "single" ) );
        else 
            $valor = get_post_meta( $post_id, $column , true );
        
        echo $valor;

    }

    // -----------------------------------------------------------------------------

}

new CPT_Banner;

?>