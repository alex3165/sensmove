<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/fields/image.php $
 * $LastChangedDate: 2015-03-16 12:03:31 +0000 (Mon, 16 Mar 2015) $
 * $LastChangedRevision: 1113864 $
 * $LastChangedBy: iworks $
 *
 */

add_filter( 'wpcf_fields_type_image_value_get', 'wpcf_fields_image_value_filter' );
add_filter( 'wpcf_fields_type_image_value_save', 'wpcf_fields_image_value_filter' );

// Do not wrap if 'url' is TRUE
add_filter( 'types_view', 'wpcf_fields_image_view_filter', 10, 6 );

/**
 * Register data (called automatically).
 * @return type
 */
function wpcf_fields_image() {
    return array(
        'id' => 'wpcf-image',
        'title' => __( 'Image', 'wpcf' ),
        'description' => __( 'Image', 'wpcf' ),
        'validate' => array('required'),
        'inherited_field_type' => 'file',
    );
}

/**
 * Editor callback form.
 *
 * @global object $wpdb
 *
 */
function wpcf_fields_image_editor_callback( $field, $data, $context, $post ) {

    // Get post_ID
    $post_ID = !empty( $post->ID ) ? $post->ID : false;

    // Get attachment
    $image = false;
    $attachment_id = false;
    if ( $post_ID ) {
        $image = get_post_meta( $post_ID,
                wpcf_types_get_meta_prefix( $field ) . $field['slug'], true );
        if ( empty( $image ) ) {
            $user_id = wpcf_usermeta_get_user();
            $image = get_user_meta( $user_id,
                    wpcf_types_get_meta_prefix( $field ) . $field['slug'], true );
        }
        if ( !empty( $image ) ) {
            // Get attachment by guid
            global $wpdb;
            $attachment_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid=%s",
                    $image
                )
            );
        }
    }
    $data['image'] = $image;
    $data['attachment_id'] = $attachment_id;

    // Set post type
    $post_type = !empty( $post->post_type ) ? $post->post_type : '';

    // Set image_data
    $image_data = wpcf_fields_image_get_data( $image );
    if ( !in_array( $post_type, array('view', 'view-template') ) ) {
        // We must ignore errors here and treat image as outsider
        if ( !empty( $image_data['error'] ) ) {
            $image_data['is_outsider'] = 1;
            $image_data['is_attachment'] = 0;
        }
    } else {
        if ( !empty( $image_data['error'] ) ) {
            $image_data['is_outsider'] = 0;
            $image_data['is_attachment'] = 0;
        }
    }

    $data['preview'] = $attachment_id ? wp_get_attachment_image( $attachment_id,
                    'thumbnail' ) : '';

    // Title and Alt
    if ( $attachment_id ) {
        $alt = trim( strip_tags( get_post_meta( $attachment_id,
                                '_wp_attachment_image_alt', true ) ) );
        $attachment_post = get_post( $attachment_id );
        if ( !empty( $attachment_post ) ) {
            $title = trim( strip_tags( $attachment_post->post_title ) );
        } else if ( !empty( $alt ) ) {
            $title = $alt;
        }
        if ( empty( $alt ) ) {
            $alt = $title;
        }
        if ( !isset( $data['title'] ) ) {
            $data['title'] = $title;
        }
        if ( !isset( $data['alt'] ) ) {
            $data['alt'] = $alt;
        }
    }

    // Align options
    $data['alignment_options'] = array(
        'none' => __( 'None', 'wpcf' ),
        'left' => __( 'Left', 'wpcf' ),
        'center' => __( 'Center', 'wpcf' ),
        'right' => __( 'Right', 'wpcf' ),
    );

    // Remote images settings
    $fetch_remote = (bool) wpcf_get_settings( 'images_remote' );
    $data['warning_remote'] = false;
    if ( !types_is_repetitive( $field )
            && $image_data['is_outsider'] && !$fetch_remote && !empty( $data['image'] ) ) {
        $data['warning_remote'] = true;
    }

    // Size settings
    $data['size_options'] = array(
        'thumbnail' => sprintf( __( 'Thumbnail - %s', 'wpcf' ),
                get_option( 'thumbnail_size_w' ) . 'x' . get_option( 'thumbnail_size_h' ) ),
        'medium' => sprintf( __( 'Medium - %s', 'wpcf' ),
                get_option( 'medium_size_w' ) . 'x' . get_option( 'medium_size_h' ) ),
        'large' => sprintf( __( 'Large - %s', 'wpcf' ),
                get_option( 'large_size_w' ) . 'x' . get_option( 'large_size_h' ) ),
        'full' => __( 'Full Size', 'wpcf' ),
    );
    $wp_image_sizes = (array) get_intermediate_image_sizes();
    foreach ( $wp_image_sizes as $wp_size ) {
        if ( $wp_size != 'post-thumbnail'
                && !array_key_exists( $wp_size, $data['size_options'] ) ) {
            $data['size_options'][$wp_size] = $wp_size;
        }
    }
    $data['size_options']['wpcf-custom'] = __( 'Custom size...', 'wpcf' );

    // Get saved settings
    $data = array_merge( wpcf_admin_fields_get_field_last_settings( $field['id'] ),
            $data );

    return array(
        'supports' => array('styling', 'style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-image', $data ),
            )
        ),
        'settings' => $data,
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_image_editor_submit( $data, $field, $context ) {

    // Saved settings
    $settings = array();

    $add = '';
    if ( !empty( $data['alt'] ) ) {
        $add .= ' alt="' . strval( $data['alt'] ) . '"';
    }
    if ( !empty( $data['title'] ) ) {
        $add .= ' title="' . strval( $data['title'] ) . '"';
    }
    $size = !empty( $data['image_size'] ) ? $data['image_size'] : false;
    if ( $size == 'wpcf-custom' ) {
        if ( !empty( $data['width'] ) ) {
            $add .= ' width="' . intval( $data['width'] ) . '"';
        }
        if ( !empty( $data['height'] ) ) {
            $add .= ' height="' . intval( $data['height'] ) . '"';
        }
    } else if ( !empty( $size ) ) {
        $add .= ' size="' . $size . '"';
        $settings['image_size'] = $size;
    }
    if ( !empty( $data['alignment'] ) ) {
        $add .= ' align="' . $data['alignment'] . '"';
        $settings['alignment'] = $data['alignment'];
    }
    if ( !empty( $data['url'] ) ) {
        $add .= ' url="true"';
    }
    if ( !empty( $data['onload'] ) ) {
        $add .= ' onload="' . $data['onload'] . '"';
    }

    if ( array_key_exists('image_size', $data) && $data['image_size'] != 'full' ) {
        if ( !empty( $data['proportional'] ) ) {
            $settings['resize'] = isset( $data['resize'] ) ? $data['resize'] : 'proportional';
            $add .= " resize=\"{$settings['resize']}\"";
            if ( $settings['resize'] == 'pad' ) {
                if ( isset( $data['padding_transparent'] ) ) {
                    $data['padding_color'] = 'transparent';
                }
                if ( empty( $data['padding_color'] ) ) {
                    $data['padding_color'] = '#FFF';
                }
                if ( ( strpos( $data['padding_color'], '#' ) !== 0 || !( strlen( $data['padding_color'] ) == 4 || strlen( $data['padding_color'] ) == 7 ) ) && $data['padding_color'] != 'transparent' ) {
                    $data['padding_color'] = '#FFF';
                }
                $settings['padding_color'] = $data['padding_color'];
                $add .= " padding_color=\"{$data['padding_color']}\"";
            }
        } else if ( !isset( $data['raw_mode'] ) ) {
            $settings['resize'] = 'stretch';
            $add .= ' resize="stretch"';
        }
    }

    $field = apply_filters( 'wpcf_fields_image_editor_submit_field', $field );

    // Save settings
    wpcf_admin_fields_save_field_last_settings( $field['id'], $settings );

    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}

