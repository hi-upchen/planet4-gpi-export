<?php

/**
 * Add styling to blockquote.
 *
 * @param $text
 * @param $blockquote1
 * @param $blockquote2
 * @param $blockquote3
 *
 * @return mixed
 */
function add_blockquote_style( $text, $blockquote1, $blockquote2, $blockquote3 ) {

	$new_blockquote1 = str_replace( '<blockquote', '<blockquote style="display: block; margin-top: 1em; margin-bottom: 1em; margin-left: 40px !important; margin-right: 40px !important;"', $blockquote1 );
	$text = str_replace( $blockquote1, $new_blockquote1, $text );

	$new_blockquote2 = str_replace( '<blockquote', '<blockquote style="display: block; margin-top: 1em; margin-bottom: 1em; margin-left: 40px !important; margin-right: 40px !important;"', $blockquote2 );
	$text = str_replace( $blockquote2, $new_blockquote2, $text );

	$new_blockquote3 = str_replace( '<blockquote', '<blockquote style="display: block; margin-top: 1em; margin-bottom: 1em; margin-left: 40px !important; margin-right: 40px !important;"', $blockquote3 );
	$text = str_replace( $blockquote3, $new_blockquote3, $text );

	return $text;
}

/**
 * Align floated images correctly.
 *
 * @param $text
 * @param $image
 *
 * @return mixed
 */
function add_image_class( $text, $image ) {
	if ( strpos( $image, 'float: left;' ) !== false ) {
		$text = str_replace( '<img', '<img class="alignleft"', $text );
	} elseif ( strpos( $image, 'float: right;') !== false ) {
		$text = str_replace( '<img', '<img class="alignright"', $text );
	}

	return $text;
}

/**
 * Fix attachments url.
 *
 * @param $text
 * @param $pdf
 * @param $images1
 * @param $images2
 * @param $images3
 * @param $images4
 * @param $images5
 * @param $images6
 * @param $images7
 * @param $images8
 * @param $images9
 * @param $images10
 * @param $images11
 * @param $images12
 * @param $images13
 * @param $images14
 * @param $images15
 * @param $images16
 * @param $images17
 * @param $images18
 * @param $images19
 * @param $images20
 * @param $images21
 * @param $images22
 * @param $images23
 * @param $images24
 *
 * @return mixed
 */
function replace_all_attachments( $text, $pdf, $images1, $images2, $images3, $images4, $images5, $images6, $images7, $images8, $images9, $images10,
	$images11, $images12, $images13, $images14, $images15, $images16, $images17, $images18, $images19, $images20, $images21, $images22, $images23, $images24 ) {

	$text = replace_attachment($text, $pdf);
	$text = replace_attachment($text, $images1);
	$text = replace_attachment($text, $images2);
	$text = replace_attachment($text, $images3);
	$text = replace_attachment($text, $images4);
	$text = replace_attachment($text, $images5);
	$text = replace_attachment($text, $images6);
	$text = replace_attachment($text, $images7);
	$text = replace_attachment($text, $images8);
	$text = replace_attachment($text, $images9);
	$text = replace_attachment($text, $images10);
	$text = replace_attachment($text, $images11);
	$text = replace_attachment($text, $images12);
	$text = replace_attachment($text, $images13);
	$text = replace_attachment($text, $images14);
	$text = replace_attachment($text, $images15);
	$text = replace_attachment($text, $images16);
	$text = replace_attachment($text, $images17);
	$text = replace_attachment($text, $images18);
	$text = replace_attachment($text, $images19);
	$text = replace_attachment($text, $images20);
	$text = replace_attachment($text, $images21);
	$text = replace_attachment($text, $images22);
	$text = replace_attachment($text, $images23);
	$text = replace_attachment($text, $images24);
	return $text;

}

/**
 * Fix attachments url.
 *
 * @param $text
 * @param $attachment
 *
 * @return mixed
 */
