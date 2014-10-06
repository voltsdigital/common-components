<?php

if( !defined( 'WPINC' ) )
  die();


/*
 Controller Product Admin
 */
class CTR_Product_Admin  {


    function __construct() {
        $this->callOdinGallery();
        add_filter( 'woocommerce_product_data_tabs' ,  array( &$this, 'addGalleryTab' ) );
        add_filter( 'woocommerce_product_options_general_product_data', array( &$this, 'showGalleryPerColor' ) );
        add_action( 'save_post_product' , array( &$this, 'saveGalleryPerColor' ) );
        add_action( 'save_post_product' , array( &$this, 'confirmRequiredFields' ), 90 );
        add_filter( 'post_updated_messages', array( &$this, 'updateStockVariationBasedOnAttributes' ) );
        add_filter( 'product_type_selector' , array( &$this, 'filterProductType' ) );
    }

    private function callOdinGallery() {
        $variacao_galeria = new Odin_Metabox(
            '',
            '',
            'product',
            'side',
            'low'
        );
    }

    public function addGalleryTab( $productTabs ) {
        $productTabs[ 'colors' ] = array(
            'label'    => 'Galerias',
            'target'   => 'images_product_data',
            'class'    => array('colors'),
        );
        return $productTabs;
    }

    public function showGalleryPerColor() {
        global $post;
        $html             = '</div><div id="images_product_data" class="panel woocommerce_options_panel">';
        $available_colors =  wp_get_post_terms( $post->ID,  Store::getTaxonomyColorName() );

        if ( ! count( $available_colors ) > 0 ) {
            $html.= '<h3>Nenhuma cor salva</h3>';
            echo $html;
            return ;
        }
        foreach( $available_colors as $color ) {
            $current = get_post_meta( $post->ID , 'product_color_gallery_'.$color->slug  , true );
            $id      = 'product_color_gallery_' . $color->slug;
            $html    .= '<h3>Galeria ' . $color->name . '</h3>';
            $html    .= '<div class="product-variation-gallery">';
            $html    .= '<div class="odin-gallery-container ">';
            $html    .= '<ul class="odin-gallery-images">';
            if ( ! empty( $current ) )
            $html    .= $this->GetImagesFromGalleryColor( $current );
            $html    .= '</ul><div class="clear"></div>';
            $html    .= sprintf( '<input type="hidden" class="odin-gallery-field" name="%s" value="%s" />', $id, $current );
            $html    .= sprintf( '<p class="odin-gallery-add hide-if-no-js"><a href="#">%s</a></p>', __( 'Add images in gallery', 'odin' ) );
            $html    .= '</div></div>';
        }
        echo $html;
    }

    public function addGeneralPrice( ) {

        $field = array(
            'id'          => '_general_price',
            'label'       => 'Preço Padrão',
            'desc_tip'    => 'true',
            'description' => 'Preço para ser aplicado a todas as variações, caso elas não possuam.'
        );

        woocommerce_wp_text_input( $field );

    }

    public function GetImagesFromGalleryColor( $current ) {
        $attachments = array_filter( explode( ',', $current ) );
        if( ! $attachments )
            return '';
        $html = "";
        foreach ( $attachments as $attachment_id ) {
            $html .= sprintf( '<li class="image" data-attachment_id="%1$s">%2$s<ul class="actions"><li><a href="#" class="delete" title="%3$s">X</a></li></ul></li>',
                $attachment_id,
                wp_get_attachment_image( $attachment_id, 'thumbnail' ),
                __( 'Remove image', 'odin' )
            );
        }
        return $html;
    }

    public function saveGalleryPerColor( $postId ) {
        if( $this->isAutoSavingProduct( $postId ) )
            return ;

        $available_colors =  wp_get_post_terms( $postId , Store::getTaxonomyColorName() );
        if ( ! count( $available_colors ) > 0  )
            return ;

        foreach( $available_colors as $color ){
            if( ! isset( $_POST[ 'product_color_gallery_'.$color->slug ] ) )
                continue;

            if( $_POST[ 'product_color_gallery_'.$color->slug ] != '' ){
                update_post_meta( $postId, 'product_color_gallery_' . $color->slug , sanitize_text_field( $_POST[ 'product_color_gallery_'.$color->slug ] ) );
            }
            else{
                update_post_meta( $postId, 'product_color_gallery_' . $color->slug , '' );
            }
        }
    }

    private function isAutoSavingProduct( $postId ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return true;
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ){
            if ( !current_user_can( 'edit_page', $postId ) )
                return true;
        }

        if ( !current_user_can( 'edit_post', $postId ) )
            return true;

