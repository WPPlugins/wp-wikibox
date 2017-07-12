<?php
/*
Plugin Name: WP-WikiBox
Plugin URI: http://bsurprised.com/2010/09/wp-wikibox-wordpress-plugin/
Description: Get Wikipedia article summary for a keyword in any language, inline with your posts and pages, with a simple shortcode and/or function.
Version: 0.1.3
Author: Behrooz Sangani
Author URI: http://bsurprised.com/
*/


/*  Copyright 2010 Behrooz Sangani (sangani at gmail.com)

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
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/** 
 * This is the main file for WP-WikiBox plugin.
 *
 * Handles loading all plugin parts
 *
 * @package wp-wikibox
 */

// ---------------------------------------- includes
require_once( 'includes/config.php' );
require_once( 'includes/functions.php' );
require_once( 'includes/install.php' );
require_once( 'includes/shortcode.php' );
require_once( 'includes/cache.php' );
// ---------------------------------------- */

// ---------------------------------------- language domain
add_action( 'init', 'wikibox_load_plugin_textdomain' );
function wikibox_load_plugin_textdomain() {
	load_plugin_textdomain( 'wikibox', false, WIKIBOX_BASENAME . '/languages' );
}
// ---------------------------------------- 
		
// ---------------------------------------- initial installation
// function included in install.php
register_activation_hook( __FILE__, 'wikibox_install' );
// ----------------------------------------

// ---------------------------------------- enqueue required libraries for the plugin
if ( ! is_admin() ) {
	// require our scripts and styles to be loaded
	wp_enqueue_style( WIKIBOX_BASENAME, WIKIBOX_WEBDIR . '/css/wikibox.css', '', WIKIBOX_VERSION, 'all' );
}
// ----------------------------------------

// ---------------------------------------- Handle cache after update options
add_action('update_option_' . WIKIBOX_PREFIX . 'option', 'wikibox_delete_cache');
// ---------------------------------------- 

// ---------------------------------------- Admin menu hook and options
// Add action hook
add_action( 'admin_menu', 'wikibox_menu' );
// Register plugin submenu
function wikibox_menu() {
	// Add a new submenu under Options
	add_options_page( __( 'WP-WikiBox', 'wikibox' ), __( 'WP-WikiBox', 'wikibox' ), 'manage_options', 'wikibox_extra', 'wikibox_extra_page' );
	// register our settings for variable data
	register_setting( WIKIBOX_PREFIX . 'options', WIKIBOX_PREFIX . 'option' );
}

