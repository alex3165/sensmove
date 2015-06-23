<?php
/*
 * Common functions.
 */
define( 'ICL_COMMON_FUNCTIONS', true );

// for retro compatibility with WP < 3.5
if( !function_exists('wp_normalize_path') ){
    function wp_normalize_path( $path ) {
        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '|/+|','/', $path );
        return $path;
    }
}

/**
 * Calculates relative path for given file.
 * 
 * @param type $file Absolute path to file
 * @return string Relative path
 */
function icl_get_file_relpath( $file ) {
    // website url form DB
    $url = get_option('siteurl');
    // fix the protocol
    $base_root = set_url_scheme( $url );

    // normalise windows paths
    $path_to_file = wp_normalize_path($file);
    // get file directory
    $file_dir = wp_normalize_path( dirname( $path_to_file ) );
    // get the path to 'wp-content'
    $from_content_dir = wp_normalize_path( realpath( WP_CONTENT_DIR ) );
    // get wp-content dirname
    $content_dir = wp_normalize_path( basename(WP_CONTENT_DIR) );

    // remove absolute path part until 'wp-content' folder
    $path = str_replace( $from_content_dir, '', $file_dir);
    // add wp-content dir to path
    $path = wp_normalize_path( $content_dir.$path );

    // build url
    $relpath = $base_root . '/' . $path;

    return $relpath;
}

/**
 * Fix WP's multiarray parsing.
 * 
 * @param type $arg
 * @param type $defaults
 * @return type 
 */
function wpv_parse_args_recursive( $arg, $defaults ) {
    $temp = false;
    if ( isset( $arg[0] ) ) {
        $temp = $arg[0];
    } else if ( isset( $defaults[0] ) ) {
        $temp = $defaults[0];
    }
    $arg = wp_parse_args( $arg, $defaults );
    if ( $temp ) {
        $arg[0] = $temp;
    }
    foreach ( $defaults as $default_setting_parent => $default_setting ) {
        if ( !is_array( $default_setting ) ) {
            if ( !isset( $arg[$default_setting_parent] ) ) {
                $arg[$default_setting_parent] = $default_setting;
            }
            continue;
        }
        if ( !isset( $arg[$default_setting_parent] ) ) {
            $arg[$default_setting_parent] = $defaults[$default_setting_parent];
        }
        $arg[$default_setting_parent] = wpv_parse_args_recursive( $arg[$default_setting_parent],
                $defaults[$default_setting_parent] );
    }

    return $arg;
}

/*
 * Extra check for date for shortcode in shortcode. Called as filter in wpv_condition bellow.
 */

function wpv_add_time_functions( $value ) {
    return wpv_filter_parse_date( $value );
}

/**
 * Condition function to evaluate and display given block based on expressions
 * 'args' => arguments for evaluation fields
 * 
 * Supported actions and symbols:
 * 
 * Integer and floating-point numbers
 * Math operators: +, -, *, /
 * Comparison operators: &lt;, &gt;, =, &lt;=, &gt;=, !=
 * Boolean operators: AND, OR, NOT
 * Nested expressions - several levels of brackets
 * Variables defined as shortcode parameters starting with a dollar sign
 * empty() function that checks for blank or non-existing fields
 * 
 * 
 */
