<?php


if (!isset($itineraryDetail) && isset($itineraryId))
{
    $itineraryDetail = getItineraryDetail($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
}
if (!isset($selectedLocations) && isset($itineraryId))
{
    $selectedLocations = getSelectedLocations($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
}

if (isset($itineraryDetail))
{
    if (is_null($itineraryDetail['date_last_modified']) || empty($itineraryDetail['date_last_modified']))
    {
        $latestDate = $itineraryDetail['date_created'];
    }
    else
    {
        $latestDate = $itineraryDetail['date_last_modified'];
    }
    $manifestCacheBreakString = date("Y-m-d H:i:s", $latestDate);

    if (!isset($filename)) {
        $filename = $itineraryDetail['page_name'].'.html';
    }

    if (!isset($manifestFilename))
    {
        $manifestFilename = 'itinerary-'.$itineraryId.'-'.$latestDate.'.manifest';
    }

    if (!isset($oldManifestFile))
    {
        $oldManifestFile = 'itinerary-'.$itineraryId.'-'.$itineraryDetail['previous_edit_date_stamp'].'.manifest';
    }
}

if (isset($selectedLocations))
{
    $manifestImgs = '';
    foreach ($selectedLocations as $selectedLocation)
    {
        $manifestImgs .= $rootFolder."img/itineraries/locations/landscape/med/".$selectedLocation["image_landscape"]."\n";
    }
}

$manifest = <<< EOF
CACHE MANIFEST
# {$manifestCacheBreakString}
#static files
CACHE:
{$rootFolder}itinerary/{$filename}
{$rootFolder}css/idangerous.swiper.css
{$rootFolder}css/swiper.css
{$rootFolder}css/style.css
{$rootFolder}css/img/sprite.png
{$rootFolder}css/img/preloader-2.gif
{$rootFolder}css/img/preloader-2-sml.gif
{$rootFolder}js/vendor/jquery.js
{$rootFolder}js/vendor/modernizr.js
{$rootFolder}js/vendor/swiper/idangerous.swiper-2.1.min.js
{$rootFolder}img/marker-orange-hollow.png
{$rootFolder}img/marker-orange.png
{$rootFolder}img/location-dot.png
#dynamic files
{$manifestImgs}

NETWORK:
*

EOF;

if (file_exists('../manifests/'.$oldManifestFile))
{
    unlink('../manifests/'.$oldManifestFile);
}

$manifestFile = fopen('../manifests/'.$manifestFilename,'w');
fwrite($manifestFile, $manifest);
fclose($manifestFile);

?>