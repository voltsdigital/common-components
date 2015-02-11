<?php

/**
 * Controller Common,
 *
 * Methodos used in front 
 */
class CTR_Common {

    
    /**
     * Construtor
     */
    public function __construct() {

        // Remover metatags não utilizadas
        $this->remove_metatags();

        // Classes para body
        add_filter( 'body_class', array( &$this, 'body_classes' ) );

        // Depois de ativar o tema
        add_action( 'after_setup_theme', array( &$this, 'setup_features' ) );

    } // __construct

    // -----------------------------------------------------------------------------

    /**
     * Remover metatags não utilizadas
     */
    public function remove_metatags() {

        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wlwmanifest_link' );

    } // remove_metatags

    // -----------------------------------------------------------------------------


    /**
     * Include new classes to body of the page    
     * @param  array $classes Classes to be added on <body> 
     * @return array          array of changed 
     */
    public function body_classes( $classes ) {
        if( is_home() || is_front_page() ) {
            $classes[] = 'page-home';
        }
        return $classes;
    }


    // -----------------------------------------------------------------------------    

    /**
     * Configuration of the themes
     * @return 
     */
    public function setup_features() {

        /**
         * Suporte de linguagem para Odin
         */
        load_theme_textdomain( 'odin', get_template_directory() . '/languages' );

        /**
         * Registrar Menus
         */
        // register_nav_menus(array(
        //     // 'main-menu' => 'Main Menu'
        // ));

        /*
         * Adicionar suporte à Imagem Destacada
         */
        add_theme_support( 'post-thumbnails' );

        /**
         * Adicionar Feeds automaticamente
         */
        add_theme_support( 'automatic-feed-links' );

        /**
         * Support de CSS pesonalizado para o editor
         */
        add_editor_style( get_template_directory_uri() . '/admin/public/css/editor-style.css' );

        /*
        * Buscar página que utiliza determinado template
        */
        add_filter( 'get_page_by_template' , array( $this , 'get_page_by_template' ) , 10 , 1 );

    } // setup_features

    public function get_page_by_template( $template_name ){
        $pages = get_pages(
            array(
                'meta_key' => '_wp_page_template',
                'meta_value' => $template_name
            )
        );

        $page = null;

        if( $pages ){
            $page = array_shift( $pages );
        }

        return $page;
    } // get_page_by_template


    // -----------------------------------------------------------------------------
}

new CTR_Common;
