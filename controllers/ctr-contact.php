<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *
 * Controller Contact
 */
class CTR_Contact
{

    private $_mail_contact        = "luis@voltsdigital.com.br";

    // -----------------------------------------------------------------------------

    function __construct() { }

    // -----------------------------------------------------------------------------

    /**
     * Send a contact mail
     * @return bool True if sucess, false if fail
     */
    public function sendMail() {

        if(!$_POST)
            return false;

        $contact_info = get_option( 'localizacao_e_contato' );

        if( isset( $contact_info['email_contato'] ) )
            $this->_mail_contact = $contact_info['email_contato'];
        
        $headers[] = "From: MS Shirt - Página de Contato  <". $this->_mail_contact . ">";
        $headers[] = 'Cco: Luís Felipe de Andrade <luis@voltsdigital.com.br>';
        $headers[] = "Content-type: text/html";

        $body      = $this->formatBodyContent();

        if( wp_mail( $this->_mail_contact  ,  " MS Shirt - Página de Contato ", $body, $headers) )
            return true;
        else
            return false;

    }

    // -----------------------------------------------------------------------------

    /**
     * Format the body content of the mail
     * @return string
     */
    private function formatBodyContent() {

        $data = new DateTime("now",  new DateTimeZone('America/Sao_Paulo'));
        $body = file_get_contents(TEMPLATEPATH . '/partials/mail-template/contact.php');
        $body = str_replace( "%name%",  $_POST['fc_name'], $body);
        $body = str_replace( "%titulo%", 'E-mail enviado via Página de Contato', $body);
        $body = str_replace( "%email%", $_POST['fc_email'], $body);
        $body = str_replace( "%telephone%", $_POST['fc_phone'], $body);
        $body = str_replace( "%message%", $_POST['fc_message'], $body);
        $body = str_replace( "%sent_date%", $data->format("d/m/Y H:i:s"), $body );
        return $body;
    }

    // -----------------------------------------------------------------------------

}

new CTR_Contact;
