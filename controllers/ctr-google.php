<?php

require_once FUNCTIONS_DIR  . '/vendor/google/apiclient/src/Google/autoload.php';

class CTRGoogle {

    private $appID;
    private $appSecret;
    private $app;
    private $sessionName;

    // -----------------------------------------------------------------------------

    function __construct() {

        $this->appID       = '365128652406-r7m7vrjl91c4knrmogmavirrvk3t1599.apps.googleusercontent.com';
        $this->appSecret   = 'oN233hGLrfLGluvJCPBxQNHn';
        $this->sessionName = "CLIENTE_TOKEN";

        $client = new Google_Client();
        $client->setApplicationName('Login em CLIENTE');
        $client->setClientId( $this->appID );
        $client->addScope( array( Google_Service_Oauth2::USERINFO_PROFILE, Google_Service_Oauth2::USERINFO_EMAIL ) );
        $client->setClientSecret( $this->appSecret );
        $client->setRedirectUri(  home_url() . "/login" );
        $this->app = $client;

    }

    // -----------------------------------------------------------------------------

    public function getRedirectLoginURL() {
        return $this->app->createAuthUrl();
    }

    // -----------------------------------------------------------------------------

    public function createUser() {

        $this->app->authenticate( $_GET['code'] );

        $oauth2   = new Google_Service_Oauth2($this->app );
        $userInfo = $oauth2->userinfo->get();

        $userData = array(
            'user_login'    => $userInfo->id,
            'username'      => $userInfo->id,
            'first_name'    => $userInfo->givenName,
            'last_name'     => $userInfo->familyName,
            'role'          => 'customer',
            'user_nicename' => $userInfo->givenName,
            'user_email'    => $userInfo->email,
            'user_url'      => $userInfo->link,
            'user_pass'     => md5(rand(1,999))
        );

        $user = get_user_by( 'login', $userData['user_login'] );

        if ( $user ){
            wc_add_notice( 'Login realizado com sucesso' );
            $userID = $user->ID;
            wp_set_auth_cookie( $userID );
            return false;
        }

        $userID = wp_insert_user( $userData );
        wp_set_auth_cookie( $userID );
        update_user_meta( $userID, '_gplus_user_id', $userData['user_login'] );
        if ( isset($userInfo->gender ) )
            update_user_meta( $userID, 'gender',  ($userInfo->gender == 'male') ? 'Masculino' : 'Feminino');
        update_user_meta( $userID, 'googleplus',$userInfo->link );
        update_user_meta( $userID, 'billing_email', $userInfo->email );
        update_user_meta( $userID, 'billing_newsletter', 1 );
        $ctrSubscriber = new CTRSubscriber();
        $ctrSubscriber->signFromRegister($userInfo->email, true );

        wc_add_notice( 'Cadastro realizado com sucesso' );
        return true;

    }


    // -----------------------------------------------------------------------------
}