/**
 * View function.
 *
 * @param type $params
 */
function wpcf_fields_image_view( $params ) {

    global $wpcf;

    $output = '';
    $alt = false;
    $title = false;
    $class = array();
    $style = array();

    // Get image data
    $image_data = wpcf_fields_image_get_data( $params['field_value'] );

    // Error
    if ( !empty( $image_data['error'] ) ) {
        return '__wpcf_skip_empty';
    }

    // Set alt
    if ( isset( $params['alt'] ) ) {
        $alt = $params['alt'];
    }

    // Set title
    if ( isset( $params['title'] ) ) {
        $title = $params['title'];
    }

    // Set attachment class
    if ( !empty( $params['size'] ) ) {
        $class[] = 'attachment-' . $params['size'];
    }

    // Set align class
    if ( !empty( $params['align'] ) && $params['align'] != 'none' ) {
        $class[] = 'align' . $params['align'];
    }

    if ( !empty( $params['class'] ) ) {
        $class[] = $params['class'];
    }
    if ( !empty( $params['style'] ) ) {
        $style[] = $params['style'];
    }

    // Compatibility with old parameters
    $old_param = isset( $params['proportional'] ) && $params['proportional'] == 'true' ? 'proportional' : 'crop';
    $resize = isset( $params['resize'] ) ? $params['resize'] : $old_param;

    // Pre-configured size (use WP function) IF NOT CROPPED
    if ( $resize == 'crop' && $image_data['is_attachment'] && !empty( $params['size'] ) ) {
        //print_r('is_attachment');
        if ( isset( $params['url'] ) && $params['url'] == 'true' ) {
            //print_r('is_url');
            $image_url = wp_get_attachment_image_src( $image_data['is_attachment'],
                    $params['size'] );
            if ( !empty( $image_url[0] ) ) {
                $output = $image_url[0];
            } else {
                $output = $params['field_value'];
            }

            // TODO This is current fix, should be re-designed
            $wpcf->__images_wrap_fix[md5( serialize( $params ) )] = $output;
        } else {
            //print_r('is_not_url');
            $output = wp_get_attachment_image( $image_data['is_attachment'],
                    $params['size'], false,
                    array(
                'class' => implode( ' ', $class ),
                'style' => implode( ' ', $style ),
                'alt' => $alt,
                'title' => $title
                    )
            );
        }
    } else { // Custom size
        //print_r('custom_size');
        $width = !empty( $params['width'] ) ? intval( $params['width'] ) : null;
        $height = !empty( $params['height'] ) ? intval( $params['height'] ) : null;

        //////////////////////////
        // If width and height are not set then check the size parameter.
        // This handles the case when the image is not an attachment.
        if ( empty( $width ) && empty( $height ) && !empty( $params['size'] ) ) {
            //print_r('no_width_no_height_and_size');
            switch ( $params['size'] ) {
                case 'thumbnail':
                    $width = get_option( 'thumbnail_size_w' );
                    $height = get_option( 'thumbnail_size_h' );
                    if ( empty( $params['proportional'] ) ) {
                        $crop = get_option( 'thumbnail_crop' );
                    }
                    break;

                case 'medium':
                    $width = get_option( 'medium_size_w' );
                    $height = get_option( 'medium_size_h' );
                    break;

                case 'large':
                    $width = get_option( 'large_size_w' );
                    $height = get_option( 'large_size_h' );
                    break;

                default:
                    global $_wp_additional_image_sizes;
                    if ( isset( $_wp_additional_image_sizes[$params['size']] )
                            && is_array( $_wp_additional_image_sizes[$params['size']] ) ) {
                        extract( $_wp_additional_image_sizes[$params['size']] );
                    }
            }
        }


        // Check if image is outsider and require $width and $height
        if ( (!empty( $width ) || !empty( $height )) && !$image_data['is_outsider'] ) {

            // Resize args
            $args = array(
                'resize' => $resize,
                'padding_color' => isset( $params['padding_color'] ) ? $params['padding_color'] : '#FFF',
                'width' => $width,
                'height' => $height,
                'return' => 'object',
                'suppress_errors' => false,
                'clear_cache' => false,
            );
            WPCF_Loader::loadView( 'image' );
            $__resized_image = types_image_resize( $image_data['fullabspath'],
                    $args );

            if ( is_wp_error( $__resized_image ) ) {
                $resized_image = $params['field_value'];
            } else {
                $resized_image = $__resized_image->url;
                $image_abspath = $__resized_image->path;
                if ( wpcf_get_settings( 'add_resized_images_to_library' )
                        && !wpcf_image_is_attachment( $__resized_image->url )
                        && $image_data['is_in_upload_path'] ) {
                    global $post;
                    wpcf_image_add_to_library( $post, $image_abspath );
                }
            }
        } else {
            $resized_image = $params['field_value'];
        }
        if ( isset( $params['url'] ) && $params['url'] == 'true' ) {
            // TODO This is current fix, should be re-designed
            $wpcf->__images_wrap_fix[md5( serialize( $params ) )] = $resized_image;

            return $resized_image;
        }

        $output = '<img alt="';
        $output .= $alt !== false ? $alt : $resized_image;
        $output .= '" title="';
        $output .= $title !== false ? $title : $resized_image;
        $output .= '"';
        $output .=!empty( $params['onload'] ) ? ' onload="' . $params['onload'] . '"' : '';
        $output .=!empty( $class ) ? ' class="' . implode( ' ', $class ) . '"' : '';
        $output .=!empty( $style ) ? ' style="' . implode( ' ', $style ) . '"' : '';
        $output .= ' src="' . $resized_image . '" />';
    }

    return $output;
}

