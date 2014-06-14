<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrSuper);

function getUsers($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE) {
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT id, username, email, firstname, lastname, role, date_created, is_valid FROM admin_users');

    $stmt->execute();
    $stmt->bind_result($id, $username, $email, $firstname, $lastname, $role, $date_created, $is_valid);

    $results = array();
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['id'] = $id;
        $results[$i]['username'] = $username;
        $results[$i]['email'] = $email;
        $results[$i]['firstname'] = $firstname;
        $results[$i]['lastname'] = $lastname;
        $results[$i]['role'] = $role;
        $results[$i]['date_created'] = $date_created;
        $results[$i]['is_valid'] = $is_valid;
        $i++;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}

$users = getUsers($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
?>
<html>
<head>
    <title>List Users</title>
    <?php
    include 'includes/head.php';
    ?>
</head>
<body>
<section>
    <div class="row">
        <div class="large-12 columns">
            <a href="dashboard.php">Home</a>
            <h1>Users</h1>
            <a href="user-add.php">Add User</a>
        </div>
    </div>
</section>


<section>
    <div class="row">
        <div class="large-12 columns">
            <table class="list" border="0">
                <thead>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Role</th>
                <th>Date Created</th>
                <th>Valid</th>
                <th>Action</th>
                </thead>
                <tbody>
                <?php
                foreach ($users as $user)
                {
                ?>
                    <tr>
                        <td><?=$user['id']?></td>
                        <td><?=$user['username']?></td>
                        <td><?=$user['firstname'] . ' ' . $user['lastname']?></td>
                        <td><?=$user['role']?></td>
                        <td><?=gmdate("D jS \of M Y h:i:s A",$user['date_created'])?></td>
                        <td><?php echo ($user['is_valid'] == 1) ? '<span class="valid">Yes</span>' : '<span class="invalid">No</span>'?></td>
                        <td><a href="user-edit.php?id=<?=$user['id']?>">edit</a></td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
</body>
</html>