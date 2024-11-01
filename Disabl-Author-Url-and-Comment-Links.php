<?php
/*
	Plugin Name: Disable Author Url and Comment Links
	Plugin URI: http://osamashabrez.com/wp-disable-author-url-and-comment-links/
	Description: DAUnCL helps you keep your comments <strong>clean from spam links left by automated or manual comment spammers</strong> who are after your valueable traffic. Based on <a href="http://simplehtmldom.sourceforge.net">SimpleHTMLDom Library</a> by S.C. Chen, this plugin removes any external link from your blog comments and comment authors name which are not explicitly allowed by the administrator.
	Author: M. Osama Shabrez
	Email: contact@osamashabrez.com
	Version: 2.2
	Author URI: http://www.osamashabrez.com/
*/

require_once('lib/simple_html_dom.php');
$DAUnCL = get_option ( 'DAUnCL_settings' );
if( $DAUnCL === false ) {
	$DAUnCL = array (
		'version' => '2.0',
		'allowed' => "google.com\rwordpress.org\r"
	);
	update_option ( 'DAUnCL_settings', $DAUnCL );
	// removing settings of previous version if any
	$disable_comment_links_options = get_option( 'disable_comment_links_settings' );
	if( $disable_comment_links_options === true ) {
		delete_option('disable_comment_links_settings');
	}
}

if( ! function_exists( 'DAUnCL_callbackFunction' ) )	{
	function DAUnCL_callbackFunction($element) {
		global $DAUnCL;
		$dauncl_allowedHosts = explode( PHP_EOL, $DAUnCL['allowed'] );
		if ($element->tag=='a') {
			$host = parse_url( $element->href, PHP_URL_HOST );
			if ( !in_array( $host, $dauncl_allowedHosts) )
				$element->outertext = $element->innertext;
		}
	}
}

if( ! function_exists( 'DAUnCL_filterFunction' ) )	{
	function DAUnCL_filterFunction( $comment )	{
		global $DAUnCL;
		$sd = new simple_html_dom();
		$sd->load( $comment );
		$sd->set_callback('DAUnCL_callbackFunction');
		$comment = $sd->save();
		$sd->clear();
		unset($sd);
		return $comment;
	}
	add_filter( 'get_comment_author_link', 'DAUnCL_filterFunction' );
	add_filter( 'comment_text', 'DAUnCL_filterFunction' );
	add_filter( 'comment_text_rss', 'DAUnCL_filterFunction' );
}

if( ! function_exists( 'disable_comment_links_page' ) ) {
	function disable_comment_links_page() {
	global $DAUnCL;
	echo '<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2>Disable Comment Links Settings</h2>
			<p>&nbsp;</p>
			<form action="options.php" method="POST">';
			settings_fields('DAUnCL_group');
			echo '
			<div class="shadowed-box stuffbox" style="box-shadow:-3px 3px 3px #666666; max-width: 750px;float:left;">
				<h3 class="hndle" style="padding:10px;font-family:Georgia;font-weight:normal;font-size:18px;">Plugin Settings</h3>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">Allowed domains</th>
							<td>
								<fieldset><legend class="screen-reader-text"><span>Allowed domains</span></legend>
								<label for="allowed">Add URLs (websites) you want to allow. One address per line. </label><br/>
								<small>
									<li style="margin-bottom: -4px;margin-left:20px;">Add your own domain to allow internal links</li>
									<li style="margin-bottom: -4px;margin-left:20px;">Add URLs <span style="color:red;">without \'http://\'</span></li>
									<li style="margin-left:20px;">www and non-www are considered two different URLs. Add them both if required</li>
								</small>
								<textarea name="DAUnCL_settings[allowed]" id="DAUnCL_settings[allowed]" rows="8" style="width:450px;">' . $DAUnCL['allowed'] . '</textarea>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit" style="text-align:center;"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes" style="border-radius:25px;width:150px;height:50px;" /></p>
			</div>
			<div class="shadowed-box stuffbox" style="box-shadow:-3px 3px 3px #666666; width: 300px;float:left;margin-left:25px;">
				<h3 class="hndle" style="padding:10px;font-family:Georgia;font-weight:normal;font-size:18px;">Plugin Information</h3>
				<p style="padding:0 10px;"><strong>2.0</strong> &rarr; The version you are currently running.</p>
				<p style="padding:0 10px;">Email Author &rarr; <strong>contact@osamashabrez.com</strong></p><br/>
				<p style="padding:0 10px;text-align:justify;">DAUnCL helps you keep your comments <strong>clean from spam links left by automated or manual comment spammers</strong> who are after your valueable traffic.</p>
				<p style="padding:0 10px;text-align:justify;">Based on <a href="http://simplehtmldom.sourceforge.net">SimpleHTMLDom Library</a> by S.C. Chen, this plugin removes any external link from your blog comments and comment authors name which are not explicitly allowed by the administrator.</p>
			</div>
			<div class="shadowed-box stuffbox" style="box-shadow:-3px 3px 3px #666666; width: 300px;float:left;margin-left:25px;">
				<h3 class="hndle" style="padding:10px;font-family:Georgia;font-weight:normal;font-size:18px;">Appreciate My Work</h3>
				<p style="padding:0 10px;text-align:center;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
				</p>
				<p><li style="list-style:none;margin-left:20px;float:left;">$1 - Buy me a coffee</li>
					<li style="list-style:none;margin-right:20px;float:right;">$5 - Buy me a lunch</li>
					<li style="list-style:none;margin-left:20px;float:left;">$10 - Support my work</li>
					<li style="list-style:none;margin-right:20px;float:right;">$20 - Join the fan club</li></p>
					<div style="clear:both;"></div>
			</div>
			</form>
			</div>
			';
	}
}
add_filter('plugin_row_meta',  'Register_Plugins_Links', 10, 2);
function Register_Plugins_Links ($links, $file) {
   $base = plugin_basename(__FILE__);
   if ($file == $base) {
	   $links[] = '<a href="options-general.php?page=dauncl_settings">' . __('Settings') . '</a>';
	   $links[] = '<a target="_blank" href="http://osamashabrez.com/wp-disable-author-url-and-comment-links/">' . __('FAQ') . '</a>';
	   $links[] = '<a target="_blank" href="http://osamashabrez.com/wp-disable-author-url-and-comment-links/">' . __('Support') . '</a>';
	   $links[] = '<a target="_blank" href="http://twitter.com/OsamaShabrez">' . __('@OsamaShabrez') . '</a>';
   }
   return $links;
}
function disable_comment_links_page_hook() {
	add_options_page('Disable Comment Links Settings', 'DAUnCL Settings', 'manage_options', 'dauncl_settings', 'disable_comment_links_page');
}
function disable_comment_links_settings_hook() {
	register_setting('DAUnCL_group','DAUnCL_settings');
}
add_action('admin_menu','disable_comment_links_page_hook');
add_action('admin_init','disable_comment_links_settings_hook');
?>
