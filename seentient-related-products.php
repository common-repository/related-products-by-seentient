<?php
/*
Plugin Name: Related Products by Seentient
Plugin URI: https://seentient.com/plugins/wordpress/
Description: Seentient Related Products Widget automatically finds and displays apparel relevant to your content on your blog article or product page.
Version: 1.3.0
Author: Seentient.com
Author URI: https://seentient.com/
*/
$seent_rp_version = '1.3.0'; // url-safe version string
$seent_rp_date = '2017-04-17'; // date this version was released, beats a version #

/*
Copyright 2017-2030 Seentient (info@seentient.com)

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
$seentient_rp_folder = 'seentient-related-products';
$seentient_rp_files = Array(
	'seentient-related-products.php',
);

function seentient_rp_drop_javascript ($html, $url) {		
	$c = "<div class=\"seentient-rp-container\" data-seent-url=\"".$url."\"></div>"
."<script>"
."window.Seentient = window.Seentient || {};"
."window.Seentient.AccessToken='".seentient_getAccessToken()."';"
."window.Seentient.WidgetServer='https://widgets.seentient.com';"
."(function() {var ca = document.createElement('script');ca.type='text/javascript';ca.async=true;ca.setAttribute('data-cfasync', false);ca.src = window.Seentient.WidgetServer+'/js/plugins/seentient.1.0.js';var t=document.getElementsByTagName('script')[0];t.parentNode.insertBefore(ca, t);})();"
."</script>";
	$html =  $html . "\n" . $c;

	return $html;
}

add_filter('the_content', 'seentient_rp_show_post');
function seentient_rp_show_post($content='') {
	$seent_show_post = get_option('seentient_show_post');
	if($seent_show_post == 'yes') {
		return seentient_rp_display_hook($content);
	}	
	else 
	{
		return $content;
	}
}

function seentient_rp_display_hook($content='') {
	if (
		(is_single()) or
		(is_page()) or
		0) {
		$url = get_permalink( $post->ID );
		$content = seentient_rp_drop_javascript($content, $url);		
	}
	return $content;
}

function seentient_getAccessToken () {
	$access_token = get_option('seentient_access_token');
	return $access_token;
}

/////////////////////////////////////////////////////
////////////// The widget settings ///////////////////////
/////////////////////////////////////////////////////
class SeentientRPWidget extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		$widget_ops = array( 'classname' => 'widget_seent_rp_widget', 'description' => 'Show Related Products' );
		$control_ops = array( 'id_base' => 'widget_seent_rp' );
		parent::__construct('widget_seent_rp', 'Related Products by Seentient', $widget_ops, $control_ops);
	}

	/**
	 * Widget frontend output
	 *
	 * @param array $args
	 * @param array $instance
	 * @author Seentient LLC
	 */
	function widget( $args, $instance ) {
		// Widget output
		extract( $args );
		$instance = wp_parse_args( (array) $instance, self::get_default_value() );
		$instance['title'] = apply_filters( 'title', empty( $instance['title'] ) ? '' : $instance['title'] );
		$instance['html'] = seentient_rp_display_hook();
		// No longer using extracted vars. This is here for backwards compatibility.
		extract( $instance );

		include( 'views/widget.php' );
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, self::get_default_value() );
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		// Output admin widget options form
		$instance = wp_parse_args( (array) $instance, self::get_default_value() );
		include( 'views/widget-admin.php' );
	}


	/**
	 * Render an array of default values.
	 *
	 * @return array default values
	 */
	private static function get_default_value() {
		$defaults = array(
			'title' => 'Related Products'
		);
		return $defaults;
	}
}
function seentient_wp_register_widgets() {
	register_widget( 'SeentientRPWidget' );
}
add_action( 'widgets_init', 'seentient_wp_register_widgets' );

/////////////////////////////////////////////////////
////////////// The admin page ///////////////////////
/////////////////////////////////////////////////////
add_filter( 'plugin_action_links_' . plugin_basename (__FILE__), 'seentient_add_settings_links');
function seentient_add_settings_links ( $links ) {
	$mylinks = array(
		'<a href="' . admin_url( 'options-general.php?page=Seentient' ) . '">'. "Settings" . '</a>',
	);
	return array_merge($mylinks,  $links);
}

/////////////////////////////////////////////////////
////////////// The admin settings page ///////////////////////
/////////////////////////////////////////////////////
// Hook the admin_menu display to add admin page
add_action('admin_menu', 'seentient_rp_admin_menu');
function seentient_rp_admin_menu() {
	add_submenu_page('options-general.php', 'Seentient', 'Seentient', 8, 'Seentient', 'seentient_rp_submenu');
}

