<?php
/*
Plugin Name: Collage Gallery
Description: Plugin automatically create responsive collage gallery (like Google, Flickr, VK.com...) from images attached to the post with lightbox.
Version: 0.4
Plugin URI: http://ukraya.ru/collage-gallery/
Author: Aleksej Solovjov
Author URI: http://ukraya.ru
Text Domain: collage-gallery
Domain Path: /languages/
License: GPL v2 or later
*/

/*  Copyright 2015 Aleksej Solovjov

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

/* Admin Page */

add_action( 'admin_init', 'ug_init' );
function ug_init() {
	load_plugin_textdomain( 'collage-gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

if ( ! class_exists( 'WP_Settings_API_Class' ) ) {
	include_once( 'inc/wp-settings-api-class.php' );
}

add_action( 'wp_head', 'ug_wp_head', 99 );
function ug_wp_head() {
	global $post;
	$options = get_option( 'ug_options' );

	echo '<style type="text/css">';
	echo '.collage_gallery:before, .collage_gallery:after { content: ""; display: table; clear:both; } .collage_gallery { display:block; max-width: 100%; margin-bottom:20px; display:none; } .collage_img_wrap { height: auto; max-width: 100%; margin-bottom:20px; } .collage_img_wrap img { border: 0 none; vertical-align: middle; height: auto; max-width: 100%; } .collage_img_wrap span { font-size:11px; } .ug-social-data { display: flex; flex-direction: row; justify-content: flex-start; } .ug-social-data > * { display: inline-block; white-space: nowrap; padding-right: 10px; } .ug-social-data div:last-child { padding-right: 0; } .ug-spacer { flex-grow: 1; }';

	/*

	.collage_gallery:before,
	.collage_gallery:after {
	content: "";
	display: table;
	clear:both;
	}

	.collage_gallery {
	display:block;
	max-width: 100%;
	margin-bottom:20px;
	display:none;
	}

	.collage_img_wrap {
	height: auto;
	max-width: 100%;
	margin-bottom:20px;
	}

	.collage_img_wrap img {
	border: 0 none;
	vertical-align: middle;
	height: auto;
	max-width: 100%;
	}

	.collage_img_wrap span {
	font-size:11px;
	}

	.ug-social-data {
	display: flex;
	flex-direction: row;
	justify-content: flex-start;
	}

	.ug-social-data > * {
	display: inline-block;
	white-space: nowrap;
	padding-right: 10px;
	}

	.ug-social-data div:last-child {
	padding-right: 0;
	}

	.ug-spacer {
	flex-grow: 1;
	}
	*/

	if ( isset( $options['css'] ) && ! empty ( $options['css'] ) ) {
		echo $options['css'];
	}

	echo '</style>';
	?>
	<script type="text/javascript">

		var ugDefaults = {
			rowHeight: <?php echo $options['row_height']; ?>,
			maxRowHeight: <?php echo $options['max_row_height']; ?>,
			<?php /*
			sizeRangeSuffixes: {
			  'lt100':'',
			  'lt240':'',
			  'lt320':'',
			  'lt500':'',
			  'lt640':'',
			  'lt1024':''
			},
			*/ ?>
			lastRow: '<?php echo $options['last_row']; ?>',
			fixedHeight: <?php echo $options['fixed_height'] == 1 ? 'true' : 'false'; ?>,
			captions: <?php echo $options['caption'] == 1 ? 'true' : 'false'; ?>,
			margins: <?php echo $options['margins']; ?>,
			border: <?php echo $options['border']; ?>
			<?php /*
			randomize: false,
			extension: "/.[^.]+$/",
			refreshTime: 250,
			waitThumbnailsLoad: true,
			rel:null,
			target:null,
			justifyThreshold:'0.35', // If available space / row width < 0.35 the last row is justified, without considering the lastRow setting.
			cssAnimation: false,
			imagesAnimationDuration:'300',
			captionSettings: {
			  animationDuration: 500,
			  visibleOpacity: 0.7,
			  nonVisibleOpacity: 0.0
			}
			*/ ?>
		};

	</script>
	<?php
}

add_action( 'wp_footer', 'ug_wp_footer', 5 );
function ug_wp_footer() {
	$options = get_option( 'ug_options' );

	if ( isset( $options['lightbox'] ) && $options['lightbox'] == 'photoswipe' ) {
		echo ug_pswp();
	}

	// http://miromannino.github.io/Justified-Gallery/options-and-events/
	?>
	<script type="text/javascript">

		jQuery(document).ready(function ($) {

			$('.ug').collageGallery();
		});

	</script>
	<?php
}


add_action( 'wp_enqueue_scripts', 'ug_enqueue_scripts' );
function ug_enqueue_scripts() {
	$options = get_option( 'ug_options' );

	wp_enqueue_script( 'justifiedGallery', plugins_url( 'inc/justified-gallery/jquery.justifiedGallery.min.js', __FILE__ ), array( 'jquery' ) );

	wp_register_style( 'justifiedGallery', plugins_url( 'inc/justified-gallery/justifiedGallery.min.css', __FILE__ ) );
	wp_enqueue_style( 'justifiedGallery' );

	if ( isset( $options['lightbox'] ) && $options['lightbox'] == 'photoswipe' ) {

		// PhotoSwipe
		wp_enqueue_script( 'photoswipe', plugins_url( 'inc/photoswipe/photoswipe.min.js', __FILE__ ), array(), false, true );
		wp_enqueue_script( 'photoswipe-ui-default', plugins_url( 'inc/photoswipe/photoswipe-ui-default.min.js', __FILE__ ), array( 'photoswipe' ), false, true );

		wp_register_style( 'photoswipe', plugins_url( 'inc/photoswipe/photoswipe.css', __FILE__ ) );
		wp_enqueue_style( 'photoswipe' );
		wp_register_style( 'photoswipe-default-skin', plugins_url( 'inc/photoswipe/default-skin/default-skin.css', __FILE__ ) );
		wp_enqueue_style( 'photoswipe-default-skin' );

		// Collage Gallery Photoswipe
		wp_enqueue_script( 'collage-gallery-photoswipe', plugins_url( 'js/collage-gallery-photoswipe.js', __FILE__ ), array(
			'photoswipe',
			'photoswipe-ui-default'
		), false, true );

		$deps = array(
			'photoswipe',
			'photoswipe-ui-default',
			'collage-gallery-photoswipe'
		);
	}
	$deps[] = 'jquery';
	wp_enqueue_script( 'collage-gallery', plugins_url( 'js/collage-gallery.js', __FILE__ ), $deps, false, true );

}

function ug_pswp() {

	$out = '
<!-- Root element of PhotoSwipe. Must have class pswp. -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

  <!-- Background of PhotoSwipe. 
  Its a separate element as animating opacity is faster than rgba(). -->
  <div class="pswp__bg"></div>

  <!-- Slides wrapper with overflow:hidden. -->
  <div class="pswp__scroll-wrap">

    <!-- Container that holds slides. 
      PhotoSwipe keeps only 3 of them in the DOM to save memory.
      Dont modify these 3 pswp__item elements, data is added later on. -->
    <div class="pswp__container">
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
    </div>

    <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
    <div class="pswp__ui pswp__ui--hidden">

      <div class="pswp__top-bar">

        <!--  Controls are self-explanatory. Order can be changed. -->

        <div class="pswp__counter"></div>

        <button class="pswp__button pswp__button--close" title="' . __( 'Close (Esc)', 'collage-gallery' ) . '"></button>

        <button class="pswp__button pswp__button--share" title="' . __( 'Share', 'collage-gallery' ) . '"></button>

        <button class="pswp__button pswp__button--fs" title="' . __( 'Toggle fullscreen', 'collage-gallery' ) . '"></button>

        <button class="pswp__button pswp__button--zoom" title="' . __( 'Zoom in/out', 'collage-gallery' ) . '"></button>

        <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
        <!-- element will get class pswp__preloader--active when preloader is running -->
        <div class="pswp__preloader">
          <div class="pswp__preloader__icn">
            <div class="pswp__preloader__cut">
              <div class="pswp__preloader__donut"></div>
            </div>
          </div>
        </div>
        
      </div>

      <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
        <div class="pswp__share-tooltip"></div> 
      </div>

      <button class="pswp__button pswp__button--arrow--left" title="' . __( 'Previous (arrow left)', 'collage-gallery' ) . '">
      </button>

      <button class="pswp__button pswp__button--arrow--right" title="' . __( 'Next (arrow right)', 'collage-gallery' ) . '">
      </button>

      <div class="pswp__caption">
        <div class="pswp__caption__center"></div>
      </div>

    </div>

  </div>

</div>';

	return $out;
}

// photo = "1,3,4-12"
function ug_gallery_helper( $photo ) {

	$photo_num_show = array();
	if ( strpos( $photo, ',' ) && strpos( $photo, '-' ) ) {
		$photos = explode( ',', $photo );
		foreach ( $photos as $ps ) {
			if ( strpos( $ps, '-' ) ) {
				$p = explode( '-', $ps );
				for ( $i = $p[0]; $i < ( $p[1] + 1 ); $i ++ ) {
					$photo_num_show[] = $i;
				}
			} else {
				$photo_num_show[] = $ps;
			}
		}
	} elseif ( strpos( $photo, ',' ) && ! strpos( $photo, '-' ) ) {
		$photos = explode( ',', $photo );
		foreach ( $photos as $ps ) {
			$photo_num_show[] = $ps;
		}
	} elseif ( ! strpos( $photo, ',' ) && strpos( $photo, '-' ) ) {
		$p = explode( '-', $photo );
		for ( $i = $p[0]; $i < ( $p[1] + 1 ); $i ++ ) {
			$photo_num_show[] = $i;
		}
	} else {
		$photo_num_show[] = $photo;
	}

	return $photo_num_show;
}

add_shortcode( 'collage_gallery', 'ug_shortcode' );
function ug_shortcode( $atts = array(), $content = '' ) {
	global $post, $comment;
	$options = get_option( 'ug_options' );

	if ( isset( $atts['in'] ) && $atts['in'] == 'comment' ) {
		$type = 'comment';
		if ( ! empty( $comment ) ) {
			$meta_id = $comment->comment_ID;
		}
	} else {
		$type = 'post';
		if ( ! empty( $post ) ) {
			$meta_id = $post->ID;
		}
	}

	if ( isset( $options['meta_name'] ) && ! empty( $options['meta_name'] ) && ! empty( $post ) && call_user_func( 'get_' . $type . '_meta', $meta_id, $options['meta_name'], true ) == false ) {
		return '';
	}

	if ( ! empty( $atts ) ) {
		extract( $atts );
	}

	if ( isset( $in ) && $in == 'comment' ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'meta_query'     => array(
				array(
					'key'   => 'comment_parent',
					'value' => $comment->comment_ID
				),
			),
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'numberposts'    => 999
		);

		$images = get_posts( $args );
	} else if ( ! empty( $id ) ) {
		$post__in = explode( ',', $id );

		$args = array(
			'post__in'       => (array) $post__in,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'numberposts'    => 999
		);

		$images = get_posts( $args );
	} else {
		$images = get_children( array(
			'post_parent'    => $post->ID,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'orderby'        => 'menu_order id',
			'order'          => 'ASC',
			'numberposts'    => 999
		) );
	}

	$n = count( $images );

	$images = apply_filters( 'ug_shortcode_images', $images, $meta_id, $type, $atts );
	$out    = '';
	$i      = 1;

	if ( ! empty( $images ) ) {
		$photo_num_show = ( isset( $photo ) ) ? ug_gallery_helper( $photo ) : null;

		foreach ( $images as $image ) {
			$temp = '';

			// Images link
			//$link = get_permalink( $image->ID );
			//$src  = wp_get_attachment_image_src( $image->ID, 'medium' );
			$src = ! empty( $image->src ) ? ( $image->src ) : wp_get_attachment_image_src( $image->ID, 'medium' );

			// PhotoSwipe
			$data_size = '';
			if ( ( isset( $lightbox ) && $lightbox == 'photoswipe' ) ||
			     ( ! isset( $lightbox ) && isset( $options['lightbox'] ) && $options['lightbox'] == 'photoswipe' )
			) {

				$src_full = ! empty( $image->size ) ? ( $image->size ) : wp_get_attachment_image_src( $image->ID, 'full' );
				//$src_full        = wp_get_attachment_image_src( $image->ID, 'full' );
				$data_size       = 'data-size = "' . $src_full[1] . 'x' . $src_full[2] . '"';
				$options['link'] = 'image';
				$link            = null;
			}

			if ( isset( $photo_num_show ) && ! empty( $photo_num_show ) ) {
				if ( ! in_array( $i, $photo_num_show ) ) {
					$i ++;
					continue;
				}
			}

			if ( ! empty( $image->title ) ) {
				$title = $image->title;
			} else {
				$title = the_title_attribute( array(
					'echo' => false,
					'post' => $image->ID
				) );
			}

			$_link = ug_shortcode_parse_str( $link, $options['link'], 'page' );
			//$options['link'] = isset( $options['link'] ) ? $options['link'] : 'page';

			$alt = ! empty( $title ) ? 'alt="' . $title . '"' : '';

			$img  = '<img ' . $alt . ' src="' . $src[0] . '" width = "' . $src[1] . '" height = "' . $src[2] . '"/>';
			$href = '';
			if ( $_link == 'image' ) {
				$href = $image->guid;
			}

			if ( $_link == 'page' ) {
				$href = get_permalink( $image->ID );
			}

			$a = '<a href="' . $href . '" ' . $data_size . ' >' . $img . '</a>';

			if ( $_link == 'none' ) {
				$a = $img;
			}

			$caption = '';

			$image_post_content = trim( $image->post_content );
			if ( ! empty( $image_post_content ) ) {
				$caption = '<div class = "caption">' . $image_post_content . '</div>';
			}

			if ( ! empty( $one_image ) ) {
				$_one_image = ug_shortcode_parse_str( $one_image, $options['one_image'], '' );
			}
			$item_class = '';
			// one_image == cresponsive
			if ( $n == 1 && $_one_image == 'responsive' ) {

				if ( ! empty( $image->post_content ) ) {
					$caption = '<p>' . $image->post_content . '</p>';
				}

				$item_class = 'collage_img_wrap';
			}

			$temp = '<div ' . $item_class . ' data-id="' . $image->ID . '">' . $a . $caption . '</div>';

			// one_image == none
			if ( $n == 1 && ! empty( $_one_image ) && $_one_image == 'none' ) {
				$temp = '';
			}

			$out[] = apply_filters(
				'ug_shortcode_item',
				$temp,
				$image,
				array(
					'class'     => $item_class,
					'src'       => $src,
					'title'     => $title,
					'href'      => $href,
					'caption'   => $caption,
					'data-size' => $data_size,
					'img'       => $img,
					'a'         => $a
				)
			);

			$i ++;
		}
	}
	if ( ! empty( $out ) && is_array( $out ) ) {
		$out = implode( '', $out );
	}

	if ( ! empty( $out ) && ( $n != 1 || ( $n == 1 && $_one_image == 'collage' ) ) ) {
		$_class = isset( $in ) && $in == 'comment' ? 'ug-comments' : 'ug';
		if ( ! isset( $ug ) ) {
			$ug = '';
		}
		//print '<pre>'.$ug.'</pre>';
		$out = '<div class = "' . $_class . '" data-ug = \'' . $ug . '\'>' . $out . '</div>';
	}

	return $out;
}

function ug_shortcode_parse_str( $att = null, $option, $default ) {

	if ( isset( $att ) ) {
		$out = $att;
	} else {
		if ( isset( $option ) ) {
			$out = $option;
		} else {
			$out = $default;
		}
	}

	return $out;
}


function ug_json_encode( $data = null ) {

	if ( empty( $data ) ) {
		return '{}';
	}
	$out = '';
	foreach ( $data as $key => $value ) {

		if ( is_array( $value ) ) {
			$out[] = '"' . $key . '":' . ug_json_encode( $value );
		} else {

			if ( is_bool( $value ) || is_numeric( $value ) || in_array( $value, array( 'false', 'true' ) ) ) {
				$out[] = '"' . $key . '":' . $value;
			} else {
				$out[] = '"' . $key . '":"' . $value . '"';
			}
		}
	}

	return '{' . implode( ",", $out ) . '}';
}

function ug_collage_gallery_js() {

	$atts = '';
	if ( ! empty( $_POST['options'] ) ) {
		foreach ( $_POST['options'] as $key => $value ) {
			$atts[] = $key . '="' . trim( $value ) . '"';
		}
	}

	if ( ! empty( $_POST['ug'] ) ) {
		$atts[] = 'ug=\'' . ug_json_encode( $_POST['ug'] ) . '\'';
	}

	if ( ! empty( $atts ) ) {
		$atts = implode( ' ', $atts );
	}

	$out = do_shortcode( '[collage_gallery ' . $atts . ']' );
	print json_encode( $out );
	exit;
}

add_action( 'wp_ajax_collage_gallery', 'ug_collage_gallery_js' );

add_filter( 'the_excerpt', 'ug_the_content_filter' );
add_filter( 'the_content', 'ug_the_content_filter' );
function ug_the_content_filter( $content ) {
	global $post;
	$options = get_option( 'ug_options' );

	if ( isset( $options['insert'] ) && $options['insert'] == 'auto' && isset( $options['insert_in'] ) ) {

		foreach ( $options['insert_in'] as $key => $value ) {

			if ( call_user_func( 'is_' . $key ) ) {

				$pattern = get_shortcode_regex();
				preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches );
				if ( ( ! is_array( $matches ) || ! in_array( 'collage_gallery', $matches[2] ) ) ) {

					if ( isset( $options['position'] ) && $options['position'] == 'before' ) {
						$content = do_shortcode( '[collage_gallery]' ) . $content;
					} else {
						$content .= do_shortcode( '[collage_gallery]' );
					}

				}

				break;
			}
		}
	}

	return $content;
}


