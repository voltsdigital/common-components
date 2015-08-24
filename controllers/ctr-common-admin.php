<?php
class Controller_Common_Admin {

    /**
     * Construtor
     */
    public function __construct() {

        /**
         * Adicionar / remover CSS do admin
         */
        add_action( 'admin_print_styles', array( &$this, 'print_styles_admin' ) );


        /**
         * Restaurar colunas no dashboard do Admin do WP
         */
        add_action( 'admin_head-index.php', array( &$this, 'restore_dashboard_columns' ) );


        /**
         * Adicionar CSS para melhorar visualização do codestyling
         */
        add_action( 'admin_head', array( &$this, 'style_codelstyling' ) );


        /**
         * Mudar label dos menus do painel
         */
        add_action( 'admin_menu', array( &$this, 'change_post_label' ) );


        add_action( 'admin_menu', array( &$this, 'removeCustomItemsFromMenu' ) );




        /**
         * Personalizar logo da página de login
         */
        add_action( 'login_head', array( &$this, 'page_login_logo' ) );


        /**
         * Reescrever link da página de login para a raiz do site
         */
        add_filter( 'login_headerurl', array( &$this, 'page_login_url_home' ) );


        /**
         * Reescrever o título do logo da página de login
         */
        add_filter( 'login_headertitle', array( &$this, 'page_login_logo_title' ) );


        /**
         * Alterar texto do rodapé da área de administração do WP
         */
        add_filter( 'admin_footer_text', array( &$this, 'footer_admin' ) );

        /**
         * Remover versão do WP do rodapé
         */
        add_action( 'update_footer', array( &$this, 'text_version' ), 999 );


        /**
         * Favicon para área de administração
         */
        add_action( 'login_head', array( &$this, 'favicon_admin' ) );
        add_action( 'admin_head', array( &$this, 'favicon_admin' ) );


        add_filter( 'wpseo_metabox_prio',  array(&$this, 'lower_wpseo_priority') );

        /*Remover dashboard padrão*/
        add_action('wp_dashboard_setup', array( $this , 'remove_dashboard_widgets') );

        add_action( 'add_meta_boxes', array ( &$this, 'removeMetaboxes' ), 9999 );

        // Remove a barra do menu
        add_filter('show_admin_bar', '__return_false');

        add_filter( 'wc_order_statuses', array( &$this, 'changeNameOrderStatus' ) );

        add_filter('woocommerce_email_customer_details_fields', array(&$this, 'notShowCustomerDetailsInEmail' ), 10, 3);

    } // __construct





    /**
     * Restaurar colunas no Admin do WP
     */
    public function restore_dashboard_columns() {

        add_screen_option(
            'layout_columns',
            array(
                /**
                 * Quantidade máximas de colunas
                 */
                'max' => 2,

                /**
                 * Valor definido como padrão
                 */
                'default' => 1
            )
        );

    } // restore_dashboard_columns





    /**
     * Adicionar / remover CSS do admin
     */
    public function print_styles_admin() {

        $is_editor   = ( current_user_can( 'editor' ) || current_user_can('shop_manager')  ) ? true : false;
        $is_dev_mode = isset( $_GET['dev'] );

        /**
         * Se o usuário for Shop Manager e não estiver em modo de desenvolvimento
         */
        if( $is_editor && ! $is_dev_mode ) {
            wp_enqueue_style( 'interface_cleaner', theme_url( 'admin/public/css/admin-interface-cleaner.css' ), array(), null );
        }

    } // print_styles_admin





    /**
     * Style para codestyling
     */
    public function style_codelstyling() {

        $screen = get_current_screen();

        if( 'tools_page_codestyling-localization/codestyling-localization' != $screen->id ) {
            return false;
        }


        // Deixar tabela com 100% da largura tela
        $style = '<style>';
            $style .= 'table.widefat.clear { width: auto }';
        $style .= '</style>';


        echo $style;

    } // style_codelstyling





    /**
     * Mudar Labels dos menus do painel
     */
    public function change_post_label() {

        global $menu;

        $menu[5][0] = 'Blog';

          $menu['55.5'][0] = 'Loja';

    } // change_post_label





