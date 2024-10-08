<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Please do not load this file directly.' );
}

function icp_get_option( $name )
{

    $icp_values = get_option( 'icp_options' );

    if ( is_array( $icp_values ) && array_key_exists( $name, $icp_values ) ) {
        return $icp_values[$name];
    }

    return false;
    
}

/*-------------------------------------------------------------------------------*/
/*  Enqueue when option on each post/page is ON ( Yes )
/*-------------------------------------------------------------------------------*/
function icp_post_page_hook( $content )
{

    global $post;

    if ( trim( icp_get_option( 'icp_global_carousel_active' ) ) != 'active' ) {

        if ( trim( get_post_meta( $post->ID, 'icp_meta_options', true ) ) != 'no' ) {

            if ( has_filter( 'icp_frontend_filter' ) ) {

                apply_filters( 'icp_frontend_filter', '' );

            }

            //deactivate WordPress function
            remove_shortcode( 'gallery', 'gallery_shortcode' );
            //activate own function
            add_shortcode( 'gallery', 'icp_gallery_shortcode' );
            //bfi_thumb
            add_filter( 'wp_get_attachment_image_attributes', 'icp_bfi_thumb' );

            if ( trim( icp_get_option( 'icp_carousel_adapt_height' ) ) == 'false' ) {

                add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );

            }

        }

    }

    return $content;

}

function icp_widget_shortcode( $attrs )
{

    if ( trim( icp_get_option( 'icp_global_carousel_active' ) ) != 'active' ) {

        if ( has_filter( 'icp_frontend_filter' ) ) {

            apply_filters( 'icp_frontend_filter', '' );

        }

        //Generate Carousel

        return icp_gallery_shortcode( $attrs );
        //bfi_thumb
        add_filter( 'wp_get_attachment_image_attributes', 'icp_bfi_thumb' );

        if ( trim( icp_get_option( 'icp_carousel_adapt_height' ) ) == 'false' ) {

            add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );

        }

    }

}

/*-------------------------------------------------------------------------------*/
/*  Make sure to set the "Link to" to Media File
/*-------------------------------------------------------------------------------*/
function icp_make_sure_link_to_media_file( $atts )
{

    $atts['link'] = 'file';

    return gallery_shortcode( $atts );

}

/*-------------------------------------------------------------------------------*/
/*  Enqueue Admin Script
/*-------------------------------------------------------------------------------*/
function icp_admin_enqueue_scripts()
{

    $is_rtl = ( is_rtl() ? '-rtl' : '' );

    wp_register_style( 'icp-settings', plugins_url( 'css/icp-settings'.$is_rtl.'.css', dirname( __FILE__ ) ), false, ICP_VERSION );
    wp_register_script( 'icp-settings-tab', plugins_url( 'js/settings/option-tab.js', dirname( __FILE__ ) ), false, ICP_VERSION );

}

/*-------------------------------------------------------------------------------*/
/*  Enqueue Frontend Script
/*-------------------------------------------------------------------------------*/
function icp_frontend_enqueue_scripts()
{

    wp_register_script( 'icp-carousel', ICP_URL.'/js/jquery/jquery.bxslider.js', array(), ICP_VERSION, false );
    wp_register_script( 'icp-easing', ICP_URL.'/js/jquery/jquery.easing.js', array(), ICP_VERSION, false );
    wp_register_script( 'icp-lazyload', ICP_URL.'/js/jquery/jquery.lazy.min.js', array(), ICP_VERSION, false );
    wp_register_style( 'icp-carousel-theme', ICP_URL.'/css/jquery.bxslider.css', array(), ICP_VERSION, false );
    wp_register_style( 'icp-carousel-animate', ICP_URL.'/css/animate/animate.css', array(), ICP_VERSION, false );

}

