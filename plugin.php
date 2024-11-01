<?php
/*
 Plugin Name: SML - Simple Multilingual
 Description: Handle languages in the same page (no page refresh, no page duplication and no more headaches)
 Version: 3.1.0
 Author: Marc Gagnon
 Author URI: https://www.MarcGagnon.com
 License: GPL v2 or later
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: Language-Translation-Tools
 Requires at least: 4.0
 Requires PHP: 7.0
 Domain Path: /languages
 */
 
if ( ! defined( 'WPINC' ) ) {
	die;
} 

DEFINE("MGASML_PLUGIN_SLUG","mgasml-simple-multilingual"); 
DEFINE("MGASML_PRO_VERSION",file_exists(plugin_dir_path(__FILE__ ).'js/script_pro.js'));

function MGASML_Scripts_Load() {
	//if( !wp_script_is( 'jquery', 'enqueued' ) ) {
		wp_enqueue_script("jquery");
	//}
    wp_enqueue_script( 'mgasml-simple-multilingual-script', plugin_dir_url( __FILE__ ).'js/script.js', array(), '1.0.1', true ); 
}
add_action( 'wp_enqueue_scripts', 'MGASML_Scripts_Load' );


function MGASML_Pro_Scripts_Load() {
    wp_enqueue_script( 'mgasml-simple-multilingual-pro-script', plugin_dir_url( __FILE__ ).'js/script_pro.js', array(), '1.0.0', true );
}

if (MGASML_PRO_VERSION) {
	add_action( 'admin_print_scripts-post-new.php', 'MGASML_Pro_Scripts_Load' );  
	add_action( 'admin_print_scripts-post.php',     'MGASML_Pro_Scripts_Load' );
	add_action( 'admin_print_scripts-post.php',     'MGASML_SwitcherStyleAndTemplate' );
}


add_filter( 'plugin_action_links_sml-simple-multilingual/plugin.php', 'MGASML_SettingsLink_Define' );
function MGASML_SettingsLink_Define( $links ) {
	$url = esc_url( add_query_arg(
		'page',
		'Simple_Multilingual',
		get_admin_url() . 'admin.php'
	) );
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	array_push(
		$links,
		$settings_link
	);
	return $links;
}

function MGASML_SettingValueDefault_Get($SettingName) {
	
	$options = get_option( 'MGASML_settings' );
	$value = trim($options[$SettingName]);
	if ($value > '') { return $value; }
	
	switch (strtolower($SettingName)) {
		case 'languages':
			return 'en,fr';
			break;
		case 'separator':
			return '|';
			break;
		case 'switchertype':
			return 'text';
			break;
		case 'switcherstyle':
			return ".SML_SimpleMultilingual_Switcher { \n"
					."\tcolor: gray;\n"
					."\ttext-align: center;\n"
					."\ttext-transform: uppercase;\n"
					."\tpadding-top: 10px;\n"
					."}\n"
					."\n"
					.".SML_SimpleMultilingual_Switcher select { \n"
					."\ttext-transform: uppercase;\n"
					."}\n"
					."\n"
					.".SML_SimpleMultilingual_Switcher img {\n"
					."\theight: 15px;\n"
					."\t//-webkit-filter: grayscale(100%);	//Safari gray icons\n"
					."\t//filter: grayscale(100%);			//Other browsers gray icons\n"
					."}\n";
			break;
		case 'mode':
			return 'prod';
			break;
		case 'fadeinspeed':
			return '500';
			break;
	}
	
	return '';
}


