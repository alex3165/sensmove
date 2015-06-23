<?php
/*
 * Frontend functions.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/frontend.php $
 * $LastChangedDate: 2015-03-10 06:46:08 +0000 (Tue, 10 Mar 2015) $
 * $LastChangedRevision: 1109249 $
 * $LastChangedBy: iworks $
 *
 */

global $wp_version;

if ( version_compare( $wp_version, '3.3', '<' ) ) {
    // add a the_content filter to allow types shortcodes to be closed.
    // This is a bit of a HACK for version 3.2.1 and less

    add_filter( 'the_content', 'wpcf_fix_closed_types_shortcodes', 9, 1 );
    add_filter( 'the_content', 'wpcf_fix_closed_types_shortcodes_after', 11, 1 );

    function wpcf_fix_closed_types_shortcodes( $content ) {
        $content = str_replace( '][/types', ']###TYPES###[/types', $content );
        return $content;
    }

    function wpcf_fix_closed_types_shortcodes_after( $content ) {
        $content = str_replace( '###TYPES###', '', $content );
        return $content;
    }

}

add_shortcode( 'types', 'wpcf_shortcode' );

/**
 * Shortcode processing.
 * 
 * Called by WP when rendering post on frontend.
 * From here follow these:
 * @see types_render_field() Renders shortcode. Can be used other ways too.
 * @see types_render_field_single() Renders single field. Useful for Repeater.
 * Afterwards wrapping options.
 *  
 * @param type $atts
 * @param type $content
 * @param type $code
 * @return string
 */
function wpcf_shortcode( $atts, $content = null, $code = '' ) {

    global $wpcf;
    
    // Switch the post if there is an attribute of 'id' in the shortcode.
    $post_id_atts = new WPV_wpcf_switch_post_from_attr_id( $atts );

    if ( !is_array( $atts ) ) {
        $wpcf->errors['shortcode_malformed'][] = func_get_args();
        return '';
    }

    $atts = array_merge( array(
        'field' => false,
        'usermeta' => false,
        'style' => '',
        'show_name' => false,
        'raw' => false,
            ), $atts
    );
    if ( $atts['field'] ) {
        return types_render_field( $atts['field'], $atts, $content, $code );
    }
    if ( $atts['usermeta'] ) {
        return types_render_usermeta( $atts['usermeta'], $atts, $content, $code );
    }
    return '';
}

/**
 * Calls view function for specific field type.
 * 
 * @param type $field
 * @param type $atts
 * @return type 
 */
function types_render_field( $field_id = null, $params = array(), $content = null, $code = '' )
{
    if ( empty($field_id) ) {
        return '';
    }

    global $wpcf;

    // HTML var holds actual output
    $html = '';

    // Set post ID to global
    $post_id = get_the_ID();

    // Check if other post required
    if ( isset( $params['post_id'] ) ) {
        // If numeric value
        if ( is_numeric( $params['post_id'] ) ) {
            $post_id = intval( $params['post_id'] );

            // WP parent
        } else if ( $params['post_id'] == '$parent' ) {
            $current_post = get_post( $post_id );
            if ( empty( $current_post->post_parent ) ) {
                return '';
            }
            $post_id = $current_post->post_parent;

            // Types parent
        } else if ( strpos( $params['post_id'], '$' ) === 0 ) {
            $post_id = intval( WPCF_Relationship::get_parent( $post_id, trim( $params['post_id'], '$' ) ) );
        }
    }

    if ( empty( $post_id ) ) {
        return '';
    }

    // Set post
    $post = get_post( $post_id );

    if ( empty( $post ) ) {
        return '';
    }

    // Get field
    $field = types_get_field( $field_id );

    // If field not found return empty string
    if ( empty( $field ) ) {

        // Log
        if ( !function_exists( 'wplogger' ) ) {
            require_once WPCF_EMBEDDED_ABSPATH . '/common/wplogger.php';
        }
        global $wplogger;
        $wplogger->log( 'types_render_field call for missing field \'' . $field_id . '\'', WPLOG_DEBUG );

        return '';
    }

    // Set field
    $wpcf->field->set( $post, $field );

    // See if repetitive
    if ( types_is_repetitive( $field ) ) {
        $wpcf->repeater->set( $post_id, $field );
        $_meta = $wpcf->repeater->_get_meta();
        $meta = $_meta['custom_order'];

        // Sometimes if meta is empty - array(0 => '') is returned
        if ( count( $meta ) == 1 && reset( $meta ) == '' ) {
            return '';
        }
        if ( !empty( $meta ) ) {
            $output = '';

            if ( isset( $params['index'] ) ) {
                $index = $params['index'];
            } else {
                $index = '';
            }

            // Allow wpv-for-each shortcode to set the index
            $index = apply_filters( 'wpv-for-each-index', $index );

            if ( $index === '' ) {
                $output = array();
                foreach ( $meta as $temp_key => $temp_value ) {
                    $params['field_value'] = $temp_value;
                    $temp_output = types_render_field_single( $field, $params, $content, $code, $temp_key );
                    if ( !empty( $temp_output ) ) {
                        $output[] = $temp_output;
                    }
                }
                if ( !empty( $output ) && isset( $params['separator'] )
                        && $params['separator'] !== '' ) {
                    $output = implode( html_entity_decode( $params['separator'] ),
                            $output );
                } else if ( !empty( $output ) ) {
                    $output = implode( ' ', $output );
                } else {
                    return '';
                }
            } else {
                // Make sure indexed right
                $_index = 0;
                foreach ( $meta as $temp_key => $temp_value ) {
                    if ( $_index == $index ) {
                        $params['field_value'] = $temp_value;
                        return types_render_field_single( $field, $params, $content, $code, $temp_key );
                    }
                    $_index++;
                }
                // If missed index
                return '';
            }
            $html = $output;
        } else {
            return '';
        }
    } else {

        // Non-repetitive field
        $params['field_value'] = wpcf_get_post_meta( $post_id,
                wpcf_types_get_meta_prefix( $field ) . $field['slug'], true );
        if ( $params['field_value'] == '' && $field['type'] != 'checkbox' ) {
            return '';
        }
        $html = types_render_field_single( $field, $params, $content, $code, $wpcf->field->meta_object->meta_id );
    }
    return $wpcf->field->html( $html, $params );
}

