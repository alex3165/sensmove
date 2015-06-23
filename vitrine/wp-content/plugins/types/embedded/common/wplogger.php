<?php
/*
 * Modified standalone version of the WPLogger class for internal purposes.
 * 
 * 
 * 
 Wordpress Logger
 http://www.turingtarpit.com/2009/05/wordpress-logger-a-plugin-to-display-php-log-messages-in-safari-and-firefox/
 Displays log messages in the browser console in Safari, Firefox and Opera. Useful for plugin and theme developers to debug PHP code.
 0.3
 Chandima Cumaranatunge
 http://www.turingtarpit.com
 
	 This program is free software; you can redistribute it and/or modify
	 it under the terms of the GNU General Public License as published by
	 the Free Software Foundation; either version 2 of the License, or
	 (at your option) any later version.
	 
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details.
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	 
	 Code to force the plugin to load before others adapted from the
	 WordPress FirePHP plugin developed by Ivan Weiller.
	 http://inchoo.net/wordpress/wordpress-firephp-plugin/
	 
	 Requirements:
	 	* PHP 5+
	 	* Wordpress 2.5+
	 	* JQuery 1.2.6
	 	* Firefox browser with firePHP plugin activated OR
	 	  Safari browser with Error Console turned on
	 
	 Usage:
	 	$wplogger->log( mixed php_expression [, const message_type] )
	 
	 	message_type can be: WPLOG_ERR. WPLOG_WARNING, WPLOG_INFO, WPLOG_DEBUG
	 	
	 Example:
	 	if ($wplogger) $wplogger->log( get_option('active_plugins') );
	 	
	 Output ( from the browser console ):
	 	[Information: from line xxx in file somefile.php] array (
		  0 => 'wplogger/wplogger.php',
		  1 => '12seconds-widget/12seconds-widget.php',
		  2 => 'get-the-image/get-the-image.php',
		)
 */

/* Types of log messages */
define( 'WPLOG_ERR', 		'error' ); 	/* Error conditions */
define( 'WPLOG_WARNING', 	'warn' ); 	/* Warning conditions */
define( 'WPLOG_INFO', 		'info' ); 	/* Informational */
define( 'WPLOG_DEBUG', 		'debug' ); 	/* Debug-level messages */
define( 'WPLOG_OFF', 		'' ); 	/* NO debug enabled */

/* New Wordpress Logger instance */
global $wplogger;
$wplogger = new WPV_WPLogger();

function wplogger( $message = '', $msgType = null ) 
{
	global $wplogger;
	$wplogger->log( $message, $msgType );
}

/* Register function to add logging script */
add_action( 'wp_footer', array($wplogger, 'flushLogMessages') ); // log scripts
/* Ensure logging works in admin pages as well */
add_action ('admin_footer', array ($wplogger, 'flushLogMessages'));

/**
 * WPV_WPLogger Class
 * renamed for compatibility reasons
 */
class WPV_WPLogger
{

    /**
     * String holding the buffered output.
     */
    var $_buffer = array();
    
    /**
     * The default priority to use when logging an event.
     */
    var $_defaultMsgType = WPLOG_INFO;
	
	/**
	 * Long descriptions of debug message types
	 */
	var $_msgTypeLong = array(
            WPLOG_ERR     => 'error',
            WPLOG_WARNING => 'warn',
            WPLOG_INFO    => 'info',
            WPLOG_DEBUG   => 'debug'
        );

    var $_msgStatusPriority = array(
    		WPLOG_ERR     => '50',
            WPLOG_WARNING => '40',
            WPLOG_INFO    => '30',
            WPLOG_DEBUG   => '20',
            WPLOG_OFF	  => '10'
    	);
    /**
     * Writes JavaScript to flush all pending ("buffered") data to
     * the Firefox or Safari console.
     *
     * @notes  requires JQuery 1.2.6 for browser detection.
     *		   browser detection is deprecated in JQuery 1.3
     * @see    http://docs.jquery.com/Utilities/jQuery.browser
     */
    function flushLogMessages()
    {
        if ( count( $this->_buffer ) )
        {           
            print '<script type="text/javascript">'."\n";
            print 'var $j=jQuery.noConflict();'."\n";
            print 'if (($j.browser.safari || $j.browser.webkit) && window.console) {'."\n";
            foreach ( $this->_buffer as $line )
            {
                printf( 'window.console.%s("%s");', $line[0], $line[1] );
                print "\n";
            }
            print '} else if ($j.browser.mozilla && (\'console\' in window)) {'."\n";
            foreach ( $this->_buffer as $line )
            {
                printf( 'console.%s("%s");', $line[0], $line[1] );
                print "\n";
            }
			print '} else if ($j.browser.opera && window.opera && opera.postError) {'."\n";
            foreach ( $this->_buffer as $line )
            {
                printf( 'opera.postError("%s");', $line[1] );
                print "\n";
            }
            print "}\n";
            print "</script>\n";
        }
        ;
        $this->_buffer = array();
    }
	
