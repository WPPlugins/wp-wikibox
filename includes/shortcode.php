<?php
 /**
 * handle wp-wikibox shortcode params and return appropiate content
 *
 * @package wp-wikibox
 */

// shortcode handler function
function wikibox_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'lang' => '',
		), $atts ) );
		// [wikibox lang="en"]Pink Floyd[/wikibox]
		
		global $wikibox_options;
		// use default language if not set in shortcode
		if ( $lang == '' ) $lang = $wikibox_options['language'];
		
		return wikibox_summary( $content, $lang, false );
		
}


// add the world_flags shortcode handler
add_shortcode('wikibox', 'wikibox_shortcode');
add_filter('widget_text', 'do_shortcode');
?>