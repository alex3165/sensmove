<?php
/*
 * Settings form
 */

/**
 * Settings form.
 * 
 * @return string 
 */
function wpcf_admin_image_settings_form() {
    $settings = wpcf_get_settings();
    $form = array();
    $form['#form']['callback'] = 'wpcf_admin_image_settings_form_submit';
    $form['images'] = array(
        '#id' => 'add_resized_images_to_library',
        '#name' => 'wpcf_settings[add_resized_images_to_library]',
        '#type' => 'checkbox',
        '#title' => __('Add resized images to the media library', 'wpcf'),
        '#description' => __('Types will automatically add the resized images as attachments to the media library. Choose this to automatically upload resized images to a CDN.',
                'wpcf'),
        '#inline' => true,
        '#default_value' => !empty($settings['add_resized_images_to_library']),
    );
    $form['images_remote'] = array(
        '#id' => 'images_remote',
        '#name' => 'wpcf_settings[images_remote]',
        '#type' => 'checkbox',
        '#title' => __('Allow resizing of remote images', 'wpcf'),
//        '#description' => __('Types will automatically add the resized images as attachments to the media library. Choose this to automatically upload resized images to a CDN.',
//                'wpcf'),
        '#inline' => true,
        '#default_value' => !empty($settings['images_remote']),
        '#after' => '<br />',
    );
    $form['images_remote_clear'] = array(
        '#id' => 'images_remote_cache_time',
        '#name' => 'wpcf_settings[images_remote_cache_time]',
        '#type' => 'select',
        '#pattern' => __('Invalidate cached images that are more than <ELEMENT> hours old.',
                'wpcf') . '<br />',
        '#options' => array(
            __('Never', 'wpcf') => '0',
            '24' => '24',
            '36' => '36',
            '48' => '48',
            '72' => '72',
        ),
//        '#description' => __('Types will automatically add the resized images as attachments to the media library. Choose this to automatically upload resized images to a CDN.',
//                'wpcf'),
        '#inline' => true,
        '#default_value' => $settings['images_remote_cache_time'],
    );
    $form['clear_images_cache'] = array(
        '#type' => 'submit',
        '#name' => 'clear-cache-images',
        '#id' => 'clear-cache-images',
        '#attributes' => array('id' => 'clear-cache-images','class' => 'button-secondary'),
        '#value' => __('Clear Cached Images', 'wpcf'),
        '#after' => '&nbsp;',
        '#inline' => true,
    );
    $form['clear_images_cache_outdated'] = array(
        '#id' => 'clear-cache-images-outdated',
        '#type' => 'submit',
        '#name' => 'clear-cache-images-outdated',
        '#attributes' => array('id' => 'clear-cache-images-outdated','class' => 'button-secondary'),
        '#value' => __('Clear Outdated Cached Images', 'wpcf'),
//        '#suffix' => '&nbsp;' . __('Clear the image cache now.', 'wpcf'),
        '#after' => '<br /><br />',
        '#inline' => true,
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#attributes' => array('id'=>'image-settings-submit','class' => 'button-primary'),
        '#value' => __('Save Changes'),
    );
    return $form;
}

function wpcf_admin_general_settings_form() {
    $settings = wpcf_get_settings();
    $form = array();
    $form['#form']['callback'] = 'wpcf_admin_general_settings_form_submit';
    
    if (function_exists('icl_register_string')) {
        $form['register_translations_on_import'] = array(
            '#id' => 'register_translations_on_import',
            '#name' => 'wpcf_settings[register_translations_on_import]',
            '#type' => 'checkbox',
            '#title' => __("When importing, add texts to WPML's String Translation table",
                    'wpcf'),
            '#inline' => true,
            '#default_value' => !empty($settings['register_translations_on_import']),
            '#after' => '<br />',
        );
    }
    // TODO Remove
//    $show_credits = get_option('wpcf_footer_credit', array());
//    $form['credits'] = array(
//        '#id' => 'show_credits',
//        '#name' => 'show_credits',
//        '#type' => 'checkbox',
//        '#title' => __('Display Types footer credits', 'wpcf'),
//        '#description' => __("Show your support to Types, by telling people that you're using it. We'll add a small footer that tells just about Types.",
//                'wpcf'),
//        '#inline' => true,
//        '#default_value' => !empty($show_credits['active']),
//    );
    $form['help-box'] = array(
        '#id' => 'help_box',
        '#name' => 'wpcf_settings[help_box]',
        '#type' => 'radios',
        '#title' => __('Help Box', 'wpcf'),
        '#options' => array(
            'all' => array(
                '#value' => 'all',
                '#title' => __("Show help box on all custom post editing screens",
                        'wpcf')
            ),
            'by_types' => array(
                '#value' => 'by_types',
                '#title' => __("Show help box only on custom post types that were created by Types",
                        'wpcf')
            ),
            'no' => array(
                '#value' => 'no',
                '#title' => __("Don't show help box on any custom post type editing screen",
                        'wpcf')
            ),
        ),
        '#inline' => false,
        '#default_value' => $settings['help_box'],
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#attributes' => array('id'=>'general-settings-submit','class' => 'button-primary'),
        '#value' => __('Save Changes'),
    );
    return $form;
}

function wpcf_admin_toolset_messages_form()
{
    $settings = wpcf_get_settings();
    $form = array();

    $form['help-box'] = array(
        '#id' => 'help_box',
        '#name' => 'wpcf_settings[toolset_messages]',
        '#type' => 'checkbox',
        '#title' => __('Disable all messages about other Toolset components', 'wpcf'),
        '#default_value' => isset($settings['toolset_messages'])? $settings['toolset_messages']:0,
    );
    $form['spinner'] = array(
        '#type' => 'markup',
        '#markup' => '<span class="spinner" style="float:left;"></span>',
    );
    return $form;
}
/**
 * Saves settings.
 * 
 * @param type $form 
 */
function wpcf_admin_image_settings_form_submit($form) {
    if (isset($_POST['clear-cache-images']) || isset($_POST['clear-cache-images-outdated'])) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/image.php';
        $cache_dir = wpcf_fields_image_get_cache_directory(true);
        if (is_wp_error($cache_dir)) {
            wpcf_admin_message_store($cache_dir->get_error_message());
        } else {
            if (isset($_POST['clear-cache-images'])) {
                wpcf_fields_image_clear_cache($cache_dir, 'all');
            } else {
                wpcf_fields_image_clear_cache($cache_dir);
            }
            wpcf_admin_message_store(__('Images cache cleared', 'wpcf'));
        }
        return true;
    }
    $settings = wpcf_get_settings();
    $data = $_POST['wpcf_settings'];
    foreach (array('add_resized_images_to_library','images_remote','images_remote_cache_time') as $setting) {
        if (!isset($data[$setting])) {
            $settings[$setting] = 0;
        } else {
			// @todo Add sanitization here
            $settings[$setting] = $data[$setting];
        }
    }
    update_option('wpcf_settings', $settings);

    wpcf_admin_message_store(__('Settings saved', 'wpcf'));
}

function wpcf_admin_general_settings_form_submit($form) {

    $settings = wpcf_get_settings();
    $data = $_POST['wpcf_settings'];
    foreach (array('register_translations_on_import','help_box') as $setting) {
        if (!isset($data[$setting])) {
            $settings[$setting] = 0;
        } else {
			// @todo Add sanitization here
            $settings[$setting] = $data[$setting];
        }
    }
    update_option('wpcf_settings', $settings);

    wpcf_admin_message_store(__('Settings saved', 'wpcf'));
}
