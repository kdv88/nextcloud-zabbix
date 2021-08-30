#!/usr/bin/php
<?php

if( !isset( $argv[3] ) ) {
        echo "Usage: {$argv[0]} major|minor url username password \r\n";
        die();
}

$typeCheck = $argv[1];
if( !in_array( $typeCheck, ['major', 'minor' ] ) ) {
        echo "Specify major or minor check";
        die();
}

function strposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
    }
    return false;
}

function getNextcloudReleases() {
        // https://stackoverflow.com/questions/37141315/file-get-contents-gets-403-from-api-github-com-every-time/37142247
        $opts = [
                'http' => [
                        'method' => 'GET',
                        'header' => [
                                'User-Agent: PHP'
                        ]
                ]
        ];
        $context = stream_context_create($opts);

        $url = "https://api.github.com/repos/nextcloud/server/releases";
        $url = "https://api.github.com/repos/nextcloud/server/git/refs/tags";
        $url = "https://api.github.com/repos/nextcloud/server/tags";
        $url = "https://api.github.com/repos/nextcloud/server/tags?per_page=100";

        $jsondata = file_get_contents($url, false, $context);

        return json_decode($jsondata, true);
}

function splitByMajor( $data ) {
        $releases = [];
        foreach( $data as $release ) {
                $name = strtolower( $release['name'] ) ;
                $name = str_replace("v", "", $name);

                $ignore = ['beta','rc'];

                if( strposa($name, $ignore ) ) {
                        continue;
                }

                $version = explode(".", $name);
                $major = $version[0];
                $minor = $version[1];
                $patch = $version[2];

                $releases[ $major ][] = "{$major}.{$minor}.{$patch}";
        }

        return $releases;
}

function highestVersion( $releases  ) {
        return array_reduce($releases, function ($highest, $current) {
            return version_compare($highest, $current, '>') ? $highest : $current;
        });
}

function nextMinorUpgrade( $version, $releaselist ) {
        $highestVersion = highestVersion( $releaselist );

        if( version_compare( $version, $highestVersion ) ) {
                return $highestVersion;
        } else {
                return false;
        }
}

function nextMajorUpgrade( $version, $releases ) {
        $maxMajor = max(array_keys($releases));

        $mySplit = explode(".", $version);
        $myMajor = $mySplit[0];

        if( $myMajor == $maxMajor ) {
                return false;
        }

        return $maxMajor;
}

// Commandline arguments
$url = $argv[2];
$username = $argv[3];
$password = $argv[4];

// Get the data from the Nextcloud instance API
$xmlstr = file_get_contents(  "https://{$username}:{$password}@{$url}/ocs/v2.php/apps/serverinfo/api/v1/info");
$ncdata  = new SimpleXMLElement($xmlstr);

// Extract the current running version, strip the last .1 part
$myVersion = $ncdata->data->nextcloud->system->version;
$myVersion = substr( $myVersion, 0, -2 );

$data = getNextcloudReleases();
$releases = splitByMajor( $data );

$mySplit = explode(".", $myVersion);
$myMajor = $mySplit[0];

if( $typeCheck == "minor" ) {
        $nextMinorUpgrade = nextMinorUpgrade( $myVersion, $releases[ $myMajor ] );

        if( $nextMinorUpgrade === false ) {
                echo "OK";
        } else {
                echo $nextMinorUpgrade;
        }
}

if( $typeCheck == "major" ) {
        $nextMajorUpgrade = nextMajorUpgrade( $myVersion, $releases );

        if( $nextMajorUpgrade === false ) {
                echo "OK";
        } else {
                $highestVersion = highestVersion( $releases[ $nextMajorUpgrade ] );
                echo $highestVersion;
        }
}

?>