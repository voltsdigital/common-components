<?php

class MTB_Advantages Extends MTB_Page{

    //Já inclui a checagem do template/page
    public function setup_metaboxes(){

        $advantages = new Odin_Metabox(
            'advantages_us_info', // Slug/ID do Metabox (obrigatório)
            'Serviços', // Nome do Metabox  (obrigatório)
            'page', // Slug do Post Type (opcional)
            'normal', // Contexto (opções: normal, advanced, ou side) (opcional)
            'high' // Prioridade (opções: high, core, default ou low) (opcional)
        );

        $advantages->set_fields(
            array(
                array(
                    'id'          => 'advantages_content',
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

    //Já inclui a checagem do template/page
    public function setup_editor_settings( $settings ) {
        $style_formats = array();
        $settings['style_formats'] = json_encode( $style_formats );
        $toolbar1 = 'bold,italic,bullist';
        $settings['toolbar1'] = $toolbar1;
        $settings['toolbar2'] = '';
        return $settings;
    }

}
//Restrição para o template passado
$mtb_advantages_us = new MTB_Advantages( 'page-vantagens.php' );