/**
 * Resizes image using WP image_resize() function.
 *
 * Caches return data if called more than one time in one pass.
 *
 * @staticvar array $cached Caches calls in one pass
 * @param <type> $url_path Full URL path (works only with images on same domain)
 * @param <type> $width
 * @param <type> $height
 * @param <type> $refresh Set to true if you want image re-created or not cached
 * @param <type> $crop Set to true if you want apspect ratio to be preserved
 * @param string $suffix Optional (default 'wpcf_$widthxheight)
 * @param <type> $dest_path Optional (defaults to original image)
 * @param <type> $quality
 * @return array
 */
function wpcf_fields_image_resize_image( $url_path, $width = 300, $height = 200,
        $return = 'relpath', $refresh = FALSE, $crop = TRUE, $suffix = '',
        $dest_path = NULL, $quality = 75 ) {

    if ( empty( $url_path ) ) {
        //print_r('return url path');
        return $url_path;
    }

    // Get image data
    $image_data = wpcf_fields_image_get_data( $url_path );

    if ( empty( $image_data['fullabspath'] ) || !empty( $image_data['error'] ) ) {
        //print_r('return url path no full or error');
        return $url_path;
    }

    // Set cache
    static $cached = array();
    $cache_key = md5( $url_path . $width . $height . intval( $crop ) . $suffix . $dest_path );

    // Check if cached in this call
    if ( !$refresh && isset( $cached[$cache_key][$return] ) ) {
        //print_r('return cached');
        return $cached[$cache_key][$return];
    }

    $width = intval( $width );
    $height = intval( $height );

    // Get size of new file
    $size = @getimagesize( $image_data['fullabspath'] );
    if ( !$size ) {
        //print_r('not size');
        return $url_path;
    }
    list($orig_w, $orig_h, $orig_type) = $size;
    $dims = image_resize_dimensions( $orig_w, $orig_h, $width, $height, $crop );
    if ( !$dims ) {
        //print_r('not dims');
        return $url_path;
    }
    list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

    // Set suffix
    if ( empty( $suffix ) ) {
        $suffix = 'wpcf_' . $dst_w . 'x' . $dst_h;
    } else {
        $suffix .= '_wpcf_' . $dst_w . 'x' . $dst_h;
    }

    $image_data['extension'] = in_array( strtolower( $image_data['extension'] ),
                    array('gif', 'png', 'jpeg') ) ? $image_data['extension'] : 'jpg';

    $image_relpath = $image_data['relpath'] . '/' . $image_data['image_name'] . '-'
            . $suffix . '.' . $image_data['extension'];
    $image_abspath = $image_data['abspath'] . DIRECTORY_SEPARATOR
            . $image_data['image_name'] . '-' . $suffix . '.'
            . $image_data['extension'];

    // Check if already resized
    if ( !$refresh && file_exists( $image_abspath ) ) {
        //print_r('file exists');
        // Cache it
        $cached[$cache_key]['relpath'] = $image_relpath;
        $cached[$cache_key]['abspath'] = $image_abspath;
        return $return == 'relpath' ? $image_relpath : $image_abspath;
    }

    // If original file don't exists
    if ( !file_exists( $image_data['fullabspath'] ) ) {
        //print_r('not file exists');
        return $url_path;
    }

    // Resize image
    $resized_image = @wpcf_image_resize(
                    $image_data['fullabspath'], $width, $height, $crop, $suffix,
                    $dest_path, $quality
    );

    // Check if error
    if ( is_wp_error( $resized_image ) ) {
        //print_r('resized wp error');
        //print_r($resized_image);
        return $url_path;
    }

    $image_abspath = $resized_image;

    // Cache it
    $cached[$cache_key]['relpath'] = $image_relpath;
    $cached[$cache_key]['abspath'] = $image_abspath;

    return $return == 'relpath' ? $image_relpath : $image_abspath;
}

