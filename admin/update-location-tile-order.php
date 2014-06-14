<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

$itinerary_id = $_POST['itinerary_id'];
$location_id = $_POST['location_id'];
$sequence = $_POST['sequence'];


if (isset($itinerary_id) && isset($location_id) && isset($sequence))
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('UPDATE itinerary_itinerary_location SET sequence = ? WHERE itinerary_location_id = ? AND itinerary_id = ?');
    $stmt->bind_param('iii', $sequence, $location_id, $itinerary_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    $json = '{"success": true, "msg": "Update successfully."}';
}
else
{
    $json = '{"success": false, "msg": "We didn\'t get all the variables."}';
}

echo json_encode($json);
?>