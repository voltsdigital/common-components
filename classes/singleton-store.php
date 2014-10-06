<?php

if( !defined( 'WPINC' ) )
  die();

/**
 *  Singleton Store
 *  @description define the attributes and methos to be used in all parts of the store
 */
class Store
{

    private static $taxonomy_color_name = "pa_cor";
    private static $taxonomy_size_name  = "pa_tamanho";

    /**
     * Retorna uma instância única de uma classe.
     *
     * @staticvar Singleton $instance A instância única dessa classe.
     *
     * @return Singleton A Instância única.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Construtor do tipo protegido previne que uma nova instância da
     * Classe seja criada atravês do operador `new` de fora dessa classe.
     */
    protected function __construct()
    {
    }

    /**
     * Método clone do tipo privado previne a clonagem dessa instância
     * da classe
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Método unserialize do tipo privado para previnir que desserialização
     * da instância dessa classe.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    public function getTaxonomyColorName( ) {
        return self::$taxonomy_color_name;
    }

    public function getTaxonomySizeName( ) {
        return self::$taxonomy_size_name;
    }

    public function getColorMetaData( $colorName ) {

        $term   = get_term_by( 'name', $colorName , self::$taxonomy_color_name );
        if( !$term )
            $term = get_term_by( 'slug', $colorName , self::$taxonomy_color_name );
        $data   = get_option( 'tax_meta_'. $term->term_id );

        if( $data['ba_image_field_id'] &&  !isset($data['thumbnail'] ) ) {

            $imageId                          = $data['ba_image_field_id']['id'];
            $image                            = ImageFactory::create( $imageId, '24', '24', array('center', 'center') );
            $data['ba_image_field_id']['src'] = $image->imageThumbnail;
            $data['thumbnail']                = true;
            update_option( 'tax_meta_'. $term->term_id , $data );
        }


        return $data;
    }
}