/**
 * Gets all necessary data for processed image.
 *
 * @param type $image
 * @return type
 */
function wpcf_fields_image_get_data( $image ) {

    global $wpcf;

    // Check if already cached
    static $cache = array();
    $cache_key = md5( $image );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }

    WPCF_Loader::loadView( 'image' );
    $utils = Types_Image_Utils::getInstance();

    // Defaults
    $data = array(
        'image' => basename( $image ),
        'image_name' => '',
        'extension' => '',
        'abspath' => '',
        'relpath' => dirname( $image ),
        'fullabspath' => '',
        'fullrelpath' => $image,
        'is_outsider' => 1,
        'is_in_upload_path' => 0,
        'is_attachment' => 0,
        'error' => '',
    );

    // Strip GET vars
    if ( !apply_filters('wpcf_allow_questionmark_in_image_url', false) ) {
        $image = strtok( $image, '?' );
    }

    // Basic URL check
    if ( strpos( $image, 'http' ) != 0 ) {
        return array('error' => sprintf( __( 'Image %s not valid', 'wpcf' ),
                    $image ));
    }
    // Extension check
    $data['extension'] = pathinfo( $image, PATHINFO_EXTENSION );
    if ( !in_array( strtolower( $data['extension'] ),
                    array('jpg', 'jpeg', 'gif', 'png') ) ) {
        return array('error' => sprintf( __( 'Image %s not valid', 'wpcf' ),
                    $image ));
    }

    // Check if it's on same domain
    $data['is_outsider'] = !$utils->onDomain($image);

    if ( empty($data['is_outsider']) ) {
        $data['is_in_upload_path'] = $utils->inUploadPath($image);
        $data['fullabspath'] = $utils->getAbsPath($image);
        $data['abspath'] = dirname( $data['fullabspath'] );

        // Check if it's attachment
        $data['is_attachment'] = wpcf_image_is_attachment( $image );
    }

    // DEbug
    $wpcf_debug = array(
        'image' => $image,
        'docroot' => $_SERVER['DOCUMENT_ROOT'],
    );

    // Set remote if enabled
    if ( $data['is_outsider'] && wpcf_get_settings( 'images_remote' ) ) {
        $remote = wpcf_fields_image_get_remote( $image );

        // DEbug
        $wpcf_debug['remote'] = $remote;

        if ( !is_wp_error( $remote ) ) {
            $data['is_outsider'] = 0;
            $data['is_in_upload_path'] = 1;
            $data['abspath'] = dirname( $remote['abspath'] );
            $data['fullabspath'] = $remote['abspath'];
            $data['image'] = basename($remote['relpath']);
            $data['relpath'] = dirname( $remote['relpath'] );
            $data['fullrelpath'] = $remote['relpath'];
            $data['remotely_fetched'] = true;
        }
    }

    // Set rest of data
    $data['image_name'] = basename( $data['image'], '.' . $data['extension'] );
    $abspath_realpath = realpath( $data['abspath'] );
    $data['abspath'] = $abspath_realpath ? $abspath_realpath : $data['abspath'];
    $fullabspath_realpath = realpath( $data['fullabspath'] );
    $data['fullabspath'] = $fullabspath_realpath ? $fullabspath_realpath : $data['fullabspath'];

    // Cache it
    $cache[$cache_key] = $data;

    // DEbug
    $wpcf_debug['data'] = $data;
    $wpcf->debug->images['processed'][md5( $image )] = $wpcf_debug;

    return $data;
}

