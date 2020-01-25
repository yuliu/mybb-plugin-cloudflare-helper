# Changelog

## (on-going) v0.1

- Code refactor

- Switch to use `$_SERVER["REMOTE_ADDR"]` to check if a access request to MyBB comes from Cloudflare, since HTTP headers can be spoofed. See [Cloudflare IP Ranges](https://www.cloudflare.com/ips/). 

## v0 Initial release

Initial release.