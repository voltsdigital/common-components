<?php

require_once FUNCTIONS_DIR  . '/vendor/Facebook/php-sdk-v4/src/Facebook/autoload.php';

class CTRFacebook {

    private $appID;
    private $appSecret;
    private $app;
    private $sessionName;

    // -----------------------------------------------------------------------------

    function __construct() {

        $this->appID       = '749306068506815';
        $this->appSecret   = '056caaa5634cd69bcb29dfd7c0d363e0';
        $this->sessionName = "TOKEN";

        $this->app = new Facebook\Facebook(array(
            'app_id'                => $this->appID ,
            'app_secret'            => $this->appSecret ,
            'default_graph_version' => 'v2.4',
        ));
    }

    // -----------------------------------------------------------------------------

    public function getRedirectLoginURL() {

        $helper      = $this->app->getRedirectLoginHelper();
        $permissions = ['email', 'public_profile']; // Optional permissions
        $loginURL    = $helper->getLoginUrl( home_url('login/') , $permissions);
        return $loginURL;
    }

    // -----------------------------------------------------------------------------

    public function getUserData() {


        $helper = $this->app->getRedirectLoginHelper();

        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        }


        // Send the request to Graph
        try {
          return $response = $this->app->get('/me?fields=id,name,first_name,last_name,link,gender,email,picture', $accessToken );
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }
    }

    // -----------------------------------------------------------------------------

    public function saveSession() {

        $_SESSION[$this->sessionName] = $this->app->getUser();

        try {

            $user_profile = $this->app->api('/me');

            if ( $user = get_user_by( 'login', $user_profile['id'] ) ) {
                wp_set_auth_cookie( $user->ID, false );

                wc_add_notice( 'Login realizado com sucesso' );

                if ( WC()->cart->get_cart_contents_count() > 0 )
                    wp_safe_redirect( WC()->cart->get_checkout_url() );
                else
                    wp_safe_redirect( home_url('meus-pedidos') . "?refresh" );

                exit;
            }

        } catch (Exception $e) {
            echo "Falha no Login: " . $e->getMessage();
        }

        $this->createUserFromFacebook();

        if ( WC()->cart->get_cart_contents_count() > 0 ){
            wp_safe_redirect( home_url('cadastro-completo') . "?refresh" );
            exit;
        }
        else{
            wp_safe_redirect( home_url() ."?refresh" );
            exit;
        }
    }

    // -----------------------------------------------------------------------------

    public function createUser() {

        $FBRequest = $this->getUserData();
        $FBData    = $FBRequest->getGraphUser();

        $userData = array(
            'user_login'    => $FBData['id'],
            'username'      => $FBData['id'],
            'first_name'    => $FBData['first_name'],
            'last_name'     => $FBData['last_name'],
            'role'          => 'customer',
            'user_nicename' => $FBData['name'],
            'user_email'    => $FBData['email'],
            'user_url'      => $FBRequest->getGraphUser()->getLink(),
            'user_pass'     => md5(rand(1,999))
        );

        $user = get_user_by( 'login', $userData['user_login'] );

        if ( ! $user )
            $user = get_user_by( 'email', $FBData['email']);

        if ( $user ){
            wc_add_notice( 'Login realizado com sucesso' );
            $userID = $user->ID;
            wp_set_auth_cookie( $userID );
            return false;
        }

        $userID = wp_insert_user( $userData );
        wp_set_auth_cookie( $userID );
        update_user_meta( $userID, '_fb_user_id', $userData['user_login'] );
        update_user_meta( $userID, 'gender',  ($FBData['gender'] == 'male') ? 'Masculino' : 'Feminino');
        update_user_meta( $userID, 'facebook',$FBRequest->getGraphUser()->getLink() );
        update_user_meta( $userID, 'billing_email', $FBData['email'] );
        update_user_meta( $userID, 'shipping_email', $FBData['email'] );
        update_user_meta( $userID, 'billing_newsletter', 1 );
        wc_add_notice( 'Cadastro realizado com sucesso' );

        $ctrSubscriber = new CTRSubscriber();
        $ctrSubscriber->signFromRegister($FBData['email'], true );
        return true;

    }


    // -----------------------------------------------------------------------------

    public function getSessionName() {
        return $this->sessionName;
    }

    // -----------------------------------------------------------------------------


}