function icp_frontend_option_loader()
{

    include_once ABSPATH.'wp-admin/includes/plugin.php';

    $params = array(
        'interval'           => ( icp_get_option( 'icp_carousel_slide_every' ) ? icp_get_option( 'icp_carousel_slide_every' ).'000' : 4000 ),
        'easing'             => ( icp_get_option( 'icp_carousel_slide_effect' ) ? icp_get_option( 'icp_carousel_slide_effect' ) : 'easeOutBounce' ),
        'caption'            => ( icp_get_option( 'icp_carousel_caption' ) ? icp_get_option( 'icp_carousel_caption' ) : 'true' ),
        'caption_effect'     => ( icp_get_option( 'icp_carousel_caption_effect' ) ? icp_get_option( 'icp_carousel_caption_effect' ) : 'true' ),
        'captionStyle'       => ( icp_get_option( 'icp_carousel_cap_style' ) ? icp_get_option( 'icp_carousel_cap_style' ) : 'default' ),
        'pager'              => ( icp_get_option( 'icp_carousel_bullet' ) ? icp_get_option( 'icp_carousel_bullet' ) : 'true' ),
        'auto'               => ( icp_get_option( 'icp_carousel_autoplay' ) ? icp_get_option( 'icp_carousel_autoplay' ) : 'true' ),
        's_width'            => ( icp_get_option( 'icp_carousel_width' ) ? icp_get_option( 'icp_carousel_width' ) : 250 ),
        'main_class'         => ( icp_get_option( 'icp_carousel_use_lightbox' ) == 'yes' && icp_glg_supported_version() ? 'ghozylab-gallery' : 'ghozylab-gallery-nolightbox' ),
        'margin'             => ( icp_get_option( 'icp_carousel_margin' ) ? icp_get_option( 'icp_carousel_margin' ) : 10 ),
        'autoHeight'         => ( icp_get_option( 'icp_carousel_adapt_height' ) ? icp_get_option( 'icp_carousel_adapt_height' ) : 'true' ),
        'transition_speed'   => ( icp_get_option( 'icp_carousel_transition_speed' ) ? icp_get_option( 'icp_carousel_transition_speed' ) : 2500 ),
        'permanent_nav'      => ( icp_get_option( 'icp_carousel_nav_fixed' ) ? icp_get_option( 'icp_carousel_nav_fixed' ) : 'true' ),
        'nav_color'          => ( icp_get_option( 'icp_carousel_nav_col' ) ? icp_get_option( 'icp_carousel_nav_col' ) : 'black' ),
        'hide_nav_on_mobile' => ( icp_get_option( 'icp_carousel_hide_nav_mobile' ) ? icp_get_option( 'icp_carousel_hide_nav_mobile' ) : 'true' ),
    );

    $params = apply_filters( 'icp_carousel_parameter', $params );

    return $params;

}

