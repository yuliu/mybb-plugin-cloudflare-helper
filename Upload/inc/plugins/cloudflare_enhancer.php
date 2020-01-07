<?php
/**
 * MyBB 1.8 plugin: Clouldflare Enhancer
 * Website: https://github.com/yuliu/mybb-plugin-cloudflare-helper
 * License: https://github.com/yuliu/mybb-plugin-cloudflare-helper/blob/master/LICENSE
 * Copyright Yu 'noyle' Liu, All Rights Reserved
 *
 */

// Make sure we can't access this file directly from the browser.
if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

define('CLOUDFLARE_HELPER_PLUGIN_REQUIRE', dirname(__FILE__).'/cloudflare_helper.php');
define('CLOUDFLARE_ENHANCER_PLUGIN', __FILE__);

if(!defined('CLOUDFLARE_HELPER_PLUGIN'))
{
	if(cloudflare_enhancer_check_dependency())
	{
		require_once CLOUDFLARE_HELPER_PLUGIN_REQUIRE;
	}
}

/**
 * Privileged access: comma separated user ids.
 */
define('CLOUDFLARE_ENHANCER_ENABLED_FOR_USERS', '1,285');
/**
 * Privileged access: comma separated usergroup ids.
 */
define('CLOUDFLARE_ENHANCER_ENABLED_FOR_USERGROUPSS', '');
/**
 * Inline styles for showing a debug info below MyBB's <debugstuff>.
 */
define('CLOUDFLARE_ENHANCER_DEBUG_INLINE_STYLE', "<style>/*#debug:after {clear: right;}*/ #cloudflare_enhancer_debug {clear: right; float: right; text-align: right; font-size: 11px;}</style>");

if(cloudflare_enhancer_check_dependency())
{
	cloudflare_enhancer_add_hook_to_debugstuff();
}

function cloudflare_enhancer_info()
{
	$plugin_info = array(
		'name'			=> 'Cloudflare Enhancer',
		'description'	=> 'A very primitive plugin for exploiting HTTP headers set by Cloudflare. Plugin <strong>Cloudflare Helper</strong> is required!',
		'website'		=> 'https://github.com/yuliu/mybb-plugin-cloudflare-helper',
		'author'		=> 'Yu \'noyle\' Liu',
		'authorsite'	=> 'https://github.com/yuliu/mybb-plugin-cloudflare-helper',
		'version'		=> '0',
		'compatibility'	=> '18*',
		'codename'		=> 'noyle-cloudflare_enhancer'
	);

	if(!cloudflare_enhancer_check_dependency())
	{
		$plugin_info['description'] .= "<br /><span style=\"font-weight: bold;\">Error: plugin <a href=\"https://github.com/yuliu/mybb-plugin-cloudflare-helper\" target=\"_blank\"><em>Cloudflare Helper</em></a> is not detected. <a href=\"https://github.com/yuliu/mybb-plugin-cloudflare-helper/issues\" target=\"_blank\">Click here for help!</a></span>";
	}
	return $plugin_info;
}

function cloudflare_enhancer_check_dependency()
{
	if(!defined('CLOUDFLARE_HELPER_PLUGIN'))
	{
		if(file_exists(CLOUDFLARE_HELPER_PLUGIN_REQUIRE))
		{
			require_once CLOUDFLARE_HELPER_PLUGIN_REQUIRE;
			return true;
		}
		else
		{
			return false;
		}
	}
	return true;
}

function cloudflare_enhancer_add_hook_to_debugstuff()
{
	$cf_cc = cloudflare_helper_get_cc();

	global $plugins;

	if(!empty(cloudflare_helper_get_cc()) && function_exists('cloudflare_enhancer_hook_'.$cf_cc))
	{
		$plugins->add_hook('cloudflare_helper_global_start_'.$cf_cc, 'cloudflare_enhancer_hook_'.$cf_cc);
	}
	else if(cloudflare_helper_from_tor())
	{
		$plugins->add_hook('cloudflare_helper_global_start_end_FROMTOR', 'cloudflare_enhancer_hook_FROMTOR');
	}
	else
	{
		$plugins->add_hook('cloudflare_helper_global_start_NOTFROMCF', 'cloudflare_enhancer_hook_NOTFROMCF');
	}

	$plugins->add_hook('pre_output_page', 'cloudflare_enhancer_hook_debug_style');
}

function cloudflare_enhancer_hook_debug_style(&$content)
{
	global $mybb;
	if($mybb->settings['extraadmininfo'] != 0 && ($mybb->usergroup['cancp'] == 1 || $mybb->dev_mode == 1))
	{
		$inline_styles = CLOUDFLARE_ENHANCER_DEBUG_INLINE_STYLE."\n</head>";
		$content = str_replace('</head>', $inline_styles, $content);
	}
}

function cloudflare_enhancer_hook_US()
{
	global $mybb, $plugins;
	if($mybb->settings['extraadmininfo'] != 0 && ($mybb->usergroup['cancp'] == 1 || $mybb->dev_mode == 1))
	{
		$plugins->add_hook('pre_output_page', 'cloudflare_enhancer_print_country_welcome_US');
	}
}

function cloudflare_enhancer_hook_CN()
{
	global $mybb, $plugins;
	if($mybb->settings['extraadmininfo'] != 0 && ($mybb->usergroup['cancp'] == 1 || $mybb->dev_mode == 1))
	{
		$plugins->add_hook('pre_output_page', 'cloudflare_enhancer_print_country_welcome_CN');
	}
}

function cloudflare_enhancer_hook_NOTFROMCF()
{
	global $mybb, $plugins;
	if($mybb->settings['extraadmininfo'] != 0 && ($mybb->usergroup['cancp'] == 1 || $mybb->dev_mode == 1))
	{
		$plugins->add_hook('pre_output_page', 'cloudflare_enhancer_print_country_welcome_NOTFROMCF');
	}
}

function cloudflare_enhancer_hook_FROMTOR()
{
	global $mybb, $plugins;
	if($mybb->settings['extraadmininfo'] != 0 && ($mybb->usergroup['cancp'] == 1 || $mybb->dev_mode == 1))
	{
		$plugins->add_hook('pre_output_page', 'cloudflare_enhancer_print_country_welcome_FROMTOR');
	}
}

function cloudflare_enhancer_print_country_welcome_US(&$content)
{
	$welcome = "<debugstuff>\n<div id=\"cloudflare_enhancer_debug\">Welcome, explorer from the United States!</div>";
	$content = str_replace('<debugstuff>', $welcome, $content);
}

function cloudflare_enhancer_print_country_welcome_CN(&$content)
{
	$welcome = "<debugstuff>\n<div id=\"cloudflare_enhancer_debug\">Welcome, my friend from the east!</div>";
	$content = str_replace('<debugstuff>', $welcome, $content);
}

function cloudflare_enhancer_print_country_welcome_NOTFROMCF(&$content)
{
	$welcome = "<debugstuff>\n<div id=\"cloudflare_enhancer_debug\">The site is currently not served through Cloudflare.<br />You're accessing it directly from its origin server!</div>";
	$content = str_replace('<debugstuff>', $welcome, $content);
}

function cloudflare_enhancer_print_country_welcome_FROMTOR(&$content)
{
	$welcome = "<debugstuff>\n<div id=\"cloudflare_enhancer_debug\">Welcome, strager from the void!<br />You're accessing it from a Tor network.</div>";
	$content = str_replace('<debugstuff>', $welcome, $content);
}
