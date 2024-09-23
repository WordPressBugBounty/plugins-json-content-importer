<?php
add_action('admin_menu', 'jci_create_menu');

function jci_create_menu() {
	//create new top-level menu
	#add_menu_page(__('JSON Content Importer', 'json-content-importer'), __('JSON Content Importer', 'json-content-importer'), 'administrator', __FILE__, 'jci_settings_page',plugins_url('/images/icon-16x16.png', __FILE__));
	add_menu_page(__('JSON Content Importer', 'json-content-importer'), __('JSON Content Importer', 'json-content-importer'), 'administrator', 'unique_jci_menu_slug', 'jci_settings_page',plugins_url('/images/icon-16x16.png', __FILE__));
	//call register settings function
	add_action( 'admin_init', 'register_jcisettings' );
}

function register_jcisettings() {
	//register our settings
	register_setting( 'jci-options', 'jci_json_url' );
	register_setting( 'jci-options', 'jci_enable_cache' );
	register_setting( 'jci-options', 'jci_cache_time' );
	register_setting( 'jci-options', 'jci_cache_time_format' );
	register_setting( 'jci-options', 'jci_oauth_bearer_access_key' );
	register_setting( 'jci-options', 'jci_http_header_default_useragent' );
	register_setting( 'jci-options', 'jci_gutenberg_off' );
	register_setting( 'jci-options', 'jci_sslverify_off' );
	register_setting( 'jci-options', 'jci_api_errorhandling' );
}

/* define tabs for plugin-admin-menu BEGIN*/
function jci_admin_tabs( $current = 'welcome' ) {
    $tabs = array(
          'welcome' => 'Welcome to JCI',
          'checkinstall' => __('Check Installation', 'json-content-importer'),
          'settings' => __('Basic Settings', 'json-content-importer'),
          'step1' => __('Step 1: Get data', 'json-content-importer'),
          'step2' => __('Step 2: Use data', 'json-content-importer'),
          'support' => __('Support', 'json-content-importer'),
          'bugbounty' => __('JCI BugBounty Program', 'json-content-importer'),
          'gdpr' => __('GDPR', 'json-content-importer'),
          'jcipro' => __('JCI PRO', 'json-content-importer'),
          'uninstall' => __('Uninstall', 'json-content-importer'),
          );

    echo '<h2 class="nav-tab-wrapper">';
	echo "<style>";
	echo ".nav-tab-active, .nav-tab-active:hover {background-color: #0071a1;color: #FFF;}";
	echo ".nav-tab-active-pro, .nav-tab-active-pro:hover {background-color: #356306;color: #FFF;}";
	echo "</style>";
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
		if ('jcipro'==$tab) {
			$class = ' nav-tab-active-pro';
		}
        echo "<a class='nav-tab".esc_attr($class)."' href='?page=unique_jci_menu_slug&tab=".esc_attr($tab)."'>".esc_html($name)."</a>";

    }
    echo '</h2>';
}
/* define tabs for plugin-admin-menu END*/

/* save settings BEGIN*/
function jci_save_check_value($key, $val, $changefound) {
  $areThereChanges = $changefound;
  #$inputValPost = trim((($_POST[$val]) ?? '')); # remove spaces at begin / end
  $inputValPost = trim(($val) ?? ''); # remove spaces at begin / end
  if (!($inputValPost == get_option($key))) {
    update_option( $key, $inputValPost );
    $areThereChanges = TRUE;
  }
  return $areThereChanges;
}
/* save settings END*/


