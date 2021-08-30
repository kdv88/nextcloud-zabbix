#!/bin/bash

ITEM=$1
URL=$2
USERNAME=$3
PASSWORD=$4

echo "https://${USERNAME}:${PASSWORD}@${URL}/ocs/v2.php/apps/serverinfo/api/v1/info" - $ITEM >> /tmp/zext_nextcloud.log

curl -s https://${USERNAME}:${PASSWORD}@${URL}/ocs/v2.php/apps/serverinfo/api/v1/info | xmllint --xpath "string(${ITEM})" -