	/**
	 * Buffers $message to be flushed to the Firebug or Safari console.
	 * 
	 * Adapted from the PEAR_Log library
	 * 
	 * @return boolean true
	 * @param mixed  $message String or object containing the message to log.
	 * @param const $msgType[optional] type of message. Valid values are:
	 * 					WPLOG_ERR. WPLOG_WARNING, WPLOG_INFO, WPLOG_DEBUG
	 */
    function log( $message, $msgType = null )
    {	
		/* backtrace */
		$bTrace = debug_backtrace(); // assoc array
	
        /* If a log message type hasn't been specified, use the default value. */
        if ( $msgType === null )
        {
            $msgType = $this->_defaultMsgType;
        }
        
        // verify the status type and output only priority messages (based on wp-config setup)
        if(!$this->isMsgVisible($msgType)) {
        	return false;
        }
        
        /* Extract the string representation of the message. */
        $message = $this->_extractMessage( $message );
        
        /* normalize line breaks */
        $message = str_replace( "\r\n", "\n", $message );
        
        /* escape line breaks */
        $message = str_replace( "\n", "\\n\\\n", $message );
        
        /* escape quotes */
        $message = str_replace( '"', '\\"', $message );
        
        /* Build the string containing the complete log line. */
		$line = sprintf('[%s: from line %d in file %s] %s', 
								$this->_msgTypeLong[ $msgType ], 
								$bTrace[0]['line'], 
								basename($bTrace[0]['file']), 
								$message );
        
        // buffer method and line
        $this->_buffer[] = array($msgType, $line);
		
        return true;
    }
    
    /**
     * Returns the string representation of the message data (from the PEAR_Log library).
     *
     * If $message is an object, _extractMessage() will attempt to extract
     * the message text using a known method (such as a PEAR_Error object's
     * getMessage() method).  If a known method, cannot be found, the
     * serialized representation of the object will be returned.
     *
     * If the message data is already a string, it will be returned unchanged.
     * 
     * Adapted from the PEAR_Log library
     *
     * @param  mixed $message   The original message data.  This may be a
     *                          string or any object.
     *
     * @return string           The string representation of the message.
     * 
     */
    function _extractMessage( $message )
    {
        /*
         * If we've been given an object, attempt to extract the message using
         * a known method.  If we can't find such a method, default to the
         * "human-readable" version of the object.
         *
         * We also use the human-readable format for arrays.
         */
        if ( is_object( $message ) )
        {
            if ( method_exists( $message, 'getmessage' ) )
            {
                $message = $message->getMessage();
            }
            else if ( method_exists( $message, 'tostring' ) )
            {
                $message = $message->toString();
            }
            else if ( method_exists( $message, '__tostring' ) )
            {
                if ( version_compare( PHP_VERSION, '5.0.0', 'ge' ) )
                {
                    $message = (string) $message;
                }
                else
                {
                    $message = $message->__toString();
                }
            }
            else
            {
                $message = var_export( $message, true );
            }
        }
        else if ( is_array( $message ) )
        {
            if ( isset($message['message']) )
            {
                if ( is_scalar( $message['message'] ) )
                {
                    $message = $message['message'];
                }
                else
                {
                    $message = var_export( $message['message'], true );
                }
            }
            else
            {
                $message = var_export( $message, true );
            }
        }
        else if ( is_bool( $message ) || $message === NULL )
        {
            $message = var_export( $message, true );
        }
        
        /* Otherwise, we assume the message is a string. */
        return $message;
    }
    
    /**
     * 
     * Is the message for the logger visible, i.e. is the status approved for output in the config
     * 
     * @param status_type $msg_status the status level
     */
    function isMsgVisible($msg_status) {
    	// verify that status for logging is set
    	if(!defined('WPV_LOGGING_STATUS')) {
    		return false;
    	}
    	
    	// use default off status if status not in the list
    	if(!in_array(WPV_LOGGING_STATUS, $this->_msgTypeLong) ||
    		!in_array($msg_status, $this->_msgTypeLong)) {
    		return false;
    	}
    	
    	// verify priorities
		if( $this->_msgStatusPriority[$msg_status] >=  $this->_msgStatusPriority[WPV_LOGGING_STATUS] ) {
			
			return true;
		}  
		
		return false;
    }
}

?>