function wpv_condition( $atts, $post_to_check = null ) {
    extract(
            shortcode_atts( array('evaluate' => FALSE), $atts )
    );

    // Do not overwrite global post
//    global $post;

    // if in admin, get the post from the URL
    if ( is_admin() ) {
        if ( empty($post_to_check->ID) ) {
            // Get post
            if ( isset( $_GET['post'] ) ) {
                $post_id = (int) $_GET['post'];
            } else if ( isset( $_POST['post_ID'] ) ) {
                $post_id = (int) $_POST['post_ID'];
            } else {
                $post_id = 0;
            }
            if ( $post_id ) {
                $post = get_post( $post_id );
            }
        } else {
            $post = $post_to_check;
        }
    }
    if ( empty($post->ID) ) {
        global $post;
    }
	$has_post = true;
    if ( empty($post->ID) ) {
        // Will not execute any condition that involves custom fields
        $has_post = false;
    }

    global $wplogger;
    
	if ( $has_post ) {
		do_action( 'wpv_condition', $post );
	}

    $logging_string = "Original expression: " . $evaluate;

    add_filter( 'wpv-extra-condition-filters', 'wpv_add_time_functions' );
    $evaluate = apply_filters( 'wpv-extra-condition-filters', $evaluate );
	
	$logging_string .= "; After extra conditions: " . $evaluate;

    // evaluate empty() statements for variables
	if ( $has_post ) {
		$empties = preg_match_all( "/empty\(\s*\\$(\w+)\s*\)/", $evaluate, $matches );
		if ( $empties && $empties > 0 ) {
			for ( $i = 0; $i < $empties; $i++ ) {
				$match_var = get_post_meta( $post->ID, $atts[$matches[1][$i]], true );
				$is_empty = '1=0';

				// mark as empty only nulls and ""  
	//            if ( is_null( $match_var ) || strlen( $match_var ) == 0 ) {
				if ( is_null( $match_var )
						|| ( is_string( $match_var ) && strlen( $match_var ) == 0 )
						|| ( is_array( $match_var ) && empty( $match_var ) ) ) {
					$is_empty = '1=1';
				}
				$evaluate = str_replace( $matches[0][$i], $is_empty, $evaluate );
				$logging_string .= "; After empty: " . $evaluate;
			}
		}
	}
    
    // find variables that are to be used as strings.
    // eg '$f1'
    // will replace $f1 with the actual field value
	if ( $has_post ) {
		$strings_count = preg_match_all( '/(\'[\$\w^\']*\')/', $evaluate, $matches );
		if ( $strings_count && $strings_count > 0 ) {
			for ( $i = 0; $i < $strings_count; $i++ ) {
				$string = $matches[1][$i];
				// remove single quotes from string literals to get value only
				$string = (strpos( $string, '\'' ) === 0) ? substr( $string, 1,
								strlen( $string ) - 2 ) : $string;
				if ( strpos( $string, '$' ) === 0 ) {
					$variable_name = substr( $string, 1 ); // omit dollar sign
					if ( isset( $atts[$variable_name] ) ) {
						$string = get_post_meta( $post->ID, $atts[$variable_name], true );
						$evaluate = str_replace( $matches[1][$i], "'" . $string . "'", $evaluate );
						$logging_string .= "; After variables I: " . $evaluate;
					}
				}
			}
		}
	}

    // find string variables and evaluate
    $strings_count = preg_match_all( '/((\$\w+)|(\'[^\']*\'))\s*([\!<>\=]+)\s*((\$\w+)|(\'[^\']*\'))/',
            $evaluate, $matches );

    // get all string comparisons - with variables and/or literals
    if ( $strings_count && $strings_count > 0 ) {
        for ( $i = 0; $i < $strings_count; $i++ ) {

            // get both sides and sign
            $first_string = $matches[1][$i];
            $second_string = $matches[5][$i];
            $math_sign = $matches[4][$i];

            // remove single quotes from string literals to get value only
            $first_string = (strpos( $first_string, '\'' ) === 0) ? substr( $first_string,
                            1, strlen( $first_string ) - 2 ) : $first_string;
            $second_string = (strpos( $second_string, '\'' ) === 0) ? substr( $second_string,
                            1, strlen( $second_string ) - 2 ) : $second_string;

            // replace variables with text representation
            if ( strpos( $first_string, '$' ) === 0 && $has_post ) {
                $variable_name = substr( $first_string, 1 ); // omit dollar sign
                if ( isset( $atts[$variable_name] ) ) {
                    $first_string = get_post_meta( $post->ID,
                            $atts[$variable_name], true );
                } else {
                    $first_string = '';
                }
            }
            if ( strpos( $second_string, '$' ) === 0 && $has_post ) {
                $variable_name = substr( $second_string, 1 );
                if ( isset( $atts[$variable_name] ) ) {
                    $second_string = get_post_meta( $post->ID,
                            $atts[$variable_name], true );
                } else {
                    $second_string = '';
                }
            }

            // don't do string comparison if variables are numbers 
            if ( !(is_numeric( $first_string ) && is_numeric( $second_string )) ) {
                // compare string and return true or false
                $compared_str_result = wpv_compare_strings( $first_string,
                        $second_string, $math_sign );

                if ( $compared_str_result ) {
                    $evaluate = str_replace( $matches[0][$i], '1=1', $evaluate );
                } else {
                    $evaluate = str_replace( $matches[0][$i], '1=0', $evaluate );
                }
            } else {
                $evaluate = str_replace( $matches[1][$i], $first_string, $evaluate );
                $evaluate = str_replace( $matches[5][$i], $second_string, $evaluate );
            }
			$logging_string .= "; After variables II: " . $evaluate;
        }
    }

    // find remaining strings that maybe numeric values.
    // This handles 1='1'
    $strings_count = preg_match_all( '/(\'[^\']*\')/', $evaluate, $matches );
    if ( $strings_count && $strings_count > 0 ) {
        for ( $i = 0; $i < $strings_count; $i++ ) {
            $string = $matches[1][$i];
            // remove single quotes from string literals to get value only
            $string = (strpos( $string, '\'' ) === 0) ? substr( $string, 1, strlen( $string ) - 2 ) : $string;
            if ( is_numeric( $string ) ) {
                $evaluate = str_replace( $matches[1][$i], $string, $evaluate );
				$logging_string .= "; After variables III: " . $evaluate;
            }
        }
    }


    // find all variable placeholders in expression
	if ( $has_post ) {
		$count = preg_match_all( '/\$(\w+)/', $evaluate, $matches );

		$logging_string .= "; Variable placeholders: " . var_export( $matches[1],
						true );

		// replace all variables with their values listed as shortcode parameters
		if ( $count && $count > 0 ) {
			// sort array by length desc, fix str_replace incorrect replacement
			$matches[1] = wpv_sort_matches_by_length( $matches[1] );

			foreach ( $matches[1] as $match ) {
				if ( isset( $atts[$match] ) ) {
					$meta = get_post_meta( $post->ID, $atts[$match], true );
					if ( empty( $meta ) ) {
						$meta = "0";
					}
				} else {
					$meta = "0";
				}
				$evaluate = str_replace( '$' . $match, $meta, $evaluate );
				$logging_string .= "; After variables IV: " . $evaluate;
			}
		}
	}

    $logging_string .= "; End evaluated expression: " . $evaluate;

    $wplogger->log( $logging_string, WPLOG_DEBUG );
    // evaluate the prepared expression using the custom eval script
    $result = wpv_evaluate_expression( $evaluate );
    
	if ( $has_post ) {
		do_action( 'wpv_condition_end', $post );
	}

    // return true, false or error string to the conditional caller
    return $result;
}