/* save settings BEGIN*/
function jci_handle_input() {
	# check if call is ok
	$jci_settings_submit = ($_POST["jci-settings-submit"] ?? '');
	$noncein = $_REQUEST['_wpnonce'] ?? '';
	if ("savesettings"==$jci_settings_submit ) {   
		$chknon = wp_verify_nonce($noncein, 'jci-set-page' );
		if (!$chknon) {
			return [__('Saving failed: Nonce-Error', 'json-content-importer'), "red"];  # invalid nonce, hence invalid call
		}	
		#isset($_REQUEST['_wpnonce']) ? $nonce = $_REQUEST['_wpnonce'] : $nonce = NULL;
		#$nonceCheck = wp_verify_nonce( $nonce, "jci-set-page" );
		#if (!$nonceCheck) {   return [__('Saving failed: Nonce-Error', 'json-content-importer'), "red"]; } # invalid nonce, hence invalid call
	} else {
		return NULL; #ok, no input handling needed
	}
	if ("savesettings"==$jci_settings_submit) {   
		global $pagenow;
		$currenttab = htmlentities(($_GET['tab'] ?? 'welcome'));
		$currentpage = htmlentities(($_GET['page'] ?? ''));
		if ( $pagenow == 'admin.php' && $currentpage == 'unique_jci_menu_slug' ){
			$areThereChanges = FALSE;
			switch ( $currenttab ){
			case 'settings' :
				$areThereChanges = jci_save_check_value("jci_sslverify_off", ($_POST["jci_sslverify_off"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_gutenberg_off", ($_POST["jci_gutenberg_off"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_cache_time", ($_POST["jci_cache_time"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_cache_time_format",($_POST["jci_cache_time_format"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_api_errorhandling",($_POST["jci_api_errorhandling"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_enable_cache", ($_POST["jci_enable_cache"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_oauth_bearer_access_key", ($_POST["jci_oauth_bearer_access_key"] ?? ''), $areThereChanges);
				$areThereChanges = jci_save_check_value("jci_http_header_default_useragent", ($_POST["jci_http_header_default_useragent"] ?? ''), $areThereChanges);
				if ($areThereChanges) {		return [__('Saving successful: Changed values saved', 'json-content-importer'), "#ccff33"];   } else {         return [esc_html_e('Nothing changed, nothing saved', 'json-content-importer'), "#ccff33"];   }
			break;
			case 'uninstall' :
				$areThereChanges = jci_save_check_value("jci_uninstall_deleteall", $_POST["jci_uninstall_deleteall"], $areThereChanges);
				if ($areThereChanges) {		return [__('Saving successful: Changed values saved', 'json-content-importer'), "#ccff33"];   } else {         return [ esc_html_e('Nothing changed, nothing saved', 'json-content-importer'), "#ccff33"];   }
			break;
			}
		}
	}
	return [__('Invalid call: Nothing changed', 'json-content-importer'), "red"];
}
/* save settings END*/


function jci_settings_page() {
  $errorLevelSaveOptionsArr = jci_handle_input(); # save new settings if needed
?>
<div class="wrap">
<style>	.precode{background-color: #EBECE4; } .jciul { list-style-type: square; margin-left: 20px;} #wpfooter { position: relative;} </style>
<h2><?php esc_html_e('JSON Content Importer: Check, Set, Start, Use, Get Support', 'json-content-importer') ?></h2>
  <?php
  global $pagenow;
  $currenttab = htmlentities(($_GET['tab'] ?? 'welcome'));
  $currentpage = htmlentities(($_GET['page'] ?? ''));
  if ( $pagenow == 'admin.php' && $currentpage == 'unique_jci_menu_slug' ){
	jci_admin_tabs($currenttab);
  } 
 ?>
 </div>

	<form method="post" action="?page=unique_jci_menu_slug&tab=<?php echo esc_attr($currenttab); ?>">
	<?php 
		settings_fields( 'jci-options' ); 
		do_settings_sections( 'jci-options' ); 
		wp_nonce_field( "jci-set-page" );
	?>
	<table class="widefat striped">
    <?php
		if (!is_null($errorLevelSaveOptionsArr)) {
			echo '<tr><td bgcolor='.esc_attr($errorLevelSaveOptionsArr[1]).'><b>'.esc_html($errorLevelSaveOptionsArr[0]).'</b></td></tr>';
		}
	  
	
		switch ( $currenttab ){
		case 'example' :
    ?>
		<tr><td>
			<h2><?php esc_html_e('Example', 'json-content-importer'); ?></h2>
			<?php		$exurl = plugin_dir_url(__FILE__)."json/gutenbergblockexample1.json"; ?>
			<strong><?php esc_html_e('Example with this URL:', 'json-content-importer') ?> <a href="<?php echo esc_url($exurl); ?>" target="_blank"><?php echo esc_html($exurl); ?></a></strong><br>
			<i>
			<?php
            $example = "[jsoncontentimporter ";
            $example .= "url=\"".$exurl."\" debugmode=\"10\" basenode=\"level1\"]\n";
            $example .= "{start}<br>{subloop-array:level2:-1}{level2.key}\n<br>\n{subloop:level2.data:-1}id: {level2.data.id}\n<br>\n{/subloop:level2.data}{/subloop-array:level2}\n";
            $example .= "\n[/jsoncontentimporter]\n";
            $example = htmlentities($example);
            echo "<code>".esc_html($example)."</code>";
			?> 
			</i>
        </td></tr>
	<?php
		break;
        case 'settings' :
    ?>
		<tr><td>
			<h1><?php esc_html_e('Basic Settings', 'json-content-importer'); ?></h1>
			<input type="hidden" name="jci-settings-submit" value="savesettings" />
			<input type="submit" name="Submit"  class="button-primary" value="<?php esc_html_e('Store and update the following Settings', 'json-content-importer'); ?>"/>
        </td></tr>
		<tr><td>
			<h2><?php esc_html_e('SSL verification?', 'json-content-importer'); ?></h2>
			<strong><?php esc_html_e("Sometimes, the API's HTTPS/SSL/TLS certificate may not be valid, resulting in an error instead of JSON. If this occurs, you can try deactivating the SSL verification here", 'json-content-importer') ?>:</strong><br>
			<?PHP
			$val_jci_sslverify_off = get_option('jci_sslverify_off') ?? 3;
			if (1!=$val_jci_sslverify_off && 2!=$val_jci_sslverify_off) {
				$val_jci_sslverify_off = 3;
			}
			?>
			<input type="radio" name="jci_sslverify_off" value="1" <?php echo ($val_jci_sslverify_off == 1)?"checked=checked":""; ?> /> <?PHP esc_html_e('Switch OFF SSL verification (send sslverify=false)', 'json-content-importer'); ?><br>
			<input type="radio" name="jci_sslverify_off" value="2" <?php echo ($val_jci_sslverify_off == 2)?"checked=checked":""; ?> /> <?PHP esc_html_e('Switch ON SSL verification (send sslverify=true)', 'json-content-importer'); ?><br>
			<input type="radio" name="jci_sslverify_off" value="3" <?php echo ($val_jci_sslverify_off == 3)?"checked=checked":""; ?> /> <?PHP esc_html_e('don\'t send any additional Info about SSL verification (WP-Default)', 'json-content-importer'); ?><br>
        </td></tr>

		<tr><td>
			<h2><?php esc_html_e('Cacher: Saving API JSON data locally saves time by avoiding HTTP requests.', 'json-content-importer'); ?></h2>
			<strong><?php esc_html_e('Enable Cache', 'json-content-importer') ?>:</strong> <input type="checkbox" name="jci_enable_cache" value="1" <?php echo (get_option('jci_enable_cache') == 1)?"checked=checked":""; ?> />
			<?php $jci_cache_time = get_option('jci_cache_time') ?? 0; 
				if (!is_numeric($jci_cache_time)) {
					$jci_cache_time = 0;
					update_option('jci_cache_time', $jci_cache_time); 
				}
			?>
        	&nbsp;&nbsp;&nbsp; <?php esc_html_e('Reload json from web - if cachefile is older than', 'json-content-importer') ?> <input type="text" name="jci_cache_time" size="2" value="<?php echo esc_attr($jci_cache_time); ?>" />
			<select name="jci_cache_time_format">
				<option value="minutes" <?php echo (get_option('jci_cache_time_format') == 'minutes')?"selected=selected":""; ?>><?php esc_html_e('Minutes', 'json-content-importer') ?></option>
				<option value="days" <?php echo (get_option('jci_cache_time_format') == 'days')?"selected=selected":""; ?>><?php esc_html_e('Days', 'json-content-importer') ?></option>
				<option value="month" <?php echo (get_option('jci_cache_time_format') == 'month')?"selected=selected":""; ?>><?php esc_html_e('Months', 'json-content-importer') ?></option>
				<option value="year" <?php echo (get_option('jci_cache_time_format') == 'year')?"selected=selected":""; ?>><?php esc_html_e('Years', 'json-content-importer') ?></option>
			</select> (use dot for non-integer numbers)
			<hr>
			<strong><?php esc_html_e('Handle unavailable APIs', 'json-content-importer') ?>:</strong> 
			<br>
			<?php 
			$pluginOption_jci_api_errorhandling = get_option('jci_api_errorhandling') ?? 0;
			if (empty($pluginOption_jci_api_errorhandling)) {
				#update_option('jci_api_errorhandling', 0);
				$pluginOption_jci_api_errorhandling = 0;
			}
			?>
		  
			<?php esc_html_e('If the request to an API to retrieve JSON fails, the plugin can attempt to use a possibly cached JSON (ensure that the cache is populated at least once with a successful API request)', 'json-content-importer') ?>:<br>
			<input type="radio" name="jci_api_errorhandling" value="0" <?php echo ($pluginOption_jci_api_errorhandling == 0)?"checked=checked":""; ?> />
			<?php esc_html_e('Do not try to use cached JSON', 'json-content-importer') ?><br>
			<input type="radio" name="jci_api_errorhandling" value="1" <?php echo ($pluginOption_jci_api_errorhandling == 1)?"checked=checked":""; ?> />
			<?php esc_html_e('If the API-http-answercode is not 200: try to use cached JSON', 'json-content-importer') ?><br>
			<input type="radio" name="jci_api_errorhandling" value="2" <?php echo ($pluginOption_jci_api_errorhandling == 2)?"checked=checked":""; ?> />
			<?php esc_html_e('If the API sends invalid JSON: try to use cached JSON', 'json-content-importer') ?><br>
			<input type="radio" name="jci_api_errorhandling" value="3" <?php echo ($pluginOption_jci_api_errorhandling == 3)?"checked=checked":""; ?> />
			<?php esc_html_e('Recommended (not switched on due to backwards-compatibility): Try to use cached JSON if the API-answercode is not 200 OR sends invalid JSON', 'json-content-importer') ?><br>
        </td></tr>

		<tr><td>
			<h2><?php esc_html_e('Gutenberg?', 'json-content-importer'); ?></h2>
			<a href="https://www.youtube.com/watch?v=t3m0PmNyOHI" target="_blank">Video: <?php esc_html_e('Easy JSON Content Importer - Gutenberg-Block', 'json-content-importer') ?></a><br>
			<?php esc_html_e('Switch off Gutenberg features (maybe a site builder needs that)', 'json-content-importer') ?>: <input type="checkbox" name="jci_gutenberg_off" value="1" <?php echo (get_option('jci_gutenberg_off') == 1)?"checked=checked":""; ?> />
        </td></tr>
		
		<tr><td>
			<h2><?php esc_html_e('API-Request: If needed, send oAuth Bearer Authentication', 'json-content-importer'); ?></h2>
			<?php esc_html_e("The API website might provide you with a static 'Bearer' token (= ACCESSKEY) that must be used when making API requests. There are multiple ways in which APIs handle this", 'json-content-importer') ?>:
			<ul class=jciul>
				<li><?php esc_html_e('Send "Bearer Authorization:ACCESSKEY"', 'json-content-importer') ?>:
				<?php esc_html_e("Insert 'Authorization: ACCESSKEY' in the following text field, and the 'Bearer' part will be added automatically.", 'json-content-importer') ?></li>
				<li><?php esc_html_e('Send "Authorization:ACCESSKEY"', 'json-content-importer') ?>: 
				<?php esc_html_e("Insert 'nobearer Authorization: ACCESSKEY' in the following text field, and no 'Bearer' will be added.", 'json-content-importer') ?></li>
				<li><?php esc_html_e('Send Header "HEADER_KEY:HEADER_VALUE"', 'json-content-importer') ?>: 
				<?php esc_html_e("Insert 'header HEADER_KEY1:HEADER_VALUE1#HEADER_KEY2:HEADER_VALUE2' in the following text field, and no 'Bearer' will be added. E.g.: 'header User-Agent:JCIfree'", 'json-content-importer') ?></li>
			</ul>
			<?php 
				$jci_oauth_bearer_access_key = get_option('jci_oauth_bearer_access_key'); 
				$jci_oauth_bearer_access_key = stripslashes($jci_oauth_bearer_access_key);
			?>
			<input type="text" name="jci_oauth_bearer_access_key" value="<?php echo esc_html($jci_oauth_bearer_access_key); ?>" size="60"/>
        </td></tr>
		<tr><td>
			<h2><?php esc_html_e('Send Browser-Useragent (some APIs need that)', 'json-content-importer') ?>:</h2> <input type="checkbox" name="jci_http_header_default_useragent" value="1" <?php echo (get_option('jci_http_header_default_useragent') == 1)?"checked=checked":""; ?> />
			<?php esc_html_e('Send Useragent \'JCI WordPress-Plugin - free Version\'', 'json-content-importer') ?>
	   </td></tr>
		<tr><td>
			<input type="submit" name="Submit"  class="button-primary" value="<?php esc_html_e('Store and update the following Settings', 'json-content-importer'); ?>"/>
        </td></tr>
		<?php
	break;
	case 'uninstall' :
	?>
	<tr><td>
           <h1><?php esc_html_e('Uninstall', 'json-content-importer'); ?></h1>
           <?php 
		   esc_html_e('By default, not all data of this plugin is deleted. If the following checkbox is NOT activated (default settings), 
		   you can deactivate and delete the free JSON Content Importer Plugin without any risk.', 'json-content-importer');
		   echo "<br>";
		   esc_html_e('After reinstalling the free JCI plugin, all data will still be retained. 
		   Only when the following checkbox is activated, templates, settings, etc., will also be deleted when the free JCI Plugin is deleted.', 'json-content-importer') 
		   ?>: 
           <br>
           <input type="checkbox" name="jci_uninstall_deleteall" value="1" <?php echo (get_option('jci_uninstall_deleteall') == 1)?"checked=checked":""; ?> /> <?php 
		   esc_html_e('delete all, incl. templates and options', 'json-content-importer'); ?>
        </td>
      </tr>
      <tr><td>
			<input type="hidden" name="jci-settings-submit" value="savesettings" />
			<input type="submit" name="Submit"  class="button-primary" value="<?php esc_html_e('Store and update the following Settings', 'json-content-importer'); ?>"/>
        </td></tr>
	<?php	
		break;
        case 'step1' :
    ?>
		<tr><td>
		<h1><?php esc_html_e('Step 1: Retrieve the data from the API', 'json-content-importer'); ?></h1>
		<h2><?php esc_html_e('Gutenberg-Block-Way', 'json-content-importer'); ?></h2>
		<ul class=jciul>
			<li><?php esc_html_e('This JCI-Plugin adds a "JSON Content Importer FREE" Gutenberg Block.', 'json-content-importer'); ?></li>
			<li><?php esc_html_e('It is highly recommended to try out the JCI block and familiarize yourself with its functionality using the provided example', 'json-content-importer'); ?></li>
			<li><?php esc_html_e('Outdated, but still helpful:', 'json-content-importer'); ?> 
				<a href="https://www.youtube.com/watch?v=t3m0PmNyOHI" target="_blank"><?php esc_html_e('The video "Easy JSON Content Importer" shows you how to use the JCI Block Version 1.3.0', 'json-content-importer'); ?> </a><br>
				<?php esc_html_e('Version 1.4 introduces a JCI-Template generator, which provides a blueprint for displaying the JSON data.', 'json-content-importer'); ?>
				</li>
			<li><?php esc_html_e('In the block settings, you can input the API URL and create a JCI template - you\'ll immediately see the API response and the merged JSON & template: Switch on the debugmode in the Block for that.', 'json-content-importer'); ?></li>
			<li><?php esc_html_e('The Debugmode of the JCI Block also gives a Shortcode you can copy paste.', 'json-content-importer'); ?></li>
			</ul>
       </td></tr>

		<tr><td>
		<h2><?php esc_html_e('Shortcode-Way', 'json-content-importer'); ?></h2>
		<?php $exurl = plugin_dir_url(__FILE__)."json/gutenbergblockexample1.json"; ?>
		<?php esc_html_e('Local test API-URL', 'json-content-importer') ?>: <a href="<?php echo esc_url($exurl); ?>" target="_blank"><?php echo esc_url($exurl); ?></a><br>
		<?php esc_html_e('Test Shortcode', 'json-content-importer') ?>:<br>
		<?php
            $example = "[jsoncontentimporter ";
            $example .= "url=\"".$exurl."\" debugmode=\"10\"]\n";
            $example .= "hello {hello}";
            $example .= "[/jsoncontentimporter]\n";
            $example = htmlentities($example);
            echo "<pre class=precode>".esc_html($example)."</pre>";
		
		esc_html_e('Use this Shortcode on a page, please. This should give you debug info', 'json-content-importer'); ?> 
		(<b class=precode>debugmode="10"</b>) <?php esc_html_e('and "hello world"', 'json-content-importer'); ?> 
		(<b class=precode><?php esc_html_e('hello {hello}', 'json-content-importer'); ?> </b>).
		<?php esc_html_e('If this is the case, you know that the plugin is working and is able to get data from your Wordpress.', 'json-content-importer'); ?> 
		<p>
		<?php esc_html_e('Now you can replace the local URL by the remote API-URL you want to use. Check the debug info on the JSON given by the URL:
		Is it the JSON you expected?', 'json-content-importer'); ?> 
		<br>
		<ul class=jciul>
		<li><?php esc_html_e('YES, that looks like the JSON the API should give: Proceed to', 'json-content-importer'); ?>  <a href="?page=unique_jci_menu_slug&tab=step2"><?php esc_html_e('Step 2', 'json-content-importer'); ?></a></li>
		<li><?php esc_html_e('NO! The API does not give the expected JSON: See below "API does not give the expected JSON?"', 'json-content-importer'); ?> </li>
		</ul>
        </td></tr>
		<tr><td>
		
		<h2><?php esc_html_e('API does not give the expected JSON?', 'json-content-importer'); ?></h2>
		<?php esc_html_e('There can be several reasons why the API-URL does not give the epxected JSON. Maybe the URL is not ok or the API expects more than a simple URL: By the http-errorcode or JSON with an errormessage the API hopefully tells what went wrong (not all APIs do tht, unfortunately). 
		E. g. Some APIs expect a API-KEY, some Authentication, some POST-requests etc.. Check your API manual for that.
		Typical situations are:', 'json-content-importer'); ?>
		<ul class=jciul>
		<li><?php esc_html_e('The API-URL is not correct, Errormessages like 404 etc.: Check the API-manual, please.', 'json-content-importer'); ?></li>
		<li><?php esc_html_e('Basic Authentication: https://USERNAME:PASSWORT@www... sends USERNAME and PASSWORT to the API doing the Authentication.', 'json-content-importer'); ?></li>
		<li><?php esc_html_e('API-KEY: https://www...?apikey=WHATEVER sends the Parameter "apikey" with value "WHATEVER" (you might get when registering at the API-Website) to the API.', 'json-content-importer'); ?></li>
		<li><?php esc_html_e('Browser-Useragent: Some APIs expect a Browser-Useragent-Info in the http-Header. Check the Box at "Basic Settings" for that.', 'json-content-importer'); ?></li>
		<li><?php esc_html_e('Send "Bearer" for authentication: Some APIs expect a so called "Bearer" in the http-Header. This is usually a Token you get at the API-Website. 
		If needed: Insert the Token at "Basic Settings".', 'json-content-importer'); ?></li>
		<li><?php esc_html_e('No JSON but something else: The free JCI Plugin can handle JSON only. Check the API-Manual on how to alter the URL to get JSON (if possible). 
		The JCI-PRO Plugin can handle any input.', 'json-content-importer'); ?></li>
		<li><?php esc_html_e('Some APIs use oAuth2, special ways to calc a Token etc.. The free JCI Plugin can\'t handle this, the JCI-PRO Plugin can.', 'json-content-importer'); ?></li>
		<li><a href="https://doc.json-content-importer.com/json-content-importer/step-1-data-access/" target="_blank"><?php esc_html_e('more on how you can get access to the data see at doc.json-content-importer.com', 'json-content-importer'); ?></a></li>
		</ul>
        </td></tr>
	<?php	
		break;
        case 'step2' :
    ?>
		<tr><td>
		<h1><?php esc_html_e('Step 2: Use data', 'json-content-importer'); ?></h1>
			<h2><?php esc_html_e('Gutenberg-Block-Way', 'json-content-importer'); ?></h2>
			<?php esc_html_e('Click on "Create JCI-Template for JSON". This will open a window with the generated template based on the complete JSON data. 
			This template is inserted into the template box of the block. By clicking on "Try Template", the template and the JSON are merged and displayed on the left side.', 'json-content-importer'); ?>
			<p>
			<?php esc_html_e('Then edit the template as you like.', 'json-content-importer'); ?>
       </td></tr>
		<tr><td>
		<h2><?php esc_html_e('Shortcode-Way: Simple Example', 'json-content-importer'); ?></h2>
		<?php $exurl = plugin_dir_url(__FILE__)."json/gutenbergblockexample1.json"; ?>
		<?php esc_html_e('API-URL', 'json-content-importer') ?>: <a href="<?php echo esc_url($exurl); ?>" target="_blank"><?php echo esc_html($exurl); ?></a><br>
		<?php esc_html_e('Test Shortcode', 'json-content-importer') ?>:<br>
		<?php
            $example = "[jsoncontentimporter ";
            $example .= "url=\"".$exurl."\" debugmode=\"10\" basenode=\"level1\"]\n";
            $example .= "{start}<br>\n{subloop-array:level2:-1}\n{level2.key}\n{subloop:level2.data:-1}id: {level2.data.id}{/subloop:level2.data}\n{/subloop-array:level2}\n";
            $example .= "[/jsoncontentimporter]\n";
            $example = htmlentities($example);
            echo "<pre class=precode>".esc_html($example)."</pre>";
		?> 
		<?php esc_html_e('Insert this Shortcode on a page: It displays debug info', 'json-content-importer'); ?>
		(<b class=precode>debugmode="10"</b>) <?php esc_html_e('and JSON data starting with the node', 'json-content-importer'); ?> (<b class=precode>basenode="level1"</b>).
        </td></tr>
		<tr><td>
		<h2><?php esc_html_e('Videos showing examples', 'json-content-importer'); ?></h2>
			<ul class=jciul>
				<li><a href="https://www.youtube.com/watch?v=IiMfE_CUPBo" target="_blank"><?php esc_html_e('Video How to: First Shortcode with JSON Content Importer', 'json-content-importer') ?></a> </li>
				<li><a href="https://www.youtube.com/watch?v=GJGBPvaKZsk" target="_blank"><?php esc_html_e('Video How to: Wikipedia API, JSON Content Importer and WordPress', 'json-content-importer') ?></a></li>
		<li><a href="https://doc.json-content-importer.com/json-content-importer/free-show-the-data/" target="_blank"><?php esc_html_e('see doc.json-content-importer.com for more syntax details', 'json-content-importer') ?></a> </li>
		<li><a href="http://api.json-content-importer.com/category/free-s/" target="_blank"><?php esc_html_e('see api.json-content-importer.com for examples', 'json-content-importer') ?></a></li>
					</ul>		
        </td></tr>
		<tr><td>
		<h2><?php esc_html_e('JCI-Shortcode: Parameter and Template', 'json-content-importer'); ?></h2>
		<?php esc_html_e('This is the basic setup of the JCI-Shortcode (remove the linefeeds when using it on a page, please)', 'json-content-importer') ?>:<br>
		<?php
            $example = "[jsoncontentimporter \n";
            $example .= 'url="http://...json"'."\n";
            $example .= 'debugmode='.__('number: if 10 show backstage- and debug-info', 'json-content-importer')."\n";
            $example .= 'urlgettimeout='.__('number: who many seconds for loading url till timeout?', 'json-content-importer')."\n";
            $example .= 'numberofdisplayeditems='.__('number: how many items of level 1 should be displayed? display all: leave empty', 'json-content-importer')."\n";
            $example .= 'basenode='.__('JSON-node where the plugins starts using the JSON', 'json-content-importer')."\n";
            $example .= 'oneofthesewordsmustbein='.__('default empty, if not empty keywords spearated by\',\'. At least one of these keywords must be in the created text (here: text=code without html-tags)', 'json-content-importer')."\n";
            $example .= 'oneofthesewordsmustbeindepth='.__('default: 1, number:where in the JSON-tree oneofthesewordsmustbein must be?', 'json-content-importer')."\n";
            $example .= 'oneofthesewordsmustnotbein='.__('default empty, if not empty keywords spearated by \',\'. If one of these keywords is in the created text, this textblock is igonred (here: text=code without html-tags)', 'json-content-importer')."\n";
            $example .= 'oneofthesewordsmustnotbeindepth='.__('default: 1, number:where in the JSON-tree oneofthesewordsmustnotbein must be?', 'json-content-importer')."\n";
            $example .= "]\n";
            $example .= __('Any HTML-Code plus "basenode"-datafields wrapped in "{}"', 'json-content-importer')."\n";

            $example .= __('{subloop:"basenode_subloop":"number of subloop-datasets to be displayed"}', 'json-content-importer')."\n";
            $example .= __('  Any HTML-Code plus "basenode_subloop"-datafields wrapped in "{}"', 'json-content-importer')."\n";
            $example .= __('  {subloop-array:"basenode_subloop_array":"number of subloop-array-datasets to be displayed"}', 'json-content-importer')."\n";
            $example .= __('    Any HTML-Code plus "basenode_subloop_array"-datafields wrapped in "{}"', 'json-content-importer')."\n";
            $example .= __('  {/subloop-array:"basenode_subloop_array"}', 'json-content-importer')."\n";
            $example .= __('{/subloop:"basenode_subloop"}', 'json-content-importer')."\n";

            $example .= "[/jsoncontentimporter]\n";
            $example = htmlentities($example);
            echo "<pre class=precode>".esc_html($example)."</pre>";
		?> 
        </td></tr>
		<tr><td>
		<h2><?php esc_html_e('JSON: List of items', 'json-content-importer'); ?></h2>
		<?php esc_html_e('The example JSON has a list of items names "listofitems". To display that see this syntax', 'json-content-importer') ?><br>
		<?php
            $example = "[jsoncontentimporter ";
            $example .= "url=\"".$exurl."\"]\n";
            $example .= "{subloop-array:listofitems:5}\n{1:ifNotEmptyAddRight:aa<br>bb}{2:ifNotEmptyAddLeft:AA}{3:ifNotEmptyAddRight:BB}\n{/subloop-array:listofitems}";
            $example .= "\n[/jsoncontentimporter]\n";
            $example = htmlentities($example);
            echo "<pre class=precode>".esc_html($example)."</pre>";
		?> 
		<?php esc_html_e('This syntax gives you the items two, three and four (the internal enumeration is starting from 0) added with some extra chars.', 'json-content-importer') ?>
		<br>
		<?php esc_html_e('The free JCI Plugin does not have a loop-feature, you only can pick a defined item. The JCI PRO Plugin is doing this much easier by twig.', 'json-content-importer') ?>
        </td></tr>
		<tr><td>
		<h2><?php esc_html_e('Syntax for the datafields', 'json-content-importer'); ?></h2>
			<ul class=jciul>
				<li>"{street:html}": <?php esc_html_e('Default-display of a datafield is NOT HTML: "<" etc. are converted to "&amp;lt;". Add "html" to display the HTML-Code as Code.', 'json-content-importer') ?></li>
				<li>"{street:htmlAndLinefeed2htmlLinefeed}":<?php esc_html_e(' Same as "{street:html}" plus Text-Linefeeds are converted to &lt;br&gt; HTML-Linebreaks', 'json-content-importer') ?></li>
				<li>"{street:ifNotEmptyAddRight:extratext}": <?php esc_html_e('If datafield "street" is not empty, add "," right of datafield-value. allowed chars are', 'json-content-importer') ?>: "a-zA-Z0-9,;_-:&lt;&gt;/ "</li>
				<li>"{street:html,ifNotEmptyAddRight:extratext}": <?php esc_html_e('you can combine "html" and "ifNotEmptyAdd..." like this', 'json-content-importer') ?></li>
				<li>"{street:ifNotEmptyAdd:extratext}": <?php esc_html_e('some as', 'json-content-importer') ?> "ifNotEmptyAddRight"</li>
				<li>"{street:ifNotEmptyAddLeft:extratext}": <?php esc_html_e('If datafield "street" is not empty, add "," left of datafield-value. allowed chars are', 'json-content-importer') ?>: "a-zA-Z0-9,;_-:&lt;&gt;/ "</li>
				<li>"{locationname:urlencode}": <?php esc_html_e('Insert the php-urlencoded value of the datafield "locationname". Needed when building URLs. "html" does not work here.', 'json-content-importer') ?></li>
			</ul>
        </td></tr>
	<?php	
		break;
        case 'support' :
    ?>
		<tr><td>
		<h1><?php esc_html_e('Get Help - Get Support', 'json-content-importer'); ?></h1>
		<h2><?php esc_html_e('Help Resources', 'json-content-importer'); ?></h2>
		<ul class=jciul>
		<li><a href="https://doc.json-content-importer.com" target="_blank"><?php esc_html_e('Visit doc.json-content-importer.com for the JCI manual.', 'json-content-importer'); ?></a></li>
		<li><a href="https://api.json-content-importer.com" target="_blank"><?php esc_html_e('Visit api.json-content-importer.com for many live examples.', 'json-content-importer'); ?></a></li>
		</ul>

		<h2><?php esc_html_e('Individual Support', 'json-content-importer'); ?></h2>
		<ul class=jciul>
		<li><a href="https://doc.json-content-importer.com/json-content-importer/help-contact/" target="_blank"><?php esc_html_e('Visit doc.json-content-importer.com for information on obtaining help and support, either either through public channels at wordpress.org or via private means.', 'json-content-importer'); ?></a>
		<br><?php esc_html_e('Private support is sometimes preferable as posting API keys or tokens publicly is not a good idea.', 'json-content-importer'); ?>
		</ul>
        </td></tr>

	<?php	
		break;
        case 'welcome' :
    ?>
		<tr><td>
		<h1><?php esc_html_e('Welcome to the JSON Content Importer Plugin!', 'json-content-importer'); ?></h1>
		<h2><?php esc_html_e('Thank you!', 'json-content-importer'); ?></h2>


		<?php	
		$imgurl = plugin_dir_url(__FILE__)."images/banner-772x250.png"; 
		echo '<img src="'.esc_attr($imgurl).'">';
	
		echo "<p>";
		echo esc_html_e("We're thrilled to have you using this Plugin. With the JSON Content Importer Plugin, you'll have the power to retrieve, transform, and publish data seamlessly within your WordPress environment.", 'json-content-importer')."<p>";
		echo "<p>".esc_html_e("Whether you're looking to incorporate dynamic content, integrate APIs, or enhance your website's functionality, our plugin is here to make the process effortless and efficient. ", 'json-content-importer')."</p>";
		echo "<p>".esc_html_e("With its user-friendly interface and powerful features, you'll be able to import and display data from JSON feeds with ease.", 'json-content-importer')."</p>";
		echo "<p>".esc_html_e("Along the way, we're happy to assist you. If you have any questions, encounter challenges, or need guidance, feel free to reach out.", 'json-content-importer')."</p>";
		echo "<p>".esc_html_e("We believe in the power of data and its ability to transform websites into dynamic, engaging platforms. With the JSON Content Importer Plugin, you're equipped with a versatile tool that opens up endless possibilities for data integration and content enhancement.
", 'json-content-importer')."</p>";
		echo "<p>".esc_html_e("Once again, welcome to the JSON Content Importer Plugin! We can't wait to see how you leverage its potential and create extraordinary experiences on your WordPress site. Get started today and unlock the true power of data-driven content.", 'json-content-importer')."</p>";

		echo "<h2>".esc_html_e('Start your JCI: Step by Step', 'json-content-importer')."</h2>";
		echo "<ol>";
		echo '<li><a href="?page=unique_jci_menu_slug&tab=checkinstall">';
		esc_attr_e("Check Installation: Is your WordPress ready for JCI? Most probably!", 'json-content-importer');
		echo '</a></li>';
		echo '<li><a href="?page=unique_jci_menu_slug&tab=settings">';
		esc_html_e("Basic Settings: Check SSL, Cacher, Gutenberg and Authentication", 'json-content-importer');
		echo '</a></li>';
		echo '<li><a href="?page=unique_jci_menu_slug&tab=step1">';
		esc_html_e("Step 1: Get data! Ask the API and check the answer", 'json-content-importer');
		echo '</a></li>';
		echo '<li><a href="?page=unique_jci_menu_slug&tab=step2">';
		esc_html_e("Step 2: Use data! See how you can display the data with a Shortcode or a Gutenberg Block", 'json-content-importer');
		echo '</a></li>';
		echo '<li><a href="?page=unique_jci_menu_slug&tab=support">';
		esc_html_e("Need Support? Help needed? Using an API might be tricky, and we're here to help!", 'json-content-importer');
		echo '</a></li>';
		echo '<li><a href="?page=unique_jci_menu_slug&tab=jcipro">';
		esc_html_e("Check the JCI PRO Plugin: Unlock the full power of JCI! Use the discount code!", 'json-content-importer');
		echo '</a></li>';
		echo "</ol>";

        echo "</td></tr>";
		
	break;
	case 'bugbounty' :
		echo "<tr><td>";
	?>
		<h1><?php esc_html_e('JCI BugBounty Program', 'json-content-importer'); ?></h1>
		<h2><?php esc_html_e('Participate in the bug bounty program and receive a reward for discovered vulnerabilities.', 'json-content-importer'); ?></h2>
		<?php 
			esc_html_e('We offer several Wordpress Plugins and Websites. The security of data and processes is of the highest priority. However, despite our best efforts, these digital services may still contain vulnerabilities that are not yet known to us.', 'json-content-importer');
		?>
		<br>
		<?php 
			esc_html_e('Therefore, we are very grateful for any indications of Vulnerabilities!Please note: Searching for vulnerabilities may possibly constitute a criminal offense. To avoid legal difficulties, we kindly ask you to adhere to the following rules.!', 'json-content-importer');
		?>
		<h2><?php esc_html_e('How exactly does that work?', 'json-content-importer'); ?></h2>
		<?PHP
			echo '<a href="https://json-content-importer.com/bugbounty" target="_blank">';
			esc_html_e('On json-content-importer.com/bugbounty you will find all the information', 'json-content-importer');
			echo '</a>'; 
			echo "</td></tr>";
		
		
	break;
	case 'gdpr' :
	?>
      <tr><td>
		<h1><?php esc_html_e('General Data Protection Regulation (GDPR)', 'json-content-importer'); ?></h1>
			<?php esc_html_e('The General Data Protection Regulation', 'json-content-importer'); ?> 
			<a href="https://eur-lex.europa.eu/eli/reg/2016/679/oj" target="_blank">(EU) 2016/679</a> <?php esc_html_e('("GDPR") is a regulation in EU law on data protection and privacy for all individuals within the European Union (EU) and the European Economic Area (EEA)', 'json-content-importer'); ?> 
			(<a href="https://en.wikipedia.org/wiki/General_Data_Protection_Regulation" target="_blank"><?php esc_html_e('see more on that at Wikipedia', 'json-content-importer'); ?></a>).
			<?php esc_html_e('In the context of this plugin, GDPR is relevant in the following way:', 'json-content-importer'); ?>
			<ul>
				<li><?php esc_html_e('If you use the plugin to retrieve data from APIs, transform it, and display it on a website, it is important to consider GDPR compliance. In this case, the plugin functions as a piece of software that interacts with the data. ', 'json-content-importer'); ?>
				<?php 
					esc_html_e('Therefore, you should include the plugin in your ', 'json-content-importer');
					echo '<a href="https://gdpr-info.eu/art-30-gdpr/" target="_blank">';
					esc_html_e('GDPR-"Records of processing activities"', 'json-content-importer');
					echo "</a>";
					esc_html_e('if the data involved contains personal information.', 'json-content-importer'); 
				?></li>
			</ul>
		</td></tr>
	<?php
	break;
	case 'jcipro' :
	?>
   <style>
      .jcibutton {
        background-color: #1c87c9;
        color: white;
        padding: 28px 28px;
        display: inline-block;
        font-size: 21px;
      }
      .jcibutton:hover {
        background-color: white;
        color: #1c87c9;
      }
    </style>

	<tr>
		<td>
		
		<div class="wrap about-wrap">
			<h1><?PHP 
			echo esc_html_e('JCI PRO is much simpler and more powerful than JCI Free!', 'json-content-importer');
		?></h1
		<p class="about-text">
		Both the free and PRO JCI Plugins serve the same purpose: retrieving data, transforming it, and publishing the results.
		<br>
		However, while the free Plugin can only handle basic challenges, the PRO JCI Plugin offers nearly full control over WordPress, the database, and applications.
			<ul>
				<li>&bull; Get JSON: Unlike the limited methods of the free JCI, the JCI PRO can access almost any data: locally from files, remotely via any known authentication.</li>
				<li>&bull; Use JSON: While the free JCI can handle JSON, the JCI PRO can work with any data source and is able to build applications.</li>
				<li>&bull; To achieve this, the JCI PRO offers the twig parser and many extensions. Various WordPress and Database functions are available to give you full control.</li>
				<?PHP 
				echo '<li>&bull; ';
				esc_html_e("Try it without risk: We offer a full refund if the PRO plugin cannot solve your challenge", 'json-content-importer');
				echo "</li>";
				?>
			</ul>
		</p>
		</div>
			<?PHP
			$imgurl = plugin_dir_url(__FILE__)."images/banner-772x250.jpg"; 
			echo '<img src="'.esc_url($imgurl).'">';
			?>
		<br><a href="https://json-content-importer.com/download/" class="jcibutton">Click here to upgrade to the JCI PRO Plugin and unlock the full power of JCI!</a>

		<h2><?PHP 
			echo esc_html_e('PRO-Plugin: All free features plus...', 'json-content-importer'); ?></h2>
		<ul>
			<li>&bull; Support and ongoing development</li>
			<li>&bull; handling of a wider range of JSON-feeds / APIs</li>
			<li>&bull; enhanced template engine: the plugin-own engine is better, the famous twig-engine is the PRO-alternative</li>
			<li>&bull; template-manager: store templates independent of pages</li>
			<li>&bull; display as widget at the sidebar or footer</li>
			<li>&bull; build applications: select JSON-feed on the fly</li>
			<li>&bull; create WordPress-Pages and CPT, fill CPF</li>
			<li>&bull; use Toolset and Elementor with JCI PRO</li>
			<li>&bull; third-party shortcodes work inside the jsoncontentimporter-shortcode
			<li>&bull; and a lot more...</li>
	
		</ul>

		<strong><a href="https://json-content-importer.com/compare/?sc=wp" target="_blank" title="<?php esc_html_e('Compare free and PRO JSON Content Importer Plugin', 'json-content-importer') ?>"><?php esc_html_e('Compare free and PRO JSON Content Importer', 'json-content-importer') ?></a></strong>
		<hr>
		<p>
			<h2><?php esc_html_e('Some JCI PRO highlights', 'json-content-importer') ?></h2>
			<ul class=jciul>
				<li><?php esc_html_e('Twig template engine: Much simpler Syntax and logic (if statements, etc.) in your templates', 'json-content-importer') ?>
				<br>
				<?php esc_html_e('E. g. instead of ', 'json-content-importer') ?><br><code>{subloop:title:-1}{title.text}{/subloop:title}</code>
				<br>
				<?php esc_html_e('use the twig Syntax', 'json-content-importer') ?><br><code>{{title.text}}</code>
				<br><a href="https://api.json-content-importer.com/free-pro-aara-show-data-from-monitoringapi-solaredge-com/" target="_blank"><?php esc_html_e('Example with a real API', 'json-content-importer') ?></a>
				
				</li>
				<li><?php esc_html_e('Template-Manager: No more hassle with line breaks, and a single place to store templates for use on multiple pages.', 'json-content-importer') ?></li>
				<li><?php esc_html_e('Format date and time', 'json-content-importer') ?></li>
				<li><?php esc_html_e('Application building: E. g. pass GET/POST-parameter from Wordpress to JSON-feed', 'json-content-importer') ?><br>
					<a href="https://doc.json-content-importer.com/json-content-importer/jci-and-openstreetmaps/" target="_blank"><?PHP echo esc_html_e('Openstreetmaps','json-content-importer'); ?></a>,<br>
					<a href="https://doc.json-content-importer.com/json-content-importer/multisite-search/" target="_blank"><?PHP echo esc_html_e('Search: Faceted / Multisite','json-content-importer'); ?></a>...				
				</li>
				<li><?php esc_html_e('Shortcode inside template', 'json-content-importer') ?></li>
				<li><?php esc_html_e('Create custom post types out of JSON', 'json-content-importer') ?>: <a href="https://www.youtube.com/watch?v=fQsiJj_Aozw" target="_blank">Show API JSON Data in Wordpress with the Plugins JsonContentImporter PRO and Toolset</a></li>
				<li><?php esc_html_e('And much more...', 'json-content-importer') ?></li>
			</ul>
        </td>
		 </tr>
		<?php
	break;
	case 'checkinstall' :
	?>
     <tr><td>
		<h1><?php esc_html_e('Check on requirements', 'json-content-importer'); ?></h1>
		
		<h2><?php esc_html_e('PHP-Version', 'json-content-importer'); ?></h2>
        <?php
			$phpvers = phpversion();
			$phpmin = "7.0";
			echo esc_html_e("Installed PHP version", 'json-content-importer').": ".esc_html($phpvers)." - ";
			echo esc_html_e("Minimal required PHP version", 'json-content-importer').": ".esc_html($phpmin)." - ";
			if (version_compare($phpmin, $phpvers)==1) {
				echo '<b><span style="color:#f00;">';
				esc_html_e('NOT', 'json-content-importer');
				echo ' ok</span></b>';
			} else {
				echo '<b><span style="color:#4CC417;">';
				esc_html_e('OK', 'json-content-importer');
				echo '</span></b>';
			}
		?>
        </td></tr>
		<tr><td>
		<h2><?php esc_html_e('allow_url_fopen', 'json-content-importer'); ?></h2>
		<?php esc_html_e('Is Worpdress allowed to do remote http-requests (some hoster switch that off for security reasons)', 'json-content-importer'); ?>: 
		<?php
			if(ini_get('allow_url_fopen') ) {
				echo '<b><span style="color:#4CC417;">';
				esc_html_e('OK', 'json-content-importer');
				echo '</span></b>';
			} else {
				echo "<br><a href=\"https://www.php.net/manual/en/features.remote-files.php\" target=\"_blank\">";
				esc_html_e('PHP allow_url_fopen check', 'json-content-importer');
				echo "</a>:";
				echo '<br><span style="color:#f00;"><b>';
				esc_html_e('NOT ok, allow_url_fopen NOT active: The security settings of your PHP / Webserver maybe prevent getting remote data via http-requests of URLs. You might get timeout-errors when using this plugin.</b>', 'json-content-importer');
				echo '<br>';
				esc_html_e('Ask your Serverhoster for setting "allow_url_fopen" TRUE', 'json-content-importer');
				echo "</span>";
			}
		?>
        </td></tr>
		<tr><td>
		<h2><?php esc_html_e('JCI-Cacher: Present and writeable?', 'json-content-importer'); ?></h2>
		<?php 
			echo esc_html_e('Check JCI-cacher and cachefolder (directory where JSON-feeds are stored to reduce API-requests)', 'json-content-importer')."<br>";
			$cacheEnabledOption = get_option('jci_enable_cache');
			if ($cacheEnabledOption==1) {
				echo esc_html_e("Cache is active (see Tab 'Basic Settings'", 'json-content-importer')."<br>";
			} else {
				echo esc_html_e("Cache is NOT active (see Tab 'Basic Settings')", 'json-content-importer')."<br>";
			}
			$cacheFolder = WP_CONTENT_DIR.'/cache/jsoncontentimporter/';
			
			
			
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$url = wp_nonce_url( 'index.php', 'my-nonce_jci_savefile' );
		$credentials = request_filesystem_credentials( $url );
		if ( ! WP_Filesystem( $credentials ) ) {
			return '';#'Failed to initialize WP_Filesystem.';
		}

		global $wp_filesystem;
					
			if ( $wp_filesystem->is_dir($cacheFolder)) {
				# cachedir is present 
				if ($wp_filesystem->is_writable($cacheFolder)) {
					echo '<span style="color:#4CC417;">';
					esc_html_e('CacheFolder is present and writeable', 'json-content-importer');
					echo ': ';
					esc_html($cacheFolder);
					echo '</span>';
				} else {
					echo '<span style="color:#f00;">';
					esc_html_e('CacheFolder is present but NOT writeable', 'json-content-importer');
					echo ': ';
					esc_html($cacheFolder);
					echo '</span>';
				}
			} else {
				# cachedir is NOT present 
				echo '<span style="color:#f00;">';
				esc_html_e('CacheFolder is NOT present', 'json-content-importer');
				echo ': ';
				echo esc_html($cacheFolder);
				echo '</span><br>';
				esc_html_e("Don't panic: this is ok if JCI-cache was never active on this wordpress installation.", 'json-content-importer');
				echo "<br>";
				esc_html_e("The directory is created the first time the cache is switched on and used!", 'json-content-importer');
			}
		
			$delmsg = "";
			$delmsgcolor = "#4CC417";
			$clearcachein = htmlentities(($_GET['clearcache'] ?? ''));
			$noncein = $_REQUEST['_wpnonce'] ?? '';
			if ($clearcachein=="y") {
				$dcwpn = wp_verify_nonce($noncein, 'jci_clearcache' );
				if (!$dcwpn) {
					$delmsg = esc_html_e("Deleting of cache failed because security check failed", 'json-content-importer');
					$delmsgcolor = "#f00";
				} else {
					$delmsgArr = clearCacheFolder($cacheFolder);
					$delmsg = $delmsgArr[1];
					$delmsgcolor = $delmsgArr[0];
				}
			}
			if (""!=$delmsg) {
				echo "</td></tr><tr><td>";
				echo '<span style="color:'.esc_attr($delmsgcolor).';">'.esc_html($delmsg)."</span>";
			}
			
			echo "</td></tr><tr><td>";
		
			$filecount = 0;
			$files = glob($cacheFolder . "*.cgi");
			//var_Dump($files);
			if ($files){
				$filecount = count($files);
			}
			echo "<h2>";
			esc_html_e('JCI-Cacher: What is stored in the JCI-Cache?', 'json-content-importer');
			echo "</h2>";
			esc_html_e("Number of JSON-Cachefiles in", 'json-content-importer');
			echo " ".esc_url($cacheFolder);
			echo ": ".esc_html($filecount)."<br>";
			if (class_exists('RecursiveDirectoryIterator')) { 
				if (is_dir($cacheFolder)) {
					$ret = get_dir_size($cacheFolder);
					$sizecachedir = $ret["size"];
					esc_html_e("Size of JCI Cache", 'json-content-importer');
					echo ": ";
					format_dir_size($sizecachedir);
					echo " ";
					esc_html_e("in", 'json-content-importer');
					echo " ";
					echo esc_html($ret["nooffiles"]);
					echo " ";
					esc_html_e("Files", 'json-content-importer');
					echo "<br>";
				}
			} else {
				echo '<span style="color:#f00;">';
				esc_html_e('Calc of Cachefolder-Size failed due to missing PHP-Class (PHP7 or higher required)', 'json-content-importer');
				echo '</span>';
			}

			$clearCacheUrl = "?page=unique_jci_menu_slug&tab=checkinstall&clearcache=y";
			$wpn_cc_url = wp_nonce_url( $clearCacheUrl, 'jci_clearcache' );

			echo '<a href="'.esc_url($wpn_cc_url).'">';
			esc_html_e("Click here to CLEAR JCI-CACHE", 'json-content-importer');
			echo "</a>";
		echo "</td></tr>";
	break;
	}
	?>
    </table>
</form>
</div>

<?php
}

function get_dir_size($directory) {
		$size = 0;
		$nooffiles = 0;
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, \FilesystemIterator::FOLLOW_SYMLINKS)) as $file) {
			if ($file->getFilename()!="." && $file->getFilename()!="..") {
				#echo $file->getFilename()."<br>";
				#if ($file->isExecutable()) {					echo "fwrite";				}
				#if ($file->isReadable()) {					echo "isReadable";				}
				$size += $file->getSize();
				$nooffiles++;
			}
		}
		$ret = Array();
		$ret["nooffiles"] = $nooffiles;
		$ret["size"] = $size;
		return $ret;
	}	

function format_dir_size($dirsizeinbyte) {
		$sizeval = "MB";
		$sizeinmb = floor(10*$dirsizeinbyte/(1024*1024))/10;
		if (0==$sizeinmb) {
			$sizeinmb = floor(10*$dirsizeinbyte/(1024))/10;
			$sizeval = "kB";
		}
		echo esc_html($sizeinmb). " " . esc_html($sizeval);
		return true;
	}	

function clearCacheFolder($cacheFolder) {
		if (!preg_match("/jsoncontentimporter\/$/", $cacheFolder)) {
			$delmsg = ["#f00", "Delete failed"];
			return $delmsg;			
		}
		$cachefiles = glob($cacheFolder.'*'); // all files
        $nofiles = 0;
        foreach($cachefiles as $file){ // loop files
            if(is_file($file)) {
             #   if (unlink($file)) {
               if (wp_delete_file($file)) {
					$nofiles++;
				}
            }
        }
		$delmsg = ["#0f0", $nofiles." ".__('JSON-files from JCI-Cacher deleted', 'json-content-importer') ];
		return $delmsg;
	}
?>