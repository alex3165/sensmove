<?php
/*
 * Conditional class.
 */

/**
 * Conditional class.
 */
class WPCF_Helper_Ajax
{

    /**
     * Process AJAX conditional verify.
     * 
     * @global type $wpcf
     * @param type $data
     * @return boolean|string
     */
    public static function conditionalVerify( $data ) {

        WPCF_Loader::loadInclude( 'fields' );
        WPCF_Loader::loadInclude( 'fields-post' );
        WPCF_Loader::loadInclude( 'conditional-display' );

        global $wpcf;
        $js_execute = '';
        $_flag_relationship = false;
        /*
         * 
         * Determine post.
         */
        if ( empty( $data['wpcf'] ) && !empty( $data['wpcf_post_relationship'] ) ) {
            /*
             * Relationship case
             */
            $_temp = $data['wpcf_post_relationship'];
            $parent_id = key( $_temp );
            $_data = array_shift( $_temp );
            $post_id = key( $_data );
            $post = get_post( $post_id );
            $posted_fields = $_data[$post_id];
            $_flag_relationship = true;
            /*
             * 
             * Regular submission
             */
        } else {
            if ( isset( $data['wpcf_main_post_id'] ) ) {
                $post_id = intval( $data['wpcf_main_post_id'] );
                $post = get_post( $post_id );
            }
        }

        // No post
        if ( empty( $post->ID ) ) {
            return false;
        }

        // Get Groups (Fields) for current post
        $groups = wpcf_admin_post_get_post_groups_fields( $post );

        $_processed = array();
        foreach ( $groups as $group ) {
            if ( !empty( $group['fields'] ) ) {
                foreach ( $group['fields'] as $field_id => $field ) {

                    // Check if already processed
                    if ( isset( $_processed[$field_id] ) ) {
                        continue;
                    }

                    if ( $wpcf->conditional->is_conditional( $field_id ) ) {
                        if ( $_flag_relationship ) {
                            // Process only submitted fields
                            if ( !isset( $posted_fields[WPCF_META_PREFIX . $field_id] ) ) {
                                continue;
                            }
                            $wpcf->conditional->set( $post, $field_id );
                            $wpcf->conditional->context = 'relationship';
                            $_relationship_name = false;
                            // Set name and other values processed by hooks
                            $parent = get_post( $parent_id );
                            if ( !empty( $parent->ID ) ) {
                                $wpcf->relationship->set( $parent, $post );
                                $wpcf->relationship->cf->set( $post, $field_id );
                                $_child = $wpcf->relationship->get_child();
                                $_child->form->cf->set( $post, $field_id );
                                $_relationship_name = $_child->form->alter_form_name( 'wpcf[' . $wpcf->conditional->cf['id'] . ']' );
                            }
                            if ( !$_relationship_name ) {
                                continue;
                            }
                            /*
                             * BREAKPOINT
                             * Adds filtering regular evaluation (not wpv_conditional)
                             */
                            add_filter( 'types_field_get_submitted_data',
                                    'wpcf_relationship_ajax_data_filter', 10, 2 );

                            $name = $_relationship_name;
                        } else {
                            $wpcf->conditional->set( $post, $field_id );
                            $name = 'wpcf[' . $wpcf->conditional->cf['id'] . ']';
                        }

                        // Evaluate
                        $passed = $wpcf->conditional->evaluate();

                        if ( $passed ) {
                            $js_execute .= 'jQuery(\'[name^="' . $name . '"]\').parents(\'.'
                                    . 'wpcf-conditional' . '\').show().removeClass(\''
                                    . 'wpcf-conditional' . '-failed\').addClass(\''
                                    . 'wpcf-conditional' . '-passed\');' . " ";
                            $js_execute .= 'jQuery(\'[name^="' . $name
                                    . '"]\').parents(\'.wpcf-repetitive-wrapper\').show();';
                        } else {
                            $js_execute .= 'jQuery(\'[name^="' . $name
                                    . '"]\').parents(\'.wpcf-repetitive-wrapper\').hide();';
                            $js_execute .= 'jQuery(\'[name^="' . $name . '"]\').parents(\'.'
                                    . 'wpcf-conditional' . '\').hide().addClass(\''
                                    . 'wpcf-conditional' . '-failed\').removeClass(\''
                                    . 'wpcf-conditional' . '-passed\');' . " ";
                        }
                    }
                    $_processed[$field_id] = true;
                }
            }
        }
        return $js_execute;
    }

}