/**
 * Strips GET vars from value.
 *
 * @param type $value
 * @return type
 */
function wpcf_fields_image_value_filter( $value ) {
    if ( is_string( $value ) && !apply_filters('wpcf_allow_questionmark_in_image_url', false) ) {
        return strtok( $value, '?' );
    }
    return $value;
}

/**
 * Gets cache directory.
 *
 * @return \WP_Error
 */
function wpcf_fields_image_get_cache_directory( $suppress_filters = false ) {
    WPCF_Loader::loadView( 'image' );
    $utils = Types_Image_Utils::getInstance();
    $cache_dir = $utils->getWritablePath();
    if ( is_wp_error( $cache_dir ) ) {
        return $cache_dir;
    }
    if ( !$suppress_filters ) {
        $cache_dir = apply_filters( 'types_image_cache_dir', $cache_dir );
        if ( !wp_mkdir_p( $cache_dir ) ) {
            return new WP_Error( 'wpcf_image_cache_dir',
                    sprintf( __( 'Image cache directory %s could not be created',
                                    'wpcf' ),
                            '<strong>' . $cache_dir . '</strong>' ) );
        }
    }
    return $cache_dir;
}

function wpcf_image_http_request_timeout( $timeout ) {
    return 20;
}

/**
 * Fetches remote images.
 *
 * @param type $url
 * @return \WP_Error
 */
