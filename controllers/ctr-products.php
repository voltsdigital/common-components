<?php


if( !defined( 'WPINC' ) )
  die();


class CTRProduct {

    public  $maxProductsPerPage;
    public  $widthImage;
    public  $heightImage;

    // -----------------------------------------------------------------------------

    public function __construct()
    {
        $this->maxProductsPerPage       = get_option( 'posts_per_page' );
        $this->widthImage               = 280;
        $this->heightImage              = 150;

        // add_filter( "relevanssi_modify_wp_query", array(&$this, 'modifyRelevanssiQuery'), 10, 2);
        // add_filter('relevanssi_hits_filter', array(&$this, 'orderTheResultsByMeta'), 10 );
    }

    // -----------------------------------------------------------------------------

    public function getCurrentQuery() {

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $this->maxProductsPerPage,
            'paged'          => get_query_var('paged'),
            'order'          => 'ASC',
            's'              => get_query_var('s')
        );

        $args         = $this->setOrderOfProductsBasendOnArgs ( $args );
        $args         = $this->setFilterProducts( $args );
        $query        = new WP_Query( $args );


        if ( get_query_var('s') )
            // relevanssi_do_query( $query );

        $query->posts = $this->getMetaDataFromQuery( $query->posts );

        //Set full query for filter
        $args['posts_per_page'] = -1;
        $args['paged']          = 1;
        $store = Store::getInstance();
        $store->setFullProductQuery( new WP_Query(  $args ) );

