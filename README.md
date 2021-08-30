# nextcloud-zabbix

With this template you can monitor a Nextcloud instance via the API

## Requirements
* php-cli
* php-xml

## Installation
1. Create an API password for your user in Nextcloud. This can be done in Settings > Security.
2. Import "zbx_export_templates.xml" to your Zabbix templates
3. Copy zext_nextcloud.sh and zext_nextcloud_update.php to your Zabbix "externalscripts"-directory
4. Create a host in Zabbix. The DNS-hostname should be your Nextcloud DNS name
5. Set macros to your Nextcloud host: "{$NCURL}", "{$NCUSERNAME}", "{$NCPW}" and add your API user and password as values.