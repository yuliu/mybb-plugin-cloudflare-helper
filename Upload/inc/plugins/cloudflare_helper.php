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

if(!defined('CLOUDFLARE_HELPER_PLUGIN'))
{
	define('CLOUDFLARE_HELPER_PLUGIN', __FILE__);
}

/**
 * Country code in ISO 3166-1 Alpha 2 format
 * See: https://support.cloudflare.com/hc/en-us/articles/200168236-What-does-Cloudflare-IP-Geolocation-do-
 * See: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
 */

/**
 * Cloudflare-defined: XX is used for clients without country code data.
 */
define('CLOUDFLARE_HELPER_CC_NODATA', 'XX');

/**
 * Cloudflare-defined: T1 is used for clients using the Tor network.
 */
define('CLOUDFLARE_HELPER_CC_TOR', 'T1');

global $plugins;
$plugins->add_hook('global_start', 'cloudflare_helper_add_hooks_global_start', 0);
$plugins->add_hook('global_start', 'cloudflare_helper_add_hooks_global_start_end', PHP_INT_MAX);

function cloudflare_helper_info()
{
	return array(
		'name'			=> 'Cloudflare Helper',
		'description'	=> 'A very primitive plugin for exploiting HTTP headers set by Cloudflare.',
		'website'		=> 'https://github.com/yuliu/mybb-plugin-cloudflare-helper',
		'author'		=> 'Yu \'noyle\' Liu',
		'authorsite'	=> 'https://github.com/yuliu/mybb-plugin-cloudflare-helper',
		'version'		=> '0',
		'compatibility'	=> '18*',
		'codename'		=> 'noyle-cloudflare_helper'
	);
}

function cloudflare_helper_add_hooks_global_start()
{
	global $plugins;
	$plugins->run_hooks('cloudflare_helper_global_start');
	
	$cf_headers = cloudflare_helper_get_cf_headers();
	if(empty($cf_headers['cf_ipcountry']))
	{
		$plugins->run_hooks('cloudflare_helper_global_start_NOTFROMCF');
	}
	else if($cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_NODATA)
	{
		$plugins->run_hooks('cloudflare_helper_global_start_NOCOUNTRY');
	}
	else if($cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_TOR)
	{
		$plugins->run_hooks('cloudflare_helper_global_start_FROMTOR');
	}
	else
	{
		$plugins->run_hooks('cloudflare_helper_global_start_'.$cf_headers['cf_ipcountry']);
	}
}

function cloudflare_helper_add_hooks_global_start_end()
{
	global $plugins;
	$plugins->run_hooks('cloudflare_helper_global_start_end');
	
	$cf_headers = cloudflare_helper_get_cf_headers();
	if(empty($cf_headers['cf_ipcountry']))
	{
		$plugins->run_hooks('cloudflare_helper_global_start_end_NOTFROMCF');
	}
	else if($cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_NODATA)
	{
		$plugins->run_hooks('cloudflare_helper_global_start_end_NOCOUNTRY');
	}
	else if($cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_TOR)
	{
		$plugins->run_hooks('cloudflare_helper_global_start_end_FROMTOR');
	}
	else
	{
		$plugins->run_hooks('cloudflare_helper_global_start_end_'.$cf_headers['cf_ipcountry']);
	}
}

function cloudflare_helper_get_cf_headers()
{
	static $cloudflare_helper_cf_headers = array();

	if(!isset($cloudflare_helper_cf_headers['cf_ipcountry']))
	{
		$cloudflare_helper_cf_headers['cf_ipcountry'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] : '';
	}

	if(!isset($cloudflare_helper_cf_headers['cf_connecting_ip']))
	{
		$cloudflare_helper_cf_headers['cf_connecting_ip'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : '';
	}

	if(!isset($cloudflare_helper_cf_headers['x_forwarded_for']))
	{
		$cloudflare_helper_cf_headers['x_forwarded_for'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : '';
	}

	if(!isset($cloudflare_helper_cf_headers['x_forwarded_proto']))
	{
		$cloudflare_helper_cf_headers['x_forwarded_proto'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_X_FORWARDED_PROTO"] : '';
	}

	if(!isset($cloudflare_helper_cf_headers['cf_ray']))
	{
		$cloudflare_helper_cf_headers['cf_ray'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_CF_RAY"] : '';
	}

	if(!isset($cloudflare_helper_cf_headers['cf_visitor']))
	{
		$cloudflare_helper_cf_headers['cf_visitor'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_CF_VISITOR"] : '';
	}

	if(!isset($cloudflare_helper_cf_headers['cdn_loop']))
	{
		$cloudflare_helper_cf_headers['cdn_loop'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_CDN_LOOP"] : '';
	}

	return $cloudflare_helper_cf_headers;
}

function cloudflare_helper_get_cc()
{
	$cf_headers = cloudflare_helper_get_cf_headers();
	return $cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_NODATA || $cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_TOR ? '' : $cf_headers['cf_ipcountry'];
}

function cloudflare_helper_get_ip()
{
	$cf_headers = cloudflare_helper_get_cf_headers();
	return $cf_headers['cf_connecting_ip'];
}

function cloudflare_helper_from_tor()
{
	$cf_headers = cloudflare_helper_get_cf_headers();
	return $cf_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_TOR ? true : false;
}
