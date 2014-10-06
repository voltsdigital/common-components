<?php

class CTR_Breadcrumb {


    private static $_separator     = '<i class="fi fi-right-open-1"></i>';
    private static $_link_class    = 'breadcrumb__list-item-link';
    private static $_current_class = 'breadcrumb__actual-item';
    private static $_before        = '<li class="breadcrumb__list-item" >';
    private static $_after         = "</li>";

    private $_prepend_shop        = "";
    private $breadcrumb_list      =  "";

    public function __construct() { }

    // -----------------------------------------------------------------------------

    public function getBreadcrumb(  ) {

        $this->breadcrumb_list  = "";
        $this->generateHome();
        return  $this->breadcrumb_list;
    }

    // -----------------------------------------------------------------------------

    private function generateHome() {

        $this->breadcrumb_list  = self::$_before;
        $this->breadcrumb_list .=  $this->openLinkTag( get_option('home' ) );
        $this->breadcrumb_list .= 'Página Inicial';
        $this->breadcrumb_list .= '</a>';
        $this->breadcrumb_list .= self::$_separator;
        $this->breadcrumb_list .= self::$_after;
    }

    // -----------------------------------------------------------------------------

    private function generateProductCategory() {

        if ( ! is_tax( 'product_cat' ) )
            return ;

        $this->breadcrumb_list .= $this->_prepend_shop;
        $current_term          = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

        $this->generateTermPath( $current_term );
        $this->breadcrumb_list.= self::$_before. '<a class="'.self::$_link_class.'"' . '>' . esc_html( $current_term->name ) . '</a>' . self::$_after;
    }

    // -----------------------------------------------------------------------------

    private function generateProduct() {

        if ( ! is_single()  && is_attachment() && get_post_type() != 'product' )
            return ;

        $this->breadcrumb_list.= $this->_prepend_shop;

        $terms = wc_get_product_terms( get_the_ID(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );

        if ( ! $terms )
            return $this->breadcrumb_list.= self::$_before  . get_the_title() . self::$_after;

        $mainTerm = $terms[0];

        $this->generateTermPath( $mainTerm );

        $link =   get_term_link( $mainTerm->slug, 'product_cat' );
        $this->breadcrumb_list.= $this->openLinkTag( $link );
        $this->breadcrumb_list.= $mainTerm->name . '</a>' . self::$_separator . self::$_after ;
        $this->breadcrumb_list.= $this->openLinkTagCurrent().  get_the_title() .  '</a>'. self::$_after;
    }

    // -----------------------------------------------------------------------------

    private function generateTermPath( $term ) {

        $ancestors = get_ancestors( $term->term_id, $term->taxonomy );
        $ancestors = array_reverse( $ancestors );

        foreach ( $ancestors as $ancestor ) {
            $ancestor = get_term( $ancestor,  get_query_var( $term->taxonomy ) );

            if ( is_wp_error( $ancestor )  )
                continue;

            $link = get_term_link( $ancestor->slug, get_query_var( $term->taxonomy ) );
            $this->breadcrumb_list.=  $this->openLinkTag( $link);
            $this->breadcrumb_list.=  $ancestor->name . '</a>' . self::$_separator . self::$_after ;
        }
    }

    // -----------------------------------------------------------------------------

    private function openLinkTag( $link, $additionalClass = "" ) {
        $regularLink = self::$_link_class;
        $href  =  self::$_before;
        $href .= "<a href={$link} class={$regularLink} {$additionalClass}>";
        return $href;
    }

    // -----------------------------------------------------------------------------

    private function openLinkTagCurrent( ) {
        $href       = self::$_before;
        $regularLink = self::$_link_class;;
        $currentLink = self::$_current_class;
        $href .= "<a class='$regularLink $currentLink'>";
        return $href;
    }

    // -----------------------------------------------------------------------------

}