/*-------------------------------------------------------------------------------*/
/*  Override the default wordpress gallery
/*-------------------------------------------------------------------------------*/
function icp_gallery_shortcode( $attr )
{

    $post      = get_post();
    $params    = icp_frontend_option_loader();
    $instance  = uniqid();
    $the_class = ( icp_get_option( 'icp_carousel_use_lightbox' ) == 'yes' && icp_glg_supported_version() ? 'ghozylab-gallery' : 'ghozylab-gallery-nolightbox' );

    if ( ! empty( $attr['ids'] ) ) {

        // 'ids' is explicitly ordered, unless you specify otherwise.
        if ( empty( $attr['orderby'] ) ) {
            $attr['orderby'] = 'post__in';
        }

        $attr['include'] = $attr['ids'];
    }

    // Allow plugins/themes to override the default gallery template.
    $output = apply_filters( 'post_gallery', '', $attr );

    if ( $output != '' ) {
        return $output;
    }

    // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
    if ( isset( $attr['orderby'] ) ) {
        $attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
        if ( ! $attr['orderby'] ) {
            unset( $attr['orderby'] );
        }

    }

    // We're trusting author input too, but for this time we need to force to use 'full' type for size :(
    if ( isset( $attr['size'] ) ) {
        $attr['size'] = 'full';
        if ( ! $attr['size'] ) {
            unset( $attr['size'] );
        }

    }

    extract( shortcode_atts( array(
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => $post ? $post->ID : 0,
        'itemtag'    => 'dl',
        'icontag'    => 'dt',
        'shadowbox'  => false,
        'captiontag' => 'dd',
        'columns'    => 3, /* @since 1.0.0.23 :: Ability to set carousel columns */
        'size' => 'full',
        'include'    => '',
        'exclude'    => '',
        'link'       => 'file',
    ), $attr, 'gallery' ) );

    $id = intval( $id );

    if ( 'RAND' == $order ) {
        $orderby = 'none';
    }

    if ( ! empty( $include ) ) {
        $_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );

        $attachments = array();

        foreach ( $_attachments as $key => $val ) {
            $attachments[$val->ID] = $_attachments[$key];
        }

    } elseif ( ! empty( $exclude ) ) {
        $attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
    } else {
        $attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
    }

    if ( empty( $attachments ) ) {
        return '';
    }

    if ( is_feed() ) {
        $output = "\n";

        foreach ( $attachments as $att_id => $attachment ) {
            $output .= icp_wp_get_attachment_link( $att_id, $size, true, false, false )."\n";
        }

        return $output;
    }

    $itemtag    = tag_escape( $itemtag );
    $captiontag = tag_escape( $captiontag );
    $icontag    = tag_escape( $icontag );
    $valid_tags = wp_kses_allowed_html( 'post' );

    if ( ! isset( $valid_tags[$itemtag] ) ) {
        $itemtag = 'dl';
    }

    if ( ! isset( $valid_tags[$captiontag] ) ) {
        $captiontag = 'dd';
    }

    if ( ! isset( $valid_tags[$icontag] ) ) {
        $icontag = 'dt';
    }

    $selector = "gallery-{$instance}";

    $gallery_style = $gallery_div = '';

    if ( apply_filters( 'use_default_gallery_style', true ) ) {
        ob_start();
    }

    wp_enqueue_script( 'icp-carousel' );
    wp_enqueue_script( 'icp-easing' );
    wp_enqueue_style( 'icp-carousel-style' );
    wp_enqueue_style( 'icp-carousel-theme' );

    ?>
<!-- Carousel Style & Script for  <?php echo $selector; ?> -->

<script type="text/javascript">
// <![CDATA[
/* START --- <?php echo ICP_ITEM_NAME; ?> --- */

jQuery(document).ready(function($) {

    <?php

    if ( icp_get_option( 'icp_fancy_caption' ) == 'true' ) {?>

    $('.<?php echo $params['main_class']; ?> img').each(function(i) {

        $alt = $(this).attr('alt');

        // Replace default title to more fancy :)
        $(this).attr('alt', $alt.replace(/-|_/g, ' '));

        $altnew = $(this).attr('alt').toLowerCase().replace(/\b[a-z]/g, function(letter) {

            return letter.toUpperCase();

        });

        $(this).attr('alt', $altnew);
        // Compatibility with Zoom Lite Theme Image LazyLoad
        $(this).removeAttr('data-lazy-src');

    });

    <?php }

    ?>

    $('#icppreloader-<?php echo $selector; ?>').fadeOut(300, function() {
        $('#<?php echo $selector; ?>').fadeIn(300);
        var slider<?php echo $instance; ?> = $('#<?php echo $selector; ?>').bxSlider({
            auto: <?php echo $params['auto']; ?>,
            pause: <?php echo $params['interval']; ?>,
            autoHover: true,
            moveSlides: <?php echo $columns; ?>,
            minSlides: <?php echo $columns; ?>,
            maxSlides: <?php echo $columns; ?>,
            slideWidth: <?php echo $params['s_width']; ?>,
            slideMargin: <?php echo $params['margin']; ?>,
            startSlide: 0,
            infiniteLoop: true,
            captions: <?php

    if ( $params['captionStyle'] == 'default' || $params['captionStyle'] == '' ) {echo $params['caption'];} else {echo 'false';}

    ?>,
            captionsMode: 'alt',
            adaptiveHeight: <?php echo $params['autoHeight']; ?>,
            useCSS: <?php
if ( $params['easing'] == 'none' ) {echo 'true';} else {echo 'false';}
    ?>,
            pager: <?php echo $params['pager']; ?>,
            easing: '<?php echo $params['easing']; ?>',
            speed: <?php
if ( $params['easing'] == 'none' ) {echo 100;} else {echo $params['transition_speed'];}
    ?>,
            preloadImages: 'all',
            onSlideBefore: function() {
                <?php
if ( $params['caption_effect'] == 'true' ) {?>
                $('#<?php echo $selector; ?> .bx-caption').slideUp();
                <?php }
    ?>
            },
            onSlideAfter: function($slideElement, oldIndex, newIndex) {
                <?php

    if ( $params['auto'] == 'true' ) {?>
                slider<?php echo $instance; ?>.stopAuto();
                slider<?php echo $instance; ?>.startAuto();
                <?php }

    if ( $params['caption_effect'] == 'true' ) {?>
                $('#<?php echo $selector; ?> .bx-caption').slideDown();
                <?php }
    ?>
            }
        });

    });
});