function wikibox_extra_page() {
	if ( ! current_user_can( 'manage_options' ) )	{
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>' . __( 'WP-WikiBox Options', 'wikibox' ) . '</h2>';
	echo '<p>' . __( 'Use this form to change options for the WP-WikiBox plugin.', 'wikibox' ) . '</p>';
	if ( $message != '' ) {
		echo '<div id="message" class="updated"><p>' . $message . '</p></div>';
	}
	echo '<form method="post" action="options.php">';
	settings_fields( WIKIBOX_PREFIX . 'options' );
	$options = get_option( WIKIBOX_PREFIX . 'option' );
	clearstatcache();
	
	echo '	<fieldset class="options"><legend><h3>' . __( 'General', 'wikibox' ) . '</h3></legend>';
	echo '		<table class="form-table">';
	echo '				<tr valign="top"><th scope="row"></th>';
	echo '						<td><input name="wikibox_option[include_title]" type="checkbox" value="yes"' . checked( 'yes', $options['include_title'], false ) . ' /> ' . __( 'Include article title', 'wikibox' ) . '<br />';
	echo '							<input name="wikibox_option[include_images]" type="checkbox" value="yes"' . checked( 'yes', $options['include_images'], false ) . ' /> ' . __( 'Include images, if any, in the box', 'wikibox' ) . '<br />';
	echo '							<input name="wikibox_option[include_links]" type="checkbox" value="yes"' . checked( 'yes', $options['include_links'], false ) . ' /> ' . __( 'Include links in the wiki content', 'wikibox' ) . '<br />';
	echo '							<input name="wikibox_option[include_originallink]" type="checkbox" value="yes"' . checked( 'yes', $options['include_originallink'], false ) . ' /> ' . __( 'Include the `view original` link to Wikipedia', 'wikibox' ) . '<br />';
	echo '							<input name="wikibox_option[include_morelink]" type="checkbox" value="yes"' . checked( 'yes', $options['include_morelink'], false ) . ' /> ' . __( 'Include the `read more` link to Wikipedia', 'wikibox' ) . '<br />';
	echo '							<input name="wikibox_option[include_editlink]" type="checkbox" value="yes"' . checked( 'yes', $options['include_editlink'], false ) . ' /> ' . __( 'Include `edit link` to Wikipedia article edit page', 'wikibox' ) . '<br />';
	echo '							<input name="wikibox_option[add_nofollow]" type="checkbox" value="yes"' . checked( 'yes', $options['add_nofollow'], false ) . ' /> ' . __( 'Add rel="nofollow" to Wikipedia links', 'wikibox' ) . '<br />';
	if( !function_exists( 'curl_init' ) ) { // gzip option if cURL not available
		echo '							<input name="wikibox_option[enable_gzip]" type="checkbox" value="yes"' . checked( 'yes', $options['enable_gzip'], false ) . ' /> ' . __( 'Enable GZip compression while fetching data from Wikipedia', 'wikibox' ) . '<br />';
	}
	echo '							<input name="wikibox_option[include_bslink]" type="checkbox" value="yes"' . checked( 'yes', $options['include_bslink'], false ) . ' /> ' . __( 'Include the WP-WikiBox powered-by icon for other users to find out about this plugin, please. Thank you!', 'wikibox' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Language', 'wikibox' ) . '</th>';
	echo '					<td>' . wikibox_lang_listbox( 'wikibox_option[language]', false ) . '</td>';
	echo '				</tr>';
/*	echo '				<tr valign="top"><th scope="row">' . __( 'Template', 'wikibox' ) . '</th>';
	echo '						<td>' . __( 'Rearrage the position of the elements shown in Wikibox', 'wikibox' ) . '<br /><textarea style="direction: ltr;" cols="50" rows="10" name="wikibox_option[template]">'. $options['template'] . '</textarea></td>';
	echo '				</tr>';	*/
	echo '				<tr valign="top"><th scope="row">' . __( 'Cache method', 'wikibox' ) . '</th>';
	echo '						<td><select name="wikibox_option[cache_type]">';
	echo '								<option value="transient"' . ( $options['cache_type'] == 'transient' ? ' selected="selected"' : '' ) . '">' . __( 'Wordpress Transient Cache', 'wikibox' ) . '</option>';
	echo '								<option value="file"' . ( $options['cache_type'] == 'file' ? ' selected="selected"' : '' ) . '">' . __( 'File Cache', 'wikibox' ) . '</option>';
	echo '							</select><br />';
	echo ( is_writable( ABSPATH . WIKIBOX_WEBDIR . '/cache' ) ) ? '<span style="color: green;">' . __( 'Cache folder is writable', 'wikibox' ) . '</span>' : '<span style="color: red;">' . __( 'Cache folder IS NOT writable', 'wikibox' ) . '</span>'; 
	echo '						</td>';
	echo '				</tr>';	
	echo '				<tr valign="top"><th scope="row">' . __( 'Cache time', 'wikibox' ) . '</th>';
	echo '						<td><input type="text" name="wikibox_option[cache_timespan]" value="'. $options['cache_timespan'] . '" /> ' . __( 'How long should we cache box data? (1 day = 60*60*24 = 86400 seconds)', 'wikibox' ) . '</td>';
	echo '				</tr>';	
	echo '		</table>';
	echo '	</fieldset>';
	echo '	<br />';
	
	echo '		<p class="submit">';
	echo '		<input type="submit" class="button-primary" value="' . __( 'Save Changes' ) . '" />';
	echo '		</p>';
	echo '</form>';
	
	echo '	<br />';
	echo '<div style="text-align: center; width: 500px; margin: 15px auto; border-top: 1px solid gray">';
	echo '	<p><a href="http://bsurprised.com/" title="WP-WikiBox Plugin by BSurprised"><img src="http://bsurprised.com/wp-content/themes/bsurprised/images/bsurprised-logo.jpg" alt="WP-WikiBox Plugin by BSurprised" /></a><br />';
	echo '	WP-WikiBox plugin by Behrooz Sangani <br /> <a href="http://bsurprised.com/" title="BSurprised Website">BSurprised.com</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FDGUSD7M825M6" title="Donations are appreciated">Donate</a> | <a href="http://wikimediafoundation.org/wiki/Support_Wikipedia/en" title="Support Wikipedia by making a donation">Support Wikipedia</a><br />';
	echo '	v' . WIKIBOX_VERSION ;
	echo '	</p></div>';
	echo '</div>';

}
// ---------------------------------------- 
?>