<?php
/**
 * MyBB 1.8 plugin: Clouldflare Helper
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

define('CLOUDFLARE_HELPER_CLASS_ROOT', dirname(__FILE__).'/cloudflare_helper');
require_once CLOUDFLARE_HELPER_CLASS_ROOT.'/cloudflare_helper.php';

if(!defined('CLOUDFLARE_HELPER_PLUGIN'))
{
	define('CLOUDFLARE_HELPER_PLUGIN', __FILE__);
}

global $mybb_plugin_cloudflare_helper;
if(!isset($mybb_plugin_cloudflare_helper))
{
	$mybb_plugin_cloudflare_helper = new CloudflareHelper;
}

global $plugins;
$plugins->add_hook('global_start', array($mybb_plugin_cloudflare_helper, 'cloudflare_helper_add_hooks_global_start'), 0);
$plugins->add_hook('global_start', array($mybb_plugin_cloudflare_helper, 'cloudflare_helper_add_hooks_global_start_end'), PHP_INT_MAX);

function cloudflare_helper_info()
{
	return array(
		'name'			=> 'Cloudflare Helper',
		'description'	=> 'A very primitive plugin for exploiting HTTP headers set by Cloudflare.',
		'website'		=> 'https://github.com/yuliu/mybb-plugin-cloudflare-helper',
		'author'		=> 'Yu \'noyle\' Liu',
		'authorsite'	=> 'https://github.com/yuliu/mybb-plugin-cloudflare-helper',
		'version'		=> '0.1',
		'compatibility'	=> '18*',
		'codename'		=> 'noyle-cloudflare_helper'
	);
}

/**
 * @deprecated Will be removed after two stable releases.
 * @return array|boolean
 */
function cloudflare_helper_get_cf_headers()
{
	global $mybb_plugin_cloudflare_helper;
	return $mybb_plugin_cloudflare_helper->get_cf_headers();
}

/**
 * @deprecated Will be removed after two stable releases.
 * @return boolean|string
 */
function cloudflare_helper_get_cc()
{
	global $mybb_plugin_cloudflare_helper;
	return $mybb_plugin_cloudflare_helper->get_country_code();
}

/**
 * @deprecated Will be removed after two stable releases.
 * @return boolean|string
 */
function cloudflare_helper_get_ip()
{
	global $mybb_plugin_cloudflare_helper;
	return $mybb_plugin_cloudflare_helper->get_connnecting_ip();
}

/**
 * @deprecated Will be removed after two stable releases.
 * @return number|boolean
 */
function cloudflare_helper_from_tor()
{
	global $mybb_plugin_cloudflare_helper;
	return $mybb_plugin_cloudflare_helper->is_from_tor();
}
