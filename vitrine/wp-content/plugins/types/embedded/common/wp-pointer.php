<?php



class WPV_wp_pointer {
    
    function __construct($id){
        
        $this->id = $id; // Use to identify which plugin is using the pointer.
        
        add_action('init', array($this, 'init'));    
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
        
        $this->pointers = array();
        $this->show_hints_selector = null;
    }

    function __destruct(){
        
    }

    function init(){
        add_action('wp_ajax_wpv_wp_pointer_set_ignore', array($this, 'ajax_set_ignore'));
    }
    
    function add_pointer($header, $content, $jquery_id, $position, $pointer_name, $activate_function = null, $activate_selector = false) {
        $this->pointers[] = array('header' => str_replace("'", '&#39;', $header),
                            'content' => str_replace("'", '&#39;', $content),
                            'jquery_id' => $jquery_id, // This should be the selector for jquery. eg select[name="_wpv_layout_settings[style]"]
                            'position' => $position, 
                            'name' => esc_js(str_replace(' ' , '_', $pointer_name)), // Used for dismissing the pointer so it doesn't show again.
                            'activate' => $activate_function, // $activate_function = null, means the pointer will activate on document ready
                            'activate_selector' => $activate_selector); // use with $activate_function to show the pointer on the passed selector
    }
    
    function add_show_hints_ui($selector_code) {
        $this->show_hints_selector = $selector_code;
    }
    
    function admin_enqueue_scripts() {
        // Using Pointers
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );

        wp_enqueue_script( 'wpv-wp-pointer' , icl_get_file_relpath(__FILE__) . '/res/js/wpv-wp-pointer.js', array());
        wp_enqueue_style( 'wpv-wp-pointer' , icl_get_file_relpath(__FILE__) . '/res/css/wpv-wp-pointer.css', array());
    