function wpcf_fields_image_get_remote( $url ) {

    global $wpcf;

    $refresh = false;

    // Set directory
    $cache_dir = wpcf_fields_image_get_cache_directory();
    if ( is_wp_error( $cache_dir ) ) {
        return $cache_dir;
    }

    // Validate image
    $extension = pathinfo( $url, PATHINFO_EXTENSION );
    if ( !in_array( strtolower( $extension ), array('jpg', 'jpeg', 'gif', 'png') ) ) {
        return new WP_Error( 'wpcf_image_cache_not_valid', sprintf( __( 'Image %s not valid',
                                        'wpcf' ), $url ) );
    }

    $image = $cache_dir . md5( $url ) . '.' . $extension;

    // Refresh if necessary
    $refresh_time = intval( wpcf_get_settings( 'images_remote_cache_time' ) );
    if ( $refresh_time != 0 && file_exists( $image ) ) {
        $time_modified = filemtime( $image );
        if ( time() - $time_modified > $refresh_time * 60 * 60 ) {
            $refresh = true;
            $files = glob( $cache_dir . DIRECTORY_SEPARATOR . md5( $url ) . "-*" );
            if ( $files ) {
                foreach ( $files as $filename ) {
                    @unlink( $filename );
                }
            }
        }
    }

    // Check if image is fetched
    if ( $refresh || !file_exists( $image ) ) {

        // fetch the remote url and write it to the placeholder file
        add_filter( 'http_request_timeout', 'wpcf_image_http_request_timeout',
                10, 1 );
        $resp = wp_remote_get( $url );

        // Check if response type is expected
        if ( is_object( $resp ) ) {
            return new WP_Error(
                            'wpcf_image_cache_file_error',
                            sprintf( __( 'Remote server returned error response %1$d %2$s',
                                            'wpcf' ),
                                    esc_html( $resp->errors["http_request_failed"][0] ),
                                    get_status_header_desc( $resp->errors["http_request_failed"][0] )
                            )
            );
        }

        remove_filter( 'http_request_timeout',
                'wpcf_image_http_request_timeout', 10, 1 );
        // make sure the fetch was successful
        if ( $resp['response']['code'] != '200' ) {
            return new WP_Error( 'wpcf_image_cache_file_error', sprintf( __( 'Remote server returned error response %1$d %2$s',
                                            'wpcf' ),
                                    esc_html( $resp['response'] ),
                                    get_status_header_desc( $resp['response'] ) ) );
        }
        if ( !isset( $resp['headers']['content-length'] )
                || strlen( $resp['body'] ) != $resp['headers']['content-length'] ) {
            return new WP_Error( 'wpcf_image_cache_file_error', __( 'Remote file is incorrect size',
                                    'wpcf' ) );
        }

        $out_fp = fopen( $image, 'w' );
        if ( !$out_fp ) {
            return new WP_Error( 'wpcf_image_cache_file_error', __( 'Could not create cache file',
                                    'wpcf' ) );
        }

        fwrite( $out_fp, $resp['body'] );
        fclose( $out_fp );

        $max_size = (int) apply_filters( 'import_attachment_size_limit', 0 );
        $filesize = filesize( $image );
        if ( !empty( $max_size ) && $filesize > $max_size ) {
            @unlink( $image );
            return new WP_Error( 'wpcf_image_cache_file_error', sprintf( __( 'Remote file is too large, limit is %s',
                                            'wpcf' ), size_format( $max_size ) ) );
        }
    }

    return array(
        'abspath' => $image,
        'relpath' => wpcf_image_attachment_url( $image ),
    );
}