// Plugin config/data setup
if (function_exists('register_activation_hook')) {
	// for WP 2
	register_activation_hook(__FILE__, 'seentient_rp_activation_hook');
}
function seentient_rp_activation_hook() {
	return seentient_rp_restore_config(false);
}
// restore built-in defaults, optionally overwriting existing values
function seentient_rp_restore_config($force=false) {
	if ($force || !get_option('seentient_access_token')) {
		update_option('seentient_access_token', 'SEENTIENT-APIACCESSTOKEN-REQUEST');
	}
	if ($force || !get_option('seentient_show_post')) {
		update_option('seentient_show_post', 'yes');
	}
	if ($force || !get_option('seentient_show_sidebar_widget')) {
		update_option('seentient_show_sidebar_widget', 'yes');
	}
}
function seentient_rp_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

// Sanity check the upload worked
function seentient_rp_upload_errors() {
	global $seentient_rp_files, $seentient_rp_folder;

	$cwd = getcwd(); // store current dir for restoration
	if (!@chdir('../wp-content/plugins'))
		return "Couldn't find wp-content/plugins folder. Please make sure WordPress is installed correctly.";
	if (!is_dir($seentient_rp_folder))
		return "Can't find ".$seentient_rp_folder." folder.";
	chdir($seentient_rp_folder);

	foreach($seentient_rp_files as $file) {
		if (substr($file, -1) == '/') {
			if (!is_dir(substr($file, 0, strlen($file) - 1)))
				return "Can't find folder:" . " <kbd>$file</kbd>";
		} else if (!is_file($file))
		return "Can't find file:" . " <kbd>$file</kbd>";
	}

	chdir($cwd); // restore cwd

	return false;
}
function seentient_rp_submenu() {
	global $seentient_rp_files, $seentient_rp_folder;

	// update options in db if requested
	if ($_REQUEST['restore']) {
		seentient_rp_restore_config(True);
		seentient_rp_message("Restored all settings to defaults.");
		
	} else if ($_REQUEST['save']) {
		if (array_key_exists ("seent_access_token", $_REQUEST)) {
			update_option('seentient_access_token', $_REQUEST['seent_access_token']);
		}
		$seent_show_post = $_REQUEST['seent_show_post']? $_REQUEST['seent_show_post']: 'no';
		update_option('seentient_show_post', $seent_show_post);
		
		$seent_show_sidebar_widget = $_REQUEST['seent_show_sidebar_widget']? $_REQUEST['seent_show_sidebar_widget']: 'no';
		update_option('seentient_show_sidebar_widget', $seent_show_sidebar_widget);
		
		seentient_rp_message("Saved changes.");
	}
	//if ($str = seentient_rp_upload_errors()) {
	//	seentient_rp_message("$str</p><p>" . "In your plugins/".$seentient_rp_folder." folder, you must have these files:" . ' <pre>' . implode("\n", $seentient_rp_files) ); 
	//}
	$seent_access_token = seentient_getAccessToken();
	$seent_show_post = get_option('seentient_show_post');
	$seent_show_sidebar_widget = get_option('seentient_show_sidebar_widget');
?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<div class="wrap" id="seentient_rp_options">
			<table width="100%">
				<tr>
					<td valign="top">
						<h3>Seentient Configuration</h3>
						<br/>
						<fieldset id="seentient_rp_access_token">
						API Access Token:<br/>
						<input type="text" name="seent_access_token" size="40" value="<?php echo $seent_access_token;?>" /> 
						</fieldset>
						<br/>
						<fieldset id="seentient_rp_show_post">
						<input type="checkbox" name="seent_show_post" value="yes" <?php if($seent_show_post=='yes')echo 'checked'; ?> /> 
						Show on every post
						</fieldset>
						<fieldset id="seentient_rp_show_post">
						<input type="checkbox" name="seent_show_sidebar_widget" value ="yes" <?php echo($seent_show_sidebar_widget=='yes')?  'checked':  '';?> /> 
						Show on sidebar only on single post 
						</fieldset>

						<p class="submit">
							<input class="seentient-button seentient-button-green" name="save" id="save" tabindex="3" value=Apply and Save" type="submit" />
							<br/><br/>
							<input class="seentient-button seentient-button-red" name="restore" id="restore" tabindex="3" value="Restore Built-in Defaults" type="submit" onclick="return confirm('Are you shure want to restore built-in defaults?');" />
						</p>
					</td>
				</tr>
			</table>
		</div>
	</form>
	<div class="wrap">
		<p><?php echo '<a href="https://seentient.com/plugins/wordpress/">Related Products by Seentient</a> is copyright 2016-2030 by Seentient, released under the GNU GPL version 2 or later.'; ?></p>
	</div>
<?php
} //end function seentient_rp_submenu
?>