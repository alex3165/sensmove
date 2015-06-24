<?php
/**
 * WPCF_Path
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/classes/path.php $
 * $LastChangedDate: 2014-11-18 06:47:25 +0000 (Tue, 18 Nov 2014) $
 * $LastChangedRevision: 1027712 $
 * $LastChangedBy: iworks $
 *
 */
final class WPCF_Path
{

    /**
     * Fix $_SERVER variables for various setups.
     *
     * @access private
     * @since 3.0.0
     */
    public static function fixServerVars()
    {
        global $PHP_SELF;
        static $fixedvars = false;

        if ( $fixedvars )
            return;

        $default_server_values = array(
            'SERVER_SOFTWARE' => '',
            'REQUEST_URI' => '',
        );

        $_SERVER = array_merge( $default_server_values, $_SERVER );

        // Fix for IIS when running with PHP ISAPI
        if ( empty( $_SERVER['REQUEST_URI'] ) || ( php_sapi_name() != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//',
                        $_SERVER['SERVER_SOFTWARE'] ) ) ) {

            // IIS Mod-Rewrite
            if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) {
                $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
            }
            // IIS Isapi_Rewrite
            else if ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
                $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
            } else {
                // Use ORIG_PATH_INFO if there is no PATH_INFO
                if ( !isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) )
                    $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];

                // Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
                if ( isset( $_SERVER['PATH_INFO'] ) ) {
                    if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
                        $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
                    else
                        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
                }

                // Append the query string if it exists and isn't null
                if ( !empty( $_SERVER['QUERY_STRING'] ) ) {
                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                }
            }
        }

        // Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
        if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'],
                        'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
            $_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

        // Fix for Dreamhost and other PHP as CGI hosts
        if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false )
            unset( $_SERVER['PATH_INFO'] );

        // Fix empty PHP_SELF
        $PHP_SELF = $_SERVER['PHP_SELF'];
        if ( empty( $PHP_SELF ) )
            $_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace( '/(\?.*)?$/', '',
                    $_SERVER["REQUEST_URI"] );

        $fixedvars = true;
    }

    // http://stackoverflow.com/questions/4049856/replace-phps-realpath
    public static function truepath( $path )
    {
        if ( function_exists( 'realpath' ) )
            return realpath( $path );

        // whether $path is unix or not
        $unipath = strlen( $path ) == 0 || $path{0} != '/';

        // attempts to detect if path is relative in which case, add cwd
        if ( strpos( $path, ':' ) === false && $unipath )
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;

        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace( array('/', '\\'), DIRECTORY_SEPARATOR, $path );
        $parts = array_filter( explode( DIRECTORY_SEPARATOR, $path ), 'strlen' );

        $absolutes = array();
        foreach ( $parts as $part ) {
            if ( '.' == $part )
                continue;
            if ( '..' == $part )
                array_pop( $absolutes );
            else
                $absolutes[] = $part;
        }
        $path = implode( DIRECTORY_SEPARATOR, $absolutes );

        // resolve any symlinks
        if ( file_exists( $path ) && linkinfo( $path ) > 0 )
            $path = readlink( $path );

        // put initial separator that could have been lost
        $path = !$unipath ? '/' . $path : $path;

        return $path;
    }

    // http://gr2.php.net/php_uname
    // http://stackoverflow.com/questions/5879043/php-script-detect-whether-running-under-linux-or-windows
    // http://stackoverflow.com/questions/1482260/how-to-get-the-os-on-which-php-is-running
    public static function getOS()
    {
        return (object) array(
                    'isNIX' => (bool) ('/' == DIRECTORY_SEPARATOR && ':' == PATH_SEPARATOR),
                    'isMAC' => (bool) ('/' == DIRECTORY_SEPARATOR && ':' == PATH_SEPARATOR),
                    'isWIN' => (bool) ('\\' == DIRECTORY_SEPARATOR && ';' == PATH_SEPARATOR)
        );
    }

    // http://www.helicron.net/php-document-root/
    // http://php.net/manual/en/reserved.variables.server.php
    // http://php.net/manual/en/function.realpath.php
    // http://stackoverflow.com/questions/9151949/root-directory-with-php-on-apache-and-iis
    // variation used here
    public static function getDocRoot( $manual = false )
    {
        static $cache = array();
        $cache_key = $manual ? 'manual' : 'auto';
        if ( isset( $cache[$cache_key] ) ) {
            return $cache[$cache_key];
        }


        self::fixServerVars();

        /*
         * Found issue with amazon server.
         * https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/164974335/comments#comment_237904711
         * https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/159425489/comments
         * Server settings aren't quite right so force manual check.
         * docroot is determined manually if file do not match docroot.
         */

        // not available in IIS
        if ( $manual === false && isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
            $docroot = $_SERVER['DOCUMENT_ROOT'];
        } else {
            // for IIS
            // these should always be available, Apache, IIS, .., PHP 4.1.0+, ..
            if ( !empty( $_SERVER['SCRIPT_FILENAME'] ) ) {
                //$docroot = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
                $docroot = str_replace( '\\', '/',
                        self::str_before( $_SERVER['SCRIPT_FILENAME'],
                                $_SERVER['SCRIPT_NAME'] ) );
            } elseif ( !empty( $_SERVER['PATH_TRANSLATED'] ) ) {
                //$docroot = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
                $docroot = str_replace( '\\', '/',
                        self::str_before( str_replace( '\\\\', '\\',
                                        $_SERVER['PATH_TRANSLATED'] ),
                                $_SERVER['SCRIPT_NAME'] ) );
            }
            else
                $docroot = '';
            //$docroot=str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $docroot);
        }

        $cache[$cache_key] = $docroot;
        return $docroot;
    }

    public static function getHostUrl( $with_port = true,
            $trailing_slash = false )
    {
        // try to determine the url manually
        // as robustly as possile
        self::fixServerVars();

        $url = 'http';

        if ( isset( $_SERVER["HTTPS"] ) && "on" == $_SERVER["HTTPS"] )
            $url .= "s";

        $url .= "://" . $_SERVER['HTTP_HOST']/* $_SERVER["SERVER_NAME"] */;

        if ( $with_port && isset( $_SERVER["SERVER_PORT"] ) && "80" != $_SERVER["SERVER_PORT"] ) {
            $re = sprintf( '/:%d$/', $_SERVER['SERVER_PORT'] );
            if ( !preg_match( $re, $url ) ) {
                $url .= ":" . $_SERVER["SERVER_PORT"];
            }
        }

        if ( $trailing_slash )
            $url .= '/';

        return $url;
    }

    private static function getCommonPath( $p1, $p2 )
    {
        return implode( '/',
                        array_intersect( explode( '/', $p1 ),
                                explode( '/', $p2 ) ) );
    }

    public static function getUSymlink()
    {
        self::fixServerVars();

        // these should always be available, Apache, IIS, .., PHP 4.1.0+, ..
        $script_name = $_SERVER['SCRIPT_NAME'];
        $local = str_replace( '\\', '/', $script_name );
        if ( false !== strpos( $local, '~' ) && isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
            $script_filename = $_SERVER['SCRIPT_FILENAME'];
            $file = str_replace( '\\', '/', /* self::truepath( */
                    $script_filename/* ) */ );
            $common = self::getCommonPath( $local, $file );
            $usymlink = self::str_before( $local, $common );
            $uabslink = self::str_before( $file, $common );
            $map = array($usymlink, $uabslink);
        } else {
            $map = array();
        }
        // get request, remove query string
        //$request=self::str_before($request_uri, '?');

        return $map;
    }

    // http://stackoverflow.com/questions/176712/how-can-i-find-an-applications-base-url
    // http://stackoverflow.com/questions/5493075/apache-rewrite-get-original-url-in-php
    // variation used here
    public static function getBaseUrl( $trailing_slash = false )
    {
        self::fixServerVars();

        // these should always be available, Apache, IIS, .., PHP 4.1.0+, ..
        $local = str_replace( '\\', '/', $_SERVER['SCRIPT_NAME'] );
        // get request, remove query string
        $request = self::str_before( $_SERVER['REQUEST_URI'], '?' );

        // no need to check for complete rewrited urls (with eg multisite subfolder and rewritten request url)
        // since when called the file is determined and base url can be found (with any virtual path also)

        $url = self::getHostUrl() . self::str_before( $request, $local );

        if ( $trailing_slash )
            $url .= '/';

        return $url;
    }

    public static function getFileUrl( $__FILE__ = null, $use_baseurl = true )
    {
        self::fixServerVars();

        if ( !$__FILE__ )
            $__FILE__ = (string) __FILE__;

        $__FILE__ = str_replace( '\\', '/', dirname( $__FILE__ ) );

        $manual = false;
        if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
            $manual = strpos( $__FILE__, $_SERVER['DOCUMENT_ROOT'] ) !== 0 ? true : false;
        }

        $docroot = self::getDocRoot( $manual );

        $baseurl = $use_baseurl ? self::getBaseUrl() : self::getHostUrl();

        if ( 0 === strpos( $__FILE__, $docroot ) ) {
            return self::_join_paths( $baseurl, self::str_after( $__FILE__, $docroot ) );
        } else {
            $map = self::getUSymlink( /* $_SERVER['SCRIPT_NAME'], $_SERVER['SCRIPT_FILENAME'] */ );
            if ( !empty( $map ) && false !== strpos( $__FILE__, $map[1] ) ) {
                return self::_join_paths( $baseurl, str_replace( $map[1], $map[0], $__FILE__ ) );
            } else {
                return icl_get_file_relpath( $__FILE__ . DIRECTORY_SEPARATOR . 'dummy.php' );
                // finally here
                return self::_join_paths( $baseurl, $__FILE__ );
            }
        }
    }

    private static function _join_paths( $part1, $part2 ) {
        return trailingslashit( $part1 ) . ltrim( $part2, '/' );
    }

    public static function getCurrentUrl( $q = true )
    {
        self::fixServerVars();

        if ( !$q )
            return $_SERVER['REQUEST_URI'];

        return self::getHostUrl() . $_SERVER['REQUEST_URI'];
    }

    private static function str_before( $h, $n, $s = 0 )
    {
        $pos = strpos( $h, $n );
        return (false !== $pos) ? substr( $h, $s, $pos ) : $h;
    }

    private static function str_after( $h, $n )
    {
        $pos = strpos( $h, $n );
        return (false !== $pos) ? substr( $h, $pos + strlen( $n ) ) : $h;
    }

    private static function get_file( $path )
    {

        if ( function_exists( 'realpath' ) )
            $path = realpath( $path );

        if ( !$path || !@is_file( $path ) )
            return '';

        return @file_get_contents( $path );
    }

    private static function buildAbsPath( $rel, $base )
    {
        // is relative path
        if ( 0 === strpos( $rel, '.' ) ) {
            $parts = explode( '/', str_replace( '\\', '/', $rel ) );
            foreach ( array_keys( $parts ) as $ii ) {
                if ( '.' == $parts[$ii] ) {
                    unset( $parts[$ii] );
                } elseif ( '..' == $parts[$ii] ) {
                    $base = dirname( $base );
                    unset( $parts[$ii] );
                }
            }
            $parts = array_values( $parts );
            array_unshift( $parts, $base );
            $rel = implode( '/', $parts );
        }
        return $rel;
    }

}
