<?php

if( !defined( 'WPINC' ) )
  die();

class CTRPosts
{

    // -----------------------------------------------------------------------------

    function __construct() {

        $this->widthCoverImage             = 650;
        $this->heightCoverImage            = 330;
        $this->coverCrop                   = false;
        $this->numberCharactersAutoExcerpt = 100;
    }

    // -----------------------------------------------------------------------------

    public function getPostsFromHome()
    {
        global $wp_query;

        $args = array(
          'post_type'      => 'post',
          'paged'          => ( isset( $wp_query->query['paged'] ) ) ? $wp_query->query['paged'] : 1,
          'posts_per_page' => get_option('posts_per_page')
        );

        //Adds nextpage to make SEO rel=next AND rel=prev work
        $query                        = new WP_Query( $args );
        $wp_query->post->post_content .='<!--nextpage-->' . $query->max_num_pages;

        $query->posts  = $this->getMetaData( $query->posts );
        return $query;

    }

    // -----------------------------------------------------------------------------

    public function getMostRecentPosts( $numberOfPosts = 3 )
    {

        $args = array(
          'post_type'      => 'post',
          'paged'          => 1,
          'posts_per_page' => $numberOfPosts,
          'orderby'        => 'post_date',
          'order'          => 'DESC',
        );

        $query         = new WP_Query( $args );
        $query->posts  = $this->getMetaData( $query->posts );
        return $query;
    }


    // -----------------------------------------------------------------------------

    public function getPostsFromRegularQuery()
    {
        global $wp_query;

        if ( isset($wp_query->query['s'] ) && $wp_query->query['s'] ) {
            $args              = $wp_query->query;
            $args['post_type'] = 'post';
            $wp_query          = new WP_Query( $args );
        }


        $wp_query->posts  = $this->getMetaData( $wp_query->posts );
        return $wp_query;
    }

    // -----------------------------------------------------------------------------


    public function getMetaData( $posts ) {

        foreach ( $posts as $post ) {
            $this->getMetaDataFromPost( $post );
        }

        return $posts;
    }

    // -----------------------------------------------------------------------------

    public function getMetaDataFromPost( $post ) {

        $post                  = $this->getAuthorDataFromPost( $post ) ;
        $post->permalink       = get_permalink( $post->ID );
        $post->comments_number = get_comments_number( $post->ID );
        $post->cover           = $this->getCoverFromPost( $post );
        $post->post_excerpt    = $this->getExcerptFromPost( $post );
        return $post;
    }

    // -----------------------------------------------------------------------------

    public function getAuthorDataFromPost( $post ) {

        $post->authorName = get_the_author_meta( 'display_name',  $post->post_author );
        $post->authorLink = get_author_posts_url( $post->post_author );
        $postTerms        = wp_get_object_terms($post->ID, "category");
        $post->category   = array_shift( $postTerms  );


        return $post;
    }

    // -----------------------------------------------------------------------------

    public function getCoverFromPost( $post ) {

        $thumbnailID  = get_post_thumbnail_id ( $post->ID );

        if ( $thumbnailID  == '' ||  !$thumbnailID  )
            return false;

        $newImage = ImageFactory::create( $thumbnailID , $this->widthCoverImage , $this->heightCoverImage, $this->coverCrop );

        if (  ! $newImage->imageThumbnail )
             $newImage->imageThumbnail = $newImage->imageSrc;

        return $newImage;
    }

    // -----------------------------------------------------------------------------

    public function getExcerptFromPost( $post ) {

        //Retrieve the post content.
        $text          = apply_filters('the_content', strip_shortcodes( $post->post_content ) );
        $text          = str_replace(']]&gt;', ']]&gt;', $text);
        $excerptLength = apply_filters('excerpt_length', $this->numberCharactersAutoExcerpt);

        $words = preg_split("/[\n\r\t ]+/", $text, $excerptLength + 1, PREG_SPLIT_NO_EMPTY);

        if ( count($words) && $excerptLength ) {
            array_pop($words);
            $text = implode(' ', $words);
            $text = $text . '...';
            $text = force_balance_tags( $text );

        } else {
            $text = implode(' ', $words);
        }

        return $text;
    }

    // -----------------------------------------------------------------------------
}

