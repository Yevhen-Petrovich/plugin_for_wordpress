<?php
/*
Plugin Name: content_plugin
Description: Добавляет атрибуты для внешних ссылок.
Version: 1.0
Author: Yevhen Kholiavka
*/

add_action( 'plugins_loaded', 'eol_plugin_setup' );
/* Register plugin activation hook. */
register_activation_hook( __FILE__, 'eol_plugin_activation' );

/* Register plugin activation hook. */
register_deactivation_hook( __FILE__, 'eol_plugin_deactivation' );
/**
 * Do things on plugin activation.
 *
 */
function eol_plugin_activation() {
	/* Flush permalinks. */
    flush_rewrite_rules();
}
/**
 * Flush permalinks on plugin deactivation.
 */
function eol_plugin_deactivation() {
    flush_rewrite_rules();
}
function eol_plugin_setup() {
// create custom plugin settings menu
/* Get the plugin directory URI. */
	define( 'EXTERNAL_OUTBONDING_LINK_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	add_action('admin_menu', 'eol_plugin_create_menu');
}

function eol_plugin_create_menu() {
	//create new top-level menu

	add_options_page('Content_plugin Settings', 'Plugin settings', 'administrator', __FILE__, 'eol_plugin_settings_page');
	//add_menu_page('External outbonding links', 'Settings plagin', 'administrator', __FILE__, 'eol_plugin_settings_page' );
	//call register settings function
	add_action( 'admin_init', 'eol_register_plugin_settings' );	
}

function eol_register_plugin_settings() {
	//register our settings	
	register_setting( 'eol-plugin-settings-group', 'external_add_nofollow' );
	register_setting( 'eol-plugin-settings-group', 'external_add_blank' );
}

function eol_plugin_settings_page() {
	?>
	
	<div class="wrap">
	<h2>Select attributes for external links</h2>
	
	<form method="POST" action="options.php">
				
		<?php do_settings_sections( 'eol-plugin-settings-group_' ); ?>
		<?php settings_fields( 'eol-plugin-settings-group' ); ?>
		
		<?php 	$eol_external_attribute_add_nofollow = get_option( 'external_add_nofollow' );	?>
		<?php 	$eol_external_attribute_add_blank = get_option( 'external_add_blank' );	?>	
	
		<table class="form-table">
		<tr valign="top">
			<th scope="row">Nofollow attribute for external links (rel="nofollow"):</th>
			<td><input type='checkbox' id='external_add_nofollow' name='external_add_nofollow' value='1' <?php echo checked( $eol_external_attribute_add_nofollow, 1, false );?> /></td>
			</tr>
		</table>
	
		<table class="form-table">
		<tr valign="top">
			<th scope="row">Open links in a new tab (target="_blank"):</th>
			<td><input type='checkbox' id='external_add_blank' name='external_add_blank' value='1' <?php echo checked( $eol_external_attribute_add_blank, 1, false );?> /></td>
			</tr>
		</table>   
		<?php submit_button(); ?>
	</form>
	</div>
	<?php 
	}
	$eol_external_attribute_add_nofollow = get_option('external_add_nofollow');
	$eol_external_attribute_add_blank = get_option('external_add_blank');


	if( $eol_external_attribute_add_nofollow == $eol_external_attribute_add_blank){
	/**
	* add links if there are two checbox
	*/
	function eol_add_external_link($content) {
		$content = preg_replace_callback(
		'/<a[^>]*href=["|\']([^"|\']*)["|\'][^>]*>([^<]*)<\/a>/i',
		function($m) {
		if (strpos($m[1], $_SERVER['SERVER_NAME']) === false)
		return '<a id="ext-link" href="'.$m[1].'" rel="nofollow" target="_blank">'.$m[2].'</a>';
		else
		return '<a  href="'.$m[1].'" >'.$m[2].'</a>';
		},
		$content);
		return $content;
	}
	add_filter('the_content', 'eol_add_external_link');
	$eol_external_attribute_add_nofollow = 0;
	$eol_external_attribute_add_blank = 0;
	}

	if(checked( $eol_external_attribute_add_nofollow, 1, false ))
	{
	/**
	* add links if nofollow
	*/
		function eol_add_external_link_nofollow($content) 
		{
			
			$content = preg_replace_callback(
			'/<a[^>]*href=["|\']([^"|\']*)["|\'][^>]*>([^<]*)<\/a>/i',
			function($m) {
			if (strpos($m[1], $_SERVER['SERVER_NAME']) === false)
			return '<a id="ext-link" href="'.$m[1].'" rel="nofollow">'.$m[2].'</a>';
			else
			return '<a  href="'.$m[1].'" >'.$m[2].'</a>';
			},
			$content);
			return $content;
		}
	add_filter('the_content', 'eol_add_external_link_nofollow');
	}
	if(checked( $eol_external_attribute_add_blank, 1, false )){
	/**
	* add links if _blank
	*/
		function eol_add_external_link_blank($content) {
			$content = preg_replace_callback(
			'/<a[^>]*href=["|\']([^"|\']*)["|\'][^>]*>([^<]*)<\/a>/i',
			function($m) {
			if (strpos($m[1], $_SERVER['SERVER_NAME']) === false)
			return '<a id="ext-link" href="'.$m[1].'" target="_blank">'.$m[2].'</a>';
			else
			return '<a  href="'.$m[1].'" >'.$m[2].'</a>';
			},
			$content);
			return $content;
		}
	add_filter('the_content', 'eol_add_external_link_blank');
	}
?>