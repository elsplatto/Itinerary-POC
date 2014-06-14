<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArr);
?>
<html>
<head>
    <?php
    include 'includes/head.php';
    ?>
</head>
<body>

<?php
include 'includes/header.php';
?>

<section>
    <div class="row">
        <div class="large-12 columns">
            <h1>
                Home
            </h1>
            <?php
            if (in_array($_SESSION['adminRole'],$securityArrAuthor))
            {?>
                <p><a href="itineraries-list.php">Itineraries</a></p>
            <?php
            }
            ?>

            <?php
            if (in_array($_SESSION['adminRole'],$securityArrAuthor))
            {?>
                <p><a href="itinerary-locations-list.php">Itinerary Locations</a></p>
            <?php
            }
            ?>

            <?php
            if (in_array($_SESSION['adminRole'],$securityArrSuper))
            {
             ?>
                <p><a href="user-list.php">Users</a></p>
            <?php
            }
            ?>
        </div>
    </div>
</section>
</body>
</html>