        return $query;

    }

    // -----------------------------------------------------------------------------

    public  function getFeaturedProducts() {

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $this->maxProductsPerPage,
            'paged'          => get_query_var('paged'),
            'meta_query'     => array(
                array(
                    'key'   => '_featured',
                    'value' => 'yes'
                )
            )
        );

        $args         = $this->setOrderOfProductsBasendOnArgs ( $args );
        $query        = new WP_Query( $args );
        $query->posts = $this->getMetaDataFromQuery( $query->posts );

        return $query;
    }


    // -----------------------------------------------------------------------------

    public function getSearchQuery( $term = "" ) {

        global $wp_query;

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => ($term == "") ?  -1 : 4,
            'paged'          => get_query_var('paged'),
            's'              => ($term == "") ? get_query_var('s') : $term,
            'meta_query'     => array(
                array(
                    'key'   => '_featured',
                    'value' => 'yes'
                )
            )
        );

        $args                 = $this->setOrderOfProductsBasendOnArgs ( $args );
        $wp_query->query_vars = $args;
        $query          = new WP_Query( $args );
        // relevanssi_do_query( $query );
        $query->posts = $this->getMetaDataFromQuery( $query->posts );
        if ( $term )
            return $query->posts;
        return $query;

    }

    // -----------------------------------------------------------------------------

    public function modifyRelevanssiQuery( $query ) {

        $args = $this->setOrderOfProductsBasendOnArgs ( $query->query );

        foreach ( $args as $k => $value )
            $query->query_vars[ $k ] = $value;

        return $query;
    }

    // -----------------------------------------------------------------------------

    public function setOrderOfProductsBasendOnArgs( $args ) {

        if ( ! isset( $_GET['orderBy'] ) || $_GET['orderBy'] == 'preco' ) {
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
        }
        else if ( $_GET['orderBy'] == 'lancamento' ) {
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';

        }
        else if ( $_GET['orderBy'] == 'nome') {
            $args['orderby'] = 'title';
            $args['order']    = 'ASC';
        }
        else if ( $_GET['orderBy'] == 'mais-vendidos') {
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order']    = 'DESC';
        }

        return $args;

    }

    // -----------------------------------------------------------------------------

    public function setFilterProducts( $args) {

        $taxonomiesName = wc_get_attribute_taxonomy_names();

        foreach ( $taxonomiesName  as $k => $taxonomy ) {
            if ( ! get_query_var(  $taxonomy ) )
                continue;
            $args[ $taxonomy ] = get_query_var(  $taxonomy );
        }


        if( get_query_var( 'product_cat' ) )
            $args['product_cat'] = get_query_var( 'product_cat' );

        return $args;
    }


    // -----------------------------------------------------------------------------

    public function getMetaDataFromQuery( $posts ) {

        foreach ( $posts as $post )
            $this->getMetaDataFromPost( $post );
        return $posts;

    }

    // -----------------------------------------------------------------------------

    public function getMetaDataFromPost( $post ) {

        $post->product                   = get_product( $post->ID );
        $post->mainImage                 = $this->getMainImage( $post );
        $post->product->promotionalPrice = $this->getPromotionalPrice( $post->product );

        return $post;
    }

    // -----------------------------------------------------------------------------

    public function getMainImage( $post ) {



        if( $post->product->is_type('variable') ) {

            if (  in_array( "pa_cor" , $post->product->get_variation_attributes() ) )
                $imageID  =  array_shift( $this->getAllImagesFromColorGalleries( $post ) );
            else
                $imageID = get_post_meta( $post->ID, '_thumbnail_id', true );
        }
        else
            $imageID = get_post_meta( $post->ID, '_thumbnail_id', true );


        return  ImageFactory::create( $imageID , $this->widthImage, $this->heightImage, 'best' );
    }

    // -----------------------------------------------------------------------------

    public function getCategory( $post )  {

        $categories = wp_get_object_terms($post->ID, "product_cat");

        if ( ! $categories )
            return false;

        return array_shift( $categories );
    }

    // -----------------------------------------------------------------------------

    public function getImagesGallery( $post ) {

        if ( isset( $_REQUEST[ Store::getTaxonomyColorName() ])  )
            $imagesID = explode( ',' , get_post_meta( $post->ID, 'product_color_gallery_'. $_REQUEST[ Store::getTaxonomyColorName() ], true) );
        else
            $imagesID = $post->product->get_gallery_attachment_ids();

        if (  $post->product->is_type('variable') && !isset( $_REQUEST[ Store::getTaxonomyColorName()]))
            $imagesID = array_merge($imagesID ,  $this->getAllImagesFromColorGalleries( $post ) );

        $images = array();
        array_unshift($imagesID,  get_post_meta( $post->ID, '_thumbnail_id', true )  );

        foreach ( $imagesID as $imageID )  {
            $image                 = ImageFactory::create( $imageID , $this->widthImage, $this->heightImage, 'best' );
            $auxImage              = ImageFactory::create( $imageID , 63, 50 , 'best' );
            $image->imageStandard  = $image->imageThumbnail;
            $image->imageThumbnail = $auxImage->imageThumbnail;
            $images[]              = $image;
        }

        return $images;
    }


    // -----------------------------------------------------------------------------

    public function getProduct( $id ) {

        $post = get_post( $id );

        if (  ! $post || $post === NULL )
            return false;

        return $this->getMetaDataFromPost( $post );

    }

    // -----------------------------------------------------------------------------

    public function getRelatedProducts( $post ) {

        $productsID = $post->product->get_upsells();

        if ( count( $productsID ) == 0 )
            $productsID  =  $post->product->get_related( 4 );

        if ( count ( $productsID ) >= 4  )
            return $productsID;

        $relatedProductsID =  $post->product->get_related( 4 );

        foreach (  $relatedProductsID as $productID  ) {

            if ( in_array( $productID, $productsID ) )
                continue;

            if ( count ($productsID) >= 4 )
                break;

            array_push( $productsID,  $productID );
        }

        return $productsID;

    }

    // -----------------------------------------------------------------------------

    public function getAllImagesFromColorGalleries ( $post ) {
        $imagesID = array();

        $variationAttributes = $post->product->get_variation_attributes();

        if ( isset( $variationAttributes[  Store::getTaxonomyColorName()  ]  ) )
            $colorValues = $variationAttributes[  Store::getTaxonomyColorName()  ];
        else
            $colorValues = $variationAttributes [ "attribute_" . Store::getTaxonomyColorName()  ];

        if ( ! $colorValues )
            return array();

        if( ! is_array(  $colorValues ) )
            return array( get_post_meta( $post->product->parent->id , 'product_color_gallery_'. $colorValues , true )  );

        foreach ( $colorValues as $color )
            $imagesID[] = get_post_meta( $post->ID, 'product_color_gallery_'. $color , true );

        return $imagesID;
    }

    // -----------------------------------------------------------------------------

    private function getPromotionalPrice ( $product ) {

        if ( $product->is_type('simple') ) {
            if ( $product->get_price() != $product->get_regular_price() )
                return $product->get_price();
            else
                return false;
        }

        if (  $product->is_type('variable') ) {

            if ( $product->get_price() != $product->get_variation_regular_price() )
                return $product->get_price();
            else
                return false;
        }
    }

    // -----------------------------------------------------------------------------


    public function orderTheResultsByMeta($hits ) {
        global $wp_query;

        if ( ! isset($wp_query->query_vars['meta_key']) )
            return $hits;
        switch ($wp_query->query_vars['orderby']) {
            case 'meta_value_num':
                $likes = array();
                foreach ($hits[0] as $hit) {
                    $likecount = get_post_meta($hit->ID, $wp_query->query_vars['meta_key'] , true);
                    if (!isset($likes[$likecount])) $likes[$likecount] = array();
                        array_push($likes[$likecount], $hit);
                    }

                if ($wp_query->query_vars['order'] == 'ASC') {
                    ksort($likes);
                } else {
                    krsort($likes);
                }

                    $sorted_hits = array();
                foreach ($likes as $likecount => $year_hits) {
                    $sorted_hits = array_merge($sorted_hits, $year_hits);
                }
            $hits[0] = $sorted_hits;
            break;

        case 'relevance':
            //do nothing
            break;
        }
        return $hits;
    }

    // -----------------------------------------------------------------------------]
}


