<?php

/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/classes/class.toolset.promo.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

if (!class_exists('Toolset_Promotion')) {

    /**
     * Class to show promotion message.
     *
     * @since 1.5
     * @access  public
     */
    class Toolset_Promotion
    {
        private $version = '1.0';

        public function __construct()
        {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_footer', array($this, 'admin_footer'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('plugins_loaded', 'on_the_go_systems_branding_plugins_loaded');
        }

        /**
         * Register script and styles
         *
         * Register script and styles for future usage.
         *
         * @since 1.5
         *
         */
        public function admin_init()
        {
            wp_register_script(
                'toolset-colorbox',
                plugins_url('/res/js/jquery.colorbox-min.js', dirname(__FILE__)),
                array('jquery'),
                '1.4.31'
            );
            wp_register_script(
                __CLASS__,
                plugins_url('/res/js/toolset-promotion.js', dirname(__FILE__)),
                array('underscore', 'toolset-colorbox'),
                $this->version,
                true
            );
            wp_register_style(
                'toolset-colorbox',
                plugins_url('/res/css/colorbox.css', dirname(__FILE__)),
                false,
                '1.4.31'
            );
            wp_register_style(
                __CLASS__,
                plugins_url('/res/css/toolset-promotion.css', dirname(__FILE__)),
                array('toolset-colorbox', 'onthego-admin-styles'),
                $this->version
            );
        }

        /**
         * Enqueue scripts & styles
         *
         * After check is a correct place, this function enqueue scripts & styles
         * for toolset promotion box.
         *
         * @since 1.5
         *
         */
        public function admin_enqueue_scripts()
        {
            if (!is_admin() || !function_exists('get_current_screen')) {
                return;
            }
            /**
             * List of admin page id
             *
             * Filter allow to add or change list of admin screen id for checking
             * where we need enqueue toolset promotion assets.
             *
             * @since 1.5
             *
             * @param array $screen_ids List of admin page screen ids.
             *
             */
            $screen_ids = apply_filters('toolset_promotion_screen_ids', array());
            if (empty($screen_ids)) {
                return;
            }
            $screen = get_current_screen();
            if (!in_array($screen->id, $screen_ids)) {
                return;
            }
            wp_enqueue_style(__CLASS__);
            wp_enqueue_script(__CLASS__);
        }

        /**
         * Print in footer
         *
         * Print nessary elemnt in admin footer
         *
         * @since 1.5
         *
         */
        public function admin_footer()
        {
            $link_learn = $this->get_affiliate_link_string('http://wp-types.com/');
            $link_button = $this->get_affiliate_link_string('http://wp-types.com/#buy-toolset');

            ob_start();
            ?>

            <div class="ddl-dialogs-container">
                <div id="js-buy-toolset-embedded-message-wrap"></div>
            </div>
            <script type="text/html" id="js-buy-toolset-embedded-message">
                <div class="toolset-modal">
                    <h2><?php _e('Want to edit Views, CRED forms and Layouts? Get the full <em>Toolset</em> package!', 'wpcf'); ?></h2>

                    <div class="content">
                        <p class="full"><?php _e('The full <em>Toolset</em> package allows you to develop and customize themes without touching PHP. You will be able to:', 'wpcf'); ?></p>

                        <div class="icons">
                            <ul>
                                <li class="template"><?php _e('Create templates', 'wpcf'); ?></li>
                                <li class="layout"><?php _e('Design page layouts using drag-and-drop', 'wpcf'); ?></li>
                                <li class="toolset-search"><?php _e('Build parametric searches', 'wpcf'); ?></li>
                            </ul>
                            <ul>
                                <li class="list"><?php _e('Display lists of content', 'wpcf'); ?></li>
                                <li class="form"><?php _e('Create front-end content editing forms', 'wpcf'); ?></li>
                                <li class="more"><?php _e('and more…', 'wpcf'); ?></li>
                            </ul>
                        </div>

                        <p class="description"><?php _e('Once you buy the full Toolset, you will be able to edit Views, CRED forms and Layouts in your site, as well as build new ones.', 'wpcf'); ?></p>

                        <a href="<?php echo $link_button; ?>"
                           class="button"><?php _e('<em>Toolset</em> Package Options', 'wpcf'); ?></a>
                        <a href="<?php echo $link_learn; ?>"
                           class="learn"><?php _e('Learn more about <em>Toolset</em>', 'wpcf'); ?></a>

                    </div>
                    <span class="icon-toolset-logo"></span>
                    <span class="js-close-promotional-message"></span>
                </div>
            </script>
            <?php
            echo ob_get_clean();
        }

        private function get_affiliate_link_string($link)
        {
            if (function_exists('installer_ep_get_configuration') === false) {
                return $link;
            }

            $info = installer_ep_get_configuration(wp_get_theme()->Name);

            if (!isset($info['repositories']) &&
                !isset($info['repositories']['toolset'])
            ) {
                return $link;

            } else if (
                isset($info['repositories']['toolset']['affiliate_id']) &&
                isset($info['repositories']['toolset']['affiliate_key'])
            ) {
                $id = $info['repositories']['toolset']['affiliate_id'];
                $key = $info['repositories']['toolset']['affiliate_key'];

                $hash = explode( '#', $link );
                if( count($hash) > 1 ){
                    $link = $hash[0];
                    $hash = "#" . $hash[1];
                } else {
                    $hash = '';
                }

                return sprintf("%s?aid=%s&affiliate_key=%s%s", $link, $id, $key, $hash);
            }

            return $link;
        }

    }

}