/**
 * Calls view function for specific field type by single field.
 * 
 * @param type $field
 * @param type $atts
 * @return type 
 */
function types_render_field_single( $field, $params, $content = null, $code = '', $meta_id = null )
{
    global $post;

    if ( empty( $post ) ) {
        $post = (object) array('ID' => '');
    }

    // Apply filters to field value
    if ( is_string( $params['field_value'] ) ) {
        $params['field_value'] = trim( $params['field_value'] );
    }

    $params = apply_filters( 'types_field_shortcode_parameters', $params,
        $field, $post, $meta_id );

    $params['field_value'] = apply_filters( 'wpcf_fields_value_display', $params['field_value'], $params, $post->ID, $field['id'], $meta_id );

    $params['field_value'] = apply_filters( 'wpcf_fields_slug_' . $field['slug'] . '_value_display', $params['field_value'], $params, $post->ID, $field['id'], $meta_id );

    $params['field_value'] = apply_filters( 'wpcf_fields_type_' . $field['type'] . '_value_display', $params['field_value'], $params, $post->ID, $field['id'], $meta_id );
    // To make sure
    if ( is_string( $params['field_value'] ) ) {
        $params['field_value'] = addslashes( stripslashes( strval( $params['field_value'] ) ) );
    }

    // Set values
    if ( is_array( $params['field_value'] ) ) {
        foreach ( $params['field_value'] as $f_key => $f_value ) {
            if ((isset($field['data']['repetitive'])) && ($field['data']['repetitive'])) {
                //Repetitive fields, used meta ID for proper string translation unique identification
                $params['field_value'] = wpcf_translate( 'field ' . $field['id'] . ' value '.$meta_id, $params['field_value'] );
            } else {
                //Non-repetitive fields, use post ID
                $params['field_value'] = wpcf_translate( 'field ' . $field['id'] . ' value '.$post->ID, $params['field_value'] );
            }
        }
    } else {
        if ((isset($field['data']['repetitive'])) && ($field['data']['repetitive'])) {
    		    		
    		//Repetitive fields, used meta ID for proper string translation unique identification
    		$params['field_value'] = wpcf_translate( 'field ' . $field['id'] . ' value '.$meta_id, $params['field_value'] );
    		
    	} else {
    		//Non-repetitive fields, use post ID
    		$params['field_value'] = wpcf_translate( 'field ' . $field['id'] . ' value '.$post->ID, $params['field_value'] );
    	}
    }

    $field['name'] = wpcf_translate( 'field ' . $field['id'] . ' name', $field['name'] );
    $params['field'] = $field;
    $params['#content'] = htmlspecialchars( $content );
    $params['#code'] = $code;

    // Set additional data
    $params['__meta_id'] = $meta_id;
    $params['field']['__meta_id'] = $meta_id;

    if ( (isset( $params['raw'] ) && $params['raw'] == 'true')
        || (isset( $params['output'] ) && $params['output'] == 'raw') ) {
            // Skype is array
            if ( $field['type'] == 'skype' && isset( $params['field_value']['skypename'] ) ) {
                $output = $params['field_value']['skypename'];
            } else if ($field['type'] == 'checkboxes' && is_array( $params['field_value'] ) ) {
                $output = '';
                foreach ($params['field_value'] as $value) {
                    if ($output != '') {
                        $output .= ', ';
                    }
                    $output .= $value[0];
                }
            } else {
                $output = $params['field_value'];
            }
        } else {
            /*
             * This is place where view function is called.
             * Returned data should be string.
             */
            $output = '';
            $_view_func = 'wpcf_fields_' . strtolower( $field['type'] ) . '_view';
            if ( is_callable( $_view_func ) ) {
                $output = strval( call_user_func( $_view_func, $params ) );
            }

            // If no output
            if ( empty( $output ) && isset( $params['field_value'] )
                && $params['field_value'] !== "" ) {
                    $output = $params['field_value'];
                } else if ( $output == '__wpcf_skip_empty' ) {
                    $output = '';
                }

            if (isset($params['output']) && $params['output'] == 'html') {
                $output = wpcf_frontend_compat_html_output( $output, $field, $content, $params );
            } else {
                // Prepend name if needed
                if ( !empty( $output ) && isset( $params['show_name'] )
                    && $params['show_name'] == 'true' ) {
                        $output = $params['field']['name'] . ': ' . $output;
                    }
            }
        }

    // Apply filters
    $output = strval( apply_filters( 'types_view', $output,
        $params['field_value'], $field['type'], $field['slug'],
        $field['name'], $params ) );
    return htmlspecialchars_decode( stripslashes( strval( $output ) ) );
}

