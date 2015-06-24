<?php
/**
 *
 * Types Tutorial Class
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/classes/class.wpcf-marketing-tutorial.php $
 * $LastChangedDate: 2015-02-18 14:28:53 +0000 (Wed, 18 Feb 2015) $
 * $LastChangedRevision: 1093394 $
 * $LastChangedBy: iworks $
 *
 */

include_once dirname(__FILE__).'/class.wpcf-marketing.php';

/**
 * Types Tutorial Class
 *
 * @since Types 1.6.5
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Help
 * @author marcin <marcin.p@icanlocalize.com>
 */
class WPCF_Types_Marketing_Tutorial extends WPCF_Types_Marketing
{
    private $id;
    private $cache;
    private $tutorials;

    public function __construct()
    {
        parent::__construct();
    }

    private function error($error_id, $message = false)
    {
        $content = wpcf_add_admin_header(__('Tutorial error', 'wpcf'));
        $content .= '<div class="error settings-error"><p><strong>';
        switch( $error_id ) {
        case 'no id':
        case 'wrong id':
            $content .= __('Wrong tutorial id.', 'wpcf');
            break;
        case 'empty url':
        case 'wrong response status':
        case 'http request failed':
            $content .= __('There is a problem with tutorial url.', 'wpcf');
            break;
        case 'empty body':
            $content .= __('Selected tutorial is empty.', 'wpcf');
            if ( current_user_can('manage_options') ) {
            }
            break;
        default:
            if ( $message ) {
                $content .= $message;
            } else {
                $content .= __('Some error occured.', 'wpcf');
            }
        }
        $content .= '</strong></p></div>';
        return $content;
    }

    private function produce($url = false)
    {
        if ( empty( $url ) ) {
            $url = $this->get('url');
        }
        if ( empty($url) ) {
            return $this->error('empty url');
        }
        $url = $this->add_ga_campain($url, 'fetch-data');

        $resp = wp_remote_get($url);

        if ( is_wp_error( $resp ) ) {
            /**
             * if user can manage_options then display a real error message
             */
            if( current_user_can('manage_options') ) {
                return $this->error(false, $resp->get_error_message());
            } else {
                return $this->error('http request failed');
            }
        }

        if ( 200 != $resp['response']['code'] ) {
            return $this->error('wrong response status');
        }

        $title = preg_split('/<header class="masthead">/', $resp['body']);
        $title = preg_split('/<h1>/', $title[1]);
        $title = preg_split('@</h1>@', $title[1]);
        $title = $title[0];

        $body = '';
        $containers = preg_split( '/<div class="container">/', $resp['body'] );
        foreach( $containers as $container ) {
            if ( !preg_match('/<div class="col-sm-[\d]+ post-content[^>]+>/', $container) ) {
                continue;
            }
            $body = $container;

        }
        if ( empty( $body ) ) {
            return $this->error('empty body');
        }
        $body = preg_split('/<aside/', $body);
        $body = $body[0];
        if ( empty( $body ) ) {
            return $this->error('empty body');
        }
        $body = sprintf(
            '<h1 class="title">%s</h1><div class="container"><div class="post-content">%s',
            $title,
            $body
        );
        set_transient( $this->cache, $body, 14 * DAY_IN_SECONDS);
        return $body;
    }

    private function add_select_site_kind_intruction()
    {
        $kind = $this->get_kind();
        if ( empty($kind) ) {
            return;
        }
        $content = '';
        /**
         * current url
         */
        $current_url = add_query_arg(
            array( 'page' => basename(WPCF_ABSPATH).'/marketing/getting-started/index.php',),
            admin_url('admin.php')
        );
        /**
         * add button to change site kind
         */
        $content .= sprintf(
            '<a class="button" href="%s">%s</a>',
            add_query_arg( array( 'kind' => 'choose',), $current_url),
            __('Select instructions for other kinds of sites', 'wpcf')
        );
        /**
         * add reload link
         */
        $content .= sprintf(
            ' <a class="alignright" href="%s">%s</a>',
            wp_nonce_url($current_url, 'reload', 'toolset'),
            __('Reload', 'wpcf')
        );
        return sprintf( '<div class="container wpcf-tutorial-other wpcf-notif"><p>%s</p></div>', $content);
    }

    public function get_content()
    {
        $class = ' class="wp-types-icon-external" ';
        $target = ' target="_blank" ';
        
        $url = $this->get_kind_url();
        $this->cache = md5($url);
        $content = get_transient($this->cache);
        /**
         * check force reload
         */
        $force_reload = isset($_GET['toolset']) && wp_verify_nonce($_GET['toolset'], 'reload');
        if ( $force_reload || false === apply_filters( 'tooleset_messages_get_transient', $content ) ) {
            $content = $this->produce($url);
        }
        /**
         * create array to replace
         */
        $replces = array(
            'from' => array(),
            'to' => array(),
        );
        
        $content = preg_replace('/(<a.*?)[ ]?target="_blank"(.*?)/', '$1$2', $content);
        
        /**
         * with '
         */
        preg_match_all('/href=\'([^\']+)\'/', $content, $matches );
        if ( $matches ) {
            foreach ( $matches[1] as $url ) {
                if ( !preg_match('/wp-types.com/', $url ) ) {
                    continue;
                }                
                $replces['from'][] = sprintf("|'%s'|", $url);
                $replces['to'][] = sprintf( "'%s'", $this->add_ga_campain($url).$class.$target);
            }
        }
        /**
         * with "
         */
        preg_match_all('/href="([^"]+)"/', $content, $matches );
        if ( $matches ) {
            foreach ( $matches[1] as $url ) {                
                if ( !preg_match('/wp-types.com/', $url ) ) {                    
                    continue;
                }
                $replces['from'][] = sprintf('|"%s"|', $url);
                $replces['to'][] = sprintf( '"%s"', $this->add_ga_campain($url)).$class.$target;
            }
        }
        
        //WP-Types External
        
        /**
         * with '
         */
        preg_match_all('/href=\'([^\']+)\'/', $content, $matches );
        if ( $matches ) {
            foreach ( $matches[1] as $url ) {
                if ( preg_match('/wp-types.com/', $url ) ) {
                    continue;
                }                
                $replces['from'][] = sprintf("|'%s'|", $url);
                $replces['to'][] = sprintf( "'%s'", $this->add_ga_campain($url).$class.$target);
            }
        }
        /**
         * with "
         */
        preg_match_all('/href="([^"]+)"/', $content, $matches );
        if ( $matches ) {
            foreach ( $matches[1] as $url ) {                
                if ( preg_match('/wp-types.com/', $url ) ) {                    
                    continue;
                }
                $replces['from'][] = sprintf('|"%s"|', $url);
                $replces['to'][] = sprintf( '"%s"', $this->add_ga_campain($url)).$class.$target;
            }
        }        
        
        
        
        if (count($replces['from'])) {
            $content = preg_replace( $replces['from'], $replces['to'], $content );
        }
        $content .= $this->add_select_site_kind_intruction();
        return $content;
    }

}
