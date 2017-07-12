<?php
 /**
 * required functions are here
 *
 * @package wp-wikibox
 */
	/**
	 *	wikibox_summary function
	 *	gets and optionally echoes a box with summary from wikipedia
	*/
	function wikibox_summary( $keyword, $lang = 'en', $echo = true ) {
		global $wikibox_options;
		
		$article = wikibox_get_summary( $keyword, $lang );
		
		if ( $article ) {
			// prepare keyword title
			$keyword = str_replace( ' ', '_', $article['title'] );
			// add wikipedia copyright notice
			$copyright = '<p class="wikiright">';
			// include wikibox link
			if ( $wikibox_options['include_bslink'] == 'yes' ) {
				$copyright .= '<a href="http://bsurprised.com/2010/09/wp-wikibox-wordpress-plugin/" title="Powered by BSurprised WP-WikiBox"><img src="' . get_settings( 'siteurl' ) . WIKIBOX_WEBDIR . '/images/wb.png" alt="Powered by BSurprised WP-WikiBox" style="border-width:0" title="Powered by BSurprised WP-WikiBox" /></a> ';
			}
			$copyright .= '<a href="http://creativecommons.org/licenses/by-sa/3.0/" title="Wikipedia article licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License"><img src="' . get_settings( 'siteurl' ) . WIKIBOX_WEBDIR . '/images/cc.png" alt="Creative Commons License" style="border-width:0" title="Wikipedia article licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License" /></a> ';
			$copyright .= sprintf( __( 'From <a rel="nofollow" title="Wikipedia" href="http://%s.wikipedia.org/">Wikipedia</a>, the free encyclopedia', 'wikibox' ), $lang );
			// include view original link
			if ( $wikibox_options['include_originallink'] == 'yes' ) {
				$copyright .= " <a rel=\"nofollow\" class=\"wikilink\" title=\"" . __( 'View on Wikipedia', 'wikibox' ) . "\" href=\"http://$lang.wikipedia.org/wiki/$keyword\">" . __( '[+]', 'wikibox' ) . "</a>";
			}
			// include edit link
			if ( $wikibox_options['include_editlink'] == 'yes' ) {
				$copyright .= " <a rel=\"nofollow\" class=\"wikilink\" title=\"" . __( 'Edit Article', 'wikibox' ) . "\" href=\"http://$lang.wikipedia.org/w/index.php?title=$keyword&action=edit\">" . __( '[edit]', 'wikibox' ) . "</a>";
			}
			$copyright .= '</p>';
			$header_meta = false;
			$box = '<div class="wikibox">';
			// title and copyright
			if ( $wikibox_options['include_title'] == 'yes' ) {
				$box .= "<h3 class=\"wikititle\">$article[title]</h3>";
				$box .= $copyright;
				$header_meta = true;
			}
			//add content
			$box .= $article['content'];
			// include more link
			if ( $wikibox_options['include_morelink'] == 'yes' ) {
				$last_tag = strrpos( $box, '</' );
				$box = substr_replace( $box, " <span class=\"wikimore\"><a rel=\"nofollow\" class=\"wikilink\" title=\"" . __( 'Read More', 'wikibox' ) . "\" href=\"http://$lang.wikipedia.org/wiki/$keyword\">" . __( '[...]', 'wikibox' ) . "</a></span>", $last_tag, 0);
			}
			// no title so include the copyright in footer
			if ( ! $header_meta ) {
				$box .= $copyright;
			}
			$box .= '</div>';
			
			if ( $echo ) echo $box; else return $box;
		}
	} // end wikibox_summary

	/**
	 *	wikibox_get_summary function
	 *	retrieves and refines a page summary from wikipedia
	*/
	function wikibox_get_summary( $keyword, $lang = 'en' ) {
		global $wikibox_options;
		
		//add wiki underscores instead of spaces
		$keyword = urlencode( str_replace( ' ', '_', $keyword ) );
		
		// check whick version of caching we have and return it if any
		if ( $wikibox_options['cache_type'] == 'transient' ) {
			if ( false !== ( $transient = get_transient( WIKIBOX_PREFIX . substr( $keyword, 0, 25 ) ) ) ) {
				// return the transient, we have this content
				return $transient;
			}
		} else if ( $wikibox_options['cache_type'] == 'file' ) {
			$cache = new wikibox_cache( ABSPATH . WIKIBOX_WEBDIR . '/cache' );
			$file_data = $cache->get( $keyword, $wikibox_options['cache_timespan'] );
			if ( $file_data !== false ) {
				// return the data, we have this content
			    return $file_data;
			}
		}
		
		// build the page name url 
		$file = sprintf( WIKIBOX_RAW_URL , $lang, $keyword );
		
		// get the page
		$result = wikibox_get_data( $file );

		if ( $result !== false ) {
			if ( substr( $result, 0, strlen( __( "Error", "wikibox" ) ) ) == __( "Error", "wikibox" ) ) return array( 'title' => 'Error', 'content' => $result );
			
			// load the result string
			$xml = @simplexml_load_string( $result );  //or die ( __( "Unable to load Wikipedia XML string!", "wikibox" ) );
			
			if ( $xml ) {
				$title = (string)$xml->query->pages->page['title'];
				$content = $xml->query->pages->page->revisions->rev;
				$headstart = explode( "\n==", $content );
				
				$summary = $headstart[0];
				
				//echo '||'.$summary.'||';
				
				// recursive remove boxes 
				$summary = preg_replace( "/\{\{((?>[^{{}}]+)|(?R))*\}\}/", "", $summary ); 

				// remove refs as they will not have ref list
				$summary = preg_replace( "#\<ref.*\<\/ref\>#imseU", "", $summary );
				$summary = preg_replace( "#\<ref.*\/\>#imseU", "", $summary );
				// remove remained dummy \n \t etc.
				$summary = trim( $summary );
			
				if ( $summary != '' ) {
					$file = sprintf( WIKIBOX_PARSE_URL , $lang );
					// parse using wiki template engine, this one sends the text in POST as url may not handle the length
					$result = wikibox_get_data( $file, true, array( 'text' => $summary ) );
					
					if ( $result !== false ) {
						if ( substr( $result, 0, strlen( __( "Error", "wikibox" ) ) ) == __( "Error", "wikibox" ) ) return array( 'title' => 'Error', 'content' => $result );
						
						// load the result string
						$xml = @simplexml_load_string( $result ); //or die ( __( "Unable to load Wikipedia parsed XML string!", "wikibox" ) );
						
						if ( $xml ) {
							$content = $xml->parse->text;
							
							//some cleanup and url repairing
							$content = wikibox_clean_html ( $content );
							// correcting the wiki urls and add http host
							$content = wikibox_repair_urls ( $content, $lang );
							
							// add transient for a day to avoid calling wikipedia too much
							if ( $content != '' && $wikibox_options['cache_type'] == 'transient' ) set_transient( WIKIBOX_PREFIX . substr( $keyword, 0, 25 ), array( 'title' => $title, 'content' => $content ), $wikibox_options['cache_timespan'] );
							if ( $content != '' && $wikibox_options['cache_type'] == 'file' ) { $cache->set( $keyword, array( 'title' => $title, 'content' => $content ) ); }
							
							//echo $content;
							return array( 'title' => $title, 'content' => $content );
							//} else {
							//	return __( 'The keyword did not match any Wikipedia page title.', 'wikibox' );
							//}
						}
					}
				}
			} 
		}
	} // end wikibox_get_summary
	
	/**
	 *	wikibox_clean_html function
	 *	cleans unwanted things, this can help preventing regex from acting strangely
	*/
	function wikibox_clean_html( $str ) {
		global $wikibox_options;
		$str = preg_replace( "/([\t\n]|\(\)|\( \)|\(\, \))/", "", $str );
		$str = str_replace( "<p><br />", "<p>", $str );
		// remove html comments
		$str = preg_replace( "#\<\!\-\-.*\-\-\>#imseU", "", $str );
		// strip images
		if ( $wikibox_options['include_images'] != 'yes' ) {
			$str = preg_replace( "#<div class=\"thumb\b[^>]*>(.*?)<\/div>#imseU", "", $str );
		}
		if ( $wikibox_options['include_links'] != 'yes' ) {
			$str = strip_tags( $str, '<p><span><b><i><u><em><strong><strike><small><ul><li><ol><dl><dd><dt><code><pre><blockquote><cite><abbr><acronym><br>' );
		}
		return $str;
	} // end wikibox_clean_html
	
	/**
	 *	wikibox_repair_urls function
	 *	adds wikipedia host to urls
	*/
	function wikibox_repair_urls( $str, $lang ) {
		global $wikibox_options;
		$str = str_replace( '"/w/', '"http://' . "$lang.wikipedia.org/w/", $str );
		$str = str_replace( '"/wiki/', '"http://' . "$lang.wikipedia.org/wiki/", $str );
		if ( $wikibox_options['add_nofollow'] == 'yes' ) {
			$str = str_replace( 'href=', 'rel="nofollow" href=', $str );
		}
		return $str;
	} // end wikibox_repair_urls
	
	/**
	 *	wikibox_get_data function
	 *	creates a stream context to retrieve data and returns it
	*/
	function wikibox_get_data( $url, $post = false, $post_content='' ) {
		global $wikibox_options;
		
		try {
			if( function_exists( 'curl_init' ) ) {
				$curl = curl_init(); // init the curl
				curl_setopt( $curl, CURLOPT_HTTPHEADER, array( "Cache-Control: max-age=0",
															 "Connection: keep-alive",
															 "Keep-Alive: 300",
															 "Accept-Charset: ISO-8859-1,utf-8",
															 "Accept-Language: $wikibox_options[language]",
															 "Pragma: " ) );
				curl_setopt( $curl, CURLOPT_URL, $url );
				curl_setopt( $curl, CURLOPT_USERAGENT, WIKIBOX_AGENT );
				curl_setopt( $curl, CURLOPT_ENCODING, 'gzip,deflate' );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 120 );
				curl_setopt( $curl, CURLOPT_TIMEOUT, 120 );
				curl_setopt( $curl, CURLOPT_MAXREDIRS, 5 );
				if ( $post ) {
					curl_setopt ( $curl, CURLOPT_POST, 1 );
 					curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query( $post_content ) );
				}
				$result = curl_exec( $curl ); // execute the curl command
				curl_close( $curl ); // close the connection
				
			} else {
				
				// create a stream to add user agent to request, as wiki requires a valid descriptive user agent
				if ( $post ) {
					$opts = array(
							  'http'=>array(
										'method' => "POST",
										'header' => "Accept-language: $wikibox_options[language]\r\n" .
													( ($wikibox_options['enable_gzip'] === 'yes') ? "Accept-Encoding: gzip\r\n" : "" ) .
													"User-Agent: " . WIKIBOX_AGENT . "\r\n",
										'content' => http_build_query( $post_content )
										)
							);
				} else {
					$opts = array(
							  'http' => array(
										'method' => "GET",
										'header' => "Accept-language: $wikibox_options[language]\r\n" .
													( ($wikibox_options['enable_gzip'] === 'yes') ? "Accept-Encoding: gzip\r\n" : "" ) .
													"User-Agent: " . WIKIBOX_AGENT . "\r\n"
										)
							);
				}
				$context = stream_context_create( $opts );
				
				// get the page
				$result = file_get_contents( $url, false, $context );
				//uncompress the result if we are using gzip, be sure to strip the first 10 bytes
				if ( $wikibox_options['enable_gzip'] === 'yes' ) $result = gzinflate( substr( $result, 10 ) );
			}
			
		} catch ( Exception $e ) {
			echo $result = __( "Error", "wikibox" ) . ': ' . __( "Failed to contact Wikipedia server.", "wikibox" ); 
		}
		
		return $result;
		
	} // end wikibox_get_data
	
	/**
	 *	wikibox_delete_cache function
	 *	deletes transient data on editing options
	*/
	function wikibox_delete_cache() {
		global $wpdb, $wikibox_options;
		if ( $wikibox_options['cache_type'] == 'transient' ) {
			$query = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient%wikibox%'";
			$wpdb->query( $query );
		} else if ( $wikibox_options['cache_type'] == 'file' ) {
			clearstatcache();
			$dir = ABSPATH . WIKIBOX_WEBDIR . '/cache/';
			if ( $dh = opendir( $dir ) ) {
		        while ( ( $file = readdir( $dh ) ) !== false ) {
		            unlink ( $dir . $file );
		        }
		        closedir( $dh );
		    }
		}
	} // end wikibox_delete_cache

	/**
	 *	wikibox_lang_listbox function
	 *	load wikipedia language list into an html options list
	*/
	function wikibox_lang_listbox( $name, $echo = true ) {
		global $wikibox_options;
		// load the xml file
		$xml = simplexml_load_file( ABSPATH . WIKIBOX_WEBDIR . '/languages/wiki-languages.xml' )  or die ( __( "Unable to load Language XML file!", "wikibox" ) );
		
		$html = '';
		$cur_lang = $wikibox_options['language'];
		if ( $xml ) {
			$html = "<select name=\"$name\">";
			foreach ( $xml as $lang ) {
				$html .= "<option value=\"$lang->code\"" . ( $cur_lang == $lang->code ? ' selected="selected"' : '' ) . ">$lang->name</option>";
			}
			$html .= '</select>';
		}
		if ( $echo ) echo $html; else return $html;
	} // end wikibox_lang_listbox
	
?>