/* END --- <?php echo ICP_ITEM_NAME; ?> --- */

// ]]>
</script>

<style type="text/css">
<?php
echo '.markup-'.$selector.' .carousel-item img {width: 100%;margin: 0 auto;border:none !important;border-radius: 0px !important;box-shadow:none !important;}';
    echo '.markup-'.$selector.' .bx-controls-direction a {display: none;}';
    echo '.markup-'.$selector.' .bx-wrapper:hover .bx-controls-direction a {display: block;}';

    if ( $params['permanent_nav'] == 'true' ) {
        echo '.markup-'.$selector.' .bx-controls-direction a { display: block !important;}';
    }

    if ( $params['nav_color'] == 'white' ) {
        echo '.markup-'.$selector.' .bx-wrapper .bx-prev {background: url("'.ICP_URL.'/css/images/controls_white.png") no-repeat 0 -32px;}';
        echo '.markup-'.$selector.' .bx-wrapper .bx-next {background: url("'.ICP_URL.'/css/images/controls_white.png") no-repeat -43px -32px;}';
    }

    if ( $params['pager'] == 'false' ) {
        echo '.markup-'.$selector.' .bx-wrapper { margin-bottom: 15px !important;}';
    }

    if ( $params['autoHeight'] == 'false' ) {
        echo '.markup-'.$selector.' .bx-viewport { height: auto !important;}';
    }

    if ( $shadowbox ) {
        echo '.markup-'.$selector.' .bx-wrapper .bx-viewport {-moz-box-shadow: 0 0 5px #ccc;-webkit-box-shadow: 0 0 5px #ccc;box-shadow: 0 0 5px #ccc;border:  3px solid #fff;background: #fff;}';
    }

    if ( $params['hide_nav_on_mobile'] == 'false' ) {
        echo ' @media only screen and (max-width : 480px) {.markup-'.$selector.' .bx-controls, .markup-'.$selector.' .bx-controls-direction a { display: block !important;}}';
    }

    echo apply_filters( 'icp_styles_filter', true );

    ?>
</style>

<?php

    $gallery_style = ob_get_clean();

    $size_class  = sanitize_html_class( $size );
    $gallery_div = "<div id='icppreloader-$selector' class='icpsliderpreloader'></div><div class='markup-".$selector."'><div style='display:none;' id='$selector' class='".$the_class." galleryid-{$id} gallery-size-{$size_class}'>";
    $output      = apply_filters( 'gallery_style', $gallery_style."\n\t\t".$gallery_div );

    $i = 0;

    foreach ( $attachments as $id => $attachment ) {

        $image_output = icp_wp_get_attachment_link( $id, $size, true, false, false );

        $output .= "<div class='carousel-item'>{$image_output}</div>";

    }

    $output .= "
		</div></div>\n";

    return apply_filters( 'icp_carousel_custom_markup', $output, $attachments, $gallery_style, $gallery_div, $size );

}

