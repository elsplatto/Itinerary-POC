<?php
include '../includes/admin-settings.php';
include '../../includes/db.php';
include '../includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

if (!empty($_POST['pagename']))
{
    if (!empty($_POST['postedId']))
    {
        $postedId = $_POST['postedId'];
    }
    else
    {
        $postedId = 0;
    }
    $pagename = $_POST['pagename'];
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT id, title FROM itinerary WHERE page_name = ? AND id <> ?');
    $stmt->bind_param('si', $pagename, $postedId);
    $stmt->execute();
    $stmt->bind_result($id, $title);


    $i = 0;
    while($stmt->fetch())
    {
        $itineraryId = $id;
        $itineraryTitle = $title;
        $i++;
    }

    $stmt->close();
    $mysqli->close();


    if ($i > 0)
    {
        $json = '{"success": true, "unique": false, "itineraryid": '.$itineraryId.', "title": "' .$itineraryTitle. '"}';
    }
    else
    {
        $json = '{"success": true, "unique": true}';
    }
}


echo json_encode($json);

?>