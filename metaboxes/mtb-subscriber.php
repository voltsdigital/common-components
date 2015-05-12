<?php

if( !defined( 'WPINC' ) )
  die();

class MTB_Subscriber {

    // -----------------------------------------------------------------------------

    public function __construct() {

        $assinante = new Odin_Metabox(
            'assinante_informacoes', // Slug/ID do Metabox (obrigatório)
            'Informações do Assinante', // Nome do Metabox  (obrigatório)
            'assinante', // Slug do Post Type (opcional)
            'normal', // Contexto (opções: normal, advanced, ou side) (opcional)
            'high' // Prioridade (opções: high, core, default ou low) (opcional)
        );

        $assinante->set_fields(
            array(
                array(
                    'id'          => 'assinante_ativo',
                    'name'        => 'Ativo',
                    'label'       => 'Ativo',
                    'type'        => 'select',
                    'options'     => array( -1 => 'Não' , 1 => 'Sim' ),
                ),
            )
        );
    }

    // -----------------------------------------------------------------------------

}

new MTB_Subscriber;

?>