// Fixed default shortcode attr ( link ) to file
function icp_wp_get_attachment_link( $id, $size, $permalink, $icon, $text )
{

    $id    = intval( $id );
    $_post = get_post( $id );

    if ( empty( $_post ) || ( 'attachment' != $_post->post_type ) || ! $url = wp_get_attachment_url( $_post->ID ) ) {
        return __( 'Missing Attachment' );
    }

    if ( $permalink ) {
        $image_attributes = wp_get_attachment_image_src( $_post->ID, 'large' );
    }

    $url = $image_attributes[0];

    if ( $text ) {
        $link_text = $text;
    } elseif ( $size && 'none' != $size ) {
        $link_text = wp_get_attachment_image( $id, $size, $icon, array( 'alt' => $_post->post_excerpt ) );
    } else {
        $link_text = '';
    }

    include_once ABSPATH.'wp-admin/includes/plugin.php';

    if ( is_plugin_active( 'gallery-lightbox-slider/gallery-lightbox-lite.php' ) && icp_get_option( 'icp_carousel_use_lightbox' ) == 'yes' && icp_glg_supported_version() ) {

        if ( get_post_meta( $id, '_icp_gallery_link_url', true ) != '' && get_post_meta( $id, '_icp_gallery_link_target', true ) != '' ) {

            $url         = trim( get_post_meta( $id, '_icp_gallery_link_url', true ) );
            $link_target = ' target="'.trim( get_post_meta( $id, '_icp_gallery_link_target', true ) ).'" ';
            $link_class  = ' class="icp_custom_link" ';

        } else {

            $url         = $url;
            $link_target = '';
            $link_class  = '';

        }

        return apply_filters( 'wp_get_attachment_link', "<a $link_class $link_target href='$url'>$link_text</a>", $id, $size, $permalink, $icon, $text );

    } else {

        if ( get_post_meta( $id, '_icp_gallery_link_url', true ) != '' && get_post_meta( $id, '_icp_gallery_link_target', true ) != '' ) {

            $url         = trim( get_post_meta( $id, '_icp_gallery_link_url', true ) );
            $link_target = ' target="'.trim( get_post_meta( $id, '_icp_gallery_link_target', true ) ).'" ';

            return apply_filters( 'wp_get_attachment_link', "<a $link_target href='$url'>$link_text</a>", $id, $size, $permalink, $icon, $text );

        } else {

            return apply_filters( 'wp_get_attachment_link', "$link_text", $id, $size, $permalink, $icon, $text );

        }

    }

}

// LazyLoad = change default src to data-src
function icp_bfi_thumb_lazyload( $attr )
{

    $params = array( 'width' => 250, 'height' => 250, 'crop' => true );

    $attr = array_merge( $attr, array(
        'data-src' => bfi_thumb( $attr['src'], $params ),
        'class'    => 'lazy',
    )
    );

    $attr['src'] = ICP_URL.'/css/images/lazy-loader.gif';

    return $attr;

}

// Convert default URL to BFI Thumb
function icp_bfi_thumb( $attr )
{

    $t_size = ( icp_get_option( 'icp_carousel_width' ) ? icp_get_option( 'icp_carousel_width' ) : 250 );

    $params = array( 'width' => $t_size, 'height' => $t_size, 'crop' => true );

    $attr['src'] = bfi_thumb( $attr['src'], $params );

    return $attr;

}

/*-------------------------------------------------------------------------------*/
/*  Get Total Image on Cache Dir
/*-------------------------------------------------------------------------------*/
function icp_get_total_images()
{

    $filecount = 0;
    $files     = glob( EWIC_FULL_BFITHUMB_UPLOAD_DIR.'*.{jpg,png,bmp,jpeg}', GLOB_BRACE );

    if ( $files ) {

        return count( $files );

    } else {

        return 'Empty';

    }

}