/**
 * Clears remote image cache.
 *
 * @param type $action
 */
function wpcf_fields_image_clear_cache( $cache_dir = null, $action = 'outdated' ) {
    if ( is_null( $cache_dir ) ) {
        $cache_dir = wpcf_fields_image_get_cache_directory();
    }
    $refresh_time = intval( wpcf_get_settings( 'images_remote_cache_time' ) );
    if ( $refresh_time == 0 && $action != 'all' ) {
        return true;
    }
    foreach ( glob( $cache_dir . DIRECTORY_SEPARATOR . "*" ) as $filename ) {
        if ( $action == 'all' ) {
            @unlink( $filename );
        } else {
            $time_modified = filemtime( $filename );
            if ( time() - $time_modified > $refresh_time * 60 * 60 ) {
                @unlink( $filename );
                // Clear resized images
                $path = pathinfo( $filename );
                foreach ( glob( $path['dirname'] . DIRECTORY_SEPARATOR . $path['filename'] . "-*" ) as $resized ) {
                    @unlink( $resized );
                }
            }
        }
    }
}

/**
 * Filters upload paths (to fix Windows issues).
 *
 * @param type $args
 * @return type
 */
function wpcf_fields_image_uploads_realpath( $args ) {

    global $wpcf;

    $fixes = array('path', 'subdir', 'basedir');
    foreach ( $fixes as $fix ) {
        if ( isset( $args[$fix] ) ) {
            /*
             * Since 1.1.5
             *
             * We need realpath(), open_basedir restriction check
             *
             * Suppressing warnings, checking realpath returning FALSE, check
             * if open_basedir ini is set.
             *
             * https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/153462252/comments
             * http://php.net/manual/en/ini.sect.safe-mode.php
             * http://php.net/manual/en/ini.core.php#ini.open-basedir
             */
            $realpath = @realpath( $args[$fix] );

            $wpcf->debug->images['relpath_fixes'][$fix] = array(
                'realpath' => $realpath,
                'args' => $args[$fix],
                'altered' => $realpath != $args[$fix] && $realpath !== false ? 'TRUE' : 'FALSE',
            );

//            $open_basedir = @ini_get( 'open_basedir' );
            if ( $realpath !== false ) {
                $args[$fix] = $realpath;
            }
        }
    }
    return $args;
}

/**
 * i18n friendly version of basename(), copy from wp-includes/formatting.php
 * to solve bug with windows
 *
 * @since 3.1.0
 *
 * @param string $path A path.
 * @param string $suffix If the filename ends in suffix this will also be cut off.
 * @return string
 */
function wpcf_basename( $path, $suffix = '' ) {
    return urldecode( basename( str_replace( array('%2F', '%5C'), '/',
                                    urlencode( $path ) ), $suffix ) );
}

/**
 * Copy from wp-includes/media.php
 * Scale down an image to fit a particular size and save a new copy of the image.
 *
 * The PNG transparency will be preserved using the function, as well as the
 * image type. If the file going in is PNG, then the resized image is going to
 * be PNG. The only supported image types are PNG, GIF, and JPEG.
 *
 * Some functionality requires API to exist, so some PHP version may lose out
 * support. This is not the fault of WordPress (where functionality is
 * downgraded, not actual defects), but of your PHP version.
 *
 * @since 2.5.0
 *
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed WP_Error on failure. String with new destination path.
 */
