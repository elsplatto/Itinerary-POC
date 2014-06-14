<?php
if (isset($_GET['logout']))
{
    $logout = $_GET['logout'];
}

if(session_id() == '') {
    session_start();
}
if (isset($logout))
{
    unset($_SESSION['adminUserId']);
    unset($_SESSION['adminName']);
    unset($_SESSION['adminUsername']);
    unset($_SESSION['adminRole']);
    session_destroy();
}
?>

<html>
<head>
    <title>Admin - Homepage</title>
    <?php
    include 'includes/head.php';
    ?>
</head>
<body>
<section>
    <div class="row">
        <div class="large-12 columns">
            <?php
            include 'includes/login-form.php';
            ?>
        </div>
    </div>
</section>
</body>
</html>