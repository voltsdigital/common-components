<?php



if( !defined( 'WPINC' ) )
  die();

/**
 *
 * Allows to create restriction for user posting
 */
class CTR_UserPostRestriction {

    private $restrictedPostTypes = array( 'timeline-project' );
    private $restrictedUserRoles = array( 'coordinator' );

    public function __construct(){
        if( ! class_exists( 'Odin_Metabox') ){
            add_action( 'admin_notices', array( $this , 'noticeMissingOdinClass' ) );
            return;
        }
        add_filter( 'checkUserHasRole' , array( $this , 'checkUserHasRole' ) , 10 , 2 );
        add_filter( 'checkUserCanEditRestrictPost' , array( $this , 'checkUserCanEditRestrictPost' ) , 10 , 2 );
        add_filter( 'post_row_actions' , array( $this, 'setUserRowActionRestriction' ), 10, 2 );
        add_filter( 'user_has_cap' , array( $this , 'setRoleHasCap' ) , 10 , 3 );
        add_action( 'init', array( $this, 'createMetaboxPostRestriction') );
    }

    // -----------------------------------------------------------------------------

    public function noticeMissingOdinClass(){
        ?>
        <div class="error">
            <p><?php _e( 'Classe Odin_Metabox não encontrada! A Restrição de posts por usuário não funcionará corretamente!', 'odin' ); ?></p>
        </div>
        <?php
    }

    // -----------------------------------------------------------------------------

    public function checkUserHasRole( $role , $user_id = null ){
        if ( is_numeric( $user_id ) )
            $user = get_userdata( $user_id );
        else
            $user = wp_get_current_user();

        if ( empty( $user ) )
            return false;

        if( is_array( $role ) ){
            $intersected_roles = array_intersect( $role, $user->roles );
            return count( $intersected_roles ) == 0 ? false : true;
        }
        return in_array( $role, (array) $user->roles );
    }

    // -----------------------------------------------------------------------------

    public function checkUserCanEditRestrictPost( $post_id , $user_id = null ){
        if ( is_numeric( $user_id ) )
            $user = get_userdata( $user_id );
        else
            $user = wp_get_current_user();

        if ( empty( $user ) )
            return false;

        if( get_post_meta( $post_id, 'user_can_edit_post_' . $user->ID , true ) == true ){
            return true;
        }

        return false;
    }

    // -----------------------------------------------------------------------------

    public function setUserRowActionRestriction( $actions, $user ){
        $post_id = get_the_ID();

        if( !apply_filters( 'checkUserHasRole' , $this->restrictedUserRoles ) ){
            return $actions;
        }

        $post_type = get_post_type();
        if( !in_array( $post_type, $this->restrictedPostTypes ) ){
            return $actions;
        }

        if( apply_filters( 'checkUserCanEditRestrictPost',  $post_id , $user ) )
            return $actions;

        unset( $actions[ 'edit' ] );
        unset( $actions[ 'inline hide-if-no-js' ] );
        return $actions;
    }

    // -----------------------------------------------------------------------------

    public function setRoleHasCap( $capabilities, $cap, $name ){
        $edit_posts_caps_restricteds = array( 'edit_posts','edit_others_posts', 'edit_published_posts' , 'delete_others_posts' , 'delete_published_posts' );
        $post_id = get_the_ID();

        if( !apply_filters( 'checkUserHasRole' , $this->restrictedUserRoles ) ){
            return $capabilities;
        }

        $post_type = get_post_type();
        if( !in_array( $post_type, $this->restrictedPostTypes ) ){
            return $capabilities;
        }

        if( !( array_intersect( $cap, $edit_posts_caps_restricteds ) ) ){
            return $capabilities;
        }

        if( apply_filters( 'checkUserCanEditRestrictPost',  $post_id  ) ){
            return $capabilities;
        }
    }

    // -----------------------------------------------------------------------------

    public function createMetaboxPostRestriction(){
        if( apply_filters( 'checkUserHasRole' , $this->restrictedUserRoles )){
            return;
        }

        $args = array( 'number' => 0 );
        $eligibleUsers = get_users( $args );
        $current_user = wp_get_current_user();

        foreach( $this->restrictedPostTypes as $post_type ){
            $restrictEligibleUsers = new Odin_Metabox(
                'eligible-user',
                'Editores',
                $post_type,
                'normal',
                'high'
            );

            $usersEligibleUsersArray = array();
            foreach( $eligibleUsers as $user ){

                if( $current_user->ID == $user->ID ){
                    continue;
                }

                $usersEligibleUsersArray[] = array(
                    'id'          => 'user_can_edit_post_' . $user->ID,
                    'label'       => $user->display_name,
                    'type'        => 'checkbox',
                    'is_column'   => false
                );
            }
        }

        $restrictEligibleUsers->set_fields(
            $usersEligibleUsersArray
        );
    }

    // -----------------------------------------------------------------------------
}

new CTR_UserPostRestriction;