        // Register our action
        add_action( 'admin_print_footer_scripts', array($this, 'admin_print_footer_scripts') );
    }

    function admin_print_footer_scripts() {
        
        if (count($this->pointers)) {

            $options = get_option('wpv-wp-pointer-' . $this->id);

            ?>
            <script type="text/javascript">
            //<![CDATA[

            <?php foreach($this->pointers as $pointer) {
                // add the ignore variables
                
                $ignore = (isset($options['ignore_'.$pointer['name']]) && $options['ignore_'.$pointer['name']] == 'ignore') ? 'true' : 'false';
                
                ?>
                    var wpv_pointer_<?php echo $pointer['name']; ?>_ignore = <?php echo $ignore; ?>;
                <?php
            }
            ?>
            
            jQuery(document).ready( function($) {
                
                <?php foreach($this->pointers as $pointer) {

                    if ($pointer['activate'] == null) {                    
                        $content = '<h3>' . $pointer['header'] . '</h3><p>' . $pointer['content'] . '</p>';
                        ?>
    
                        if (!wpv_pointer_<?php echo $pointer['name']; ?>_ignore) {
                            jQuery('<?php echo $pointer['jquery_id']; ?>').pointer({
                                content: '<?php echo $content; ?>',
                                position: '<?php echo esc_js($pointer['position']); ?>',
                                close: function() {
                                    // Once the close button is hit
                                    wpv_pointer_<?php echo $pointer['name']; ?>_ignore = true;
                        			wpv_wp_pointer_ignore("<?php echo $pointer['name']; ?>","<?php echo wp_create_nonce('wpv-ignore-nonce'); ?>", "ignore", "<?php echo $this->id; ?>");
                                    
                                }
                            }).pointer('open');
                        }
                        <?php
                    }
                }
                ?>
            });
            
            <?php

                foreach($this->pointers as $pointer) {

                    if ($pointer['activate'] != null) {
                        // create a js function to call.
                        
                        if ($pointer['activate_selector'] == false) {
                            ?>
                            function <?php echo $pointer['activate']; ?>() {
    
                                <?php $content = '<h3>' . $pointer['header'] . '</h3><p>' . $pointer['content'] . '</p>'; ?>
            
                                if (!wpv_pointer_<?php echo $pointer['name']; ?>_ignore) {
                                    jQuery('<?php echo $pointer['jquery_id']; ?>').pointer({
                                        content: '<?php echo $content; ?>',
                                        position: '<?php echo esc_js($pointer['position']); ?>',
                                        close: function() {
                                            // Once the close button is hit
                                            wpv_pointer_<?php echo $pointer['name']; ?>_ignore = true;
                                			wpv_wp_pointer_ignore("<?php echo $pointer['name']; ?>","<?php echo wp_create_nonce('wpv-ignore-nonce'); ?>", "ignore", "<?php echo $this->id; ?>");
                                        }
                                    }).pointer('open');
                                }                                
                            }
                            <?php
                        } else {
                            ?>
                            function <?php echo $pointer['activate']; ?>(selector) {
    
                                <?php $content = '<h3>' . $pointer['header'] . '</h3><p>' . $pointer['content'] . '</p>'; ?>
            
                                if (!wpv_pointer_<?php echo $pointer['name']; ?>_ignore) {
                                    jQuery(selector).pointer({
                                        content: '<?php echo $content; ?>',
                                        position: '<?php echo esc_js($pointer['position']); ?>',
                                        close: function() {
                                            wpv_pointer_<?php echo $pointer['name']; ?>_ignore = true;
                                			wpv_wp_pointer_ignore('<?php echo $pointer['name']; ?>',"<?php echo wp_create_nonce('wpv-ignore-nonce'); ?>", "ignore", "<?php echo $this->id; ?>");
                                        }
                                    }).pointer('open');
                                }                                
                            }
                            <?php
                        }
                    }
                }
                
                if ($this->show_hints_selector) {
                    
                    $found = false;
                    foreach($this->pointers as $pointer) {
                        if (isset($options['ignore_'.$pointer['name']]) && $options['ignore_'.$pointer['name']] == 'ignore') {
                            $found = true;
                        }
                    }
                    
                    $text = sprintf(esc_js(__('Some pointer hints have been hidden on this page. %sShow them again%s', 'wpv-views')),
                                    '<a href="#" onclick="wpv_wp_pointer_clear_ignores_' . $this->id . '();return false;">',
                                    '</a>');
                    
                    ?>
                    function wpv_wp_pointer_clear_ignores_<?php echo $this->id; ?>() {

                        <?php foreach($this->pointers as $pointer) { ?>
                            wpv_pointer_<?php echo $pointer['name']; ?>_ignore = false;
                            wpv_wp_pointer_ignore('<?php echo $pointer['name']; ?>',"<?php echo wp_create_nonce('wpv-ignore-nonce'); ?>", "", "<?php echo $this->id; ?>");
                        <?php } ?>
                        
                        jQuery('#wpv_wp_pointer_clear_ignores_<?php echo $this->id; ?>').fadeOut();
                    
                    }
                    
                    jQuery(document).ready( function($) {
                    
                        <?php echo $this->show_hints_selector; ?>.append('<div class="wpv_wp_pointer_clear_ignores" id="wpv_wp_pointer_clear_ignores_<?php echo $this->id; ?>" <?php echo $found ? '' : 'style="display:none;"'?>><?php echo $text; ?></div>');
                    });
                    <?php
                }
                ?>
            
            //]]>
            </script>
            <?php
        }
    }

    function ajax_set_ignore() {
        if ( ! current_user_can('manage_options') ) {
            die('-1');
        }
		if (wp_verify_nonce($_POST['wpv_nonce'], 'wpv-ignore-nonce')) {
    
            $options = get_option('wpv-wp-pointer-' . $this->id);
            $options['ignore_'.$_POST['option']] = $_POST['value'];
            update_option('wpv-wp-pointer-' . $this->id, $options);
            die('1');
        }
    }
    
}


