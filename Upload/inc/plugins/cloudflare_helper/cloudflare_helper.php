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

/**
 * Cloudflare IP ranges.
 * See: https://www.cloudflare.com/ips/
 */
define('CLOUDFLARE_HELPER_CF_IPV4ADDR_URL', 'https://www.cloudflare.com/ips-v4');
define('CLOUDFLARE_HELPER_CF_IPV6ADDR_URL', 'https://www.cloudflare.com/ips-v6');
define('CLOUDFLARE_HELPER_CF_IPV4ADDR_FILE', dirname(__FILE__).'/ips-v4.txt');
define('CLOUDFLARE_HELPER_CF_IPV6ADDR_FILE', dirname(__FILE__).'/ips-v6.txt');

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


class CloudflareHelper
{
	private $ready = false;
	private $via_cf_network = false;
	private $through_cf_network = false;
	
	private $cf_added_headers = array(
		'cf_ipcountry' => false,
		'cf_connecting_ip' => false,
		'x_forwarded_for' => false,
		'x_forwarded_proto' => false,
		'cf_ray' => false,
		'cf_visitor' => false,
		'cdn_loop' => false,
	);
	
	private $from_tor = false;
	private $cc = false;
	
	private $ips_v4 = '';
	private $ips_v6 = '';
	
	public function __construct()
	{
		$this->_load_cf_ips();
		$this->_init();
	}
	
	public function get_ip_country()
	{
		$this->ready ? 0 : $this->_init();
		return $this->is_via_cf_network() ? $this->cf_added_headers['cf_ipcountry'] : false;
	}
	
	public function get_country_code()
	{
		$this->ready ? 0 : $this->_init();
		return $this->is_via_cf_network() ? $this->cc : false;
	}
	
	public function get_country()
	{
		$this->ready ? 0 : $this->_init();
		//
	}
	
	public function get_connnecting_ip()
	{
		$this->ready ? 0 : $this->_init();
		return $this->is_via_cf_network() ? $this->cf_added_headers['cf_connecting_ip'] : false;
	}
	
	public function is_via_cf_network()
	{
		$this->ready ? 0 : $this->_init();
		return $this->via_cf_network;
	}
	
	public function is_through_cf_network()
	{
		$this->ready ? 0 : $this->_init();
		return $this->through_cf_network;
	}
	
	/**
	 * Get if a request is made from a Tor network via Cloudflare network.
	 * @return int|boolean 1 if the client is using Tor and the request is via Cloudflare, 0 if not using Tor but via Cloudflare. false if request is not via Cloudfalre network;
	 */
	public function is_from_tor()
	{
		$this->ready ? 0 : $this->_init();
		return $this->is_via_cf_network() ? $this->from_tor : false;
	}
	
	/**
	 * Get HTTP headers added by Cloudflare.
	 * @return array|boolean An array containing relevant HTTP headers; false if the request is not via Cloudflare network.
	 */
	public function get_cf_headers()
	{
		$this->ready ? 0 : $this->_init();
		if(!$this->is_via_cf_network())
		{
			return false;
		}
		return $this->cf_added_headers;
	}
	
	/**
	 * Check if current request is made via Cloudflare by checking if the IP in HTTP header 'remote_addr' is in the Cloudflare network.
	 * @return boolean true if the request is made via Cloudflare, false otherwise.
	 */
	private function _check_remote_address_via_cf_network()
	{
		if(empty($this->ips_v4) || empty($this->ips_v6))
		{
			return false;
		}
		
		$remote_ip = $_SERVER["REMOTE_ADDR"];
		foreach(array_merge($this->ips_v4, $this->ips_v6) as $cf_ips_cidr)
		{
			$cf_ip_range = fetch_ip_range($cf_ips_cidr);
			$packed_remote_ip = my_inet_pton($remote_ip);
			
			if(is_array($cf_ip_range))
			{
				if(strcmp($cf_ip_range[0], $packed_remote_ip) <= 0 && strcmp($cf_ip_range[1], $packed_remote_ip) >= 0)
				{
					return true;
				}
			}
			elseif($remote_ip == $cf_ips_cidr)
			{
				return true;
			}
		}
		return false;
	}
	
