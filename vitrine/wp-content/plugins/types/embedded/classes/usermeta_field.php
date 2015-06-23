<?php
/*
 * Usermeta Field class extends Field.
 */

//require_once WPCF_EMBEDDED_ABSPATH . '/classes/field.php';


class WPCF_Usermeta_Field extends WPCF_Field
{

    /**
     * Set current post and field.
     *
     * @param type $post
     * @param type $cf
     */
    function set( $user_id, $cf ) {

        global $wpcf;

        /*
         *
         * Check if $cf is string
         */
        if ( is_string( $cf ) ) {
            WPCF_Loader::loadInclude( 'fields' );
            $cf = wpcf_admin_fields_get_field( $this->__get_slug_no_prefix( $cf ) );
            if ( empty( $cf ) ) {
                $this->_reset();
                return false;
            }
        }

        $this->currentUID = $user_id;
        $this->ID = $cf['id'];
        $this->cf = $cf;
        $this->slug = wpcf_types_get_meta_prefix( $this->cf ) . $this->cf['slug'];
        $this->meta = $this->_get_meta();
        $this->config = $this->_get_config();
        $this->unique_id = wpcf_unique_id( serialize( (array) $this ) );
        $this->cf['value'] = $this->meta;
        // Debug
        $wpcf->debug->fieds[$this->unique_id] = $this->cf;
        $wpcf->debug->meta[$this->slug][] = $this->meta;

        // Load files
        if ( isset( $this->cf['type'] ) ) {
            $file = WPCF_EMBEDDED_INC_ABSPATH . '/fields/' . $this->cf['type'] . '.php';
            if ( file_exists( $file ) ) {
                include_once $file;
            }
            if ( defined( 'WPCF_INC_ABSPATH' ) ) {
                $file = WPCF_INC_ABSPATH . '/fields/' . $this->cf['type'] . '.php';
                if ( file_exists( $file ) ) {
                    include_once $file;
                }
            }
        }
    }

    /**
     * Save usermeta field.
     *
     *
     * @param type $value
     */
    function usermeta_save( $value = null ) {

        // If $value null, look for submitted data
        if ( is_null( $value ) ) {
            $value = $this->get_submitted_data();
        }
        /*
         *
         *
         * Since Types 1.2
         * We completely rewrite meta.
         * It has no impact on frontend and covers a lot of cases
         * (e.g. user change mode from single to repetitive)
         */

        delete_user_meta( $this->currentUID, $this->slug );


        // Save
        if ( !empty( $value ) || is_numeric( $value ) ) {

            // Trim
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }

            // Apply filters
            $_value = $this->_filter_save_value( $value );
            if ( !empty( $_value ) || is_numeric( $_value ) ) {
                // Save field
                $mid = update_user_meta( $this->currentUID, $this->slug, $_value );
                $this->_action_save( $this->cf, $_value, $mid, $value );
            }
        }
    }

    /**
     * Fetch and sort fields.
     *
     * @global object $wpdb
     *
     */
    function _get_meta() {
        global $wpdb;

        $cache_key = md5( 'usermeta::_get_meta' . $this->currentUID . $this->slug );
        $cache_group = 'types_cache';
        $cached_object = wp_cache_get( $cache_key, $cache_group );

        if ( $this->use_cache ) {
			if ( false != $cached_object && is_array( $cached_object ) && isset( $cached_object[0] ) ) {// WordPress cache
				$r = $cached_object[0];
			} else {
				// Cache all the postmeta for this same user
				$all_usermeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->usermeta} WHERE user_id=%d", $this->currentUID), OBJECT );
				if ( !empty( $all_usermeta ) ) {
					$cache_key_keys = array();
					foreach ( $all_usermeta as $metarow ) {
						$mpid = intval($metarow->user_id);
						$mkey = $metarow->meta_key;
						$cache_key_keys[$mpid . $mkey][] = $metarow;
						$cache_key_looped = md5( 'usermeta::_get_meta' . $mpid . $mkey );
						if ( $mkey == $this->slug ) {
							$r = $metarow;
						}
					}
					foreach ( $cache_key_keys as $single_meta_keys => $single_meta_values ) {
						$cache_key_looped_new = md5( 'usermeta::_get_meta' . $single_meta_keys );
						wp_cache_add( $cache_key_looped_new, $single_meta_values, $cache_group );// WordPress cache
					}
				}
			}
		} else {
			//$r = get_user_meta( $this->currentUID, $this->slug, true);
			// Get straight from DB single value
			$r = $wpdb->get_row(
					$wpdb->prepare(
							"SELECT * FROM $wpdb->usermeta
					WHERE user_id=%d
					AND meta_key=%s",
							$this->currentUID, $this->slug )
			);
			// Cache it
            wp_cache_add( $cache_key, array( $r ), $cache_group );// WordPress cache
        }

        // Sort meta
        $meta = array();
        if ( !empty( $r ) ) {
            $meta = maybe_unserialize( $r->meta_value );
            $this->meta_object = $r;
        } else {
            $meta = null;
            $this->meta_object = new stdClass();
            $this->meta_object->umeta_id = null;
            $this->meta_object->meta_key = null;
            $this->meta_object->meta_value = null;
        }

        /*
         * Secret public object :)
         * Keeps original data
         */
        $this->__meta = $meta;

        /*
         *
         * Apply filters
         * !!! IMPORTANT !!!
         * TODO Make this only place where field meta value is filtered
         */
        $meta = apply_filters( 'wpcf_fields_value_get', $meta, $this );
        $meta = apply_filters( 'wpcf_fields_slug_' . $this->cf['slug'] . '_value_get', $meta, $this );
        $meta = apply_filters( 'wpcf_fields_type_' . $this->cf['type'] . '_value_get', $meta, $this );
        return $meta;
    }

}
