<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *  Singleton Cart
 *  @description define the attributes and methos to be used in all parts of the store
 */
class Cart   {

    private static $heightThumbnailShortVision = '55';
    private static $widthThumbnailShortVision  = '48';
    private static $heightThumbnailFullVision  = '100';
    private static $widthThumbnailFullVision   = '100';

    public static $instance      = null;

    public static function getInstance() {
        static $instance = null;
        if (null === $instance)
            $instance = new Cart();
        return $instance;
    }

    protected function __construct() {
        add_action( 'init', array( &$this , 'checkIfIsActionCart' ) );
    }

    public function checkIfIsActionCart() {
        if (  is_admin() )
            return ;

        if ( isset( $_GET[ 'remove_item' ] ) )
            self::removeFromCart();

        if ( ! isset( $_POST[ 'woocommerce_func' ] ) )
            return;

        if ( $_POST[ 'woocommerce_func' ] == 'addtocart' )
            self::addToCart();

        if ( $_POST[ 'woocommerce_func' ] == 'updateCartItemQuantity' )
            self::updateCartItemQuantity();

        if ( $_POST[ 'woocommerce_func' ] == 'calculateShipping' )
            self::calculateShipping();

        if ( $_POST[ 'woocommerce_func' ] == 'applyCouponCode' )
            self::applyCouponCode();

        return ;
    }


    public function removeFromCart() {
        $cart = self::getWCCartObject();
        $cart->set_quantity( $_GET['remove_item'], 0, true) ;
    }

    public function addToCart() {

        global $woocommerce, $post;

        $variationId = $_POST['product_variation_id'];
        $productQtd  = $_POST['product_qtd'];

        if( isset( $post->ID) )
            $productId = $post->ID;

        if( $productQtd == 0 )
            $productQtd = 1 ;

        $addedProductToCart = $woocommerce->cart->add_to_cart( $productId , $productQtd , $variationId );
        $productData        = get_product( $variationId ? $variationId : $productId );

        if ( $addedProductToCart  )
            wc_add_notice( sprintf( __( '&quot;%s&quot; was successfully added to your cart.', 'woocommerce' ), $productData->get_title() ), 'success' );
    }


    public function updateCartItemQuantity() {

        $cart            = self::getWCCartObject();
        $cartItems       = $cart->get_cart();
        $productQuantity = ( isset( $_POST['productQuantity'] ) ) ? $_POST['productQuantity'] : 0 ;
        $cartItemId      = ( isset( $_POST['cartItemId'] ) ) ? $_POST['cartItemId'] : 0 ;

        if ( $productQuantity == 0  || $cartItemId == 0 ) {
            wc_add_notice( 'Erro ao atualizar o carrinho' , 'error' );
            return;
        }

        if ( ! isset( $cartItems [ $cartItemId  ] ) ) {
            wc_add_notice( 'Produto não encontrado no carrinho' , 'error' );
            return ;
        }

        $product       = $cartItems [ $cartItemId  ];
        $product       = $product['data'];
        $stockQuantity = $product->get_stock_quantity();

        if ( $productQuantity <= $stockQuantity ) {
            $cart->set_quantity( $cartItemId,  $productQuantity ) ;
            wc_add_notice( 'Carrinho atualizado com sucesso' , 'success' );
            return;
        }

        if ( $stockQuantity == 0 )
            wc_add_notice( sprintf( __( 'Nenhum unidade de &quot;%s&quot; disponível', 'woocommerce' ), $product->get_title() ) , 'error' );

        if( $stockQuantity == 1)
            wc_add_notice( sprintf( __( 'Apenas 1 unidade de  &quot;%s&quot; disponível', 'woocommerce' ), $product->get_title() ) , 'error' );

        if ( $stockQuantity > 1 )
             wc_add_notice( sprintf( __( 'Apenas %d unidades de &quot;%s&quot; disponíveis', 'woocommerce' ), $stockQuantity, $product->get_title() ), 'error' );

        return;
    }

    public static function calculateShipping() {
        global $woocommerce;

        $shipping = new CTR_Shipping();
        $shipping->calculateShippingFromPostCode( $_POST['fc_cep'] );
        $package                       = array_shift( $woocommerce->shipping->get_packages() );
        $rate                          = array_shift( $package['rates'] );
    }

    public static function applyCouponCode( ) {
        $cart = self:: getWCCartObject();

        $cart->remove_coupons();
        $cart->add_discount( sanitize_text_field( $_POST['fc_coupon'] ) );
        $cart->calculate_totals();
    }

    public static function getWCCartObject() {
        global $woocommerce;
        return $woocommerce->cart;
    }

    public static function getShortContent() {
        global $woocommerce;
        $productsInCart = $woocommerce->cart->get_cart();

        if( !$productsInCart )
            return false;

        $content = array();
        foreach( $productsInCart  as $secretKeyItem => $product ) {
            $images                     = self::getImagesIdFromProductVariation( $product['data'] );
            $product['data']->mainImage = ImageFactory::createProductImage( $images[0] , self::$heightThumbnailShortVision, self::$widthThumbnailShortVision, array('center', 'top') );
            $content[ $secretKeyItem ]  = $product;
        }

        return $content;
    }

    public static function getFullContent() {
        global $woocommerce;
        $productsInCart = $woocommerce->cart->get_cart();

        if( !$productsInCart )
            return false;

        $content = array();
        foreach( $productsInCart as $secretKeyItem => $product ) {
            $images                     = self::getImagesIdFromProductVariation( $product['data'] );
            $product['data']->mainImage = ImageFactory::createProductImage( $images[0] , self::$heightThumbnailFullVision, self::$widthThumbnailFullVision, array('center', 'top') );
            $product['data']->size      = self::getAttributeValueFromProduct( $product['data'], Store::getTaxonomySizeName() );
            $product['data']->color     = self::getAttributeValueFromProduct( $product['data'], Store::getTaxonomyColorName() );
            $content[ $secretKeyItem ]  = $product;
        }

        return $content;
    }

    public static function getAttributeValueFromProduct( $product, $attribute ) {
        $termValue = $product->variation_data['attribute_'. $attribute ];
        $term      = get_term_by( 'slug', $termValue , $attribute );
        return $term->name;
    }

    public static function getImagesIdFromProductVariation( $productVariation ) {

        $attributesVariation = $productVariation->get_variation_attributes();
        $color               = $attributesVariation['attribute_'. Store::getTaxonomyColorName() ];

        $imagesId      = get_post_meta( $productVariation->parent->id, 'product_color_gallery_'. $color, true );
        $imagesId      = explode( ',' , $imagesId );
        foreach( $imagesId as $k => $imageId ) {

            if( ! $imageId  )
                unset($imagesId[$k]);
        }
        return $imagesId;
    }

}

Cart::getInstance();
