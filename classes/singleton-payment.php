<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *  Singleton Payment
 *  @description define the attributes and methos to be used to process a payment
 */
class Payment
{
    public static function getInstance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new Payment();
        }

        return $instance;
    }

    protected function __construct() {
        add_filter( 'process_checkout_order' , array(&$this, 'ProcessCheckoutOrder') );
        add_action( 'woocommerce_checkout_order_processed', array( &$this,  'AfterProcessCheckout' ) );
    }

    private function __clone() { }

    private function __wakeup() { }




    public function getAvailablePaymentMethods() {

        $paymentMethods = WC()->payment_gateways->get_available_payment_gateways();

        foreach($paymentMethods as $k => $method  ) {
            self::setIconFromPaymentMethod( $method );
        }

        return  $paymentMethods;
    }

    private function setIconFromPaymentMethod( $method ) {

        $icons     = array(
            'boleto'   => 'icon icon-bar-code',
            'cobrebem' => 'icon all-credit-cards'
        );

        $method->icon = $icons [ $method->id ];
    }

    public function ProcessCheckoutOrder() {

        if ( empty ($_POST) )
            return;

        if ( isset( $_POST['fsia_postcode'] ) ) {
            self::updateShippingAddress();
            return;
        }

        if ( $_POST['woocommerce_func'] != 'processCheckoutOrder' )
            return ;

        global $woocommerce;
        $checkout             = new WC_Checkout();
        $woocommerce_checkout = $woocommerce->checkout();
        $woocommerce_checkout->process_checkout();
    }

    public static function updateShippingAddress() {

        $userId = get_current_user_id();
        foreach( $_POST as $field => $value ){

            $field = str_replace( 'fsia_', 'shipping_', $field);
            update_user_meta( $userId,   $field ,  $value );
        }



    }

    public function AfterProcessCheckout( $orderId, $orderData ) {

    }



}