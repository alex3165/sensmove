<?php
/**
 *
 * Types Marketing Class
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/classes/class.wpcf-marketing-messages.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

include_once dirname(__FILE__).'/class.wpcf-marketing.php';

/**
 * Types Marketing Class
 *
 * @since Types 1.6.5
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Help
 * @author marcin <marcin.p@icanlocalize.com>
 */
class WPCF_Types_Marketing_Messages extends WPCF_Types_Marketing
{
    private $state;

    public function __construct()
    {
        parent::__construct();
        add_action('admin_enqueue_scripts', array($this, 'register_scripts'), 1);
        add_action('wp_ajax_toolset_messages', array( $this, 'update_toolset_messages' ));
        add_action('admin_notices', array($this, 'add_message_after_activate'));
        $this->set_state();
    }

    private function set_state()
    {
        $this->state = '0' == get_option($this->option_disable, '0')? 'endabled':'disabled';

        if ('disabled' == $this->state) {
            return;
        }
        if ( self::check_register() ) {
            $this->state = 'disabled';
        }
    }

    public static function check_register()
    {
        if(!function_exists('WP_Installer')){
            return false;
        }
        $repos = array(
            'toolset'
        );
        foreach( $repos as $repository_id ) {
            $key = WP_Installer()->repository_has_subscription($repository_id);
            if ( empty($key) ) {
                continue;
            }
            return true;
        }
        return false;
    }

    private function get_data()
    {
        /**
         * check kind
         */
        $kind = $this->get_kind();
        /**
         * get default
         */
        if ( empty($kind) ) {
            $kind = $this->get_default_kind();
        }
        /**
         * check exists?
         */
        if ( empty($kind) || !array_key_exists($kind, $this->adverts ) ) {
            return;
        }

        /**
         * check type
         */
        $type = $this->get_page_type();
        if ( empty($type) || !array_key_exists($type, $this->adverts[$kind]) ) {
            return;
        }
        if ( !is_array($this->adverts[$kind][$type]) ) {
            return;
        }
        /**
         * get number
         */
        $number = intval(get_user_option('types-modal'));
        if ( !isset($this->adverts[$kind][$type][$number]) ) {
            if ( empty($this->adverts[$kind][$type]) ) {
                return;
            }
                $number = 0;
        }

        $data = $this->adverts[$kind][$type][$number];
        $data['number'] = $number;
        $data['count'] = count($this->adverts[$kind][$type]);
        return $data;
    }

    private function replace_placeholders($text)
    {
        $type = $this->get_page_type();
        switch($type) {
        case 'cpt':
            if (
                is_array($_GET)
                && array_key_exists('wpcf-post-type', $_GET)
            ) {
                $types = get_option('wpcf-custom-types', array());
				$candidate_key = sanitize_text_field( $_GET['wpcf-post-type'] );
                if ( array_key_exists($candidate_key, $types ) ) {
                    $text = preg_replace( '/PPP/', $types[$candidate_key]['labels']['name'], $text);
                }
            }
            break;

        case 'taxonomy':
            if (
                is_array($_GET)
                && array_key_exists('wpcf-tax', $_GET)
            ) {
                $taxonomies = get_option('wpcf-custom-taxonomies', array());
				$candidate_key = sanitize_text_field( $_GET['wpcf-tax'] );
                if ( array_key_exists($candidate_key, $taxonomies) ) {
                    $text = preg_replace( '/TTT/', $taxonomies[$candidate_key]['labels']['name'], $text);
                    if ( array_key_exists('supports', $taxonomies[$candidate_key]) ) {
                        $types = get_option('wpcf-custom-types', array());
                        $post_type = array_keys($taxonomies[$candidate_key]['supports']);
                        if ( !empty($post_type) ) {
                            $post_type = $post_type[array_rand($post_type)];
                            $post_type = get_post_type_object($post_type);
                            if ( $post_type ) {
                                $text = preg_replace( '/PPP/', $post_type->labels->name, $text);
                            }
                        }
                    }
                }
            }
            break;
        }
        /**
         * defaults
         */
        $text = preg_replace( '/PPP/', __('Posts'), $text);
        $text = preg_replace( '/TTT/', __('Tags'), $text);

        return $text;
    }

    public function register_scripts()
    {

        $data = $this->get_data();
        if ( empty($data) ) {
            return;
        }
        /**
         * common question
         */
        $data['message'] = __('Saving your changes', 'wpcf');
        $data['spinner'] = apply_filters('wpcf_marketing_message', admin_url('/images/spinner.gif'), $data, 'spinner');
        $data['question'] = apply_filters('wpcf_marketing_message', __('Did you know?', 'wcpf'), $data, 'question');
        /**
         * random image & class
         */
        $image = isset($data['image'])? $data['image']:'views';
        $src = sprintf(
            '%s/marketing/assets/images/%s.png',
            WPCF_RELPATH,
            $image
        );
        $data['image'] = apply_filters('wpcf_marketing_message', $src, $data, 'image');
        $data['class'] = apply_filters('wpcf_marketing_message', $image, $data, 'class');
        /**
         * values depend on type
         */
        foreach ( array('header', 'description') as $key ) {
            $value = '';
            if ( isset($data[$key]) && $data[$key] ) {
                $value = $this->replace_placeholders($data[$key]);
            }
            $data[$key] = apply_filters('wpcf_marketing_message', $value, $data, $key );
            $data['state'] = $this->state;
        }
        wp_register_script( 'types-modal', WPCF_EMBEDDED_RES_RELPATH.'/js/modal.js', array('jquery'), WPCF_VERSION, true);
        wp_localize_script( 'types-modal', 'types_modal', $data);
        wp_enqueue_script('types-modal');
    }

