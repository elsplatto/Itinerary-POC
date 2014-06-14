<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

function getItineraryLocations($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE) {
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT id, title, is_live FROM itinerary_locations');

    $stmt->execute();
    $stmt->bind_result($id, $title, $is_live);

    $results = array();
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['id'] = $id;
        $results[$i]['title'] = $title;
        $results[$i]['is_live'] = $is_live;
        $i++;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}

$itineraryLocations = getItineraryLocations($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
?>
<html>
<head>
    <title>List Itineraries</title>
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
            <a href="dashboard.php">Home</a>
            <h1>Itinerary Locations</h1>
            <a href="itinerary-location-add.php">Add Itinerary Location</a>
        </div>
    </div>
</section>

<section>
    <div class="row">
        <div class="large-12 columns">
            <table class="list" border="0">
                <thead>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Action</th>
                </thead>
                <tbody>
                <?php
                foreach ($itineraryLocations as $itineraryLocation)
                {
                    ?>
                    <tr>
                        <td><?=$itineraryLocation['id']?></td>
                        <td><?=$itineraryLocation['title']?></td>
                        <td><?php echo ($itineraryLocation['is_live'] == 1 ? 'Live' :  'Not Live') ?></td>
                        <td><a href="itinerary-location-edit.php?id=<?=$itineraryLocation['id']?>">edit</a></td>
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