function ug_settings_admin_init() {
	global $ug_settings;

	$ug_settings = new WP_Settings_API_Class;

	$tabs = array(
		'ug_options' => array(
			'id'       => 'ug_options',
			'name'     => 'ug_options',
			'title'    => __( 'Collage Gallery', 'collage-gallery' ),
			'desc'     => __( '', 'collage-gallery' ),
			'sections' => array(

				'ug_section' => array(
					'id'    => 'ug_section',
					'name'  => 'ug_section',
					'title' => __( 'Global Settings', 'collage-gallery' ),
					'desc'  => __( 'Collage Gallery plugin global settings.', 'collage-gallery' ),
				),
				'ug_jg'      => array(
					'id'    => 'ug_jg',
					'name'  => 'ug_jg',
					'title' => __( 'Collage Settings', 'collage-gallery' ),
					'desc'  => __( 'Collage based on jQuery plugin "Justified Gallery" by <i>miromannino</i> (<a href = "https://github.com/miromannino/Justified-Gallery" target = "_blank">git</a>). Explanation of the settings are taken from plugin official <a href = "http://miromannino.github.io/Justified-Gallery/" target = "_blank">site</a>.', 'collage-gallery' ),
				),

			)
		)
	);
	$tabs = apply_filters( 'ug_tabs', $tabs, $tabs );

	$fields = array(
		'ug_section' => array(

			array(
				'name'    => 'insert',
				'label'   => __( 'Add to post', 'collage-gallery' ),
				'desc'    => __( 'Plugin can automatically convert all attached to the post images in a collage or you can use shortcode <i>[collage_gallery]</i>.', 'collage-gallery' ),
				'type'    => 'radio',
				'default' => 'manual',
				'options' => array(
					'auto'   => __( 'Auto', 'collage-gallery' ),
					'manual' => __( 'Manual / <i>use shortcode [collage_gallery]</i>', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'position',
				'desc'    => __( 'Where in the post position the collage gallery.<br/>Fires <strong>only</strong> if selected Add to post: <strong>Auto</strong>.', 'collage-gallery' ),
				'type'    => 'radio',
				'default' => 'after',
				'options' => array(
					'before' => __( 'Before content', 'collage-gallery' ),
					'after'  => __( 'After content', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'lightbox',
				'label'   => __( 'Use LightBox', 'collage-gallery' ),
				'desc'    => __( 'When clicked on image open it in lightbox or not.', 'collage-gallery' ),
				'type'    => 'radio',
				'default' => 'photoswipe',
				'options' => array(
					'no'         => __( 'No', 'collage-gallery' ),
					'photoswipe' => __( 'PhotoSwipe <small>touch, mobile, responsive</small>', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'insert_in',
				'label'   => __( 'Show in pages', 'collage-gallery' ),
				'desc'    => __( 'You can select the pages types on which will be added collage.', 'collage-gallery' ),
				'type'    => 'multicheck',
				'default' => 'single',
				'options' => array(
					'front_page' => __( 'Front page, <small>is_front_page()</small>.', 'collage-gallery' ),
					'single'     => __( 'Single (posts) pages, <small>is_single()</small>.', 'collage-gallery' ),
					'tax'        => __( 'Tax pages, <small>is_tax()</small>.', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'one_image',
				'label'   => __( 'If one image', 'collage-gallery' ),
				'desc'    => __( 'Actions, if only one image in post.', 'collage-gallery' ),
				'type'    => 'radio',
				'default' => 'none',
				'options' => array(
					'none'       => __( 'Hide image', 'collage-gallery' ),
					'collage'    => __( 'Show as Collage', 'collage-gallery' ),
					'responsive' => __( 'Show as responsive image', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'link',
				'label'   => __( 'Link', 'collage-gallery' ),
				'desc'    => __( 'When click on image, opens...', 'collage-gallery' ),
				'type'    => 'radio',
				'default' => 'page',
				'options' => array(
					'page'  => __( 'Image page / attachment page', 'collage-gallery' ),
					'image' => __( 'Image', 'collage-gallery' ),
					'none'  => __( 'Nothing happens', 'collage-gallery' )
				)
			),
			array(
				'name'  => 'meta_name',
				'label' => __( 'post_meta name', 'collage-gallery' ),
				'desc'  => __( 'Add collage to post only if it has <i>post_meta</i> with given name. Leave blank if not required.', 'collage-gallery' ),
				'type'  => 'text',
			),

		),
		'ug_jg'      => array(
			array(
				'name'    => 'row_height',
				'label'   => __( 'Row Height', 'collage-gallery' ),
				'desc'    => __( 'The approximately height of rows in pixel (px).<br/>i.e.: <code>120</code>.', 'collage-gallery' ),
				'type'    => 'text',
				'default' => '120'
			),
			array(
				'name'    => 'max_row_height',
				'label'   => __( 'Max&nbsp;row&nbsp;height', 'collage-gallery' ),
				'desc'    => __( "The maximum row height in pixel. Negative value to haven't limits. Zero to have a limit of <i>1.5 * Row Height</i>.<br/>i.e.: <code>0</code>.", 'collage-gallery' ),
				'type'    => 'text',
				'default' => '0'
			),
			array(
				'name'    => 'last_row',
				'label'   => __( 'Last Row', 'collage-gallery' ),
				'desc'    => __( "Decide if you want to justify the last row or not, or to hide the row if it can't be justified.", 'collage-gallery' ),
				'type'    => 'radio',
				'default' => 'nojustify',
				'options' => array(
					'nojustify' => __( 'No Justify', 'collage-gallery' ),
					'justify'   => __( 'Justify', 'collage-gallery' ),
					'hide'      => __( 'Hide', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'fixed_height',
				'label'   => __( 'Fixed Height', 'collage-gallery' ),
				'desc'    => __( "Decide if you want to have a fixed height. This mean that all the rows will be exactly with the specified Row Height.", 'collage-gallery' ),
				'type'    => 'radio',
				'default' => '0',
				'options' => array(
					'1' => __( 'Yes', 'collage-gallery' ),
					'0' => __( 'No', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'caption',
				'label'   => __( 'Caption', 'collage-gallery' ),
				'desc'    => __( "Decide if you want to show the caption or not, that appears when your mouse is over the image.", 'collage-gallery' ),
				'type'    => 'radio',
				'default' => '1',
				'options' => array(
					'1' => __( 'Yes', 'collage-gallery' ),
					'0' => __( 'No', 'collage-gallery' )
				)
			),
			array(
				'name'    => 'margins',
				'label'   => __( 'Margins', 'collage-gallery' ),
				'desc'    => __( "Decide the margins (px) between the images.", 'collage-gallery' ),
				'type'    => 'text',
				'default' => '1'
			),
			array(
				'name'    => 'border',
				'label'   => __( 'Border', 'collage-gallery' ),
				'desc'    => __( "Decide the border size (px) of the gallery. With a negative value the border will be the same as the margins.", 'collage-gallery' ),
				'type'    => 'text',
				'default' => '-1'
			),
			array(
				'name'  => 'css',
				'label' => __( 'CSS', 'collage-gallery' ),
				'desc'  => __( "Add custom CSS.", 'collage-gallery' ),
				'type'  => 'textarea'
			),
		)

	);
	$fields = apply_filters( 'ug_fields', $fields, $fields );

	//set sections and fields
	$ug_settings->set_option_name( 'ug_options' );
	$ug_settings->set_sections( $tabs );
	$ug_settings->set_fields( $fields );

	//initialize them
	$ug_settings->admin_init();

}

add_action( 'admin_init', 'ug_settings_admin_init' );


// Register the plugin page
function ug_admin_menu() {
	global $ug_settings_page;

	add_menu_page( __( 'Collage Gallery', 'collage-gallery' ), __( 'Collage Gallery', 'collage-gallery' ), 'activate_plugins', 'collage-gallery', 'ug_settings_page', null, '99' );

	$ug_settings_page = add_submenu_page( 'collage-gallery', __( 'Collage Gallery', 'collage-gallery' ), __( 'Collage Gallery', 'collage-gallery' ), 'activate_plugins', 'collage-gallery', 'ug_settings_page' );

}

add_action( 'admin_menu', 'ug_admin_menu', 20 );


// Display the plugin settings options page
function ug_settings_page() {
	global $ug_settings;

	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>' . __( 'Collage Gallery', 'collage-gallery' ) . '</h2>';

	echo '<div id = "col-container">';
	echo '<div id = "col-right" class = "evc">';
	echo '<div class = "evc-box">';
	ug_ad();
	echo '</div>';
	echo '</div>';
	echo '<div id = "col-left" class = "evc">';
	settings_errors();
	$ug_settings->show_navigation();
	$ug_settings->show_forms();
	echo '</div>';
	echo '</div>';

	echo '</div>';
}


add_action( 'admin_head', 'ug_admin_head', 99 );
function ug_admin_head() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'collage-gallery' ) {

		?>
		<style type="text/css">
			#col-right.evc {
				width: 35%;
			}

			#col-left.evc {
				width: 64%;
			}

			.evc-box {
				padding: 0 20px 0 40px;
			}

			.evc-boxx {
				background: none repeat scroll 0 0 #FFFFFF;
				border-left: 4px solid #2EA2CC;
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
				margin: 5px 0 15px;
				padding: 1px 12px;
			}

			.evc-boxx h3 {
				line-height: 1.5;
			}

			.evc-boxx p {
				margin: 0.5em 0;
				padding: 2px;
			}
		</style>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {

				if ($(".evc-box").length) {

					$("#col-right").stick_in_parent({
						parent: '#col-container',
						offset_top: $('#wpadminbar').height() + 10,
					});
				}
			});
		</script>
		<?php
	}
}

function ug_ad() {

	echo '
    <div class = "evc-boxx">
      <p>' . __( 'Collage Gallery plugin <a href = "http://ukraya.ru/collage-gallery/support" target = "_blank">Support</a>', 'collage-gallery' ) . '</p>
    </div>';
}

add_action( 'admin_init', 'ug_admin_init' );
function ug_admin_init() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'collage-gallery' ) {
		wp_enqueue_script( 'sticky-kit', plugins_url( 'js/jquery.sticky-kit.min.js', __FILE__ ), array( 'jquery' ), null, false );
	}
}

