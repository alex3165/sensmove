<?php

final class WP_Installer{
    protected static $_instance = null;
    
    private $repositories = array();
    
    protected $api_debug = '';
    
    private $config = array();
    
    protected $_plugins_renew_warnings = array();
    
    protected $_gz_on = false;

    public static function instance() {
        
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function __construct(){

        if(!is_admin() || !is_user_logged_in()) return; //Only for admin

        $this->_gz_on = function_exists('gzuncompress') && function_exists('gzcompress');
        $this->settings = $this->get_settings();

        add_action('admin_notices', array($this, 'show_site_key_nags'));

        add_action('admin_init', array($this, 'load_embedded_plugins'), 0);

        add_action('admin_menu', array($this, 'menu_setup'));
        add_action('network_admin_menu', array($this, 'menu_setup'));

        if(defined('DOING_AJAX') && isset($_POST['action']) && $_POST['action'] == 'installer_download_plugin'){
            add_filter( 'site_transient_update_plugins', array( $this, 'plugins_upgrade_check') );
        }
        add_filter('plugins_api', array( $this, 'custom_plugins_api_call'), 10, 3);
        add_filter('pre_set_site_transient_update_plugins', array( $this, 'plugins_upgrade_check'));

        // register repositories
        $this->load_repositories_list();

        if(empty($this->settings['last_repositories_update']) || time() - $this->settings['last_repositories_update'] > 86400){
            $this->refresh_repositories_data();
        }

        /* Original setup for plugins updates check
        add_action('wp_maybe_auto_update', array($this, 'refresh_repositories_data'));
        if(isset($_GET['force-check']) && $_GET['force-check']){
            add_action('core_upgrade_preamble', array($this, 'refresh_repositories_data'));
        }
        add_action('wp_update_plugins', array($this, 'refresh_repositories_data'));
        */

        // Hook to wp_update_plugins before the WP API request
        // Using this in place of a missing hook in wp_update_plugins
        // This is being triggered every time WP checks for plugin updates
        add_filter('plugins_update_check_locales', array($this, 'update_plugins_information'));

        // default config
        $this->config['plugins_install_tab'] = false;    
        
        add_action('init', array($this, 'init'));
        
        //add_filter('wp_installer_buy_url', array($this, 'append_parameters_to_buy_url'));


    }
    
    public function set_config($key, $value){
        
        $this->config[$key] = $value;
        
    }
    
    public function init(){
        global $pagenow;
        
        if(empty($this->settings['_pre_1_0_clean_up'])) {
            $this->_pre_1_0_clean_up();
        }

        wp_enqueue_script('installer-admin', $this->res_url() . '/res/js/admin.js', array('jquery'), $this->version());
        wp_enqueue_style('installer-admin', $this->res_url() . '/res/css/admin.css', array(), $this->version());
        
        if($pagenow == 'plugins.php'){
            add_action('admin_notices', array($this, 'setup_plugins_page_notices'));
            add_action('admin_notices', array($this, 'setup_plugins_renew_warnings'), 10);
            add_action('admin_notices', array($this, 'queue_plugins_renew_warnings'), 20);
            
            add_action('admin_init', array($this, 'setup_plugins_action_links'));

        }

        if($this->is_repositories_page()){
            add_action('admin_init', array($this, 'validate_repository_subscription'));
        }

        if(defined('DOING_AJAX')){
            add_action('wp_ajax_save_site_key', array($this, 'save_site_key'));
            add_action('wp_ajax_remove_site_key', array($this, 'remove_site_key'));
            add_action('wp_ajax_update_site_key', array($this, 'update_site_key'));
            
            add_action('wp_ajax_installer_download_plugin', array($this, 'download_plugin_ajax_handler'));
            add_action('wp_ajax_installer_activate_plugin', array($this, 'activate_plugin'));
            
            add_action('wp_ajax_installer_dismiss_nag', array($this, 'dismiss_nag'));
        }

        if($pagenow == 'update.php'){
            if(isset($_GET['action']) && $_GET['action'] == 'update-selected'){
                add_action('admin_head', array($this, 'plugin_upgrade_custom_errors'));         //iframe/bulk
            }else{
                add_action('all_admin_notices', array($this, 'plugin_upgrade_custom_errors'));  //regular/singular
            }
        }

        // WP 4.2
        if(defined('DOING_AJAX')){
            add_action('wp_ajax_update-plugin', array($this, 'plugin_upgrade_custom_errors'), 0); // high priority, before WP
        }




        }

    public function load_embedded_plugins(){
        if(file_exists($this->plugin_path() . '/embedded-plugins' )) {
            include_once $this->plugin_path() . '/embedded-plugins/embedded-plugins.class.php';
            $this->installer_embedded_plugins = new Installer_Embedded_Plugins();
        }
    }

    public function menu_setup(){
        global $pagenow;

        if(is_multisite() && !is_network_admin()){
            $this->menu_multisite_redirect();
            add_options_page(__('Installer', 'installer'), __('Installer', 'installer'), 'manage_options', 'installer', array($this, 'show_products'))            ;
        }else{
            if($this->config['plugins_install_tab'] && is_admin() && $pagenow == 'plugin-install.php'){
                // Default GUI, under Plugins -> Install
                add_filter('install_plugins_tabs', array($this, 'add_install_plugins_tab'));
                add_action('install_plugins_commercial', array($this, 'show_products'));
            }
        }
        
    }
    
    public function menu_url(){        
        if(is_multisite()){
            if(is_network_admin()){
                $url = network_admin_url('plugin-install.php?tab=commercial');    
            }else{
                $url = admin_url('options-general.php?page=installer');                    
            }            
        }else{
            $url = admin_url('plugin-install.php?tab=commercial');    
        }
        return $url;
    }
    
    private function menu_multisite_redirect(){
        global $pagenow;
        
        if($pagenow == 'plugin-install.php' && isset($_GET['tab']) && $_GET['tab'] == 'commercial'){
            wp_redirect($this->menu_url());
            exit;
        }
        
    } 
    
    private function _pre_1_0_clean_up(){
        global $wpdb;
        
        if(!defined('WPRC_VERSION')){
            $old_tables = array(
                    $wpdb->prefix . 'wprc_cached_requests',
                    $wpdb->prefix . 'wprc_extension_types',
                    $wpdb->prefix . 'wprc_extensions',
                    $wpdb->prefix . 'wprc_repositories',
                    $wpdb->prefix . 'wprc_repositories_relationships',
            );
            
            foreach($old_tables as $table){
                $wpdb->query(sprintf("DROP TABLE IF EXISTS %s", $table));
            }
            
        }
        
        $this->settings['_pre_1_0_clean_up'] = true;
        $this->save_settings();
    }
    
    public function setup_plugins_action_links(){
        
        $plugins = get_plugins();
              
        foreach($this->settings['repositories'] as $repository_id => $repository){
            
            foreach($repository['data']['packages'] as $package){
                
                foreach($package['products'] as $product){
                    
                    foreach($product['downloads'] as $download){
                        
                        if(!isset($rep_plugins[$download['basename']])){
                            $r_plugins[$download['basename']] = $download['name'];
                        }
                        
                    }
                    
                }
                
            }
            
            $site_key = $this->get_site_key($repository_id);
            $repositories_plugins[$repository_id] = array( 'plugins' => $r_plugins, 'registered' => !empty($site_key));

            foreach($plugins as $plugin_id => $plugin){
                
                foreach($repositories_plugins as $repository_id => $r_plugins){
                    
                    foreach($r_plugins['plugins'] as $basename => $name){
                        
                        if($name == $plugin['Name'] && dirname($plugin_id) == $basename){
                            
                            if($r_plugins['registered']){
                                add_filter( 'plugin_action_links_' . $plugin_id, array($this, 'plugins_action_links_registered'));                            
                            }else{
                                add_filter( 'plugin_action_links_' . $plugin_id, array($this, 'plugins_action_links_not_registered'));                        
                            }
                            
                        }
                        
                    }
                        
                }
                
            }            
            
        }
        
    }

    public function plugins_action_links_registered($links){
        $links[] = '<a href="' . $this->menu_url() . '">' . __('Registered', 'installer') . '</a>';        
        return $links;
    }
    
    public function plugins_action_links_not_registered($links){
        $links[] = '<a href="' . $this->menu_url() . '">' . __('Register', 'installer') . '</a>';        
        return $links;
    }
    
    public function version(){
        return WP_INSTALLER_VERSION;        
    }
    
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( dirname(__FILE__) ) );
    }
    
    public function plugin_url() {
        if(isset($this->config['in_theme_folder']) && !empty($this->config['in_theme_folder'])){
            $url = untrailingslashit(get_template_directory_uri() . '/' . $this->config['in_theme_folder']);
        }else{
            $url = untrailingslashit( plugins_url( '/', dirname(__FILE__) ) );
        }

        return $url;
    }

    public function is_repositories_page(){
        global $pagenow;

        return $pagenow == 'plugin-install.php' && isset($_GET['tab']) && $_GET['tab'] == 'commercial';
    }

    public function res_url(){        
        if(isset($this->config['in_theme_folder']) && !empty($this->config['in_theme_folder'])){
            $url = untrailingslashit(get_template_directory_uri() . '/' . $this->config['in_theme_folder']);
        }else{
            $url = $this->plugin_url();
        }
        return $url;
    }
        
    public function save_settings(){
                
        $_settings = serialize($this->settings);
        if($this->_gz_on){
            $_settings =  gzcompress($_settings);
        }
        $_settings = base64_encode($_settings);

            update_option( 'wp_installer_settings', $_settings );

        if( is_multisite() && is_main_site() && isset($this->settings['repositories']) ){
            $network_settings = array();

            foreach( $this->settings['repositories'] as $rep_id => $repository ){
                if( isset($repository['subscription']) )
                    $network_settings[$rep_id] = $repository['subscription'];
            }

            update_site_option( 'wp_installer_network', $network_settings );
        }

    }

    public function get_settings(){

        $_settings = get_option( 'wp_installer_settings' );


        if( is_array( $_settings ) || empty( $_settings ) ){ //backward compatibility 1.1
            $settings   = $_settings;
            return $settings;
        }else{
            $_settings = base64_decode( $_settings );
            if($this->_gz_on){
                $_settings = gzuncompress( $_settings );
            }
            $settings = unserialize( $_settings );
        }

        if( is_multisite() && isset($settings['repositories']) ) {
            $network_settings = maybe_unserialize( get_site_option('wp_installer_network') );
            if( $network_settings ){
                foreach( $settings['repositories'] as $rep_id => $repository ) {
                    if( isset($network_settings[$rep_id] ) ) {
                        $settings['repositories'][$rep_id]['subscription'] = $network_settings[$rep_id];
                    }
                }
            }
        }

        return $settings;
    }

    public function get_installer_site_url( $repository_id = false ){
        $site_url = get_site_url();

        if( $repository_id && is_multisite() && isset( $this->settings['repositories'] ) ){
            $network_settings = maybe_unserialize( get_site_option('wp_installer_network') );

            if ( isset( $network_settings[$repository_id] ) ) {
                $site_url = network_site_url();
            }
        }

        return $site_url;
    }
    
    public function show_site_key_nags(){
        $screen = get_current_screen();
        
        if($screen->base == 'settings_page_installer' || ($screen->base == 'plugin-install' && isset($_GET['tab']) && $_GET['tab'] == 'commercial')){
            return;
        }
        
        if(!empty($this->config['site_key_nags'])){
            
            foreach($this->config['site_key_nags'] as $nag){
                
                if(!$this->repository_has_subscription($nag['repository_id'] )){
                    $show = true;
                    if(!empty($nag['condition_cb'])){
                        $show = call_user_func($nag['condition_cb']);
                    }
                    
                    if(empty($this->settings['dismissed_nags'][$nag['repository_id']]) && $show){                
                        echo '<div class="updated error"><p>';                
                        printf(__("To get automatic updates, you need to register %s for this site. %sRegister %s%s", 'sitepress'), 
                            $nag['product_name'], '<a class="button-primary" href="' . $this->menu_url() . '">', $nag['product_name'], '</a>');
                        
                        echo '<br /><a class="alignright installer-dismiss-nag" href="#" data-repository="' . $nag['repository_id']  . '">' . __('Dismiss', 'sitepress') . '</a><br clear="all" />';    
                        echo '</p></div>';
                    }
                }
                
            }
            
        }
        
    }
    
    public function dismiss_nag(){
        $this->settings['dismissed_nags'][$_POST['repository']] = 1;
        
        $this->save_settings();
        
        echo json_encode(array());
        exit;
    }
    
    public function add_install_plugins_tab($tabs){
        
        $tabs['commercial'] = __('Commercial', 'installer');
        
        return $tabs;
    }
    
    public function load_repositories_list(){
        global $wp_installer_instances;

        foreach ($wp_installer_instances as $instance) {

            if (file_exists(dirname($instance['bootfile']) . '/repositories.xml')) {
                $config_file = dirname($instance['bootfile']) . '/repositories.xml';

                if (file_exists(dirname($instance['bootfile']) . '/repositories.sandbox.xml')) {
                    $config_file = dirname($instance['bootfile']) . '/repositories.sandbox.xml';
                }

                $repos = simplexml_load_file($config_file);

                foreach ($repos as $repo) {
                    $id = strval($repo->id);

                    $data['api-url'] = strval($repo->apiurl);
                    $data['products'] = strval($repo->products);

                    // excludes rule;
                    if (isset($this->config['repositories_exclude']) && in_array($id, $this->config['repositories_exclude'])) {
                        continue;
                    }

                    // includes rule;
                    if (isset($this->config['repositories_include']) && !in_array($id, $this->config['repositories_include'])) {
                        continue;
                    }

                    $this->repositories[$id] = $data;

                }

            }
        }

    }
    
    public function filter_repositories_list(){
        
        foreach($this->settings['repositories'] as $id => $repo_data){
            
            // excludes rule;
            if(isset($this->config['repositories_exclude']) && in_array($id, $this->config['repositories_exclude'])){
                unset($this->settings['repositories'][$id]);
            }
            
            // includes rule;
            if(isset($this->config['repositories_include']) && !in_array($id, $this->config['repositories_include'])){
                unset($this->settings['repositories'][$id]);
            }
            
            
        }
        
        
    }

    // Using this in place of a missing hook in wp_update_plugins
    // This is being triggered every time WP checks for plugin updates
    public function update_plugins_information($locale_data){

        $this->refresh_repositories_data();

        return $locale_data;
    }

    public function refresh_repositories_data(){

        foreach($this->repositories as $id => $data){
            
            $response = wp_remote_get($data['products']);
            
            if(is_wp_error($response)){
                // http fallback
                $data['products'] = preg_replace("@^https://@", 'http://', $data['products']);
                $response = wp_remote_get($data['products']);
            }
            
            if(is_wp_error($response)){
                $error = sprintf(__("Can't connect to %s") . "\n", $data['products']);
                if(WP_DEBUG){
                    trigger_error($error, E_USER_WARNING);    
                }else{
                    error_log($error, E_USER_WARNING);
                }
                
                continue;  
            } 
            
            if($response && isset($response['response']['code']) && $response['response']['code'] == 200){
                $body = wp_remote_retrieve_body($response);     
                if($body){
                    $products = json_decode($body, true);
                    
                    if(is_array($products)){
                        $this->settings['repositories'][$id]['data'] = $products;        
                    }                    
                }       
                
            }
            
        }
        
        // cleanup
        if(empty($this->settings['repositories'])){
            $this->settings['repositories'] = array();
        }
        foreach($this->settings['repositories'] as $id => $data){
            if(!in_array($id, array_keys($this->repositories))){
                unset($this->settings['repositories'][$id]);
            }
        }

        $this->settings['last_repositories_update']= time();
        
        $this->save_settings();

    }
    
    public function show_products($args = array()){
        
        $screen = get_current_screen();
        
        if($screen->base == 'settings_page_installer'){ // settings page
            echo '<div class="wrap">';
            echo '<h2>' . __('Installer', 'installer') . '</h2>';
            echo '<br />';
        }
        
        if(!is_array($args)) $args = array();
        if(empty($args['template'])) $args['template'] = 'default';
        
        $this->filter_repositories_list();
        
        if(!empty($this->settings['repositories'])){

            $this->localize_strings();
            $this->set_filtered_prices($args);
            $this->filter_downloads_by_icl(); //downloads for ICL users
            $this->set_hierarchy_and_order();

            foreach($this->settings['repositories'] as $repository_id => $repository){
                
                if($args['template'] == 'compact'){
                    
                    if(isset($args['repository']) && $args['repository'] == $repository_id){
                        include $this->plugin_path() . '/templates/products-compact.php';
                    }
                        
                }else{
                    
                    include $this->plugin_path() . '/templates/repository-listing.php';
                    
                }
                
                unset($site_key, $subscription_type, $expired, $upgrade_options, $products_avaliable);
                
            }
            
        }else{
            
            echo '<center>' . __('No repositories defined.', 'installer') . '</center>';
            
        }
        
        if($screen->base == 'settings_page_installer'){ // settings page
            echo '</div>';
        }
        
        
    }

    public function get_product_price($repository_id, $package_id, $product_id, $incl_discount = false){

        $price = false;

        foreach($this->settings['repositories'][$repository_id]['data']['packages'] as $package ){

            if($package['id'] == $package_id){
                if(isset($package['products'][$product_id])){
                    if($incl_discount && isset($package['products'][$product_id]['price_disc'])){
                        $price = $package['products'][$product_id]['price_disc'];
                    }elseif(isset($package['products'][$product_id]['price'])){
                        $price = $package['products'][$product_id]['price'];
                    }
                }
                break;
            }
        }

        return $price;
    }

    private function _render_product_packages($packages, $subscription_type, $expired, $upgrade_options, $repository_id){

        $data = array();

        foreach($packages as $package_id => $package){

            $row = array('products' => array(), 'downloads' => array());
            foreach($package['products'] as $product){

                // buy base
                if(empty($subscription_type) || $expired) {

                    $p['url'] = $this->append_parameters_to_buy_url($product['url'], $repository_id);
                    if (!empty($product['price_disc'])) {
                        $p['label'] = $product['call2action'] . ' - ' . sprintf('$%s %s$%d%s (USD)', $product['price_disc'], '&nbsp;&nbsp;<del>', $product['price'], '</del>');
                    } else {
                        $p['label'] = $product['call2action'] . ' - ' . sprintf('$%d (USD)', $product['price']);
                    }
                    $row['products'][] = $p;

                    // renew
                } elseif(isset($subscription_type) && $product['subscription_type'] == $subscription_type){

                    if($product['renewals']) {
                        foreach ($product['renewals'] as $renewal) {
                            $p['url'] = $this->append_parameters_to_buy_url($renewal['url'], $repository_id);
                            $p['label'] = $renewal['call2action'] . ' - ' . sprintf('$%d (USD)', $renewal['price']);
                        }

                        $row['products'][] = $p;
                    }

                }

                // upgrades
                if(!empty($upgrade_options[$product['subscription_type']])){

                    foreach($upgrade_options[$product['subscription_type']] as $stype => $upgrade){
                        if($stype != $subscription_type) continue;

                        $p['url'] = $this->append_parameters_to_buy_url($upgrade['url'], $repository_id);
                        if (!empty($upgrade['price_disc'])) {
                            $p['label'] = $upgrade['call2action'] . ' - ' . sprintf('$%s %s$%d%s (USD)', $upgrade['price_disc'], '&nbsp;&nbsp;<del>', $upgrade['price'], '</del>');
                        } else {
                            $p['label'] = $upgrade['call2action'] . ' - ' . sprintf('$%d (USD)', $upgrade['price']);
                        }
                        $row['products'][] = $p;

                    }

                }

                // downloads
                if(isset($subscription_type) && !$expired && $product['subscription_type'] == $subscription_type){
                    $row['downloads'] = $product['downloads'];
                }

                //subpackages
                if(!empty($package['sub-packages'])){
                    $row['sub-packages'] = $package['sub-packages'];
                }

            }

            $row['id']          = $package['id'];
            $row['image_url']   = $package['image_url'];
            $row['name']        = $package['name'];
            $row['description'] = $package['description'];

            if(!empty($row['products']) || !empty($row['downloads']) || !empty($row['sub-packages'])){
                $data[] = $row;
            }


        }

        return $data;

    }

    public function append_parameters_to_buy_url($url, $repository_id, $args = array()){

        $url = add_query_arg( array('icl_site_url' => $this->get_installer_site_url() ), $url );
        
        $affiliate_id   = false;
        $affiliate_key  = false;

        if(isset($this->config['affiliate_id:' . $repository_id]) && isset($this->config['affiliate_key:' . $repository_id])){
            
            $affiliate_id  = $this->config['affiliate_id:' . $repository_id];
            $affiliate_key = $this->config['affiliate_key:' . $repository_id];
            
        }elseif(isset($args['affiliate_id:' . $repository_id]) && isset($args['affiliate_key:' . $repository_id])){
            
            $affiliate_id   = $args['affiliate_id:' . $repository_id];
            $affiliate_key  = $args['affiliate_key:' . $repository_id];
            
        }elseif(defined('ICL_AFFILIATE_ID') && defined('ICL_AFFILIATE_KEY')){ //support for 1 repo
            
            $affiliate_id  = ICL_AFFILIATE_ID;    
            $affiliate_key = ICL_AFFILIATE_KEY;    
            
        }elseif(isset($this->config['affiliate_id']) && isset($this->config['affiliate_key'])) {
            // BACKWARDS COMPATIBILITY
            $affiliate_id = $this->config['affiliate_id'];
            $affiliate_key = $this->config['affiliate_key'];
        }

        if($affiliate_id && $affiliate_key){
            $url = add_query_arg(array('aid' => $affiliate_id, 'affiliate_key' => $affiliate_key), $url);
        }
        
        return $url; 
        
    }
    
    public function save_site_key($args = array()){
        
        $error = '';
        
        $repository_id  = isset($args['repository_id']) ? $args['repository_id'] : (isset($_POST['repository_id']) ? $_POST['repository_id'] : false);
        $nonce          = isset($args['nonce']) ? $args['nonce'] : (isset($_POST['nonce']) ? $_POST['nonce'] : '');
        $site_key       = isset($args['site_key']) ? $args['site_key'] : $_POST['site_key_' . $repository_id];
        
        $site_key = preg_replace("/[^A-Za-z0-9]/", '', $site_key);
        
        if($repository_id && $nonce && wp_create_nonce('save_site_key_' . $repository_id) == $nonce){
            
            $subscription_data = $this->fetch_subscription_data($repository_id, $site_key);
            
            if(is_wp_error($subscription_data)){
                $error = $subscription_data->get_error_message();
                if(preg_match('#Could not resolve host: (.*)#', $error, $matches)){
                    $error = sprintf(__("%s cannot access %s to register. Try again to see if it's a temporary problem. If the problem continues, make sure that this site has access to the Internet. You can still use the plugin without registration, but you will not receive automated updates.", 'installer'), 
                        '<strong><i>' . $this->get_generic_product_name($repository_id) . '</i></strong>', 
                        '<strong><i>' . $matches[1]. '</i></strong>'
                    ) ;
                }
                
            }elseif($subscription_data){
                $this->settings['repositories'][$repository_id]['subscription'] = array('key' => $site_key, 'data' => $subscription_data);
                $this->save_settings();
            }else{
                $error = __('Invalid site key for the current site.', 'installer');
            }
            
        }
        
        $return = array('error' => $error);
        
        if($this->api_debug){
            $return['debug'] = $this->api_debug;    
        }
        
        if(!empty($args['return'])){
            return $return;
        }else{
            echo json_encode($return);
            exit;
        }
        
    }
    
    public function get_site_key($repository_id){
        
        if(isset($this->settings['repositories'][$repository_id]['subscription'])){
            $site_key = $this->settings['repositories'][$repository_id]['subscription']['key'];
        }else{
            $site_key = false;
        }
        
        return $site_key;
    }
    
    public function remove_site_key(){
        if($_POST['nonce'] == wp_create_nonce('remove_site_key_' . $_POST['repository_id'])){
            unset($this->settings['repositories'][$_POST['repository_id']]['subscription']);
            $this->save_settings();
            
            $this->refresh_repositories_data();
        }
        exit;
    }

    public function validate_repository_subscription(){
        $repository_id = isset($_GET['validate_repository']) ? $_GET['validate_repository'] : false;
        if($repository_id){

            $site_key = $this->get_site_key($repository_id);
            if($site_key) {
                $subscription_data = $this->fetch_subscription_data($repository_id, $site_key);
                if(empty($subscription_data)){
                    unset($this->settings['repositories'][$repository_id]['subscription']);
                    delete_site_transient('update_plugins');
                    $this->save_settings();
                }
            }

            wp_redirect($this->menu_url() . '#repository-' . $repository_id);
            exit;

        }

    }

    public function update_site_key(){

        $error = '';
                
        if($_POST['nonce'] == wp_create_nonce('update_site_key_' . $_POST['repository_id'])){
            
            $repository_id = $_POST['repository_id'];
            $site_key = $this->get_site_key($_POST['repository_id']);
            
            if($site_key){
                $subscription_data = $this->fetch_subscription_data($repository_id, $site_key);    
                
                if($subscription_data){
                    $this->settings['repositories'][$repository_id]['subscription'] = array('key' => $site_key, 'data' => $subscription_data);

                    //also refresh products information
                    $this->refresh_repositories_data();

                }else{
                    unset($this->settings['repositories'][$repository_id]['subscription']);
                    $error = __('Invalid site key for the current site.', 'installer');
                }
                
                $this->save_settings();
                
            }
            
        }
        
        echo json_encode(array('error' => $error));
        
        exit;
    }
    
    public function api_debug_log($text){
        
        if(defined('WPML_DEBUG_INSTALLER') && WPML_DEBUG_INSTALLER){
        
            if(!is_scalar($text)){
                $text = print_r($text, 1);
            }
            
            $this->api_debug .= $text . "\n";           
            
        }
        
    }
    
    public function fetch_subscription_data($repository_id, $site_key){
        
        $subscription_data = false;
        
        $args['body'] = array(
                'action'    => 'site_key_validation',
                'site_key'  => $site_key,
                'site_url'  => $this->get_installer_site_url($repository_id),
        );
        $args['timeout'] = 45;

        $response = wp_remote_post($this->repositories[$repository_id]['api-url'], $args);
        
        $this->api_debug_log("POST {$this->repositories[$repository_id]['api-url']}");
        $this->api_debug_log($args);
        
        if(!is_wp_error($response)){
            $datas = wp_remote_retrieve_body($response);
            
            if(is_serialized($datas)){
                $data =  unserialize($datas);            
                $this->api_debug_log($data);
            }else{
                $this->api_debug_log($datas);    
            }
            
            if(!empty($data->subscription_data)){
                $subscription_data =  $data->subscription_data;
            }
        }else{
            
            $this->api_debug_log($response);
            $subscription_data = $response;    
        }

        return $subscription_data;
        
    }
    
    public function get_repository_site_key($repository_id){
        $site_key = false;
        
        if(!empty($this->settings['repositories'][$repository_id]['subscription']['key'])){
            $site_key = $this->settings['repositories'][$repository_id]['subscription']['key'];    
        }
        
        return $site_key;
    }
    
    public function repository_has_valid_subscription($repository_id){
        
        $valid = false;
        
        if(!empty($this->settings['repositories'][$repository_id]['subscription'])){
            
            $subscription = $this->settings['repositories'][$repository_id]['subscription']['data'];
            $valid = ( $subscription->status == 1 && (strtotime($subscription->expires) > time() || empty($subscription->expires)) ) || $subscription->status == 4;
            
        }
        return $valid;
        
    }
    
    public function repository_has_subscription($repository_id){
        $key = false;
        if(!empty($this->settings['repositories'][$repository_id]['subscription']['key'])){
            $key = $this->settings['repositories'][$repository_id]['subscription']['key'];
        }
        
        return $key;
        
    }
    
    public function repository_has_expired_subscription($repository_id){
        
        return $this->repository_has_subscription($repository_id) && !$this->repository_has_valid_subscription($repository_id);
        
    }
    
    public function get_generic_product_name($repository_id){
        
        return $this->settings['repositories'][$repository_id]['data']['product-name'];
        
    }
    
    public function show_subscription_renew_warning($repository_id, $subscription_id){
        
        $show = false;
        
        $data = $this->settings['repositories'][$repository_id]['data'];
        if(!empty($data['subscriptions_meta'])){
            if(isset($data['subscriptions_meta']['expiration'])){
                
                if(!empty($data['subscriptions_meta']['expiration'][$subscription_id])){
                    
                    $days       = $data['subscriptions_meta']['expiration'][$subscription_id]['days_warning'];
                    $message    = $data['subscriptions_meta']['expiration'][$subscription_id]['warning_message'];
                    
                }else{
                    
                    //defaults
                    $days       = 30;
                    $message    = __('You will have to renew your subscription in order to continue getting the updates and support.', 'installer');
                    
                }
                
                if(!empty($this->settings['repositories'][$repository_id]['subscription'])){
                    $subscription = $this->settings['repositories'][$repository_id]['subscription'];
                    
                    if($subscription['data']->subscription_type == $subscription_id && !empty($subscription['data']->expires)){
                        
                        if(strtotime($subscription['data']->expires) < strtotime(sprintf("+%d day", $days))){
                            
                            $days_to_expiration = ceil((strtotime($subscription['data']->expires) - time()) / 86400);
                            
                            echo '<div><p class="installer-warn-box">' .
                                sprintf(_n('Your subscription expires in %d day.', 'Your subscription expires in %d days.', $days_to_expiration, 'installer'), $days_to_expiration) . 
                                    '<br />' . $message .
                            '</p></div>';
                            
                            $show = true;
                            
                        }
                        
                    }
                        
                }
                                
                                
            }
        }
        

        return $show;
        
    }    
    
    public function setup_plugins_renew_warnings(){
        
        $plugins = get_plugins();
        
        $subscriptions_with_warnings = array();
        foreach($this->settings['repositories'] as $repository_id => $repository){
            
            if($this->repository_has_valid_subscription($repository_id)){
                $subscription_type = $this->settings['repositories'][$repository_id]['subscription']['data']->subscription_type;
                $expires           = $this->settings['repositories'][$repository_id]['subscription']['data']->expires;  
                
                $never_expires = isset($this->settings['repositories'][$repository_id]['subscription']) 
                                    && empty($this->settings['repositories'][$repository_id]['subscription']['data']->expires)
                                    && (
                                        $this->settings['repositories'][$repository_id]['subscription']['data']->status == 4 ||
                                        $this->settings['repositories'][$repository_id]['subscription']['data']->status == 1
                                    );
                                    
                if(!$never_expires){                
                    if(isset($this->settings['repositories'][$repository_id]['data']['subscriptions_meta']['expiration'][$subscription_type])){
                        
                        $days_warning = $this->settings['repositories'][$repository_id]['data']['subscriptions_meta']['expiration'][$subscription_type]['days_warning'];
                        $custom_message    = $this->settings['repositories'][$repository_id]['data']['subscriptions_meta']['expiration'][$subscription_type]['warning_message'];
                        
                    }else{
                        //defaults
                        $days_warning = 30;
                        $custom_message    = __('You will have to renew your subscription in order to continue getting the updates and support.', 'installer');
                    }                
                    
                    if(strtotime($expires) < strtotime(sprintf('+%d day', $days_warning)) ){
                        
                        $days_to_expiration = ceil((strtotime($expires) - time()) / 86400);
                        
                        $message = sprintf(_n('Your subscription expires in %d day.', 'Your subscription expires in %d days.', $days_to_expiration, 'installer'), $days_to_expiration);
                        $subscriptions_with_warnings[$subscription_type] = $message . ' ' . $custom_message;
                        
                    }
                }
                
            }
            
        }
         
        
        
        foreach($plugins as $plugin_id => $plugin){
            $slug = dirname($plugin_id);
            if(empty($slug)) continue;
            
            $name = $plugin['Name'];
            
            foreach($this->settings['repositories'] as $repository_id => $repository){
                
                if($this->repository_has_valid_subscription($repository_id)){
                    
                    foreach($repository['data']['packages'] as $package){
                        
                        foreach($package['products'] as $product){
                            
                            foreach($product['downloads'] as $download){
                                
                                if($download['name'] == $name && $download['basename'] == $slug){
                                        
                                    if(isset($subscriptions_with_warnings[$product['subscription_type']])){
                                        
                                        $this->_plugins_renew_warnings[$plugin_id] = $subscriptions_with_warnings[$product['subscription_type']];
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                    }                    
                    
                }
                
            }
            
        }
        
    }   
    
    public function queue_plugins_renew_warnings() {
        
        if(!empty($this->_plugins_renew_warnings)){
            
            foreach($this->_plugins_renew_warnings as $plugin_id => $message){
                
                add_action( "after_plugin_row_" . $plugin_id, array($this, 'plugins_renew_warning'), 10, 3 );   
            }
            
        }
        
    }
    
    public function plugins_renew_warning($plugin_file, $plugin_data, $status){
        
        if(empty($this->_plugins_renew_warnings[$plugin_file])) return;
        
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        ?>
        
        <tr class="plugin-update-tr"><td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="plugin-update colspanchange">
            <div class="update-message">
            <?php 
                echo $this->_plugins_renew_warnings[$plugin_file]. ' ';
                printf(__('%sRenew here%s.', 'installer'), 
                    '<a href="' . $this->menu_url() . '">', '</a>');
            ?>
            </div>
        </tr>
        
        <?php 
        
    }
        
    public function get_subscription_type_for_repository($repository_id){

        $subscription_type = false;
        
        if(!empty($this->settings['repositories'][$repository_id]['subscription'])){
            $subscription_type = $this->settings['repositories'][$repository_id]['subscription']['data']->subscription_type;    
        }
        
        return $subscription_type;
        
    }
    
    public function have_superior_subscription($subscription_type, $product){
        
        $have = false;
        
        if(is_array($product['upgrades'])){
            foreach($product['upgrades'] as $u){
                if($u['subscription_type'] == $subscription_type){
                    $have = true;
                    break;
                }
            }
        }
        
        return $have;
    }
    
    public function is_product_available_for_download($product_name, $repository_id){

        $available = false;

        $subscription_type = $this->get_subscription_type_for_repository($repository_id);
        $expired = $this->repository_has_expired_subscription($repository_id);

        if($this->repository_has_subscription($repository_id) && !$expired){

            $this->set_hierarchy_and_order();

            foreach($this->settings['repositories'][$repository_id]['data']['packages'] as $package_id => $package){

                $has_top_package = false;

                foreach($package['products'] as $product){

                    if($subscription_type == $product['subscription_type']){
                        $has_top_package = true;
                        if($product['name'] == $product_name){
                            return $available = true;
                        }                        
                    }
                    
                }

                if(!empty($package['sub-packages'])){
                    foreach($package['sub-packages'] as $sub_package){
                        foreach($sub_package['products'] as $product){
                            if($product['name'] == $product_name && ($subscription_type == $product['subscription_type'] || $has_top_package)){
                                return $available = true;
                            }
                        }
                    }
                }

            }
        }

        return $available;
        
    }

    public function get_upgrade_options($repository_id){
        $all_upgrades = array();

        //get all products: packages and subpackages
        $all_products = array();
        foreach($this->settings['repositories'][$repository_id]['data']['packages'] as $package){
            foreach($package['products'] as $product) {
                $all_products[] = $product;
            }
            if(!empty($package['sub-packages'])){
                foreach($package['sub-packages'] as $subpackage){
                    foreach($subpackage['products'] as $product) {
                        $all_products[] = $product;
                    }

                }

            }

        }

        foreach($all_products as $product) {
            if ($product['upgrades']) {
                foreach ($product['upgrades'] as $upgrade) {
                    if ($this->repository_has_valid_subscription($repository_id) || ($this->repository_has_subscription($repository_id) && $upgrade['including_expired'])) {
                        $all_upgrades[$upgrade['subscription_type']][$product['subscription_type']] = $upgrade;
                    }
                }
            }
        }

        return $all_upgrades;
        
    }
    
    public function append_site_key_to_download_url($url, $key, $repository_id){
        
        $url = add_query_arg(array('site_key' => $key, 'site_url' => $this->get_installer_site_url($repository_id) ), $url);
        
        return $url;
            
    }
    
    public function plugin_is_installed($name, $folder, $version = null){
        
        $is = false;
        
        $plugins = get_plugins();
        
        foreach($plugins as $plugin_id => $plugin){
            
            // Exception: embedded plugins
            if(($plugin['Name'] == $name && dirname($plugin_id) == $folder) || ($plugin['Name'] == $name . ' Embedded' && dirname($plugin_id) == $folder . '-embedded') ){                
                if($version){
                    if(version_compare($plugin['Version'], $version, '>=')){
                        $is = $plugin['Version'];    
                    }                        
                }else{
                    $is = $plugin['Version'];    
                }
                
                break;
            }
            
        }
        
        return $is;
    }

    public function plugin_is_embedded_version($name, $basename){

        $is = false;

        if($this->plugin_is_installed($name, $basename)){
            return false;
        }
        
        $plugins = get_plugins();
        
        foreach($plugins as $plugin_id => $plugin){
            
            // TBD
            if( dirname($plugin_id) == $basename . '-embedded' &&  $plugin['Name'] == $name . ' Embedded'){                          
                $is = true;                
                break;
            }

        }
        
        return $is;

    }

    //Alias for plugin_is_installed
    public function get_plugin_installed_version($name, $plugin_basename){

        return $this->plugin_is_installed($name, $plugin_basename);
        
    }

    public function get_plugin_repository_version($repository_id, $plugin_basename){
        $version = false;

        if(!empty($this->settings['repositories'][$repository_id]['data']['packages'])){
            foreach($this->settings['repositories'][$repository_id]['data']['packages'] as $package){
                foreach($package['products'] as $product) {
                    
                    foreach($product['downloads'] as $download){

                        if($download['basename'] == $plugin_basename){
                            $version  = $download['version'];
                            break (3);
                        }

                    }

                }
            }
        }

        return $version;
    }

    public function is_uploading_allowed(){

        if(!isset($this->uploading_allowed)){
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once WP_Installer()->plugin_path() . '/includes/installer-upgrader-skins.php';

            $upgrader_skins = new Installer_Upgrader_Skins(); //use our custom (mute) Skin
            $upgrader = new Plugin_Upgrader($upgrader_skins);

            ob_start();
            $res = $upgrader->fs_connect( array(WP_CONTENT_DIR, WP_PLUGIN_DIR) );
            ob_end_clean();

            if ( ! $res || is_wp_error( $res ) ) {
                $this->uploading_allowed = false;
            }else{
                $this->uploading_allowed = true;
            }
        }


        return $this->uploading_allowed;

    }

    public function download_plugin_ajax_handler(){

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once $this->plugin_path() . '/includes/installer-upgrader-skins.php';
        
        if(isset($_POST['data'])){

            $data    = json_decode( base64_decode( $_POST['data'] ), true );

        }

        $ret        = false;
        $plugin_id  = false;
        $message    = '';
        
        //validate subscription
        $site_key = $this->get_repository_site_key($data['repository_id']);
        $subscription_data = $this->fetch_subscription_data($data['repository_id'], $site_key);

        if($subscription_data && !is_wp_error($subscription_data) && $this->repository_has_valid_subscription($data['repository_id'])){
                
            if($data['nonce'] == wp_create_nonce('install_plugin_' . $data['url'])){
                
                $upgrader_skins = new Installer_Upgrader_Skins(); //use our custom (mute) Skin
                $upgrader = new Plugin_Upgrader($upgrader_skins);
                
                remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
                
                $plugins = get_plugins();
                            
                //upgrade or install?
                foreach($plugins as $id => $plugin){
                    if(dirname($id) == $data['basename']){                    
                        $plugin_id = $id;
                        break;
                    }    
                }
                
                if($plugin_id){ //upgrade
                    $response['upgrade'] = 1;
                    
                    $plugin_is_active = is_plugin_active($plugin_id);                

                    $ret = $upgrader->upgrade($plugin_id);    
                    
                    if(!$ret && !empty($upgrader->skin->installer_error)){
                        if(is_wp_error($upgrader->skin->installer_error)){
                            $message = $upgrader->skin->installer_error->get_error_message() . 
                                ' (' . $upgrader->skin->installer_error->get_error_data() . ')';
                        }                        
                    }
                    
                    if($plugin_is_active){
                        //prevent redirects
                        add_filter('wp_redirect', '__return_false');
                        activate_plugin($plugin_id);
                    }
                    
                }else{ //install
                
                    $response['install'] = 1;
                    $ret = $upgrader->install($data['url']);    
                    if(!$ret && !empty($upgrader->skin->installer_error)){
                        if(is_wp_error($upgrader->skin->installer_error)){
                            $message = $upgrader->skin->installer_error->get_error_message() . 
                                ' (' . $upgrader->skin->installer_error->get_error_data() . ')';
                        }                        
                    }
                }
                
                $plugins = get_plugins(); //read again
                if($ret && !empty($_POST['activate'])){
                    foreach($plugins as $id => $plugin){
                        if(dirname($id) == $data['basename']){                    
                            $plugin_version = $plugin['Version'];
                            $plugin_id = $id;
                            break;
                        }    
                    }
                    
                }
                
            }
            
        } else { //subscription not valid
        
            $ret = false;
            $message = __('Your subscription appears to no longer be valid. Please try to register again using a valid site key.', 'installer');
        }
            
        $response['version']     = isset($plugin_version) ? $plugin_version : 0;
        $response['plugin_id']   = $plugin_id;
        $response['nonce']       = wp_create_nonce('activate_' . $plugin_id);
        $response['success']     = $ret;
        $response['message']     = $message;
        
        echo json_encode( $response );
        exit;
        
    }

    public function download_plugin($basename, $url){

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once $this->plugin_path() . '/includes/installer-upgrader-skins.php';

        $upgrader_skins = new Installer_Upgrader_Skins(); //use our custom (mute) Skin
        $upgrader = new Plugin_Upgrader($upgrader_skins);

        remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

        $plugins = get_plugins();

        $plugin_id = false;

        //upgrade or install?
        foreach($plugins as $id => $plugin){
            if(dirname($id) == $basename){
                $plugin_id = $id;
                break;
            }
        }

        if($plugin_id){ //upgrade

            $plugin_is_active = is_plugin_active($plugin_id);

            $ret = $upgrader->upgrade($plugin_id);

            if($plugin_is_active){
                activate_plugin($plugin_id);
            }

        }else{ //install
            $ret = $upgrader->install($url);
        }

        return $ret;

    }

    public function activate_plugin(){
        
        $error = '';
        
        if(isset($_POST['nonce']) &&  isset($_POST['plugin_id']) && $_POST['nonce'] == wp_create_nonce('activate_' . $_POST['plugin_id'])){

            $plugin_id = $_POST['plugin_id'];

            // Deactivate any embedded version
            $plugin_folder = dirname($plugin_id);
            $active_plugins = get_option('active_plugins');
            foreach($active_plugins as $plugin){
                if(dirname($plugin) == $plugin_folder . '-embedded'){
                    deactivate_plugins(array($plugin));
                    break;
                }
            }

            //prevent redirects
            add_filter('wp_redirect', '__return_false', 10000);

            $return = activate_plugin($plugin_id);

            if(is_wp_error($return)){
                $error = $return->get_error_message();                
            }
            
        }else{
            $error = 'error';    
        }
        
        $ret = array('error' => $error);
        
        echo json_encode($ret);
        exit;
        
    }
    
    public function custom_plugins_api_call($false, $action, $args){
            
        if($action == 'plugin_information'){
            
            $slug = $args->slug;
            
            foreach($this->settings['repositories'] as $repository_id => $repository){
                
                foreach($repository['data']['packages'] as $package){
                    
                    foreach($package['products'] as $product){
                        
                        foreach($product['downloads'] as $download){
                            
                            if($download['basename'] == $slug){
                                
                                $res = new stdClass();
                                $res->external = true;
                                
                                $res->name = $download['name'];
                                $res->slug = $slug;
                                $res->version = $download['version'];
                                $res->author = '';
                                $res->author_profile = '';
                                $res->last_updated = $download['date'];
                                //$res->homepage = $download['url'];
                                $res->homepage = $repository['data']['url'];
                                $res->sections = array('Description' => $download['description'], 'Changelog' => $download['changelog']);
                                
                                return $res;
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        return $false;
                
    }
    
    public function plugins_upgrade_check($update_plugins){

        if(!empty($this->settings['repositories'])){
            $plugins = get_plugins();
            
            foreach($plugins as $plugin_id => $plugin){
                
                $slug = dirname($plugin_id);
                if(empty($slug)) continue;
                
                $version = $plugin['Version'];
                $name = $plugin['Name'];
            
                foreach($this->settings['repositories'] as $repository_id => $repository){
                    
                    if(!$this->repository_has_valid_subscription($repository_id)){
                        $site_key = false;
                    }else{
                        $site_key = $repository['subscription']['key']; 
                        //$subscription_type = $this->get_subscription_type_for_repository($repository_id);
                    }
                    
                    foreach($repository['data']['packages'] as $package){
                        
                        foreach($package['products'] as $product){
                            
                            foreach($product['downloads'] as $download){
                                
                                if(empty($update_plugins->response[$plugin_id]) && $download['name'] == $name && $download['basename'] == $slug && version_compare($download['version'], $version, '>')){
                                    
                                    $response = new stdClass();
                                    $response->id = 0;
                                    $response->slug = $slug;
                                    $response->plugin = $plugin_id;
                                    $response->new_version = $download['version'];
                                    $response->upgrade_notice = '';
                                    $response->url = $download['url'];
                                    if($site_key){
                                        $response->package = $this->append_site_key_to_download_url($download['url'], $site_key, $repository_id);
                                    }                                
                                    $update_plugins->checked[$plugin_id]  = $version;
                                    $update_plugins->response[$plugin_id] = $response;
                                    
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        return $update_plugins;
        
    }
    
    public function setup_plugins_page_notices(){
        
        $plugins = get_plugins();
        
        foreach($plugins as $plugin_id => $plugin){
            
            $slug = dirname($plugin_id);
            if(empty($slug)) continue;
            
            $name = $plugin['Name'];
        
            foreach($this->settings['repositories'] as $repository_id => $repository){
                
                if(!$this->repository_has_valid_subscription($repository_id)){
                    $site_key = false;
                }else{
                    $site_key = $repository['subscription']['key']; 
                }
                
                foreach($repository['data']['packages'] as $package){
                    
                    foreach($package['products'] as $product){
                        
                        foreach($product['downloads'] as $download){
                            
                            if($download['name'] == $name && $download['basename'] == $slug){
                                
                                if(!$site_key){
                                    add_action( "after_plugin_row_" . $plugin_id, array($this, 'show_purchase_notice_under_plugin'), 10, 3 );
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }        
        
    }
    
    public function show_purchase_notice_under_plugin($plugin_file, $plugin_data, $status){
        
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        ?>
        
        <tr class="plugin-update-tr"><td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="plugin-update colspanchange">
            <div class="update-message installer-q-icon">
            <?php 
                printf(__('You need to have a valid subscription in order to get upgrades or support for this plugin. %sPurchase a subscription or enter an existing site key%s.', 'installer'), 
                    '<a href="' . $this->menu_url() . '">', '</a>');
            ?>
            </div>
        </tr>
        
        <?php 
        
    }
    
    public function localize_strings(){
        global $sitepress;

        if(!empty($this->settings['repositories'])){
            foreach($this->settings['repositories'] as $repository_id => $repository){
                //set name as call2action when don't have any
                //products
                foreach($repository['data']['packages'] as $package_id => $package){
                    foreach($package['products'] as $product_id => $product){
                        if(empty($product['call2action'])){
                            $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['call2action'] = $product['name'];
                        }
                        
                        foreach($product['upgrades'] as $idx => $upg){
                            if(empty($upg['call2action'])){
                                $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['upgrades'][$idx]['call2action'] = $upg['name'];
                            }                            
                        }
                        
                        foreach($product['renewals'] as $idx => $rnw){
                            if(empty($rnw['call2action'])){
                                $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['renewals'][$idx]['call2action'] = $rnw['name'];
                            }                            
                            
                        }
                        
                    }
                }                
            }
        }
        
        if(is_null($sitepress)) return;
        
        // default strings are always in English
        $user_admin_language = $sitepress->get_admin_language();
        
        if($user_admin_language != 'en'){
            foreach($this->settings['repositories'] as $repository_id => $repository){
                
                $localization = $repository['data']['localization'];
                
                //packages
                foreach($repository['data']['packages'] as $package_id => $package){
                    
                    if( isset($localization['packages'][$package_id]['name'][$user_admin_language]) ){
                        $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['name'] = $localization['packages'][$package_id]['name'][$user_admin_language];    
                    }
                    if( isset($localization['packages'][$package_id]['description'][$user_admin_language]) ){
                        $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['description'] = $localization['packages'][$package_id]['description'][$user_admin_language];    
                    }
                    
                }
                
                //products
                foreach($repository['data']['packages'] as $package_id => $package){
                    foreach($package['products'] as $product_id => $product){
                        
                        if( isset($localization['products'][$product_id]['name'][$user_admin_language]) ){
                            $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['name'] 
                                = $localization['products'][$product_id]['name'][$user_admin_language];    
                        }
                        if( isset($localization['products'][$product_id]['description'][$user_admin_language]) ){
                            $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['description'] 
                                = $localization['products'][$product_id]['description'][$user_admin_language];    
                        }
                        if( isset($localization['products'][$product_id]['call2action'][$user_admin_language]) ){
                            $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['name'] 
                                = $localization['products'][$product_id]['call2action'][$user_admin_language];    
                        }
                        
                        
                    }
                }
                
                //subscription info
                if(isset($repository['data']['subscriptions_meta']['expiration'])){
                    foreach($repository['data']['subscriptions_meta']['expiration'] as $subscription_id => $note){
                        if(isset($localization['subscriptions-notes'][$subscription_id]['expiration-warning'][$user_admin_language])){
                            $this->settings['repositories'][$repository_id]['data']['subscriptions_meta']['expiration'][$subscription_id]['warning_message'] 
                                = $localization['subscriptions-notes'][$subscription_id]['expiration-warning'][$user_admin_language];    
                        }
                    }
                }
                
            }
        }
        
    }
    
    public function get_matching_cp($repository, $args = array()){
        $match = false;
        
        
        $cp_name = $cp_author = false;
        
        if(isset($this->config['src_name']) && isset($this->config['src_author'])){
            
            $cp_name    = $this->config['src_name'];
            $cp_author  = $this->config['src_author'];
            
        }elseif(isset($args['src_name']) && isset($args['src_author'])){
            
            $cp_name    = $args['src_name'];
            $cp_author  = $args['src_author'];
            
        }
        
        if(isset($repository['data']['marketing_cp'])){
            
            foreach($repository['data']['marketing_cp'] as $cp){
                
                if(!empty($cp['exp']) && time() > $cp['exp']){
                    continue;
                }
                
                //Use theme_name for plugins too
                if(!empty($cp['theme_name'])){
                    if($cp['author_name'] == $cp_author && $cp['theme_name'] == $cp_name){
                        $match = $cp;
                        continue;
                    }                    
                }else{
                    if($cp['author_name'] == $cp_author){
                        $match = $cp;
                        continue;
                    }                    
                }
                
            }
            
        }
        
        return $match;
    }
    
    public function set_filtered_prices($args = array()){
        
        foreach($this->settings['repositories'] as $repository_id => $repository){
            
            $match = $this->get_matching_cp($repository, $args);
            
            if(empty($match)) continue;
            
            foreach($repository['data']['packages'] as $package_id => $package){
                
                foreach($package['products'] as $product_id => $product){

                    if($match['dtp'] == '%'){
                        $fprice = round( $product['price'] * (1 - $match['amt']/100), 2 );
                        $fprice = $fprice != round($fprice) ? sprintf('%.2f', $fprice) : round($fprice, 0);
                    }elseif($match['dtp'] == '-'){
                        $fprice = $product['price'] - $match['amt'];
                    }else{
                        $fprice = $product['price'];
                    }

                    if($fprice){
                        $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['price_disc'] = $fprice;
                        
                        $url_glue = false !== strpos($this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['url'], '?') ? '&' : '?';
                        $cpndata = base64_encode(json_encode(array('theme_author' => $match['author_name'], 'theme_name' => $match['theme_name'], 'vlc' => $match['vlc'])));
                        $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['url'] .= $url_glue . 'cpn=' . $cpndata;
                    
                        foreach($product['upgrades'] as $upgrade_id => $upgrade){
                            
                            $fprice = false;
                            if($match['dtp'] == '%'){
                                $fprice = round( $upgrade['price'] * (1 - $match['amt']/100), 2 );
                                $fprice = $fprice != round($fprice) ? sprintf('%.2f', $fprice) : round($fprice, 0);
                            }elseif($match['dtp'] == '-'){
                                $fprice = $upgrade['price'] - $match['amt'];
                            }
                            if($fprice){
                                $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['upgrades'][$upgrade_id]['price_disc'] = $fprice;    
                                $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['upgrades'][$upgrade_id]['url'] .= $url_glue . 'cpn=' . $cpndata;
                            }
                            
                            
                        }
                    
                    }
                    
                }
                
            }
            
        }
        
    }

    public function set_hierarchy_and_order(){

        //2 levels
        if(!empty($this->settings['repositories'])) {
            foreach ($this->settings['repositories'] as $repository_id => $repository) {

                if( empty( $repository['data']['packages'] ) ) continue;

                $all_packages = $repository['data']['packages'];
                $ordered_packages = array();

                //backward compatibility - 'order'
                foreach($all_packages as $k => $v){
                    if(!isset($v['order'])){
                        $all_packages[$k]['order'] = 0;
                    }
                }

                //select parents
                foreach ($all_packages as $package_id => $package) {
                    if(empty($package['parent'])){
                        $ordered_packages[$package_id] = $package;
                    }
                }

                //add sub-packages
                foreach($all_packages as $package_id => $package){
                    if(!empty($package['parent'])) {
                        if(isset($ordered_packages[$package['parent']])){
                            $ordered_packages[$package['parent']]['sub-packages'][$package_id] = $package;
                        }
                    }
                }

                // order parents
                usort($ordered_packages, array($this, '_order_packages_callback'));
                //order sub-packages
                foreach($ordered_packages as $package_id => $package){
                    if(!empty($package['sub-packages'])) {
                        usort($ordered_packages[$package_id]['sub-packages'], create_function('$a, $b', 'return $a[\'order\'] > $b[\'order\'];'));
                    }
                }

                $this->settings['repositories'][$repository_id]['data']['packages'] = $ordered_packages;


            }
        }


    }

    public function _order_packages_callback($a, $b){
        return $a['order'] > $b['order'];
    }

    public function filter_downloads_by_icl(){
        if(function_exists('wpml_site_uses_icl') && wpml_site_uses_icl()){

            if(!empty($this->settings['repositories'])) {
                foreach ($this->settings['repositories'] as $repository_id => $repository) {

                    if (empty($repository['data']['packages'])) continue;

                    foreach ($repository['data']['packages'] as $package_id => $package) {
                        foreach($package['products'] as $product_id => $product){

                            foreach($product['downloads'] as $download_id => $download){
                                $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['downloads'][$download_id]['changelog'] = '';
                                $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['downloads'][$download_id]['description'] = '';

                                if(isset($download['version-for-icl']) && isset($download['url-for-icl'])){
                                    $download['version'] = $download['version-for-icl'];
                                    $download['url'] = $download['url-for-icl'];
                                    unset($download['version-for-icl']);
                                    unset($download['url-for-icl']);
                                    $this->settings['repositories'][$repository_id]['data']['packages'][$package_id]['products'][$product_id]['downloads'][$download_id] = $download;

                                }

                            }
                        }

                    }

                }
            }

        }
    }

    public function get_support_tag_by_name( $name, $repository ){

        if( is_array($this->settings['repositories'][$repository]['data']['support_tags'] )){
            foreach( $this->settings['repositories'][$repository]['data']['support_tags'] as $support_tag){
                if( $support_tag['name'] == $name ){
                    return $support_tag['url'];
                }
            }
        }

        return false;
    }

    public function plugin_upgrade_custom_errors(){

        if ( isset($_REQUEST['action']) ) {

            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

            //bulk mode
            if('update-selected' == $action) {

                global $plugins;

                if(isset($plugins) && is_array($plugins)) {

                    foreach ($plugins as $k => $plugin) {
                        $plugin_repository = false;

                        foreach ($this->settings['repositories'] as $repository_id => $repository) {

                            foreach ($repository['data']['packages'] as $package) {

                                foreach ($package['products'] as $product) {

                                    foreach ($product['downloads'] as $download) {

                                        //match by folder, will change to match by name and folder
                                        if ($download['basename'] == dirname($plugin)) {
                                            $plugin_repository = $repository_id;
                                            $product_name = $repository['data']['product-name'];
                                            $plugin_name = $download['name'];
                                            break;
                                        }

                                    }

                                }

                            }

                        }

                        if ($plugin_repository) {

                            //validate subscription
                            static $sub_cache = array();

                            if(empty($sub_cache[$plugin_repository])){
                                $site_key = $this->get_repository_site_key($plugin_repository);
                                if ($site_key) {
                                    $subscription_data = $this->fetch_subscription_data($plugin_repository, $site_key);
                                }
                                $sub_cache[$plugin_repository]['site_key']             = $site_key;
                                $sub_cache[$plugin_repository]['subscription_data']    = isset($subscription_data) ? $subscription_data : false;
                            }else{

                                $site_key           = $sub_cache[$plugin_repository]['site_key'];
                                $subscription_data  = $sub_cache[$plugin_repository]['subscription_data'];

                            }

                            if (empty($site_key) || empty($subscription_data)) {


                                $error_message = sprintf(__("%s cannot update because your site's registration is not valid. Please %sregister %s%s again for this site first.", 'installer'),
                                    '<strong>' . $plugin_name . '</strong>', '<a target="_top" href="' . $this->menu_url() . '&validate_repository=' . $plugin_repository .
                                    '#repository-' . $plugin_repository . '">', $product_name, '</a>');

                                echo '<div class="updated error"><p>' . $error_message . '</p></div>';

                                unset($plugins[$k]);


                            }

                        }

                    }

                }

            }


            if( 'upgrade-plugin' == $action || 'update-plugin' == $action ) {

                $plugin = isset($_REQUEST['plugin']) ? trim($_REQUEST['plugin']) : '';

                $plugin_repository = false;

                foreach($this->settings['repositories'] as $repository_id => $repository){

                    foreach($repository['data']['packages'] as $package){

                        foreach($package['products'] as $product){

                            foreach($product['downloads'] as $download){

                                //match by folder, will change to match by name and folder
                                if($download['basename'] == dirname($plugin)) {
                                    $plugin_repository = $repository_id;
                                    $product_name = $repository['data']['product-name'];
                                    $plugin_name = $download['name'];
                                    break;
                                }

                            }

                        }

                    }

                }

                if($plugin_repository) {

                    //validate subscription
                    $site_key = $this->get_repository_site_key($plugin_repository);
                    if ($site_key) {
                        $subscription_data = $this->fetch_subscription_data($plugin_repository, $site_key);
                    }

                    if (empty($site_key) || empty($subscription_data)) {

                        $error_message = sprintf(__("%s cannot update because your site's registration is not valid. Please %sregister %s%s again for this site first.", 'installer'),
                            '<strong>'.$plugin_name . '</strong>', '<a href="' . $this->menu_url() . '&validate_repository=' . $plugin_repository .
                            '#repository-' . $plugin_repository . '">', $product_name, '</a>');

                        if(defined('DOING_AJAX')){ //WP 4.2

                            $status = array(
                                'update'     => 'plugin',
                                'plugin'     => $plugin,
                                'slug'       => sanitize_key( $_POST['slug'] ),
                                'oldVersion' => '',
                                'newVersion' => '',
                            );

                            $status['errorCode'] = 'wp_installer_invalid_subscription';
                            $status['error'] = $error_message;

                            wp_send_json_error( $status );

                        } else { // WP 4.1.1
                            echo '<div class="updated error"><p>' . $error_message . '</p></div>';


                            echo '<div class="wrap">';
                            echo '<h2>' . __('Update Plugin') . '</h2>';
                            echo '<a href="' . admin_url('plugins.php') . '">' . __('Return to the plugins page') . '</a>';
                            echo '</div>';
                            require_once(ABSPATH . 'wp-admin/admin-footer.php');
                            exit;

                        }

                    }


                }

            }
        }

    }

}
