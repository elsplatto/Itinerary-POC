<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

$itinerary_id = $_POST['itinerary_id'];
$location_id = $_POST['location_id'];
$sequence = $_POST['sequence'];
$action = $_POST['action'];

if ($action === 'remove')
{
    if (isset($itinerary_id) && isset($location_id))
    {
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $stmt = $mysqli->prepare('DELETE FROM itinerary_itinerary_location WHERE itinerary_location_id = ? AND itinerary_id = ?');
        $stmt->bind_param('ii', $location_id, $itinerary_id);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        $json = '{"success": true, "msg": "Record deleted."}';
    }
}
else if ($action === 'add')
{
    if (isset($itinerary_id) && isset($location_id))
    {
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $stmt = $mysqli->prepare('INSERT INTO itinerary_itinerary_location (itinerary_location_id, itinerary_id, sequence) VALUES (?,?,?)');
        $stmt->bind_param('iii', $location_id, $itinerary_id,$sequence);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        $json = '{"success": true, "msg": "Record added."}';
    }
}
else
{
    $json = '{"success": false, "msg": "Variables not passed correctly."}';
}

echo json_encode($json);
?>