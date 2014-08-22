<?php

class MTB_Page{

    public $page_template = '';

    public function __construct( $page_template ){
        $this->page_template = $page_template;
        add_action( 'init' , array( $this, 'create_metaboxes' ) );
        add_action( 'tiny_mce_before_init' , array( $this, 'set_editor_options' ) );
    }

    public function create_metaboxes(){

        $post_id = null;

        if( isset( $_GET[ 'post' ] ) ){
            $post_id = $_GET[ 'post' ] ;
        }

        if( isset( $_POST[ 'post_ID' ] ) ){
            $post_id = $_POST[ 'post_ID' ] ;
        }

        if( $post_id == 0 ){
            return;
        }

        $page_template = get_page_template_slug( $post_id );

        if( $page_template == $this->page_template ){
            if( method_exists( $this, 'setup_metaboxes') ){
                $this->setup_metaboxes( );
            }
        }
    }

    public function set_editor_options( $settings ) {
        if( get_post_type() == 'page' ){
            $post_id = null;

            if( isset( $_GET[ 'post' ] ) ){
                $post_id = $_GET[ 'post' ] ;
            }

            if( isset( $_POST[ 'post_ID' ] ) ){
                $post_id = $_POST[ 'post_ID' ] ;
            }

            if( $post_id == 0 ){
                return;
            }

            if( method_exists( $this, 'setup_editor_settings') ){
                return $this->setup_editor_settings( $settings );
            }
        }
        return $settings;
    }
}