    /**
     * Personalizar logo da página de login
     */
    public function page_login_logo() {
        $logo_url = theme_url( 'public/images/logo-prodata.png' );
        $img_width = 260;
        $img_height = 51;

        $css = '<style>';
            $css .= 'body.login #login h1 a {';
                $css .= "background: url( '{$logo_url}' ) no-repeat scroll center top transparent; background-size: auto;";
                $css .= "height: {$img_height}px;";
                $css .= "width: {$img_width}px;";
            $css .= '}';
        $css .= '</style>';


        echo $css;

    } // page_login_logo





    /**
     * Reescrever link da página de login para a raiz do site
     */
    public function page_login_url_home() {

        return home_url();

    } // page_login_url_home





    /**
     * Reescrever o título do logo da página de login
     */
    public function page_login_logo_title() {

        return esc_attr( get_bloginfo( 'name' ) );

    } // page_login_logo_title





    /**
     * Alterar texto do rodapé da área de administração do WP
     */
    public function footer_admin() {

        $footer_text = '&copy; ' . date( 'Y' ) . ' - ' . get_bloginfo( 'name' );
        $footer_text .=  ' | Criado por <a href="http://www.voltsdigital.com.br" target="_blank">Volts Digital</a>';
        $footer_text .= ' usando <a href="http://www.wordpress.org">WordPress</a>';

        echo $footer_text;

    } // footer_admin





    /**
     * Remover versão do WP do rodapé
     */
    public function text_version() {

        return '';

    } // text_version





    /**
     * Favicon para área de administração
     */
    public function favicon_admin() {

        $favicon  = '<!-- Favicon IE 9 -->';
        $favicon .= '<!--[if lte IE 9]><link rel="icon" type="image/x-icon" href="' . theme_url( 'public/images/favicon.ico' ) . '" /> <![endif]-->';

        $favicon .= '<!-- Favicon Outros Navegadores -->';
        $favicon .= '<link rel="shortcut icon" type="image/png" href="' . theme_url( 'public/images/favicon.png' ) . '" />';

        $favicon .='<!-- Favicon iPhone -->';
        $favicon .='<link rel="apple-touch-icon" href="' . theme_url( 'public/images/favicon.png' ) . '" />';

        echo $favicon;

    } // favicon_admin


    // -----------------------------------------------------------------------------


    /**
     * Muda a prioridade do metaboxe do Yoast
     *
     */
    function lower_wpseo_priority( $html ) {
        return 'low';
    }

    // -----------------------------------------------------------------------------

    public function remove_dashboard_widgets() {
        global $wp_meta_boxes;

        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);

    }


     public function removeCustomItemsFromMenu() {


        $user      = new WP_User( get_current_user_id() );
        $user_role = array_shift($user->roles);


        if ($user_role  == "administrator" )
            return;


        add_menu_page('Dados Gerais', 'Dados Gerais', 'read', 'themes.php?page=dados_gerais', '', 'dashicons-location', 55);
        $role = get_role(    $user_role );
        $this->addRoleCustomCaps( $role );

        remove_menu_page( 'upload.php' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'edit.php' );
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'options-general.php' );
        remove_menu_page( 'profile.php' );
        remove_menu_page( 'link-manager.php' );
    }

    // -----------------------------------------------------------------------------

    public function addRoleCustomCaps( $role ) {

        $role->add_cap( 'edit_theme_options' );
        $role->add_cap( 'manage_options' );

    }


    // -----------------------------------------------------------------------------

    public function removeMetaboxes() {
        $user      = new WP_User( get_current_user_id() );
        $user_role = array_shift($user->roles);

        if ($user_role == "administrator")
            return true;

        remove_meta_box('commentsdiv', 'product', 'normal');
        // remove_meta_box('postexcerpt', 'product', 'normal');
        remove_meta_box('postimagediv', 'page', 'side');
        remove_meta_box('postcustom', 'product', 'normal');
        remove_meta_box('pageparentdiv', 'page', 'side');
        remove_meta_box('tagsdiv-product_tag', 'product', 'side');

    }

    // -----------------------------------------------------------------------------

    public function changeNameOrderStatus( $order_statuses  ) {
        $order_statuses['wc-processing'] = "Processando Internamente";
        $order_statuses['wc-on-hold']    = "Aguardando Confirmação";
        return $order_statuses;
    }

    // -----------------------------------------------------------------------------

    public function notShowCustomerDetailsInEmail( $fields, $sent_to_admin, $order ) {


        if ($sent_to_admin == true )
            return $fields;
        else
            return false;
    }

    // -----------------------------------------------------------------------------


} // Controller_Common_Admin

new Controller_Common_Admin;