function wpcf_frontend_compat_html_output( $output, $field, $content, $params ) {
    // Count fields (if there are duplicates)
    static $count = array();
    // Count it
    if ( !isset( $count[$field['slug']] ) ) {
        $count[$field['slug']] = 1;
    } else {
        $count[$field['slug']] += 1;
    }
    // If no output
    if ( empty( $output ) && !empty( $params['field_value'] ) ) {
        $output = wpcf_frontend_wrap_field_value( $field,
                $params['field_value'], $params );
        $output = wpcf_frontend_wrap_field( $field, $output, $params );
    } else if ( $output != '__wpcf_skip_empty' ) {
        $output = wpcf_frontend_wrap_field_value( $field, $output, $params );
        $output = wpcf_frontend_wrap_field( $field, $output, $params );
    } else {
        $output = '';
    }
    // Add count
    if ( isset( $count[$field['slug']] ) && intval( $count[$field['slug']] ) > 1 ) {
        $add = '-' . intval( $count[$field['slug']] );
        $output = str_replace( 'id="wpcf-field-' . $field['slug'] . '"',
                'id="wpcf-field-' . $field['slug'] . $add . '"', $output );
    }
    return $output;
}

/**
 * Wraps field content.
 * 
 * @param type $field
 * @param type $content
 * @return type 
 */
function wpcf_frontend_wrap_field( $field, $content, $params = array() ) {
    if ( isset( $params['output'] ) && $params['output'] == 'html' ) {
        $class = array();
        if ( !empty( $params['class'] )
                && !in_array( $field['type'],
                        array('file', 'image', 'email', 'url', 'wysiwyg') ) ) {
            $class[] = $params['class'];
        }
        $class[] = 'wpcf-field-' . $field['type'] . ' wpcf-field-'
                . $field['slug'];
        // Add name if needed
        if ( isset( $params['show_name'] ) && $params['show_name'] == 'true'
                && strpos( $content,
                        'class="wpcf-field-' . $field['type']
                        . '-name ' ) === false ) {
            $content = wpcf_frontend_wrap_field_name( $field, $field['name'],
                            $params ) . $content;
        }
        $output = '<div id="wpcf-field-' . $field['slug'] . '"'
                . ' class="' . implode( ' ', $class ) . '"';
        if ( !empty( $params['style'] )
                && !in_array( $field['type'],
                        array('date', 'file', 'image', 'email', 'url', 'wysiwyg') ) ) {
            $output .= ' style="' . $params['style'] . '"';
        }
        $output .= '>' . $content . '</div>';
        return $output;
    } else {
        if ( isset( $params['show_name'] ) && $params['show_name'] == 'true'
                && strpos( $content, $field['name'] . ':' ) === false ) {
            $content = wpcf_frontend_wrap_field_name( $field,
                            $params['field']['name'], $params ) . $content;
        }
        return $content;
    }
}