function MGASML_Switcher ($atts, $content = '') {

	$F = __FUNCTION__;
	
	$atts = shortcode_atts( 
		array(
			'languages' => MGASML_SettingValueDefault_Get('Languages'),
			'separator' => MGASML_SettingValueDefault_Get('Separator'),
			'switchertype' => MGASML_SettingValueDefault_Get('SwitcherType'),
			'switchertextcolor' => MGASML_SettingValueDefault_Get('SwitcherTextColor'),
			'switcherflagheight' => MGASML_SettingValueDefault_Get('SwitcherFlagHeight'),
			'switcherstyle' => MGASML_SettingValueDefault_Get('SwitcherStyle'),
			'mode' => MGASML_SettingValueDefault_Get('Mode'),
			'fadeinspeed' => MGASML_SettingValueDefault_Get('FadeInSpeed'),
		), 
		$atts
	);
	extract($atts);

	$languages = str_replace(" ","",$languages);
	$aLanguages = explode(",",$languages);
	$fadeinspeed = intval($fadeinspeed);
	if ($fadeinspeed < 10) { $fadeinspeed = 500; }
	$NbLanguages = count($aLanguages);
	$DefaultLanguage = $aLanguages[0];
	
	$languages = esc_html($languages);
	$separator = esc_html($separator);
	$fadeinspeed = esc_html($fadeinspeed);

$result = <<<TEXT
<div class="SML_SimpleMultilingual_Switcher" 
	style="cursor: pointer; display:none" 
	Languages="{$languages}" 
	Separator="{$separator}" 
	ClassName="language_"  
	FadeInSpeed="{$fadeinspeed}" 
	Mode="prod" >
TEXT;

	switch (strtolower($switchertype)) {
		case "text":
		case "texts":
			foreach($aLanguages as $lan) {
				$lan = esc_html($lan);
				$result .= "<span class=\"SML_SimpleMultilingual language_{$lan}\" style=\"display:none;\" >&nbsp;{$lan}&nbsp;</span>";
			}
			break;
		case "flag":
		case "flags":
			foreach($aLanguages as $lan) {
				$lan = esc_html($lan);
				$url = plugin_dir_url(__FILE__ )."images/flag_{$lan}.png";
				$result .= "<img class=\"SML_SimpleMultilingual language_{$lan}\" src=\"{$url}\" style=\"display:none;\" >	";
			}
			break;
		case "dropdown":
			$result .= "<select id=\"SML_SimpleMultilingual_SwitcherSelect\" >";
			foreach($aLanguages as $lan) {
				$lan = esc_html($lan);
				$result .= "<option value=\"{$lan}\">&nbsp;{$lan}&nbsp;</option>";
			}
			$result .= "</select>";
			break;
	}

	$result .= "</div>";

	return $result;
}
add_shortcode('SML-SimpleMultilingual', 'MGASML_Switcher');  

function MGASML_SwitcherStyleAndTemplate () {
	?>
	<!-- SML_SimpleMultilingual_HTMLStyle start -->
	<style>
		<?php echo esc_html(MGASML_SettingValueDefault_Get('SwitcherStyle')); ?>
	</style>
	<div id="SML_SimpleMultilingual_SwitcherTemplateContainer" style="display:none;">
		<?php echo MGASML_Switcher(array('foo'=>'foo')); ?>
	</div>
	<!-- SML_SimpleMultilingual_HTMLStyle end -->
	<?php
	return "";
}
add_action( 'wp_body_open', 'MGASML_SwitcherStyleAndTemplate' );

add_action( 'admin_menu', 'MGASML_AdminMenu_Add' );
add_action( 'admin_init', 'MGASML_Settings_Init' );


function MGASML_AdminMenu_Add(  ) { 

	add_options_page( 'Simple Multilingual', 'Simple Multilingual', 'manage_options', 'Simple_Multilingual', 'MGASML_OptionsPage_Render' );

}


