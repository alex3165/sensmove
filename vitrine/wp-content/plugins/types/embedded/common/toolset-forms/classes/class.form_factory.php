<?php
require_once 'abstract.form.php';
require_once 'class.eforms.php';
require_once 'class.field_factory.php';

define( "CLASS_NAME_PREFIX", "WPToolset_Field_" );

/**
 * FormFactory
 * Creation Form Class
 * @author onTheGo System
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.form_factory.php $
 * $LastChangedDate: 2015-04-01 14:15:17 +0000 (Wed, 01 Apr 2015) $
 * $LastChangedRevision: 1125405 $
 * $LastChangedBy: iworks $
 *
 *
 */
class FormFactory extends FormAbstract
{

    private $field_count = 0;
    private $form = array();
    private $nameForm;
    private $theForm;
    protected $_validation, $_conditional, $_repetitive, $_use_bootstrap;

    public function __construct( $nameForm = 'default' )
    {
        if ( !isset( $GLOBALS['formFactories'] ) )
            $GLOBALS['formFactories'] = array();
        $this->nameForm = $nameForm;
        $this->field_count = 0;
        $this->theForm = new Enlimbo_Forms( $nameForm );
        $this->_use_bootstrap = false;

        wp_register_script( 'wptoolset-forms',
            WPTOOLSET_FORMS_RELPATH . '/js/main.js',
            array('jquery', 'underscore', 'suggest'), WPTOOLSET_FORMS_VERSION, false );
        wp_enqueue_script( 'wptoolset-forms' );
		$wptoolset_forms_localization = array(
			'ajaxurl' => admin_url( 'admin-ajax.php', null )
		);
		wp_localize_script( 'wptoolset-forms', 'wptoolset_forms_local', $wptoolset_forms_localization );

        if ( is_admin() ) {
            wp_register_style( 'wptoolset-forms-admin',
                WPTOOLSET_FORMS_RELPATH . '/css/wpt-toolset-backend.css', array(),
                WPTOOLSET_FORMS_VERSION );
            wp_enqueue_style( 'wptoolset-forms-admin' );
        } else {
            /**
             * get cred form settings
             */
            $cred_cred_settings = get_option( 'cred_cred_settings' );
            /**
             * load or not cred.css
             * and check use bootstrap
             */
            $load_cred_css = true;
            if ( is_array($cred_cred_settings) ) {
                if (
                    array_key_exists('dont_load_cred_css', $cred_cred_settings )
                    && $cred_cred_settings['dont_load_cred_css']
                ) {
                    $load_cred_css = false;
                }
                if (
                    array_key_exists( 'use_bootstrap', $cred_cred_settings )
                    && $cred_cred_settings['use_bootstrap']
                ) {
                    $this->_use_bootstrap = true;
                }
            }
            /**
             * register
             */
            if ( $load_cred_css ) {
                wp_register_style(
                    'wptoolset-forms-cred',
                    WPTOOLSET_FORMS_RELPATH . '/css/wpt-toolset-frontend.css',
                    array(),
                    WPTOOLSET_FORMS_VERSION
                );
                wp_enqueue_style( 'wptoolset-forms-cred' );
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see classes/FormAbstract::formNameExists()
     */
    public function formNameExists( &$nameForm ) {
        if ( !in_array( $nameForm, $GLOBALS['formFactories'] ) ) {
            $GLOBALS['formFactories'][] = $nameForm;
            return false;
        } else {
            echo "Form name already exists!";
            return true;
        }
    }

    /**
     * getClassFromType
     * Return the class name from a type
     * @param unknown_type $type
     */
    protected function getClassFromType( $type ) {
        return CLASS_NAME_PREFIX . ucfirst( $type );
    }

    /**
     * (non-PHPdoc)
     * @see classes/FormAbstract::getFieldObject()
     */
    public function getFieldObject( $data, $global_name_field, $value )
    {
        if ( $class = $this->loadFieldClass( $data['type'] ) ) {
            return new $class( $data, $global_name_field, $value );
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see classes/FormAbstract::addFormField()
     */
    public function addFormField( $data ) {
        //check mandatory info in data like type and name field
        $global_name_field = $this->nameForm . '_field_' . $this->field_count;
        $obj = $this->getFieldObject( $data, $global_name_field );
        $this->form[$global_name_field] = $obj->metaform();
        $this->field_count++;
    }

    /**
     * (non-PHPdoc)
     * @see classes/FormAbstract::createForm()
     */
    public function createForm( $nameForm /*= 'default'*/ ) {
        if ( $this->formNameExists( $nameForm ) ) return;
        $this->theForm->autoHandle( $nameForm, $this->form );

        $out = "";
        $out .= '<form method="post" action="" id="' . $nameForm . '">';
        $out .= $this->theForm->renderElements( $this->form );
        //$out .= $this->theForm->renderForm();
        $out .= '</form>';

        return $out;
    }

    /**
     * (non-PHPdoc)
     * @see classes/FormAbstract::displayForm()
     */
    public function displayForm( $nameForm /*= 'default'*/ ) {
        if ( $this->formNameExists( $nameForm ) ) return;
        $myform = $this->theForm;
        $this->theForm->autoHandle( $nameForm, $this->form );

        echo '<form method="post" action="" id="' . $nameForm . '">';
        echo $this->theForm->renderForm();
        echo '</form>';
    }

    /**
     * metaform
     * @param type $name
     * @param type $type
     * @param type $config
     * @param type $global_name_field
     * @param type $value
     * @return type
     */
    public function metaform( $config, $global_name_field, $value )
    {
        /**
         * add bootstrap config to every field
         */
        $config['use_bootstrap'] = $this->theForm->form_settings['use_bootstrap'];
        $config['has_media_button'] = $this->theForm->form_settings['has_media_button'];
        /**
         * WMPL configuration
         */
        $config['wpml_action'] = $this->get_wpml_action($config['id']);

        $htmlArray = array();
        $_gnf = $global_name_field;
        $_cfg = $config;
        if ( empty( $value ) ) $value = array(null);
        elseif ( !is_array( $value ) ) $value = array($value);
        $count = 0;
               
        //Fix if i get skype i receive skype i have 2 elements array in $value !!
        if ($config['type']=='skype') {
            if (isset($value['style'])) unset($value['style']);
            if (isset($value['button_style'])) unset($value['button_style']);
        }
        
        foreach ( $value as $val ) {            
            if ( !empty( $config['repetitive'] ) ) {
                $_gnf = $_cfg['name'] = "{$global_name_field}[{$count}]";
            }     
            //CHECKGEN			
            if ( isset($_cfg['validation']) && 
                 is_array($_cfg['validation'])  && 
                 count($_cfg['validation']) > 0 && 
                 !is_admin() && $_SERVER['REQUEST_METHOD'] == 'POST' &&
                 isset( $_GET['_tt'] ) && 
                 !isset( $_GET['_success'] ) && 
                 !isset( $_GET['_success_message'] )  )
            {
                $_cfg['validate'] = 1;	
            }
            if ( !is_wp_error( $field = $this->loadField( $_cfg, $_gnf, $val ) ) ) {
                $form = $field->metaform();
                // Set $config['validate'] to trigger PHP validation
                // when rendering metaform
                if ( !empty( $_cfg['validate'] ) && 
                     is_wp_error( $valid = $this->validateField( $field, $val ) ) ) {
                    $key = key( $form );
                    $error = $valid->get_error_data();
                    if ( is_array( $error ) ) {
                        $error = array_shift( $error );
                    }
                    $form[$key]['#error'] = $error;
                }

                if ( isset( $_cfg['validation_error'] ) ) {
                    $key = key( $form );
                    $form[$key]['#error'] = $_cfg['validation_error'];
                }
                $this->form[$global_name_field] = $form;
                $this->field_count++;
                $htmlArray[] = $this->theForm->renderElements( $form );                
                if ( empty( $config['repetitive'] ) ) break;
                $count++;
            } else {
                if ( current_user_can('manage_options') ) {
                    $htmlArray[] = sprintf(
                        '<div id="message" class="error"><p>%s</p><p>%s</p></div>',
                        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/196628627/comments#310360880
                        //changed render to rendering
                        sprintf(
                            __('There is a problem rendering field <strong>%s (%s)</strong>.', 'wpv-views'),
                            $_cfg['title'],
                            $_cfg['type']
                        ),
                        $field->get_error_message()
                    );
                }
            }            
        }
        if ( !empty( $htmlArray ) && isset($config['repetitive']) && $config['repetitive'] ) {
            $_gnf = $_cfg['name'] = "{$global_name_field}[%%{$count}%%]";
            if ( !is_wp_error( $field = $this->loadField( $_cfg, $_gnf, null ) ) ) {
                $tpl = $this->_tplItem( $config,
                        $this->theForm->renderElements( $field->metaform() ) );
                $this->_repetitive()->add( $config, $tpl );
            }
        }

        return !empty( $htmlArray ) ? $this->_tpl( $config, $htmlArray ) : '';
    }

    /**
     *
     * @staticvar array $loaded
     * @param type $config
     * @param string $global_name_field
     * @param type $value
     * @return \WP_Error|\class
     */
    public function loadField( $config, $global_name_field, $value ){
        global $wp_version;
        static $loaded = array();
        $type = $config['type'];
        $global_name_field = $this->nameForm . '_field_' . $this->field_count;
        $field = $this->getFieldObject( $config, $global_name_field, $value );

        if ( is_null( $field ) ) {
            return new WP_Error( 'wptoolset_forms', 'wrong field type' );
        }

        $settings = $field->getSettings();
        if ( isset( $settings['min_wp_version'] ) && version_compare( $wp_version,
                        $settings['min_wp_version'], '<' ) ) {
            return new WP_Error( 'wptoolset_forms', 'Higher WP version required' );
        }

        $this->_setGlobalField( $field );

        // Load/enqueue scripts
        if ( !isset( $loaded[$type] ) ) {
            $loaded[$type] = 1;
            // These should be performed only once
            $field->registerScripts();
            $field->registerStyles();
            $field->enqueueScripts();
            $field->enqueueStyles();
            $field->addFilters();
            $field->addActions();
        }
        $this->_checkValidation( $config );
        $this->_checkConditional( $config );

        return $field;
    }

    protected function _checkValidation( $config ) {
        if ( isset( $config['validation'] ) && is_null( $this->_validation ) ) {
            require_once 'class.validation.php';
            $this->_validation = new WPToolset_Forms_Validation( $this->nameForm );
        }
    }

    protected function _checkConditional( $config ) {
        if ( !empty( $config['conditional'] ) ) {
            $this->getConditionalClass()->add( $config );
        }
    }

    public function addConditional( $config ) {
        $this->getConditionalClass()->add( $config );
    }

    public function getConditionalClass(){
        if ( is_null( $this->_conditional ) ) {
            require_once 'class.conditional.php';
            $this->_conditional = new WPToolset_Forms_Conditional( $this->nameForm );
        }
        return $this->_conditional;
    }

    protected function _repetitive() {
        if ( is_null( $this->_repetitive ) ) {
            require_once 'class.repetitive.php';
            $this->_repetitive = new WPToolset_Forms_Repetitive();
        }
        return $this->_repetitive;
    }

    protected function _tpl( $cfg, $html ) {
        ob_start();
        include WPTOOLSET_FORMS_ABSPATH . '/templates/metaform.php';
        $o = ob_get_contents();
        ob_get_clean();
        return $o;
    }

    protected function _tplItem( $cfg, $out ) {
        ob_start();
        include WPTOOLSET_FORMS_ABSPATH . '/templates/metaform-item.php';
        $o = ob_get_contents();
        ob_get_clean();
        return $o;
    }

    static $_validate_flag = array();
    public function validateField( $field, $value ) {
        if ( is_array( $field ) ) {
            $field = $this->loadField( $field, $field['name'], $value );
        }
        
        /**
         * Temporary fixing validation for checkbox/radios/skype because _cakeValidation is not working for thats
         * https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/186243370/comments
         */
        if (!is_admin()) {            
            //I receive wpcf-id and wpcf[id] for the same type
            if ( $field->getId()==$field->getName() &&
                ($field->getType()=='checkbox' ||
                 $field->getType()=='radios' ||
                    $field->getType()=='skype' )               
                ) 
            {   
                //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188604193/comments
                //added sanitize_text_field for sucuri warning php.backdoor.eval_POST.010
                $field_value = isset($_POST[$field->getName()])?sanitize_text_field($_POST[$field->getName()]):"";
                if ($field->getType()=='skype') {
                    $field_value = isset($_POST[$field->getName()]['skypename'])?sanitize_text_field($_POST[$field->getName()]['skypename']):"";
                }
                //##########################################################################################
                
                $_tmp = $field->getValidationData();
                if (isset($_tmp['required']) &&
                    !isset($field_value)) 
                {
                    $mess = $field->getTitle().' Field is required';
                    return new WP_Error( 'wptoolset_forms', $mess,
                                array($field->getTitle().' Field is required') );
                }
            }     
        }
        //****************************************************************

        if ( !is_wp_error( $field ) ) {            
            if ( $field->getValidationData() ) {
                return $this->_validation->validateField( $field );
            }
            return true;
        }
        return new WP_Error( 'wptoolset_forms', 'Field do not exist',
                array('Field do not exist') );
    }

    protected function _setGlobalField( $field ) {
        global $wptoolset_field;
        $wptoolset_field = $field;
    }

    public function __toString() {
        return join( "\n", $this->elements );
    }

    public function loadFieldClass( $type ) {
        $type = strtolower( $type );
        $class = $this->getClassFromType( $type );

        /**
         * try to load custom class
         */
        $loader = $class.'_loader';
        if ( function_exists($loader) ) {
            $loader();
        }

        if ( !class_exists( $class ) ) {
            $file = WPTOOLSET_FORMS_ABSPATH . "/classes/class.{$type}.php";
            if ( file_exists( $file ) ) {
                require_once $file;
                return $class;
            } else {
                // third party fields array $type => __FILE__
                $third_party_fields = apply_filters( 'wptoolset_registered_fields',
                        array() );
                if ( isset( $third_party_fields[$type] ) && file_exists( $third_party_fields[$type] ) ) {
                    require_once $third_party_fields[$type];
                    return $class;
                }
            }
        }
        return class_exists( $class ) ? $class : false;
    }

    private function get_wpml_action($id)
    {
        global $iclTranslationManagement;
        if (
            is_object($iclTranslationManagement)
            && 'TranslationManagement' == get_class($iclTranslationManagement)
            && isset($iclTranslationManagement->settings['custom_fields_translation'][$id])
        ) {
            return $iclTranslationManagement->settings['custom_fields_translation'][$id];
        }
        return 0;
    }
}
