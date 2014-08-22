<?php

class MTB_Services Extends MTB_Page{

    public function setup_metaboxes(){

        $services = new Odin_Metabox(
            'services_us_info', // Slug/ID do Metabox (obrigatório)
            'Serviços', // Nome do Metabox  (obrigatório)
            'page', // Slug do Post Type (opcional)
            'normal', // Contexto (opções: normal, advanced, ou side) (opcional)
            'high' // Prioridade (opções: high, core, default ou low) (opcional)
        );

        $services->set_fields(
            array(
                array(
                    'id'          => 'services_content',
                    'label'        => 'Descrição',
                    'type'        => 'editor', // Obrigatório
                    'options'     => array(
                            'textarea_rows' => 5,
                            'media_buttons' => false
                        ),
                ),
            )
        );
    }

    public function setup_editor_settings( $settings ) {
        $style_formats = array();
        $settings['style_formats'] = json_encode( $style_formats );
        $toolbar1 = 'bold,italic,bullist';
        $settings['toolbar1'] = $toolbar1;
        $settings['toolbar2'] = '';
        return $settings;
    }

}

$mtb_services_us = new MTB_Services( 'page-servicos.php' );