function MGASML_Settings_Init(  ) { 

	register_setting( 'MGASML_pluginPage', 'MGASML_settings' );

	add_settings_section(
		'MGASML_pluginPage_section', 
		__( 'Global Settings', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingsSection_Callback', 
		'MGASML_pluginPage'
	);

	add_settings_field( 
		'Languages', 
		__( 'Languages', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingLanguages_Render', 
		'MGASML_pluginPage', 
		'MGASML_pluginPage_section' 
	);

	add_settings_field( 
		'Separator', 
		__( 'Separator', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingSeparator_Render', 
		'MGASML_pluginPage', 
		'MGASML_pluginPage_section' 
	);
	
	add_settings_field( 
		'SwitcherType', 
		__( 'Switcher type', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingSwitcherType_Render', 
		'MGASML_pluginPage', 
		'MGASML_pluginPage_section' 
	);

	add_settings_field( 
		'SwitcherStyle', 
		__( 'Switcher CSS', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingSwitcherStyle_Render', 
		'MGASML_pluginPage', 
		'MGASML_pluginPage_section' 
	);

/*
	add_settings_field( 
		'SwitcherMode', 
		__( 'Mode', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingMode_Render', 
		'MGASML_pluginPage', 
		'MGASML_pluginPage_section' 
	);
*/

	add_settings_field( 
		'FadeInSpeed', 
		__( 'FadeIn speed', 'MGASML_SimpleMultilingual' ), 
		'MGASML_SettingFadeInSpeed_Render', 
		'MGASML_pluginPage', 
		'MGASML_pluginPage_section' 
	);

	if (MGASML_PRO_VERSION) {
		add_settings_field( 
			'GoogleTranslateAPIKey', 
			__( 'Google Translate API Key', 'MGASML_SimpleMultilingual' ), 
			'MGASML_SettingGoogleTranslateAPIKey_Render', 
			'MGASML_pluginPage', 
			'MGASML_pluginPage_section' 
		);
	}

}


function MGASML_SettingLanguages_Render(  ) { 

	?>
	<input type='text' name='MGASML_settings[Languages]' value='<?php echo esc_html(MGASML_SettingValueDefault_Get('Languages')); ?>'>
	<div class="note">Enter comma seperated language codes (ex: en, fr). The first language is the default. You will always need to enter the languages in this order.<br/><span style="color: red">Language order must not be changed. Add  new languages at the end of this list.</span></div>
	<?php

}


function MGASML_SettingSeparator_Render(  ) { 

	?>
	<input type='text' name='MGASML_settings[Separator]' value='<?php echo esc_html(MGASML_SettingValueDefault_Get('Separator')); ?>'>
	<div class="note">(suggestion: '|' or '~' or any other character BUT avoid using common characters)</div>
	<?php

}


function MGASML_SettingSwitcherType_Render(  ) { 

	$v = MGASML_SettingValueDefault_Get('SwitcherType');
	?>
	<select id="SwitcherType" name='MGASML_settings[SwitcherType]'>
		<option value='text' <?php selected( $v, 'text' ); ?>>Language acronyms (text only)</option>
		<option value='flag' <?php selected( $v, 'flag' ); ?>>Language flags</option>
		<option value='dropdown' <?php selected( $v, 'dropdown' ); ?>>Dropdown</option>
	</select>
	<?php

}


function MGASML_SettingSwitcherStyle_Render(  ) { 

	?>
	<textarea rows="15" cols="80" name='MGASML_settings[SwitcherStyle]' ><?php echo esc_html(MGASML_SettingValueDefault_Get('SwitcherStyle')); ?></textarea>
	<?php

}

function MGASML_SettingMode_Render(  ) { 

	$v = MGASML_SettingValueDefault_Get('Mode');
	?>
	<select id="Mode" name='MGASML_settings[Mode]'>
		<option value='prod' <?php selected( $v, 'prod' ); ?>>Production</option>
		<option value='dev' <?php selected( $v, 'dev' ); ?>>Development</option>
	</select>
	<div class="note">In production mode, if a tradcution is missing, the default language will be shown. Instead, in development mode, the missing texts will be shown as '???'</div>
	<?php

}

function MGASML_SettingFadeInSpeed_Render(  ) { 

	?>
	<input type='number' name='MGASML_settings[FadeInSpeed]' min="0" max="9999" value='<?php echo esc_html(MGASML_SettingValueDefault_Get('FadeInSpeed')); ?>'> ms
	<div class="note">Time to load the switcher on page load</div>
	<?php

}

function MGASML_SettingGoogleTranslateAPIKey_Render(  ) { 

	?>
	<input type='text' name='MGASML_settings[GoogleTranslateAPIKey]' value='<?php echo esc_html(MGASML_SettingValueDefault_Get('GoogleTranslateAPIKey')); ?>' size="78">
	<div class="note">(PRO version : unlock auto-translation and many others features)</div>
	<?php

}


function MGASML_SettingsSection_Callback(  ) { 

	//echo __( 'Global settings', 'MGASML_SimpleMultilingual' );

}


function MGASML_OptionsPage_Render(  ) { 

		?>
		
		<style>
		i {
			background-color: lightblue;
		}
		</style>
		
		<form action='options.php' method='post'>

			<h1>SML - Simple Multilingual</h1>

			<br/>
			
			<h2>Shortcode</h2>

			<div>
				<div style="">By default the language switcher is automatically inserted in the top navigation menu.<br/>If you would like to place the switcher elsewhere (ie: in a widget), you can simply insert the following shortcode.</div><div style="padding-left:10px; padding-top:10px"><input type="text" value="[SML-SimpleMultilingual]" size="30" /></div>
				<div style="margin-top:10px; color:red">If you use the shortcode to determine where you want your language switcher, it will deactivate the default main menu switcher.
				</div>
			</div>
			
			<br/>
			<br/>
			

			<?php	
			settings_fields( 'MGASML_pluginPage' );
			do_settings_sections( 'MGASML_pluginPage' );
			?>
			
			<br/>
			
			<h2>Notes to users</h2>

			<div>
				Add class 'language_XX' (ex <i>language_fr</i>) to any element to make it visible for a specific language. <br/>
				This can be useful when some editors don't give you the ability to use the separator you defined. You just have to duplicate the element, translate it and put the appopriate class name.<br/>
			</div>

			<br/>

			<h2>Notes to programmers</h2>

			<div>
				If you need to get the current language code, you can use <i>jQuery('.SML_SimpleMultilingual_Switcher').attr("CurrentLanguage");</i> You can also look for a lot of others attributes there.<br/>
				<br/>
				If you need to deactivate the plugin (ie for debug) you can add <i>SML_Activated=false</i> to the url.<br/>
				<br/>
				If you need it (ex when using jQuery Date Picker) you can call <i>MGASML_SimpleMultilingual_Init();</i> to properly display new texts (added dynamically afterwards). Optionally, you can add a delay (millisecs) before lunching the refresh process <i>MGASML_SimpleMultilingual_Init(500);</i><br/>
			</div>

			<?php	
			submit_button();
			?>

		</form>
		

		<?php
	
}


		
