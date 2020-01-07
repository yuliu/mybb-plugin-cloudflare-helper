# Cloudflare Helper
A very primitive MyBB plugin for exploiting HTTP headers set by Cloudflare.

## Features
This plugin detects HTTP header fields that are [added by Cloudflare](https://support.cloudflare.com/hc/en-us/articles/200170986-How-does-Cloudflare-handle-HTTP-Request-headers-) when a request is made through the Cloudflare network. Relevant headers are as follows:
- `CF-IPCountry`
- `CF-Connecting-IP`
- `X-Forwarded-For`
- `X-Forwarded-Proto`
- `CF-RAY`
- `CF-Visitor`
- `CDN-Loop`

## Requirements

- MyBB 1.8.x
- PHP >= 5.6
- The plugin doesn't need Cloudflare's proxy to be enabled for your forum. But without Cloudflare's proxy, it's pretty useless.

## Version

The initial version is `0`. It's only for test at this moment.

## Installation

1. Upload all files/folders under the `Upload` directory to your MyBB root folder. Please maintain the folder structure within the `Upload` directory.
2. Activate plugins "Cloudflare Helper" and "Cloudflare Enhancer" at your MyBB's Admin Control Panel.

## Hooks

Following hooks are attached to MyBB's `global_start` hook:
- `cloudflare_helper_global_start`\
  `cloudflare_helper_global_start_end`

- `cloudflare_helper_global_start_NOTFROMCF`\
  `cloudflare_helper_global_start_end_NOTFROMCF`\
  The request is made from outside the Cloudflare network.
  
- `cloudflare_helper_global_start_NOCOUNTRY`\
  `cloudflare_helper_global_start_end_NOCOUNTRY`\
  Cloudflare can't detect the country where the request is made from. See also [Configuring Cloudflare IP Geolocation](https://support.cloudflare.com/hc/en-us/articles/200168236-What-does-Cloudflare-IP-Geolocation-do-).
  
- `cloudflare_helper_global_start_FROMTOR`\
  `cloudflare_helper_global_start_end_FROMTOR`\
  The request is made from Tor network through the Cloudflare network.
  
- `cloudflare_helper_global_start_{CC_CODE}`\
  `cloudflare_helper_global_start_end_{CC_CODE}`\
  The request comes from a country with country code [`{CC_CODE}`](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2). See [Configuring Cloudflare IP Geolocation](https://support.cloudflare.com/hc/en-us/articles/200168236-What-does-Cloudflare-IP-Geolocation-do-).

## TODO

- Switch to use `$_SERVER["REMOTE_ADDR"]` to check if a access request to MyBB comes from Cloudflare, since HTTP headers can be spoofed. See [Cloudflare IP Rages](https://www.cloudflare.com/ips/).
- Add hooks that would be executed when requests are not from a specific country.

## About "Cloudflare Enhancer"

The "Cloudflare Enhancer" plugin is a demo to show how this Cloudflare Helper works by displaying some information below MyBB's debug stuff (normally at the page bottom).

_**Note:**_ You need to enable **"Advanced Stats / Debug information"** (in "Board Settings >> Server and Optimization Options" setting group) for MyBB to show MyBB's debug stuff.

## License
See the [LICENSE](https://github.com/yuliu/mybb-plugin-cloudflare-helper/blob/master/LICENSE) file.

## Huh?
...