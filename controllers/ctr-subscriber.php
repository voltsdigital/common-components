<?php

if( !defined( 'WPINC' ) )
  die();

/**
 * Faz as operações relacionadas aos assinantes da Newsletter do Portal
 */
class CTR_Subscriber {

    // -----------------------------------------------------------------------------

    public function __construct() { }

    // -----------------------------------------------------------------------------

    /**
     * Verifica se o assinante já existe
     * @param  string $email E-mail do Assinante
     * @return bool
     */
    private function __subscriber_exists( $email ) {
        if ( get_page_by_title(  $email , 'OBJECT', "assinante" ) )
            return true;
        else
            return false;
    }

    // -----------------------------------------------------------------------------

    /**
     * Realiza a assinatura na lista de newsletter do site
     * @return [type] [description]
     */
    public function sign_subscribe() {
        $email =  $_POST['nrf-email'];
        if(  ! $this->__subscriber_exists( $email ) ) {

            $postarr = array ("post_title" => $email, "post_type" => "assinante" );
            $post_id = wp_insert_post( $postarr );

            update_post_meta( $post_id,  'assinante_ativo' , 1 );
            update_post_meta( $post_id , 'assinante_ip' , $_SERVER['SERVER_ADDR'] );
            update_post_meta( $post_id , 'assinante_tipo_cadastro' , 'newsletter_site' );
            update_post_meta( $post_id , 'assinante_data_atualizacao' , time() );

            return true;
        }
        else  {
            return false;
        }
    }

    // -----------------------------------------------------------------------------
}



new CTR_Subscriber;