function wpv_eval_check_syntax( $code ) {
    return @eval( 'return true;' . $code );
}

/**
 * 
 * Sort matches array by length so evaluate longest variable names first
 * 
 * Otherwise the str_replace would break a field named $f11 if there is another field named $f1
 * 
 * @param array $matches all variable names
 */
function wpv_sort_matches_by_length( $matches ) {
    $length = count( $matches );
    for ( $i = 0; $i < $length; $i++ ) {
        $max = strlen( $matches[$i] );
        $max_index = $i;

        // find the longest variable
        for ( $j = $i + 1; $j < $length; $j++ ) {
            if ( strlen( $matches[$j] ) > $max ) {
                $max = $matches[$j];
                $max_index = $j;
            }
        }

        // swap
        $temp = $matches[$i];
        $matches[$i] = $matches[$max_index];
        $matches[$max_index] = $temp;
    }

    return $matches;

}

/**
 * Boolean function for string comparison
 *
 * @param string $first first string to be compared
 * @param string $second second string for comparison
 * 
 * 
 */
function wpv_compare_strings( $first, $second, $sign ) {
    // get comparison results
    $comparison = strcmp( $first, $second );

    // verify cases 'less than' and 'less than or equal': <, <=
    if ( $comparison < 0 && ($sign == '<' || $sign == '<=') ) {
        return true;
    }

    // verify cases 'greater than' and 'greater than or equal': >, >=
    if ( $comparison > 0 && ($sign == '>' || $sign == '>=') ) {
        return true;
    }

    // verify equal cases: =, <=, >=
    if ( $comparison == 0 && ($sign == '=' || $sign == '<=' || $sign == '>=') ) {
        return true;
    }

    // verify != case
    if ( $comparison != 0 && $sign == '!=' ) {
        return true;
    }

    // or result is incorrect
    return false;
}

