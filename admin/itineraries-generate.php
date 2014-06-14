<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

if (!empty($_GET['id']))
{
    $itineraryId = $_GET['id'];
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

    $stmt = $mysqli->prepare('SELECT title, sub_title, intro_text, description, image_landscape, image_portrait, date_created, date_last_modified, json_filename, last_json_filename, is_live FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $itineraryId);
    $stmt->execute();
    $stmt->bind_result($title, $sub_title, $intro_text, $description, $image_landscape, $image_portrait, $date_created, $date_last_modified, $json_filename, $last_json_filename, $is_live);

    $results = array();
    $json = '';
    $json .= '{"itinerary": [';
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['title'] = $title;
        $results[$i]['sub_title'] = $sub_title;
        $results[$i]['intro_text'] = $intro_text;
        $results[$i]['description'] = $description;
        $results[$i]['image_landscape'] = $image_landscape;
        $results[$i]['image_portrait'] = $image_portrait;
        $results[$i]['date_created'] = $date_created;
        $results[$i]['date_last_modified'] = $date_last_modified;
        $results[$i]['json_filename'] = $json_filename;
        $results[$i]['last_json_filename'] = $last_json_filename;
        $i++;
    }

    $resultsNumRows = count($results);
    $resultsCount = 0;
    if ($resultsNumRows > 0)
    {
        foreach ($results as $result)
        {
            $json .= json_encode($result);
            $filename = $result['json_filename'];
            $last_filename = $result['last_json_filename'];
        }
    }

    $mysqli2 = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt2 = $mysqli2->prepare('SELECT il.id, il.title, il.image_landscape, il.image_portrait, il.lat, il.lng, il.tags FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence');
    $stmt2->bind_param('i', $itineraryId);
    $stmt2->execute();
    $stmt2->bind_result($id, $title, $image_landscape, $image_portrait, $lat, $lng, $tags);
    $innerResults = array();
    $i = 0;
    $json .= ', "locations": [';
    while($stmt2->fetch())
    {
        $innerResults[$i]['id'] = $id;
        $innerResults[$i]['title'] = $title;
        $innerResults[$i]['image_landscape'] = $image_landscape;
        $innerResults[$i]['image_portrait'] = $image_portrait;
        $innerResults[$i]['lat'] = $lat;
        $innerResults[$i]['lng'] = $lng;
        $innerResults[$i]['tags'] = $tags;
        $i++;
    }

    $innerNumRows = count($innerResults);
    $innerCount = 0;
    if ($innerNumRows > 0)
    {
        foreach ($innerResults as $innerRow)
        {
            $json .= json_encode($innerRow);
            $innerCount++;
            if ($innerCount < $innerNumRows)
            {
                $json .= ',';
            }
        }
    }

    $stmt2->close();
    $mysqli2->close();


    $json .= ']';
    $json .= ']}';

    $stmt->close();
    $mysqli->close();

    echo $json;

    if (file_exists('../json/'.$last_filename) && !is_null($last_filename))
    {
        unlink('../json/'.$last_filename);
    }

    $file = fopen('../json/'.$filename,'w');
    fwrite($file, $json);
    fclose($file);
}
?>