function replace_attachment($text, $attachment) {
	$basename = basename($attachment);

	$basename = preg_replace_callback('/\.\w+$/', function($m){
		return strtolower($m[0]);
	}, $basename);

	// Validate file name, and remove first non alphanumeric char.
	if ( !preg_match( "/^[a-zA-Z0-9]$/", substr( $basename, 0, 1 ) ) ) {
          $basename = substr( $basename, 1);
        }

	$basename = str_replace(' ', '-', urldecode($basename));
	$bodytag = str_replace($attachment, "https://storage.googleapis.com/planet4-eu-unit-stateless-release/2018/07/". $basename , $text);
	return $bodytag;
}


add_action('pmxi_saved_post','post_saved',10,1);

/**
 * To manage default the cache busting enabled issue, fetch attachement url from post content and update it using 'pmxi_saved_post' hook.
 *
 * @param $postid
 */
function post_saved( $postid ) {

	$local_path = 'https://release.k8s.p4.greenpeace.org/eu-unit/wp-content/uploads/';
	$gcs_path   = 'https://storage.googleapis.com/planet4-eu-unit-stateless-release/';

	$attachments = get_attached_media( '', $postid );

	$content_post = get_post( $postid );
	$content = $content_post->post_content;
	$content = apply_filters( 'the_content', $content );
	$content = str_replace( ']]>', ']]&gt;', $content );

	preg_match_all( '@src="([^"]+)"@' , $content, $match_img );

	$img_files = array_pop( $match_img );

	preg_match_all( '@href="([^"]+\.pdf|PDF)"@' , $content, $match_pdf );

	$pdf_files = array_pop( $match_pdf );

	foreach ( $img_files as $image_file ) {
		$basename = basename( $image_file );

		foreach ( $attachments as $attachment ) {

			if ( preg_match( '/'.$basename.'$/i', $attachment->guid ) ) {

				if (strpos($attachment->guid, $local_path) !== false) {

					$gcs_file_name = str_replace($local_path, $gcs_path, $attachment->guid);

					$random_str = explode('-', basename( $gcs_file_name ));
					$first_str = $random_str[0];

					if ( 2 !== substr_count( basename( $gcs_file_name ), $first_str ) ) {
						//$gcs_file_name = $first_str . '-' . $gcs_file_name;
						$gcs_file_name = str_replace( $first_str , $first_str.'-'.$first_str, $gcs_file_name );
					}

					wp_update_post(array('ID' => $attachment->ID, 'guid' => $gcs_file_name));

					$content = str_replace( $image_file, $gcs_file_name, $content );
				}
				else
				{
					$content = str_replace( $image_file, $attachment->guid, $content );
				}
			}
		}
	}

	foreach ( $pdf_files as $image_file ) {
		$basename = basename( $image_file );

		foreach ( $attachments as $attachment ) {

			if ( preg_match( '/'.$basename.'$/i', $attachment->guid ) ) {

				if (strpos($attachment->guid, $local_path) !== false) {

					$gcs_file_name = str_replace($local_path, $gcs_path, $attachment->guid);

					wp_update_post(array('ID' => $attachment->ID, 'guid' => $gcs_file_name));

					$content = str_replace( $image_file, $gcs_file_name, $content );
				}
				else
				{
					$content = str_replace( $image_file, $attachment->guid, $content );
				}
			}
		}
	}

	$updated_post = array();
	$updated_post['ID'] = $postid;
	$updated_post['post_content'] = $content;
	wp_update_post( $updated_post );
}


add_action('pmxi_attachment_uploaded', 'fix_attachment_uploaded', 10, 3);

/**
 * To manage default the cache busting enabled issue, fetch attachement url and update it using 'pmxi_attachment_uploaded' hook.
 *
 * @param $postid
 */

function fix_attachment_uploaded($pid, $attid, $filepath){
  $attachment = get_post($attid);

  $local_path = 'https://release.k8s.p4.greenpeace.org/eu-unit/wp-content/uploads/';
  $gcs_path   = 'https://storage.googleapis.com/planet4-eu-unit-stateless-release/';

  if ( preg_match( '/^'.$local_path.'/i', $attachment->guid ) ) {
		wp_update_post(array('ID' => $attid, 'guid' => str_replace($local_path, $gcs_path, $attachment->guid)));
  }
}

?>