/**
 * 
 * Function that prepares the expression and calls eval()
 * Validates the input for a list of whitechars and handles internal errors if any
 * 
 * @param string $expression the expression to be evaluated 
 */
function wpv_evaluate_expression( $expression ){
    //Replace AND, OR, ==
    $expression = strtoupper( $expression );
    $expression = str_replace( "AND", "&&", $expression );
    $expression = str_replace( "OR", "||", $expression );
    $expression = str_replace( "NOT", "!", $expression );
    $expression = str_replace( "=", "==", $expression );
    $expression = str_replace( "<==", "<=", $expression );
    $expression = str_replace( ">==", ">=", $expression );
    $expression = str_replace( "!==", "!=", $expression ); // due to the line above
    // validate against allowed input characters
    $count = preg_match( '/[0-9+-\=\*\/<>&\!\|\s\(\)]+/', $expression, $matches );

    // find out if there is full match for the entire expression	
    if ( $count > 0 ) {
        if ( strlen( $matches[0] ) == strlen( $expression ) ) {
            $valid_eval = wpv_eval_check_syntax( "return $expression;" );
            if ( $valid_eval ) {
                return eval( "return $expression;" );
            } else {
                return __( "Error while parsing the evaluate expression",
                                'wpv-views' );
            }
        } else {
            return __( "Conditional expression includes illegal characters",
                            'wpv-views' );
        }
    } else {
        return __( "Correct conditional expression has not been found",
                        'wpv-views' );
    }

}

/**
 * class WPV_wpcf_switch_post_from_attr_id
 *
 * This class handles the "id" attribute in a wpv-post-xxxxx shortcode
 * and sets the global $id, $post, and $authordata
 *
 * It also handles types. eg [types field='my-field' id='233']
 *
 * id can be a integer to refer directly to a post
 * id can be $parent to refer to the parent
 * id can be $current_page or refer to the current page
 *
 * id can also refer to a related post type
 * eg. for a stay the related post types could be guest and room
 * [types field='my-field' id='$guest']
 * [types field='my-field' id='$room']
 */
class WPV_wpcf_switch_post_from_attr_id
{

    function __construct( $atts ){
        $this->found = false;

        if ( isset( $atts['id'] ) ) {

            global $post, $authordata, $id, $WPV_wpcf_post_relationship;

            $post_id = 0;

            if ( strpos( $atts['id'], '$' ) === 0 ) {
                // Handle the parent if the id is $parent
                if ( $atts['id'] == '$parent' && isset( $post->post_parent ) ) {
                    $post_id = $post->post_parent;
                } else if ( $atts['id'] == '$current_page' ) {
                    if ( is_single() || is_page() ) {
                        global $wp_query;

                        if ( isset( $wp_query->posts[0] ) ) {
                            $current_post = $wp_query->posts[0];
                            $post_id = $current_post->ID;
                        }
                    }
                } else {
                    // See if Views has the variable
                    global $WP_Views;
                    if ( isset( $WP_Views ) ) {
                        $post_id = $WP_Views->get_variable( $atts['id'] . '_id' );
                    }
                    if ( $post_id == 0 ) {
                        // Try the local storage.
                        if ( isset( $WPV_wpcf_post_relationship[$atts['id'] . '_id'] ) ) {
                            $post_id = $WPV_wpcf_post_relationship[$atts['id'] . '_id'];
                        }
                    }
                }
            } else {
                $post_id = intval( $atts['id'] );
            }

            if ( $post_id > 0 ) {

                $this->found = true;

                // save original post 
                $this->post = ( isset( $post ) && ( $post instanceof WP_Post ) ) ? clone $post : null;
                if ( $authordata ) {
                    $this->authordata = clone $authordata;
                } else {
                    $this->authordata = null;
                }
                $this->id = $id;

                // set the global post values
                $id = $post_id;
                $post = get_post( $id );
                $authordata = new WP_User( $post->post_author );
            }
        }

    }

