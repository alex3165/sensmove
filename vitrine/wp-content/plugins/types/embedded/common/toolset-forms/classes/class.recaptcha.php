<?php
require_once 'class.textfield.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_Recaptcha extends WPToolset_Field_Textfield
{
    private $pubkey = '';
    private $privkey = '';
    private $settings;
    
    public function init() {          
        require_once ( WPTOOLSET_FORMS_ABSPATH."/js/recaptcha-php-1.11/recaptchalib.php");
        
        //$settings_model = CRED_Loader::get('MODEL/Settings');
        //$this->settings = $settings_model->getSettings();        
        $attr = $this->getAttr();
        $this->pubkey = isset($attr['public_key']) ? $attr['public_key'] : '';
        $this->privkey = isset($attr['private_key']) ? $attr['private_key'] : '';

        wp_register_script( 'wpt-cred-recaptcha',
                WPTOOLSET_FORMS_RELPATH . '/js/recaptcha-php-1.11/recaptcha_ajax.js',
                array('wptoolset-forms'), WPTOOLSET_FORMS_VERSION, true );
		wp_enqueue_script( 'wpt-cred-recaptcha' );
    }

    public static function registerStyles() {
    }

    public function enqueueScripts() {
        
    }

    public function enqueueStyles() {        
    }

    public function metaform() {        
        $form = array();
		
	$capture = '';
        if ($this->pubkey || !is_admin()) {
            try {
                $capture = recaptcha_get_html($this->pubkey,null,is_ssl());
            } catch(Exception $e ) {
                //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188424989/comments
                if ( current_user_can( 'manage_options' ) ) {
                    $id_field = $this->getId();
                    $text = 'Caught exception: '.  $e->getMessage();
                    $capture = "<label id=\"lbl_$id_field\" class=\"wpt-form-error\">$text</label><div style=\"clear:both;\"></div>";                    
                }
                //###########################################################################################
            }
        }

        $form[] = array(
            '#type' => 'textfield',
            '#title' => '',
            '#name' => '_recaptcha',
            '#value' => '',
            '#attributes' => array( 'style' => 'display:none;'),
            '#before' => $capture
        );
        
        return $form;
    }
}
