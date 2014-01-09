<?php

function custom_excerpt_length( $length ) {
	return 100;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

/* no use for get_the_excerpt
function new_excerpt_more( $more ) {
	return '[.....]';
}
add_filter('excerpt_more', 'new_excerpt_more');
 */


/**
 *
 * 返回摘要，50个汉字加省略号
 *
 * @raw_excerpt 由调用函数传来的未经处理的excerpt
 *
 */
function wechat_get_excerpt($raw_excerpt) {
    $excerpt = wp_strip_all_tags( $raw_excerpt );
    $excerpt = trim( preg_replace( "/[\n\r\t ]+/", ' ', $excerpt ), ' ' );
    $excerpt = mb_substr($excerpt, 0, 50, 'utf8');
    $excerpt = $excerpt . '...';
    return $excerpt;
}

/**
 *
 * 返回post缩略图
 *
 */
function wechat_get_thumb( $post, $size ){
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    if ( $thumbnail_id ) {
        $thumb = wp_get_attachment_image_src($thumbnail_id, $size);
        $thumb = $thumb[0];
    }

    if (empty($thumb)) {
        $thumb = 'http://www.freebuf.com/buf/themes/freebuf/images/logo2.jpg';
    }

    return $thumb;
}


?>
