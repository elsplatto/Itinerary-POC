<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

function getItineraries($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE) {
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT id, title, is_live FROM itinerary');

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

$itineraries = getItineraries($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
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
            <h1>Itineraries</h1>
            <a href="itineraries-add.php">Add Itinerary</a>
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
                foreach ($itineraries as $itinerary)
                {
                ?>
                    <tr>
                        <td><?=$itinerary['id']?></td>
                        <td><?=$itinerary['title']?></td>
                        <td><?php echo ($itinerary['is_live'] == 1 ? 'Live' :  'Not Live') ?></td>
                        <td><a href="itineraries-edit.php?id=<?=$itinerary['id']?>">edit</a></td>
                        <!--td><a href="itineraries-generate.php?id=<?=$itinerary['id']?>" class="ajaxGen">generate</a></td-->
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<script>

</script>
</body>
</html>