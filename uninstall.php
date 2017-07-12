<?php
 /**
 * handle uninstall and remove our options
 *
 * @package wp-wikibox
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

require_once( 'includes/config.php' );
require_once( 'includes/functions.php' );

	/**
	 *	wikibox_delete_plugin function
	 *
	 *	handles removing plugin options
	*/
	function wikibox_delete_plugin() {
		
		delete_option( WIKIBOX_PREFIX . 'option' );
		wikibox_delete_cache();
	}

wikibox_delete_plugin();

?>