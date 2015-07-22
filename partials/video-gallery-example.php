<?php



/*
 *  Vídeos - Exemplo de Listagmem de Vídeos e miniaturas
 */


foreach( $videos as $video ) :

    //https://www.youtube.com/watch?v=iQ_s35e8jC8
    if ( strstr($video, 'watch?v=') )
        $codigoVideo = array_pop( explode('watch?v=', $video) );
    else
        $codigoVideo = array_pop( explode("/", $video));  //http://youtu.be/WqjjE1g6_RU
    ?>

    <div class="swiper-slide" style='background:url(http://img.youtube.com/vi/<?php echo $codigoVideo; ?>/maxresdefault.jpg) no-repeat center center / cover;'><div class='play' data-video='https://www.youtube.com/embed/<?php echo $codigoVideo; ?>'></div></div>
<?php

endforeach; ?>