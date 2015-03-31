<?php

if( !defined( 'WPINC' ) )
  die();

class Theme_Options extends Odin_Theme_Options {

    // -----------------------------------------------------------------------------

    public function __construct() {

        $this->createThemeOptions();
    }

    // -----------------------------------------------------------------------------

    public function createThemeOptions() {

        $tema_opcoes = new Odin_Theme_Options(
            'dados_gerais', // Slug/ID da página (Obrigatório)
            'Dados Gerais',
            'edit_theme_options'
        );

        $tema_opcoes->set_tabs(
            array(
                array(
                    'id'    => 'localizacao_e_contato',
                    'title' => 'Localização e Contato' // Título da aba.
                ),
                array(
                    'id'    => 'redes_sociais',
                    'title' =>  'Redes Sociais'
                )
            )
        );

        $tema_opcoes->set_fields(
            array(
                'localizationFields'      =>  $this->getLocalizationFields(),
                'socialNetworks' =>  $this->getSocialNetwork()
            )
        );
    }

    // -----------------------------------------------------------------------------

    public function getLocalizationFields() {
        return
        array(
            'tab'   => 'localizacao_e_contato', 
            'title' => 'Dados de Localização',
            'fields' => array(
                array(
                    'id'          => 'endereco',
                    'label'       => 'Endereço',
                    'type'        => 'text',
                    'description' => 'Rua xxx, No Y - Bairro - Cidade/UF Estado'
                ),
                array(
                    'id'          => 'telefone',
                    'label'       => 'Telefone',
                    'type'        => 'text',
                    'description' => '(DD) XXXX-XXXX'
                ),
                array(
                    'id'          => 'email_contato',
                    'label'       => 'E-mail de Contato:',
                    'type'        => 'text',
                    'description' => 'Ex: contato@xxxx.com.br'
                ),
                array(
                    'id'    => 'logo_email',
                    'label' => 'Logo E-mail',
                    'type'  => 'image',
                ),
                array(
                    'id'          => 'cnpj',
                    'label'       => 'CNPJ:',
                    'type'        => 'text',
                    'description' => 'Ex: 12.256.014/0001-37'
                ),
                array(
                    'id'          => 'horario_atendimento',
                    'label'       => 'Horário de Atendimento:',
                    'type'        => 'text',
                    'description' => 'Ex: Seg. à Sex. das 8:30 às 17:30'
                ),

            )
        );

    }

    // -----------------------------------------------------------------------------

    public function getSocialNetwork() {
        return array(
            'tab'   => 'redes_sociais',
            'title' => 'Links das Redes Sociais',
            'fields' => array(
                array(
                    'id'    => 'link_facebook',
                    'label' => 'Link Facebook',
                    'type'  => 'text'
                ),
                array(
                    'id'    => 'link_twitter',
                    'label' => 'Link Twitter',
                    'type'  => 'text'
                ),
                array(
                    'id'    => 'link_instagram',
                    'label' => 'Link Instagram',
                    'type'  => 'text'
                ),
                array(
                    'id'    => 'link_pinterest',
                    'label' => 'Link Pinterest',
                    'type'  => 'text'
                )
            )
        );
    }

    // -----------------------------------------------------------------------------
}


new Theme_Options;
