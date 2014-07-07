<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

$itinerary_id = $_POST['itinerary_id'];

if (isset($itinerary_id))
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('DELETE FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $itinerary_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    $json = '{"success": true, "msg": "Deleted successfully."}';
}
else
{
    $json = '{"success": false, "msg": "There was a problem with that delete"}';
}

echo json_encode($json);
?>