    public function update_message($message = false)
    {
        if (empty($message)) {
            return;
        }
        echo '<div class="updated"><p>', $message, '</p></div>';
    }

    public function update_options()
    {
        if(!isset($_POST['marketing'])) {
            return;
        }
        if ( !wp_verify_nonce($_POST['marketing'], 'update')) {
            return;
        }
        if (
            array_key_exists($this->option_name, $_POST)
            && array_key_exists($_POST[$this->option_name], $this->options)
        ) {
			// @todo we need to sanitize $_POST[$this->option_name]: is it a string, an array or what?
            if ( !add_option($this->option_name, $_POST[$this->option_name], '', 'no') ) {
                update_option($this->option_name, $_POST[$this->option_name]);
            }
        }
        $this->set_state();
    }

    public function delete_option_kind()
    {
        delete_option($this->option_name);
    }

    public function get_kind_list()
    {
        $type = get_option($this->option_name);
        $content = '<ul class="marketing-kind-list">';
        foreach( $this->options as $key => $one ) {
            $content .= '<li>';
            $content .= sprintf(
                '<input type="radio" name="%s" value="%s" id="getting_started_%s" %s/>',
                $this->get_option_name(),
                $key,
                $key,
                $type == $key? ' checked="checked" ':''
            );
            $content .= sprintf(
                '<label for="getting_started_%s"> <strong>%s</strong>%s%s</label>',
                $key,
                $one['title'],
                array_key_exists('description', $one)? ' | ':'',
                array_key_exists('description', $one)? $one['description']:''
            );
            $content .= '</li>';
        }
        $content .= '</ul>';
        return $content;
    }

    public function kind_list()
    {
        echo $this->get_kind_list();
    }

    public function show_top($update = true)
    {
        $data = $this->get_data();
        if ( empty($data) ) {
            return false;
        }
        $content = '<div class="icon-toolset-logo icon-toolset">';
        $content .= sprintf('<p class="wpcf-notif-header">%s</p>', $update? __('Updated!', 'wpcf'):__('Created!', 'wpcf') );
        if ( 'endabled' == $this->state) {
            $content .= '<p class="wpcf-notif-description">';
            if ( isset($data['link']) ) {
                $content .= sprintf(
                    '<a href="%s">%s</a>',
                    $this->add_ga_campain($data['link'], 'save-updated'),
                    $data['description']
                );
            } else {
                $content .= $data['description'];
            }
            $content .= '</p>';
        }
        $content .= '</div>';

        $content = $this->replace_placeholders($content);

        /**
         * after all set up types-modal for next time
         */
        $key = rand( 0, $data['count']-1 );
        $user_id = get_current_user_id();
        update_user_option($user_id, 'types-modal', $key);

        return $content;
    }

    public function get_content()
    {
        if ( $url = $this->get_kind_url() ) {
            include_once dirname(__FILE__).'/class.wpcf-marketing-tutorial.php';
            $tutorial = new WPCF_Types_Marketing_Tutorial();
            return $tutorial->get_content('kind');
        }
        return;
    }

    // @todo nonce ?
    // @todo auth ?
    public function update_toolset_messages()
    {
        $settings = wpcf_get_settings();
        if (
            array_key_exists('value', $_POST)
            && 'checked' == $_POST['value']
        ) {
            if ( !add_option($this->option_disable, '1', '', 'no') ) {
                update_option($this->option_disable, '1');
            }
            $settings['toolset_messages'] = true;
        } else {
            delete_option($this->option_disable);
            $settings['toolset_messages'] = false;
        }
        update_option('wpcf_settings', $settings);
        echo '<div class="updated"><p>';
        _e('Toolset Messages state saved!', 'wpcf');
        echo '</p></div>';
        die;
    }

    public function add_message_after_activate()
    {
        if ( !isset($_GET['activate']) ) {
            return;
        }
        if ( is_multisite() ) {
            return;
        }
        if ( 'show' != get_option('types_show_on_activate') ) {
            return;
        }
        wp_enqueue_style('onthego-admin-styles');
        wp_enqueue_style('wpcf-css-embedded');
        $data = array(
            'header' => __('Need help with <em>Types</em>?', 'wpcf'),
            'text' => __('Types plugin includes a lot of options. Tell us what kind of site you are building and we\'ll show you how to use Types in the best way.', 'wpcf'),
            'button_primary_url' => add_query_arg( 'page', basename(dirname(dirname(__FILE__))).'/marketing/getting-started/index.php', admin_url('admin.php') ),
            'button_primary_text' => __('Get Started', 'wpcf'),
            'button_dismiss_url' => '',
            'button_dismiss_text' =>  __('Dismiss', 'wpcf'),
        );
        wp_localize_script('marketing-getting-started', 'types_activate', $data);
        wp_enqueue_script('marketing-getting-started');
        update_option('types_show_on_activate', 'hide');
    }

}

