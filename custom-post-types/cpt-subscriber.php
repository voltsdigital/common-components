<?php

if( !defined( 'WPINC' ) )
  die();

class CPTSubscriber {

    // -----------------------------------------------------------------------------

    public function __construct() {

        $assinante = new Odin_Post_Type(
            'Assinante',
            'assinante'
        );

        $assinante->set_arguments(
            array(
                'supports'            => array( 'title' ),
                'hierarchical'        => false,
                'menu_icon'           => 'dashicons-book',
                'exclude_from_search' => true,
                'rewrite'             => false
            )
        );

        add_filter( 'manage_edit-assinante_columns',        array( $this, 'columnsToShowOnList' ));
        add_action( 'manage_assinante_posts_custom_column', array( $this, 'valuesToShowOnList'), 10,2);
        add_action( 'months_dropdown_results',              array( $this, 'definVisibilityDropdown' ), 10, 1);
        add_action( 'admin_menu',                           array( $this, 'exportSubscriber') );
        add_action( 'admin_init',                           array( $this, 'generateXLS') );

    }
     // -----------------------------------------------------------------------------
    /**
     * Defini se o filtro dropdown será exibido na listagem do post
     * @param  array $year_month_array   Dropdown Itens
     * @return array
     */
    public function definVisibilityDropdown( $year_month_array ){
        switch( get_post_type()){
            case 'assinante':
                return array();
                break;
            default:
                return $year_month_array;
        }
    }

    // -----------------------------------------------------------------------------
    /**
     * Edita as colunas que serão exibigas na listagem do post
     * @param  array $columns   Colunas
     * @return array
     */
    public function columnsToShowOnList( $columns ) {

        unset($columns["date"]);
        unset($columns["wpseo-metadesc"]);
        unset($columns["wpseo-title"]);
        unset($columns["wpseo-focuskw"]);
        unset($columns["wpseo-score"]);

        $columns[ 'title' ] = 'Email';

        $new_columns = array(
            'assinante_nome'   => 'Nome',
            'assinante_ativo'  => 'Ativo?'
        );

        return array_merge( $columns, $new_columns );

    }

    // -----------------------------------------------------------------------------

    /**
     * Pega os valores das colunas customizadas
     * @param  array $column  Colunas com os indíces a serem exibidas
     * @param  int $post_id ID
     * @return  string          Valor do campo
     */
    public function valuesToShowOnList( $column, $post_id ) {
        $logo_size = array( 50 , 50 );
        switch ( $column ) {
            case 'assinante_ativo':
                $ativo = get_post_meta( $post_id, $column , true) ;
                if( $ativo == 1 ){
                    $valor = 'Sim';
                }
                else{
                    $valor = 'Não';
                }
                break;

            default:
                $valor = get_post_meta( $post_id, $column , true );
                break;
        }

        echo $valor;
    }

    // -----------------------------------------------------------------------------

    public function generateXLS(){
        if( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'exportar_assinantes_xls' ){
            $this->downloadXLS();
            exit;
        }
    }

    // -----------------------------------------------------------------------------

    public function exportSubscriber(){
        $page = add_submenu_page( 'edit.php?post_type=assinante' ,'Exportar Assinantes em XLS' , 'Exportar Assinantes em XLS' , 'manage_categories' ,'admin.php?page=exportar_assinantes_xls' );
    }

    // -----------------------------------------------------------------------------

    public function getSubscribers( $somente_ativos  = false ){
        $args_assinantes = array(
            'post_type' => 'assinante',
            'posts_per_page' => -1
        );


        if( $somente_ativos ){
            $args_assinantes[ 'meta_query' ] = array(
                array(
                    'key' => 'assinante_ativo',
                    'value' => '1',
                    'compare' => '='
                )
            );
         }

        $posts_assinantes = get_posts( $args_assinantes );

        return $posts_assinantes;
    }

    // -----------------------------------------------------------------------------

    public function downloadXLS(){

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="arquivo.csv";');

        $assinantes = $this->getSubscribers();
        $out        = fopen('php://output', 'w');

        foreach( $assinantes as $assinante ){
            $dados =   array(
                get_post_meta( $assinante->ID , 'assinante_nome' , true ),
                $assinante->post_title,
                get_post_meta( $assinante->ID , 'assinante_ativo' , true ) == 1 ? 'Sim' : 'Não'
            );
            fputcsv($out, $dados);
        }

        fclose($out);

        exit;

    }

    // -----------------------------------------------------------------------------
}

new CPTSubscriber;

?>