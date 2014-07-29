<?php
include 'includes/admin-settings.php';
include 'includes/global-admin-functions.php';
include '../includes/db.php';
include 'includes/class.upload.php';
assessLogin($securityArrAuthor);


if (!empty($_POST))
{
    $title = $_POST['txtTitle'];
    $sub_title = $_POST['txtSubTitle'];
    $address = $_POST['txtAddress'];
    $lat = $_POST['txtLat'];
    $lng = $_POST['txtLng'];
    $content = $_POST['txtContent'];
    $content = str_replace("\n", '', $content);
    $content = str_replace("\r", '', $content);
    $image_landscape = $_POST['txtImgLandscape'];
    //$image_portrait = $_POST['txtImgPortrait'];
    $tags = $_POST['txtTags'];
    if (!empty($_POST['chkIsLive']))
    {
        $is_live = $_POST['chkIsLive'];
    }
    else
    {
        $is_live = 0;
    }


    if (isset($_FILES['landscapeImgUpload']))
    {
        // ---------- IMAGE UPLOAD ----------

        // we create an instance of the class, giving as argument the PHP object
        // corresponding to the file field from the form
        // All the uploads are accessible from the PHP object $_FILES
        $landscapeImg = new Upload($_FILES['landscapeImgUpload']);


        //Get directory
        $landscapeImgDir = '../img/itineraries/locations/landscape/';
        $landscapeImgDirLge = $landscapeImgDir.'lge/';
        $landscapeImgDirMed = $landscapeImgDir.'med/';
        $landscapeImgDirSml = $landscapeImgDir.'sml/';
        if (isset($_POST['landscapeDir']))
        {
            $landscapeImgDir = $_POST['landscapeDir'];
            $landscapeImgDirLge = $landscapeImgDir.'lge/';
            $landscapeImgDirMed = $landscapeImgDir.'med/';
            $landscapeImgDirSml = $landscapeImgDir.'sml/';
        }

        if ($landscapeImg->uploaded)
        {
            // uploaded - process and save to correct directory
            $landscapeImg->image_resize = false;
            $landscapeImg->process($landscapeImgDirLge);

            $landscapeImg->image_resize = true;
            $landscapeImg->image_ratio_y = true;
            $landscapeImg->image_x = 620;
            $landscapeImg->process($landscapeImgDirMed);

            $landscapeImg->image_resize = true;
            $landscapeImg->image_ratio_y = true;
            $landscapeImg->image_x = 230;
            $landscapeImg->process($landscapeImgDirSml);

            $image_landscape = $landscapeImg->file_dst_name;
        }
    }



    if (!empty($_POST['itineraryLocationId']) && $_POST['itineraryLocationId'] > 0)
    {
        $itinerary_location_id = $_POST['itineraryLocationId'];
        $date_last_modified = time();
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $query = 'UPDATE itinerary_locations SET title = ?, sub_title = ?, address = ?, lat = ?, lng = ?, image_landscape = ?, content = ?, tags = ?, date_last_modified = ?, is_live = ? WHERE id = ?';
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sssddsssiii', $title, $sub_title, $address, $lat, $lng, $image_landscape, $mysqli->real_escape_string($content), $tags, $date_last_modified, $is_live, $itinerary_location_id);
    }
    else
    {
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $date_created = time();
        $stmt = $mysqli->prepare('INSERT INTO itinerary_locations (title, sub_title, address, lat, lng, image_landscape, content, tags, date_created, is_live) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->bind_param('sssddsssii', $title, $sub_title, $mysqli->real_escape_string($address), $lat, $lng, $image_landscape, $mysqli->real_escape_string($content), $tags, $date_created, $is_live);

    }
    $stmt->execute();

    $stmt->close();
    $mysqli->close();


}

header('Location: itinerary-locations-list.php');
?>