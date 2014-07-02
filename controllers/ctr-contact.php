<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *
 * Controller Contato
 */
class CTR_Contact
{

    private $_mail_contact        = "luis@voltsdigital.com.br";

    // -----------------------------------------------------------------------------

    function __construct() {
        add_filter( "ctr_contact_send_mail" ,  array( &$this, 'send_mail' ), 10, 2);
    }

    // -----------------------------------------------------------------------------

    /**
     * Format the body content of the mail
     * @return string 
     */
    private function format_body_content() {
        $body = file_get_contents(TEMPLATEPATH . '/partials/mail-template/mail-template-contact.php');
        $body = str_replace( "%name%",  $_POST['cf_name'], $body);
        $body = str_replace( "%titulo%", 'E-mail enviado via Página de Contato', $body);
        $body = str_replace( "%email%", $_POST['cf_email'], $body);
        $body = str_replace( "%telephone%", $_POST['cf_phone'], $body);
        $body = str_replace( "%message%", $_POST['cf_message'], $body);
        $body = str_replace( "%subscribe_news%", $_POST['cf_subscribe_news'], $body);
        $body = str_replace( "%sent_date%", date_i18n( "d \d\e F \d\e Y \à\s H:i", time()), $body);
        return $body;

    }

    // -----------------------------------------------------------------------------

    /**
     * Send a contact mail 
     * @return bool True if sucess, false if fail
     */
    public function send_mail() {

        $contact_info = get_option( 'localizacao_e_contato' );

        if( isset( $contact_info['email_contato'] ) )
            $this->_mail_contact = $contact_info['email_contato'];


        if(!$_POST)
            return false;

        $headers[] = "From: Cliente - Página de Contato  <". $this->_mail_contact . ">";
        $headers[] = 'Cco: Luís Felipe de Andrade <luis@voltsdigital.com.br>';
        $headers[] = "Content-type: text/html";

        $body      = $this->format_body_content();

        if( wp_mail( $this->_mail_contact  ,  " Cliente - Página de Contato ", $body, $headers) )
            return true;
        else
            return false;

    }

    // -----------------------------------------------------------------------------

}

new CTR_Contact;
