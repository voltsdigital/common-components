<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *
 * Controller Contact
 */
class CTR_Contact
{

    private $defaultRecipient = "luis@voltsdigital.com.br";
    private $customerName     = "Cliente";

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

        $contactInfo = get_option( 'localizacao_e_contato' );

        if( isset( $contactInfo['email_contato'] ) )
            $this->defaultRecipient = $contactInfo['email_contato'];

        $headers[] = "From: " . $this->customerName . " - Página de Contato  <". $this->defaultRecipient . ">";
        $headers[] = 'Bcc: Luís Felipe de Andrade <luis@voltsdigital.com.br>';
        $headers[] = "Content-type: text/html";

        $body      = $this->formatBodyContent();
        $subject   = $this->customerName . " - Página de Contato ";

        if( wp_mail( $this->defaultRecipient, $subject , $body, $headers) )
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

        $contactInfo = get_option('localizacao_e_contato');

        if ( isset ( $contactInfo[ 'logo_email' ][0] ) )
            $logo = $contactInfo[ 'logo_email' ][0];
        else
            $logo = 'http://www.voltsdigital.com.br/site/themes/volts-2013/media/images/logo-volts.png';

        $body = file_get_contents(TEMPLATEPATH . '/partials/mail-template/contact.php');
        $body = str_replace( "%name%",  $_POST['fc_name'], $body);
        $body = str_replace( "%logo%",  $logo,  $body);
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
