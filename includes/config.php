<?php
 /**
 * config file for the plugin
 *
 * @package wp-wikibox
 */
	
	// require the wp bootstrap, this is for our php files that are not loaded by WP and are called by ajax, etc.
	//require_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php' );
	
	// ---------------------------------------- constants
	define ( 'WIKIBOX_BASENAME', dirname ( dirname( plugin_basename( __FILE__ ) ) ) );
	define ( 'WIKIBOX_WEBDIR', '/' . PLUGINDIR . '/' . WIKIBOX_BASENAME );
	define ( 'WIKIBOX_VERSION', '0.1.3' );
	define ( 'WIKIBOX_PREFIX', 'wikibox_' );
	define ( 'WIKIBOX_AGENT', 'BSurprised WikiBox ' . WIKIBOX_VERSION . ' (+http://bsurprised.com/)' );
	define ( 'WIKIBOX_RAW_URL', 'http://%s.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=xml&redirects&titles=%s' );
	define ( 'WIKIBOX_PARSE_URL', 'http://%s.wikipedia.org/w/api.php?action=parse&prop=text&format=xml' );
	
	// ---------------------------------------- 
	
	global $wikibox_options;
	$wikibox_options = get_option( WIKIBOX_PREFIX . 'option' );
	
?>