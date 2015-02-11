<?php

class CTR_Search{
    public function __construct(){
        add_action( 'pre_get_posts', array( $this ,'search_keyword_plural_to_singular') , 20 );
    }

    public function search_keyword_plural_to_singular( $query ){
        if ( is_admin() || ! $query->is_main_query() )
            return;

        if ( is_archive( 's' ) || is_search() ) {
            $s =  $query->get( 's' );
            $query->set( 's_unfiltered' , $s );
            $s = preg_replace("/ões$/i", 'ão' , rtrim( $s ) );
            $s = preg_replace("/ons$/i", 'om' , rtrim( $s ) );
            $s = preg_replace("/s$/i", '' , rtrim( $s ) );
            $query->set( 's' , $s );
        }
    }
}

new Ctr_Search;