/**
 * Wraps field name.
 * 
 * @param type $field
 * @param type $content
 * @return type 
 */
function wpcf_frontend_wrap_field_name( $field, $content, $params = array() ) {
    if ( isset( $params['output'] ) && $params['output'] == 'html' ) {
        $class = array();
        if ( $field['type'] == 'checkboxes' && isset( $params['option'] ) ) {
            if ( isset( $params['field']['data']['options'][$params['option']]['title'] ) ) {
                $content = $params['field']['data']['options'][$params['option']]['title'];
            }
            $class[] = $params['option'] . '-name';
        }
        if ( !in_array( $field['type'],
                        array('file', 'image', 'email', 'url', 'wysiwyg') )
                && !empty( $params['class'] ) ) {
            $class[] = $params['class'];
        }
        $class[] = 'wpcf-field-name wpcf-field-' . $field['type'] . ' wpcf-field-'
                . $field['slug'] . '-name';
        if ( $field['type'] == 'wysiwyg' || $field['type'] == 'textarea' ) {
            $output = '<div class="' . implode( ' ', $class ) . '"';
            if ( !empty( $params['style'] ) ) {
                $output .= ' style="' . $params['style'] . '"';
            }
            $output .= '>' . stripslashes( strval( $content ) ) . ':</div> ';
            return $output;
        }
        $output = '<span class="' . implode( ' ', $class ) . '"';
        if ( !empty( $params['style'] )
                && !in_array( $field['type'],
                        array('date', 'file', 'image', 'email', 'url', 'wysiwyg') ) ) {
            $output .= ' style="' . $params['style'] . '"';
        }
        $output .= '>' . stripslashes( strval( $content ) ) . ':</span> ';
        return $output;
    } else {
        return stripslashes( strval( $content ) ) . ': ';
    }
}

/**
 * Wraps field value.
 * 
 * @param type $field
 * @param type $content
 * @return type 
 */
function wpcf_frontend_wrap_field_value( $field, $content, $params = array() ) {
    if ( isset( $params['output'] ) && $params['output'] == 'html' ) {
        $class = array();
        if ( $field['type'] == 'checkboxes' && isset( $params['option'] ) ) {
            $class[] = $params['option'] . '-value';
        }
        if ( !empty( $params['class'] )
                && !in_array( $field['type'],
                        array('file', 'image', 'email', 'url', 'wysiwyg') ) ) {
            $class[] = $params['class'];
        }

        // add some default
        if ( !array_key_exists( 'style', $params ) )
            $params['style'] = '';

        $class[] = 'wpcf-field-value wpcf-field-' . $field['type']
                . '-value wpcf-field-' . $field['slug'] . '-value';
        if ( $field['type'] == 'skype' || $field['type'] == 'image' || ($field['type'] == 'date' && $params['style'] == 'calendar')
                || $field['type'] == 'wysiwyg' || $field['type'] == 'textarea' ) {
            $output = '<div class="' . implode( ' ', $class ) . '"';
            if ( !empty( $params['style'] )
                    && !in_array( $field['type'],
                            array('date', 'file', 'image', 'email', 'url', 'wysiwyg') ) ) {
                $output .= ' style="' . $params['style'] . '"';
            }
            $output .= '>' . stripslashes( strval( $content ) ) . '</div>';
            return $output;
        }
        $output = '<span class="' . implode( ' ', $class ) . '"';
        if ( !empty( $params['style'] )
                && !in_array( $field['type'],
                        array('date', 'file', 'image', 'email', 'url', 'wysiwyg') ) ) {
            $output .= ' style="' . $params['style'] . '"';
        }
        $output .= '>' . stripslashes( strval( $content ) ) . '</span>';
        return $output;
    } else {
        return stripslashes( strval( $content ) );
    }
}

// Add a filter to handle Views queries with checkboxes.

add_filter( 'wpv_filter_query', 'wpcf_views_query', 12, 2 ); // after custom fields.

/**
 * Filter to handle Views queries with checkboxes.
 * 
 * @todo DOCUMENT THIS!
 * 
 * @param type $query
 * @param type $view_settings
 * @return string
 */
