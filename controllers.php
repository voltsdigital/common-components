<?php
if( ! defined( 'WPINC' ) ) {
    header( 'Location: /' );
    exit;
}

// --------------------------------

if( ! defined( 'CONTROLLERS_DIR' ) ) {
    define( 'CONTROLLERS_DIR', FUNCTIONS_DIR . '/controllers' );
}

//Carrega todos os arquivos no diretório
foreach (glob( CONTROLLERS_DIR ."/*.php") as $arquivo) {
	require_once  $arquivo;
}


