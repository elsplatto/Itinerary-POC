<?php
include '../includes/admin-settings.php';
include '../../includes/db.php';
include '../includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

if (!empty($_POST['username']))
{
    if (!empty($_POST['postedId']))
    {
        $postedId = $_POST['postedId'];
    }
    else
    {
        $postedId = 0;
    }
    $username = $_POST['username'];
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT id, firstname, lastname FROM admin_users WHERE username = ? AND id <> ?');
    $stmt->bind_param('si', $username, $postedId);
    $stmt->execute();
    $stmt->bind_result($id, $firstname, $lastname);


    $i = 0;
    while($stmt->fetch())
    {
        $userId = $id;
        $userFirstname = $firstname;
        $userLastname = $lastname;
        $i++;
    }

    $stmt->close();
    $mysqli->close();


    if ($i > 0)
    {
        $json = '{"success": true, "unique": false, "userid": '.$userId.', "firstname": "' .$userFirstname. '", "lastname": "' .$userLastname. '"}';
    }
    else
    {
        $json = '{"success": true, "unique": true}';
    }
}


echo json_encode($json);

?>