/*-------------------------------------------------------------------------------*/
/*  Get Total filesize for all Caches & auto format
/*-------------------------------------------------------------------------------*/
function icp_get_size_of_cache()
{

    if ( version_compare( PHP_VERSION, '5.0.0', '>' ) === true ) {

        $size = 0;

        if ( is_dir( EWIC_FULL_BFITHUMB_UPLOAD_DIR ) ) {

            $files = glob( EWIC_FULL_BFITHUMB_UPLOAD_DIR.'*.{jpg,png,bmp,jpeg}', GLOB_BRACE );

            if ( $files ) {

                foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( EWIC_FULL_BFITHUMB_UPLOAD_DIR ) ) as $file ) {

                    $size += $file->getSize();

                }

                $mod   = 1024;
                $units = explode( ' ', 'B KB MB GB TB PB' );

                for ( $i = 0; $size > $mod; $i++ ) {

                    $size /= $mod;

                }

                return round( $size, 2 ).' '.$units[$i];

            } else {
                return _e( 'No cache found', 'image-carousel' );
            }

        } else {
            return _e( 'The cache files are not generated', 'image-carousel' );
        }

    } else {
        return _e( 'You need to use php5 or greater to get cache size information.', 'image-carousel' );
    }

}

/*-------------------------------------------------------------------------------*/
/*  icp_apply_filter_attachment_fields_to_edit
/*-------------------------------------------------------------------------------*/
function icp_apply_filter_attachment_fields_to_edit( $form_fields, $post )
{

    // Gallery Link Separator
    $form_fields['icp_gallery_link_sep'] = array(
        'label' => '<div style="width:100%;margin-top:10px;margin-bottom:8px;border-bottom: 1px solid #ddd;"></div>'.__( 'Image Carousel Custom Link', 'image-carousel' ),
        'input' => 'html',
        'html'  => '<p style="margin-top:5px;"></p>',
    );
    // Gallery Link URL field
    $form_fields['icp_gallery_link_url'] = array(
        'label' => __( 'Link URL', 'image-carousel' ),
        'input' => 'text',
        'value' => get_post_meta( $post->ID, '_icp_gallery_link_url', true ),
    );
    // Gallery Link Target field
    $target_value                           = get_post_meta( $post->ID, '_icp_gallery_link_target', true );
    $form_fields['icp_gallery_link_target'] = array(
        'label' => __( 'Target Link', 'image-carousel' ),
        'input' => 'html',
        'html'  => '
				<select name="attachments['.$post->ID.'][icp_gallery_link_target]" id="attachments['.$post->ID.'][icp_gallery_link_target]">
					<option value="_blank"'.( $target_value == '_blank' ? ' selected="selected"' : '' ).'>'.__( 'Open link in a new tab', 'image-carousel' ).'</option>
					<option value="_self"'.( $target_value == '_self' ? ' selected="selected"' : '' ).'>'.__( 'Open link in same tab', 'image-carousel' ).'</option>
				</select><br /><br />',
    );

    return $form_fields;

}

add_filter( 'attachment_fields_to_edit', 'icp_apply_filter_attachment_fields_to_edit', null, 2 );

/*-------------------------------------------------------------------------------*/
/*  apply_filter_attachment_fields_to_save
/*-------------------------------------------------------------------------------*/
function icp_apply_filter_attachment_fields_to_save( $post, $attachment )
{

    if ( isset( $attachment['icp_gallery_link_url'] ) ) {
        update_post_meta( $post['ID'], '_icp_gallery_link_url', $attachment['icp_gallery_link_url'] );
    }

    if ( isset( $attachment['icp_gallery_link_target'] ) ) {
        update_post_meta( $post['ID'], '_icp_gallery_link_target', $attachment['icp_gallery_link_target'] );
    }

    return $post;

}

add_filter( 'attachment_fields_to_save', 'icp_apply_filter_attachment_fields_to_save', null, 2 );

/* @since 1.0.0.23 */
function icp_glg_supported_version()
{

    $req_plugin = WP_PLUGIN_DIR.'/gallery-lightbox-slider';

    if ( ! is_dir( $req_plugin ) ) {
        return false;
    }

    $plugin_data = get_plugin_data( $req_plugin.'/gallery-lightbox-lite.php' );

    if ( version_compare( $plugin_data['Version'], '1.0.0.30', '<=' ) ) {
        return false;
    }

    return true;

}