<?php
/*
 * Import Export Class
 */

/**
 * Import Export Class
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Import Export
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Import_Export
{

    /**
     * Meta keys that are used to generate checksum.
     * 
     * @var type 
     */
    var $group_meta_keys = array(
        '_wpcf_conditional_display',
        '_wp_types_group_fields',
        '_wp_types_group_post_types',
        '_wp_types_group_templates',
        '_wp_types_group_terms',
    );

    /**
     * Restricted data - ommited from checksum, applies to all content types.
     * 
     * @var type 
     */
    var $_remove_data_keys = array('id', 'ID', 'menu_icon', 'wpml_action',
        'wpcf-post-type', 'wpcf-tax', 'hash', 'checksum');

    /**
     * Required Group meta keys
     * 
     * @todo Make sure only this is used to fetch required meta_keys
     * @return type
     */
    function get_group_meta_keys() {
        return $this->group_meta_keys;
    }

    /**
     * Fetches required meta ny meta_key
     * 
     * @param type $group_id
     * @return type
     */
    function get_group_checksum_data( $group_id ) {

        $checksum = array();
        $group = wpcf_admin_fields_get_group( $group_id );

        if ( !empty( $group ) ) {
            unset( $group['slug'], $group['name'] );
            $checksum = $group;
            foreach ( $this->group_meta_keys as $meta_key ) {
                $meta = get_post_meta( $group['id'], $meta_key, true );
                if ( !empty( $meta ) ) {
                    $checksum[$meta_key] = $meta;
                }
            }
        }

        return $checksum;
    }

    /**
     * Sort by key recursively.
     * 
     * @param type $data
     * @return type
     */
    function ksort_by_string( $data ) {
        if ( is_array( $data ) ) {
            ksort( $data, SORT_STRING );
            foreach ( $data as $k => $v ) {
                $data[$k] = $this->ksort_by_string( $v );
            }
        }
        return $data;
    }

    /**
     * Generates checksums for defined content types.
     * 
     * @param type $type
     * @param type $item_id
     * @return type
     */
    function generate_checksum( $type, $item_id = null ) {
        switch ( $type ) {
            case 'group':
                $checksum = $this->get_group_checksum_data( $item_id );
                break;

            case 'field':
                $checksum = wpcf_admin_fields_get_field( $item_id );
                ksort( $checksum, SORT_STRING );
                break;

            case 'custom_post_type':
                $checksum = wpcf_get_custom_post_type_settings( $item_id );

                break;

            case 'custom_taxonomy':
                $checksum = wpcf_get_custom_taxonomy_settings( $item_id );
                break;

            default:
                /*
                 * Enable $this->generate_checksum('test');
                 */
                $checksum = $type;
                break;
        }

        // Unset various not wanted data
        foreach ( $this->_remove_data_keys as $key ) {
            if ( isset( $checksum[$key] ) ) {
                unset( $checksum[$key] );
            }

        }
       
        //EMERSON: Remove empty conditional_display for consistent checksum computation with Module manager 1.1 during import
        if (isset($checksum['data']['conditional_display'])) {
        	if (empty($checksum['data']['conditional_display'])) {
        		 
        		unset($checksum['data']['conditional_display']);
        	}
        }

        //EMERSON: Convert to integer value to provide correct checksum computation of this field during Module manager 1.1. import
        if (isset($checksum['data']['repetitive'])) {
        
        	$checksum['data']['repetitive']=(integer)$checksum['data']['repetitive'];
        }

        //EMERSON: Remove __types_id and __types_title to provide correct checksum computation of CPT during Module manager 1.1. import
        if ((isset($checksum['__types_id'])) || (isset($checksum['__types_title']))) {
        
        	unset($checksum['__types_id']);
        	unset($checksum['__types_title']);
        }
        
        //EMERSON: Change custom taxonomies data type to integer to provide correct hashes for MM 1.1.
        if ((isset($checksum['taxonomies'])) && (!(empty($checksum['taxonomies'])))) {
        	
        	foreach ($checksum['taxonomies'] as $tax_module_passed_name=>$tax_module_passed_value) {
        		
        		if ($tax_module_passed_name!='category') {
        			
        			$checksum['taxonomies'][$tax_module_passed_name]=(integer)$checksum['taxonomies'][$tax_module_passed_name];
        			
        		}
        		
        	}

        }       

        return md5( maybe_serialize( $this->ksort_by_string( $checksum ) ) );        
    }

    /**
     * Generates and compares checksums.
     * 
     * @param type $type
     * @param type $item_id
     * @param type $import_checksum Imported checksum
     * @return type
     */
    function checksum( $type, $item_id, $import_checksum ) {
        // Generate checksum of installed content
        $checksum = $this->generate_checksum( $type, $item_id );
        // Compare
        return $checksum == strval( $import_checksum );
    }

    /**
     * Checks if item exists.
     * 
     * @param type $type
     * @param type $item_id
     * @return boolean
     */
    function item_exists( $type, $item_id ) {
        switch ( $type ) {
            case 'group':
                $check = wpcf_admin_fields_get_group( $item_id );
                break;

            case 'field':
                $check = wpcf_admin_fields_get_field( $item_id );
                break;

            case 'custom_post_type':
                $check = wpcf_get_custom_post_type_settings( $item_id );
                break;

            case 'custom_taxonomy':
                $check = wpcf_get_custom_taxonomy_settings( $item_id );
                break;

            default:
                return false;
                break;
        }
        return !empty( $check );
    }

}
