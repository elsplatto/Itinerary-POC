<?php
function assessLogin($array = null)
{
    if ($array == null)
    {
        array_push($array,'super');
    }
    if(session_id() == '' || !isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['adminUserId']))
    {
        header('Location: login-fail.php');
    }
    else
    {
        if(!in_array($_SESSION['adminRole'],$array))
        {
            header('Location: dashboard.php');
        }
    }
}

function getItineraryDetail($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT title, page_name, sub_title, intro_text, description, image_landscape, image_portrait, date_created, date_last_modified, json_filename, previous_edit_date_stamp FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($title, $page_name, $sub_title, $intro_text, $description, $image_landscape, $image_portrait, $date_created, $date_last_modified, $json_filename, $previous_edit_date_stamp);

    $results = array();
    while($stmt->fetch())
    {
        $results['title'] = $title;
        $results['page_name'] = $page_name;
        $results['sub_title'] = $sub_title;
        $results['intro_text'] = $intro_text;
        $results['description'] = $description;
        $results['image_landscape'] = $image_landscape;
        $results['image_portrait'] = $image_portrait;

        $results['date_created'] = $date_created;
        $results['date_last_modified'] = $date_last_modified;
        $results['json_filename'] = $json_filename;
        $results['previous_edit_date_stamp'] = $previous_edit_date_stamp;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}

function getSelectedLocations($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT il.id, il.title, sub_title, il.image_landscape, il.lat, il.lng, content FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($id, $title, $sub_title, $image_landscape, $lat, $lng, $content);
    $results = array();
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['id'] = $id;
        $results[$i]['title'] = $title;
        $results[$i]['sub_title'] = $sub_title;
        $results[$i]['image_landscape'] = $image_landscape;
        $results[$i]['lat'] = $lat;
        $results[$i]['lng'] = $lng;
        $results[$i]['content'] = $content;
        $i++;
    }
    $stmt->close();
    $mysqli->close();
    return $results;
}
?>