	private function _init()
	{
		if(!$this->_check_remote_address_via_cf_network())
		{
			$this->via_cf_network = false;
			$this->ready = true;
			return;
		}

		$this->via_cf_network = true;
		$this->through_cf_network = true;
		$this->ready = true;

		$this->cf_added_headers['cf_ipcountry'] = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] : '';
		$this->cf_added_headers['cf_connecting_ip'] = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : '';
		$this->cf_added_headers['cf_ray'] = isset($_SERVER["HTTP_CF_RAY"]) ? $_SERVER["HTTP_CF_RAY"] : '';
		$this->cf_added_headers['cf_visitor'] = isset($_SERVER["HTTP_CF_VISITOR"]) ? $_SERVER["HTTP_CF_VISITOR"] : '';
		$this->cf_added_headers['cdn_loop'] = isset($_SERVER["HTTP_CDN_LOOP"]) ? $_SERVER["HTTP_CDN_LOOP"] : '';

		$this->cf_added_headers['x_forwarded_for'] = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : '';
		$this->cf_added_headers['x_forwarded_proto'] = isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) ? $_SERVER["HTTP_X_FORWARDED_PROTO"] : '';
		
		$this->from_tor = $this->cf_added_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_TOR ? 1 : 0;
		$this->cc = $this->cf_added_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_NODATA || $this->cf_added_headers['cf_ipcountry'] == CLOUDFLARE_HELPER_CC_TOR ? '' : $this->cf_added_headers['cf_ipcountry'];
	}
	
	private function _load_cf_ips_from_file($file)
	{
		if(!file_exists($file))
		{
			return false;
		}
		$ips = file_get_contents($file);
		if($ips !== false)
		{
			$ips = str_replace("\r", "\n", $ips);
			$ips = array_unique(array_filter(explode("\n", $ips)));
			return $ips;
		}
		else
		{
			return false;
		}
	}
	
	private function _load_cf_ips()
	{
		$ips = $this->_load_cf_ips_from_file(CLOUDFLARE_HELPER_CF_IPV4ADDR_FILE);
		if($ips !== false)
		{
			$this->ips_v4 = $ips;
		}
		
		$ips = $this->_load_cf_ips_from_file(CLOUDFLARE_HELPER_CF_IPV6ADDR_FILE);
		if($ips !== false)
		{
			$this->ips_v6 = $ips;
		}
		
		// TODO: read/store IPs from/into MyBB's data_cache.
		$cached_data = array(
			'timestamp' => TIME_NOW,
			'ips_v4' => $this->ips_v4,
			'ips_v6' => $this->ips_v6,
		);
	}
	
	public function cloudflare_helper_add_hooks_global_start()
	{
		$this->ready ? 0 : $this->_init();
		
		global $plugins;
		$plugins->run_hooks('cloudflare_helper_global_start');
		
		if(!$this->via_cf_network)
		{
			$plugins->run_hooks('cloudflare_helper_global_start_NOTFROMCF');
		}
		else if($this->from_tor === 1)
		{
			$plugins->run_hooks('cloudflare_helper_global_start_FROMTOR');
		}
		else if(empty($this->cc))
		{
			$plugins->run_hooks('cloudflare_helper_global_start_NOCOUNTRY');
		}
		else
		{
			$plugins->run_hooks('cloudflare_helper_global_start_'.$this->cc);
		}
	}
	
	public function cloudflare_helper_add_hooks_global_start_end()
	{
		$this->ready ? 0 : $this->_init();
		
		global $plugins;
		$plugins->run_hooks('cloudflare_helper_global_start_end');
		
		if(!$this->via_cf_network)
		{
			$plugins->run_hooks('cloudflare_helper_global_start_end_NOTFROMCF');
		}
		else if($this->from_tor === 1)
		{
			$plugins->run_hooks('cloudflare_helper_global_start_end_FROMTOR');
		}
		else if(empty($this->cc))
		{
			$plugins->run_hooks('cloudflare_helper_global_start_end_NOCOUNTRY');
		}
		else
		{
			$plugins->run_hooks('cloudflare_helper_global_start_end_'.$this->cc);
		}
	}
}