function wpcf_image_resize( $file, $max_w, $max_h, $crop = false,
        $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

    $image = wp_load_image( $file );
    if ( !is_resource( $image ) )
        return new WP_Error( 'error_loading_image', $image, $file );

    $size = @getimagesize( $file );
    if ( !$size )
        return new WP_Error( 'invalid_image', __( 'Could not read image size' ), $file );
    list($orig_w, $orig_h, $orig_type) = $size;

    $dims = image_resize_dimensions( $orig_w, $orig_h, $max_w, $max_h, $crop );
    if ( !$dims )
        return new WP_Error( 'error_getting_dimensions', __( 'Could not calculate resized image dimensions' ) );
    list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

    $newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );

    imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y,
            $dst_w, $dst_h, $src_w, $src_h );

    // convert from full colors to index colors, like original PNG.
    if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imageistruecolor' ) && !imageistruecolor( $image ) )
        imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

    // we don't need the original in memory anymore
    imagedestroy( $image );

    // $suffix will be appended to the destination filename, just before the extension
    if ( !$suffix )
        $suffix = "{$dst_w}x{$dst_h}";

    $info = pathinfo( $file );
    $dir = $info['dirname'];
    $ext = $info['extension'];
    $name = wpcf_basename( $file, ".$ext" ); // use fix here for windows

    if ( !is_null( $dest_path ) and $_dest_path = realpath( $dest_path ) )
        $dir = $_dest_path;
    $destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

    if ( IMAGETYPE_GIF == $orig_type ) {
        if ( !imagegif( $newimage, $destfilename ) )
            return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
    } elseif ( IMAGETYPE_PNG == $orig_type ) {
        if ( !imagepng( $newimage, $destfilename ) )
            return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
    } else {
        // all other formats are converted to jpg
        if ( 'jpg' != $ext && 'jpeg' != $ext )
            $destfilename = "{$dir}/{$name}-{$suffix}.jpg";
        if ( !imagejpeg( $newimage, $destfilename,
                        apply_filters( 'jpeg_quality', $jpeg_quality,
                                'image_resize' ) ) )
            return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
    }

    imagedestroy( $newimage );

    // Set correct file permissions
    $stat = stat( dirname( $destfilename ) );
    $perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
    @ chmod( $destfilename, $perms );

    return $destfilename;
}

/**
 * Fixes for Win.
 *
 * For now we fix file path to have unified type slashes.
 *
 * @param type $file
 * @param type $attachment_id
 * @return type
 */
function wpcf_fields_image_win32_update_attached_file_filter( $file,
        $attachment_id ) {

    global $wpcf;

    $return = str_replace( '\\', '/', $file );
    $wpcf->debug->images['Win']['update_attached_file_filter'][] = array(
        'file' => $file,
        'return' => $return,
        'attachment_id' => $attachment_id,
    );

    return $return;
}

/**
 * Filters image view.
 *
 * This is added to handle image 'url' parameter.
 * We need to unwrap value. Also added to avoid cludging frontend.php.
 *
 * @param boolean $params
 * @param type $field
 * @return boolean
 */
function wpcf_fields_image_view_filter( $output, $value, $type, $slug, $name,
        $params ) {

    global $wpcf;

    if ( $type == 'image' ) {
        // If 'url' param is used, force return un-wrapped
        if ( isset( $params['url'] ) && $params['url'] != 'false' ) {
            $cache_key = md5( serialize( $params ) );
            if ( isset( $wpcf->__images_wrap_fix[$cache_key] ) ) {
                $output = $wpcf->__images_wrap_fix[$cache_key];
            }
        }
    }

    return $output;
}

/**
 * Adds image to library.
 *
 * @param type $post
 * @param type $abspath
 */
function wpcf_image_add_to_library( $post, $abspath ){
    $guid = wpcf_image_attachment_url( $abspath );
    if ( !wpcf_image_is_attachment( $guid ) ) {
        $wp_filetype = wp_check_filetype( basename( $abspath ), null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $abspath ) ),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $guid,
        );
        $attach_id = wp_insert_attachment( $attachment, $abspath, $post->ID );
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $abspath );
        $attach_data['file'] = str_replace( '\\', '/',
                wpcf_image_normalize_attachment( $attach_data['file'] ) );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        update_attached_file( $attach_id, $attach_data['file'] );
    }
}

/**
 * Checks if image is attachment.
 *
 * @global object $wpdb
 * @param type $guid
 * @return type
 */
function wpcf_image_is_attachment( $guid ) {
    global $wpdb;
    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid=%s",
            $guid
        )
    );
}

/**
 * Gets attachment URL (in uploads, root or date structure).
 *
 * @param type $abspath
 * @return type
 */
function wpcf_image_attachment_url( $abspath ) {
    WPCF_Loader::loadView( 'image' );
    return Types_Image_Utils::getInstance()->normalizeAttachmentUrl( $abspath );
}

/**
 * Returns path to attachment relative to upload_dir.
 *
 * @param type $abspath
 * @return string '2014/01/img.jpg'
 */
function wpcf_image_normalize_attachment( $abspath ) {
    WPCF_Loader::loadView( 'image' );
    return Types_Image_Utils::getInstance()->normalizeAttachment( $abspath );
}