function wpcf_views_query( $query, $view_settings ) {

    $meta_filter_required = false;

    $opt = get_option( 'wpcf-fields' );

    if ( isset( $query['meta_query'] ) ) {
        foreach ( $query['meta_query'] as $index => $meta ) {
            if ( is_array( $meta ) && isset( $meta['key'] ) ) {
                $field_name = $meta['key'];
                if ( _wpcf_is_checkboxes_field( $field_name ) ) {

                    $orginal = $query['meta_query'][$index];

                    unset($query['meta_query'][$index]);

                    // We'll use SQL regexp to find the checked items.
                    // Note that we are creating something here that
                    // then gets modified to a proper SQL REGEXP in
                    // the get_meta_sql filter.

                    $field_name = substr( $field_name, 5 );

                    $meta_filter_required = true;

                    /* According to http://codex.wordpress.org/Class_Reference/WP_Meta_Query#Accepted_Arguments,
					 * $meta['value'] can be an array or a string. In case of a string we additionally allow
					 * multiple comma-separated values. */
					if( is_array( $meta['value'] ) ) {
						$values = $meta['value'];
					} elseif( is_string( $meta['value'] ) ) {
						$values = explode( ',', $meta['value'] );
					} else {
						// This can happen if $meta['value'] is a number, for example.
						$values = array( $meta['value'] );
					}
                    $options = $opt[$field_name]['data']['options'];

                    global $wp_version;

					if ( version_compare( $wp_version, '4.1', '<' ) ) { 
						// We can not use nested meta_query entries
						foreach ( $values as $value ) {
							foreach ( $options as $key => $option ) {
								if ( $option['title'] == $value ) {
									$query['meta_query'][] = array(
										'key' => $meta['key'],
										'compare' => in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'NOT LIKE' : 'LIKE',
										'value' => $key,
										'type' => 'CHAR',
									);
									break;
								}
							}
						}
					} else {
						// We can use nested meta_query entries
						if ( count( $values ) < 2 ) {
							// Only one value to filter by, so no need to add nested meta_query entries
							foreach ( $values as $value ) {
								foreach ( $options as $key => $option ) {
									if ( $option['title'] == $value ) {
										$query['meta_query'][] = array(
											'key' => $meta['key'],
											'compare' => in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'NOT LIKE' : 'LIKE',
											'value' => $key,
											'type' => 'CHAR',
										);
										break;
									}
								}
							}
						} else {
							// We will translate each value into a meta_query clause and add them all as a nested meta_query entry
							$inner_relation = in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'AND' : 'OR';
							$inner_compare = in_array( $orginal['compare'], array( '!=', 'NOT LIKE', 'NOT IN' ) ) ? 'NOT LIKE' : 'LIKE';
							$inner_meta_query = array(
								'relation' => $inner_relation
							);
							foreach ( $values as $value ) {
								foreach ( $options as $key => $option ) {
									if ( $option['title'] == $value ) {
										$inner_meta_query[] = array(
											'key' => $meta['key'],
											'compare' => $inner_compare,
											'value' => $key,
											'type' => 'CHAR',
										);
										break;
									}
								}
							}
							$query['meta_query'][] = $inner_meta_query;
						}
					}
                }
            }
        }
    }

    if ( $meta_filter_required ) {
        add_filter( 'get_meta_sql', 'wpcf_views_get_meta_sql', 10, 6 );
    }
    return $query;
}

function _wpcf_is_checkboxes_field( $field_name ) {
    $opt = get_option( 'wpcf-fields' );
    if ( $opt && strpos( $field_name, 'wpcf-' ) === 0 ) {
        $field_name = substr( $field_name, 5 );
        if ( isset( $opt[$field_name]['type'] ) ) {
            $field_type = strtolower( $opt[$field_name]['type'] );
            if ( $field_type == 'checkboxes' ) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @todo Will someone document this?
 * @param type $clause
 * @param type $queries
 * @param type $type
 * @param type $primary_table
 * @param type $primary_id_column
 * @param type $context
 * @return type
 */
function wpcf_views_get_meta_sql( $clause, $queries, $type, $primary_table,
        $primary_id_column, $context ) {

    // Look for the REGEXP code we added and covert it to a proper SQL REGEXP 
    $regex = '/= \'REGEXP\(([^\)]*)\)\'/siU';

    if ( preg_match_all( $regex, $clause['where'], $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $clause['where'] = str_replace( $match[0],
                    'REGEXP \'' . $match[1] . '\'', $clause['where'] );
        }
    }

    remove_filter( 'get_meta_sql', 'wpcf_views_get_meta_sql', 10, 6 );

    return $clause;
}
