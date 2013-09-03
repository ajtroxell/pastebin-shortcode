<?php
/*
Plugin Name: Pastebin Shortcode
Plugin URI: http://www.ajtroxell.com/pastebin-shortcode-plugin
Description: Easily insert specific Pastebin files with this shortcode [pastebin id="xxxxxx"], and give the include a GitHub styled makeover.
Version: 1.0
Author: AJ Troxell
Author URI: http://www.ajtroxell.com/
*/
 
/*
 * USAGE:
 * Two ways are provided to insert the shortcode. The value "xxxxxx" represents your Pastebin file ID.
 * Insert [pastebin id="xxxxxx"] manually.
 * The plane text editor "Pastebin" shortcode button.
 * You can place these shortcodes in pages, posts or any custom content.
 *
 * INTALLATION
 * Unzip pastebin-shortcode.zip and upload pastebin-shortcode folder to wp-content/plugins.
 * On Wordpress admin panel, activate the plugin.
 *
 * LICENSE
 * Released under the GPLv2 or later.
 */

	// Check for updates
	require 'plugin-updates/plugin-update-checker.php';
		$pastebin_shortcode_update = new PluginUpdateChecker(
	    'http://labs.ajtroxell.com/plugins/pastebin-shortcode-plugin/info.json',
	    __FILE__,
	    'pastebin-shortcode-plugin'
	);

	function pastebin_shortcode_styles() {
		wp_enqueue_style('pastebin-shortcode', plugins_url('css/pastebin-shortcode-style.css', __FILE__));
	}
    add_action('wp_enqueue_scripts', 'pastebin_shortcode_styles',10);


	// Main Function
	function pastebin_shortcode($atts, $content = null) {
	 
		extract(shortcode_atts(array(
			'id' => ''
		), $atts));
	 
		$output =  '<script src="http://pastebin.com/embed_js.php?i='.trim($id).'"></script>';
		
		if($content != null){
			$output = $output.'<noscript><pre>'.$content.'</pre></noscript>';
		}
		
		return $output;
		
	}
 
	// Create Shortcode
	add_shortcode('pastebin', 'pastebin_shortcode');

	//Register HTML editor Button
	if( !function_exists('_add_pastebin_shortcode_quicktags') ){
		function _add_pastebin_shortcode_quicktags(){
?>
<script type="text/javascript">  
	//Add custom Quicktag button to the WordPress editor
	QTags.addButton( 'pastebin', 'Pastebin', prompt_user );
    function prompt_user(e, c, ed) {
        prmt = prompt('Enter Pastebin ID');
        if ( prmt === null ) return;
        rtrn = '[pastebin id="' + prmt + '"]';
        this.tagStart = rtrn;
        QTags.TagButton.prototype.callback.call(this, e, c, ed);
    }
</script>
<?php }
	add_action('admin_print_footer_scripts',  '_add_pastebin_shortcode_quicktags'); }

	function pastebin_shortcode_presstrends_plugin() {
	    // PressTrends Account API Key
	    $api_key = '1uv0ak16ziqw785pmqxn0eykq5pmhic3kvqv';
	    $auth    = 'uer2zz1rk3s26fdpkg5lzw3hdvyaeumjw';
	    // Start of Metrics
	    global $wpdb;
	    $data = get_transient( 'presstrends_cache_data' );
	    if ( !$data || $data == '' ) {
	        $api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
	        $url      = $api_base . $auth . '/api/' . $api_key . '/';
	        $count_posts    = wp_count_posts();
	        $count_pages    = wp_count_posts( 'page' );
	        $comments_count = wp_count_comments();
	        if ( function_exists( 'wp_get_theme' ) ) {
	            $theme_data = wp_get_theme();
	            $theme_name = urlencode( $theme_data->Name );
	        } else {
	            $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
	            $theme_name = $theme_data['Name'];
	        }
	        $plugin_name = '&';
	        foreach ( get_plugins() as $plugin_info ) {
	            $plugin_name .= $plugin_info['Name'] . '&';
	        }
	        // CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
	        $plugin_data         = get_plugin_data( __FILE__ );
	        $posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
	        $data                = array(
	            'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
	            'posts'           => $count_posts->publish,
	            'pages'           => $count_pages->publish,
	            'comments'        => $comments_count->total_comments,
	            'approved'        => $comments_count->approved,
	            'spam'            => $comments_count->spam,
	            'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
	            'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
	            'theme_version'   => $plugin_data['Version'],
	            'theme_name'      => $theme_name,
	            'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
	            'plugins'         => count( get_option( 'active_plugins' ) ),
	            'plugin'          => urlencode( $plugin_name ),
	            'wpversion'       => get_bloginfo( 'version' ),
	        );
	        foreach ( $data as $k => $v ) {
	            $url .= $k . '/' . $v . '/';
	        }
	        wp_remote_get( $url );
	        set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
	        }
	    }
	// PressTrends WordPress Action
	add_action('admin_init', 'pastebin_shortcode_presstrends_plugin');

?>