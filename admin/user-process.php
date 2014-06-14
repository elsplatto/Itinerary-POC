<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrSuper);


if (!empty($_POST))
{
    if (!empty($_POST['userId']))
    {
        $userId = $_POST['userId'];
    }
    else
    {
        $userId = 0;
    }
    $firstname = $_POST['txtFirstname'];
    $lastname = $_POST['txtLastname'];
    $username = $_POST['txtUsername'];
    $role = $_POST['selRole'];
    $email = $_POST['txtEmail'];

    if ($userId == 0)
    {
        $password = md5($_POST['txtPassword']);
        $date_created = time();
    }

    if (!empty($_POST['chkValid']))
    {
        $is_valid = $_POST['chkValid'];
    }
    else
    {
        $is_valid = 0;
    }

    if ($userId > 0)
    {
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $stmt = $mysqli->prepare('UPDATE admin_users SET username = ?, firstname = ?, lastname = ?, role = ?, email = ?, is_valid = ? WHERE id = ?');
        $stmt->bind_param('sssssii', $username, $firstname, $lastname, $role, $email, $is_valid, $userId);
    }
    else
    {
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $query = 'INSERT INTO admin_users (username, firstname, lastname, role, password, email, date_created, is_valid) VALUES (?,?,?,?,?,?,?,?)';
        //echo 'INSERT INTO admin_users (username, firstname, lastname, role, password, email, date_created, is_valid) VALUES ('.$username.', '.$firstname.', '.$lastname.', '.$role.', '.$password.', '.$email.', '.$date_created.', '.$is_valid.')<br />';
        //echo '['.$username.']['.$firstname.']['.$lastname.']['.$role.']['.$password.']['.$email.']['.$date_created.']['.$is_valid.']';
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssssssii', $username, $firstname, $lastname, $role, $password, $email, $date_created, $is_valid);

    }
    $stmt->execute();

    $stmt->close();
    $mysqli->close();
}

header('Location: user-list.php');
?>