<?php 
 /**
 * handles installing plugin required tables and options
 *
 * @package wp_wikibox
 */

	function wikibox_install() {
		
		$options = get_option( WIKIBOX_PREFIX . 'option' );
		if ( ! $options ) {
			$options = array( 
						'language' => 'en',
						'include_title' => 'yes',
						'include_images' => 'no',
						'include_morelink' => 'yes',
						'include_originallink' => 'yes',
						'include_editlink' => 'no',
						'template' => '
<div class="wikibox">
  <h3 class="wikititle">
    {{title}}
  </h3>
  <p class="wikiright">
    {{cc}} {{wikipedia}} {{original_link}} {{edit_link}}
  </p>
  {{content}}
</div>',
						'add_nofollow' => 'yes',
						'enable_gzip' => 'no',
						'cache_timespan' => 60*60*24,
						'cache_type' => 'transient',
						'include_links' => 'yes',
						'include_bslink' => 'yes',
						);
			update_option( WIKIBOX_PREFIX . 'option', $options );
		} else {
			// add new options for this version
			$options['cache_type'] = 'transient';
			$options['include_links'] = 'yes';
			$options['include_bslink'] = 'yes';
			update_option( WIKIBOX_PREFIX . 'option', $options );
		}
		
	}
	
?>