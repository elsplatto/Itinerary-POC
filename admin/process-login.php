<?php
include '../includes/db.php';

$username = $_POST['fldUsername'];
$password = $_POST['fldPassword'];


if (isset($username) && isset($password))
{
    $password = md5($password);

    $q = "SELECT id, firstname, username, role FROM admin_users WHERE password = '$password' AND username = '$username' AND is_valid = 1";
    $result = mysql_query($q, $connection);

    $numRows = mysql_num_rows($result);

    if($numRows > 0)
    {
        if(session_id() == '') {
            session_start();
        }
        // Storing instagram user data into session
        while($data = mysql_fetch_assoc($result))
        {
            $_SESSION['adminUserId'] = $data["id"];
            $_SESSION['adminName'] = $data["firstname"];
            $_SESSION['adminUsername'] = $data["username"];
            $_SESSION['adminRole'] = $data["role"];
        }
    }

    ;
}

if (isset($_SESSION['adminUserId']))
{
    //echo 'here';
    //$sessionDataId = $_SESSION['adminUserId'];
    header('Location: dashboard.php');
}
else
{
    header('Location: login-fail.php');
}



?>