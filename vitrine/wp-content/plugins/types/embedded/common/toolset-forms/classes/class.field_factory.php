<?php

/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.field_factory.php $
 * $LastChangedDate: 2015-03-02 10:49:00 +0000 (Mon, 02 Mar 2015) $
 * $LastChangedRevision: 1103173 $
 * $LastChangedBy: iworks $
 *
 */

require 'abstract.field.php';

abstract class FieldFactory extends FieldAbstract
{
    protected $_nameField, $_data, $_value, $_use_bootstrap;

    function __construct($data, $global_name_field, $value)
    {
        $this->_nameField = $global_name_field;
        $this->_data = $data;
        $this->_value = $value;

        $this->init();
    }

    public function init()
    {
        $cred_cred_settings = get_option( 'cred_cred_settings' );
        $this->_use_bootstrap = is_array($cred_cred_settings) && array_key_exists( 'use_bootstrap', $cred_cred_settings ) && $cred_cred_settings['use_bootstrap'];
        $this->set_placeholder_as_attribute();
    }

    public function set_placeholder_as_attribute()
    {
        if ( !isset($this->_data['attribute']) ) {
            $this->_data['attribute'] = array();
        }
        if ( isset($this->_data['placeholder']) && !empty($this->_data['placeholder'])) {
            $this->_data['attribute']['placeholder'] = htmlentities(stripcslashes($this->_data['placeholder']));
        }
    }

    public function set_metaform($metaform)
    {
        $this->_metaform = $metaform;
    }

    public function get_metaform()
    {
        return $this->_metaform;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function set_data($data)
    {
        $this->data = $data;
    }

    public function set_nameField($nameField)
    {
        $this->_nameField = $nameField;
    }

    public function get_nameField()
    {
        return $this->_nameField;
    }

    public function getId()
    {
        return $this->_data['id'];
    }

    public function getType()
    {
        return $this->_data['type'];
    }

    public function getValue()
    {
        global $post;
        $value = $this->_value;
        $value = apply_filters( 'wpcf_fields_value_get', $value, $post );
        if ( array_key_exists('slug', $this->_data ) ) {
            $value = apply_filters( 'wpcf_fields_slug_' . $this->_data['slug'] . '_value_get', $value, $post );
        }
        $value = apply_filters( 'wpcf_fields_type_' . $this->_data['type'] . '_value_get', $value, $post );
        return $value;
    }

    public function getTitle($_title = false)
    {
        if ( $_title && empty($this->_data['title']) && isset($this->_data['_title']) ) {
            return $this->_data['_title'];
        }
        return $this->_data['title'];
    }

    public function getDescription()
    {
        return wpautop( $this->_data['description'] );
    }

    public function getName()
    {
        return $this->_data['name'];
    }

    public function getData()
    {
        return $this->_data;
    }

    public function getValidationData()
    {
        return !empty( $this->_data['validation'] ) ? $this->_data['validation'] : array();
    }

    public function setValidationData($validation)
    {
        $this->_data['validation'] = $validation;
    }

    public function getSettings()
    {
        return isset( $this->_settings ) ? $this->_settings : array();
    }

    public function isRepetitive()
    {
        return (bool)$this->_data['repetitive'];
    }

    public function getAttr() {
        if ( array_key_exists( 'attribute', $this->_data ) ) {
            return $this->_data['attribute'];
        }
        return array();
    }

    public function getWPMLAction()
    {
        if ( array_key_exists( 'wpml_action', $this->_data ) ) {
            return $this->_data['wpml_action'];
        }
        return 0;
    }

    public static function registerScripts() {}
    public static function registerStyles() {}
    public static function addFilters() {}
    public static function addActions() {}

    public function enqueueScripts() {}
    public function enqueueStyles() {}
    public function metaform() {}
    public function editform() {}
    public function mediaEditor() {}
}
