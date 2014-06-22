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

/**
 * GET 请求
 * @param string $url
 * @return string $result
 */

function http_get($url) {
  $oCurl = curl_init();
  if(stripos($url,"https://")!==FALSE) {
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
  }

  curl_setopt($oCurl, CURLOPT_URL, $url);
  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

  $result = curl_exec($oCurl);
  $status = curl_getinfo($oCurl);
  curl_close($oCurl);

  if(intval($status["http_code"])==200)
    return $result;
  return $status["http_code"];
}


/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @return string $result
 */
function http_post($url, $param) {

  $oCurl = curl_init();

  if(stripos($url,"https://")!==FALSE){
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
	}

	if (is_string($param)) {
	   $strPOST = $param;
	} else {
    $aPOST = array();
    foreach($param as $key=>$val) {
      $aPOST[] = $key."=".urlencode($val);
		}
		$strPOST =  join("&", $aPOST);
  }

  curl_setopt($oCurl, CURLOPT_URL, $url);
  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt($oCurl, CURLOPT_POST,true);
  curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);

	$result = curl_exec($oCurl);
	$status = curl_getinfo($oCurl);
	curl_close($oCurl);
	if(intval($status["http_code"])==200)
    return $result;
  return $status["http_code"];
}
?>