    function __destruct(){
        if ( $this->found ) {
            global $post, $authordata, $id;

            // restore the global post values.
            $post = ( isset( $this->post ) && ( $this->post instanceof WP_Post ) ) ? clone $this->post : null;
            if ( $this->authordata ) {
                $authordata = clone $this->authordata;
            } else {
                $authordata = null;
            }
            $id = $this->id;
        }

    }

}

// Add a filter on the content so that we can record any related posts.
// These can then be used ine id of Types and Views shortcodes
// eg. for a stay we can have
// [types field='my-field' id="$room"] displays my-field from the related room
// [wpv-post-title id="$room"] display the title of the related room

add_filter( 'the_content', 'WPV_wpcf_record_post_relationship_belongs', 0, 1 );

$WPV_wpcf_post_relationship = Array();

function WPV_wpcf_record_post_relationship_belongs( $content ) {

    global $post, $WPV_wpcf_post_relationship;
    static $related = array();

    if ( !empty( $post->ID ) && function_exists( 'wpcf_pr_get_belongs' ) ) {

        if ( !isset( $related[$post->post_type] ) ) {
            $related[$post->post_type] = wpcf_pr_get_belongs( $post->post_type );
        }
        if ( is_array( $related[$post->post_type] ) ) {
            foreach ( $related[$post->post_type] as $post_type => $data ) {
                $related_id = wpcf_pr_post_get_belongs( $post->ID, $post_type );
                if ( $related_id ) {
                    $WPV_wpcf_post_relationship['$' . $post_type . '_id'] = $related_id;
                } else {
					$WPV_wpcf_post_relationship['$' . $post_type . '_id'] = 0;
				}
            }
        }
    }


    return $content;
}

/**
 * Form for Enlimbo calls for wpv-control shortcode calls
 *
 * @param unknown_type $elements
 */
function wpv_form_control( $elements ) {
    static $form = NULL;
    require_once 'classes/control_forms.php';
    if ( is_null( $form ) ) {
        $form = new Enlimbo_Control_Forms();
    }
    return $form->renderElements( $elements );
}

/**
 * Dismiss message.
 * 
 * @param type $message_id
 * @param string $message
 * @param type $class 
 */
function wpv_add_dismiss_message( $message_id, $message,
        $clear_dismissed = false, $class = 'updated' ) {
    $dismissed_messages = get_option( 'wpv-dismissed-messages', array() );
    if ( $clear_dismissed ) {
        if ( isset( $dismissed_messages[$message_id] ) ) {
            unset( $dismissed_messages[$message_id] );
            update_option( 'wpv-dismissed-messages', $dismissed_messages );
        }
    }
    if ( !array_key_exists( $message_id, $dismissed_messages ) ) {
        $message = $message . '<div style="float:right; margin:-15px 0 0 15px;"><a onclick="jQuery(this).parent().parent().fadeOut();jQuery.get(\''
                . admin_url( 'admin-ajax.php?action=wpv_dismiss_message&amp;message_id='
                        . $message_id . '&amp;_wpnonce='
                        . wp_create_nonce( 'dismiss_message' ) ) . '\');return false;"'
                . 'class="button-secondary" href="javascript:void(0);">'
                . __( "Don't show this message again", 'wpv-views' ) . '</a></div>';
        wpv_admin_message_store( $message_id, $message, false );
    }
}

add_action( 'wp_ajax_wpv_dismiss_message', 'wpv_dismiss_message_ajax' );

/**
 * Dismiss message AJAX. 
 */
function wpv_dismiss_message_ajax() {
    if ( isset( $_GET['message_id'] ) && isset( $_GET['_wpnonce'] )
            && wp_verify_nonce( $_GET['_wpnonce'], 'dismiss_message' ) ) {
        $dismissed_messages = get_option( 'wpv-dismissed-messages', array() );
		$dismissed_image_val = isset( $_GET['timestamp'] ) ? $_GET['timestamp'] : 1;
        $dismissed_messages[strval( $_GET['message_id'] )] = $dismissed_image_val;
        update_option( 'wpv-dismissed-messages', $dismissed_messages );
    }
    die( 'ajax' );
}