        return false;
    }

    public function updateStockVariationBasedOnAttributes( ) {
        global $post;
        if ( $post->post_type != "product" )
            return false;

        $postId = $post->ID;

        $this->ResetStockBasedOnAttribute( $postId, Store::getTaxonomyColorName() );
        $this->ResetStockBasedOnAttribute( $postId, Store::getTaxonomySizeName()  );

        $parentProduct          = get_product( $postId );
        $postsProductVariations = $this->getProductVariations( $postId );
        $stockOfColors          = array();
        $stockOfSizes           = array();

        foreach( $postsProductVariations as $postProductVariation ) {
            $productVariation = get_product( $postProductVariation->ID );

            if ( $this->getIfHasStockOfProductVariationBasedOnTaxonomy( $productVariation , Store::getTaxonomySizeName() ) )
                $stockOfSizes[ $productVariation->variation_data['attribute_'. Store::getTaxonomySizeName() ] ]++;

            if ( $this->getIfHasStockOfProductVariationBasedOnTaxonomy( $productVariation , Store::getTaxonomyColorName() ) )
                $stockOfColors[ $productVariation->variation_data['attribute_'. Store::getTaxonomyColorName() ] ]++;
        }
        $this->updatePostMetaStockOfParentProduct( $postId , $stockOfSizes );
        $this->updatePostMetaStockOfParentProduct( $postId , $stockOfColors);
    }

    public function ResetStockBasedOnAttribute( $postId, $taxonomy ) {
        $terms = get_terms( $taxonomy ,  array( 'hide_empty' => 0 ) );
        foreach( $terms as $k  => $term ){
            update_post_meta( $postId, 'stock_'. $term->slug , 0 );
        }
    }

    public function getProductVariations( $postId ) {
         $args = array(
            'post_type'      => 'product_variation',
            'posts_per_page' => -1,
            'post_parent'    => $postId,
        );
        return  get_posts( $args );
    }

    public function getIfHasStockOfProductVariationBasedOnTaxonomy( $productVariation, $taxonomy ) {
        if ( ! isset( $productVariation->variation_data['attribute_'. $taxonomy ] ) )
            return ;
        if ( $productVariation->stock  > 0 )
            return true;
        else
            return false;
    }

    public function updatePostMetaStockOfParentProduct( $productId, $stocksOfAttributes ) {
        foreach( $stocksOfAttributes as $attributeValue => $stock ) {
            update_post_meta( $productId,  'stock_' . $attributeValue , $stock );
        }
    }

    // -----------------------------------------------------------------------------

    public function filterProductType( $productTypes ) {

        foreach( $productTypes as $key => $productType ) {

            if ( $key != 'variable' )
                unset( $productTypes[ $key ] );
        }

        return $productTypes;
    }

    // -----------------------------------------------------------------------------

    public function confirmRequiredFields( $postID ) {
        if( $this->isAutoSavingProduct( $postId ) )
            return ;

        if ( $_POST['product-type'] != 'variable' )
            return ;

        if ( ! $this->isVariationPriceSet() )
            $this->setRequiredFieldMessage( "É necessário definir o preço das variações ");

        $this->setDefaultVariation();
    }

    private function isVariationPriceSet() {

        $isVariationPriceSet = true;

        if ( !$_POST['variable_regular_price'] )
            return;

        foreach( $_POST['variable_regular_price'] as $k => $price ) {
            if ( ! $price  )
                $isVariationPriceSet = false;
        }

        return $isVariationPriceSet;

    }

    private function setRequiredFieldMessage( $message )  {

        remove_action('save_post_product', array( &$this, 'confirmRequiredFields'), 90 );
        wp_update_post(  array("ID" => get_the_ID(), "post_status" => "draft") );
        // set_transient( rand(3,95), $message, 30 );
        add_action('save_post_product', array( &$this, 'confirmRequiredFields'), 90 );
    }

    private function setDefaultVariation() {

        if ( $_POST['default_attribute_'. Store::getTaxonomySizeName() ] && $_POST['default_attribute_'. Store::getTaxonomyColorName()] )
            return;

        if ( !is_array($_POST['attribute_'.Store::getTaxonomySizeName()]) || !is_array( $_POST['attribute_'.Store::getTaxonomyColorName() ] ) )
            return ;


        $_POST['default_attribute_'. Store::getTaxonomySizeName() ]   =  array_shift($_POST['attribute_'.Store::getTaxonomySizeName() ] );
        $_POST['default_attribute_'. Store::getTaxonomyColorName() ]  =  array_shift($_POST['attribute_'.Store::getTaxonomyColorName() ] );
    }


}

new CTR_Product_Admin();