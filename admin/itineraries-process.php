<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
include 'includes/class.upload.php';
assessLogin($securityArrAuthor);

$error_msg = '';

if (!empty($_POST))
{
    if (!empty($_POST['itineraryId']))
    {
        if ($_POST['itineraryId'] > 0)
        {
            $itineraryId = $_POST['itineraryId'];
            $date_last_modified = time();
        }
        else
        {
            $itineraryId = 0;
            $date_created = time();
        }
    }
    else
    {
        $itineraryId = 0;
        $date_created = time();
    }
    $title = $_POST['txtTitle'];
    $page_name = $_POST['txtPageName'];
    $sub_title = $_POST['txtSubTitle'];
    $intro_text = $_POST['txtIntroText'];
    $description = $_POST['txtDescription'];
    $image_landscape = $_POST['txtImgLandscape'];
    $image_portrait = $_POST['txtImgPortrait'];

    if (isset($_FILES['landscapeImgUpload']))
    {
        // ---------- IMAGE UPLOAD ----------

        // we create an instance of the class, giving as argument the PHP object
        // corresponding to the file field from the form
        // All the uploads are accessible from the PHP object $_FILES
        $landscapeImg = new Upload($_FILES['landscapeImgUpload']);

        //Get directory
        $landscapeDir = '../img/itineraries/landscape/';
        if (isset($_POST['landscapeDir']))
        {
            $landscapeDir = $_POST['landscapeDir'];
        }

        if ($landscapeImg->uploaded)
        {
            // uploaded - process and save to correct directory
            $landscapeImg->image_resize = false;
            $landscapeImg->process($landscapeDir);

            if ($landscapeImg->processed)
            {
                $landscapeImg->clean();
            }
            else
            {
                if (strlen($error_msg) > 0)
                {
                    $error_msg += ',';
                }
                $error_msg += $landscapeImg->error;
            }
            $image_landscape = $landscapeImg->file_dst_name;
        }
    }

    if (isset($_FILES['portraitImgUpload']))
    {
        // ---------- IMAGE UPLOAD ----------

        // we create an instance of the class, giving as argument the PHP object
        // corresponding to the file field from the form
        // All the uploads are accessible from the PHP object $_FILES
        $portraitImg = new Upload($_FILES['portraitImgUpload']);

        //Get directory
        $portraitDir = '../img/itineraries/portrait/';
        if (isset($_POST['portraitDir']))
        {
            $portraitDir = $_POST['portraitDir'];
        }

        if ($portraitImg->uploaded)
        {
            // uploaded - process and save to correct directory
            $portraitImg->image_resize = false;
            $portraitImg->process($portraitDir);
            if ($portraitImg->processed)
            {
                $portraitImg->clean();
            }
            else
            {
                if (strlen($error_msg) > 0)
                {
                    $error_msg += ',';
                }
                $error_msg += $portraitImg->error;
            }
            $image_portrait = $portraitImg->file_dst_name;
        }
    }

    if (!empty($_POST['chkIsLive']))
    {
        $is_live = $_POST['chkIsLive'];
    }
    else
    {
        $is_live = 0;
    }

    if ($itineraryId > 0)
    {
        $filename = $json_file_prefix . $itineraryId . '-' . $date_last_modified .'.json';
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $query = 'UPDATE itinerary SET title = ?, page_name = ?, sub_title = ?, intro_text = ?, description = ?, image_landscape = ?, image_portrait = ?, date_last_modified = ?, json_filename = ?, is_live = ? WHERE id = ?';
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sssssssisii', $title, $page_name, $mysqli->real_escape_string($sub_title), $mysqli->real_escape_string($intro_text), $mysqli->real_escape_string($description), $image_landscape, $image_portrait, $date_last_modified, $filename, $is_live, $itineraryId);
        $stmt->execute();
    }
    else
    {
        $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $query = 'INSERT INTO itinerary (title, page_name, sub_title, intro_text, description, image_landscape, image_portrait, date_created, is_live) VALUES (?,?,?,?,?,?,?,?,?)';

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sssssssii', $title, $page_name, $mysqli->real_escape_string($sub_title), $mysqli->real_escape_string($intro_text), $mysqli->real_escape_string($description), $image_landscape, $image_portrait, $date_created, $is_live);
        $stmt->execute();
        $new_id = $mysqli->insert_id;

        $filename = $json_file_prefix . $new_id . '-' . $date_created .'.json';
        $query = 'UPDATE itinerary SET json_filename = ? WHERE id = ?';
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $filename, $new_id);
        $stmt->execute();
    }


    $stmt->close();
    $mysqli->close();
}

/*
=====================================================
START-PROCESS JSON FILE
=====================================================
*/

if (!isset($itineraryId))
{
    if (isset($new_id))
    {
        $itineraryId = $new_id;
    }
}

if (isset($itineraryId))
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

    $stmt = $mysqli->prepare('SELECT title, page_name, sub_title, intro_text, description, image_landscape, image_portrait, date_created, date_last_modified, json_filename, last_json_filename, is_live FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $itineraryId);
    $stmt->execute();
    $stmt->bind_result($title, $page_name, $sub_title, $intro_text, $description, $image_landscape, $image_portrait, $date_created, $date_last_modified, $json_filename, $last_json_filename, $is_live);

    $results = array();
    $json = '';
    $json .= '{"itinerary": [';
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['title'] = $title;
        $results[$i]['page_name'] = $page_name;
        $results[$i]['sub_title'] = $sub_title;
        $results[$i]['intro_text'] = $intro_text;
        $results[$i]['description'] = $description;
        $results[$i]['image_landscape'] = $image_landscape;
        $results[$i]['image_portrait'] = $image_portrait;
        $results[$i]['date_created'] = $date_created;
        $results[$i]['date_last_modified'] = $date_last_modified;
        $results[$i]['json_filename'] = $json_filename;
        $results[$i]['last_json_filename'] = $last_json_filename;
        $i++;
    }

    $resultsNumRows = count($results);
    $resultsCount = 0;
    if ($resultsNumRows > 0)
    {
        foreach ($results as $result)
        {
            $json .= json_encode($result);
            $filename = $result['json_filename'];
            $last_filename = $result['last_json_filename'];
        }
    }

    $mysqli2 = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt2 = $mysqli2->prepare('SELECT il.id, il.title, il.image_landscape, il.image_portrait, il.lat, il.lng, il.tags FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence');
    $stmt2->bind_param('i', $itineraryId);
    $stmt2->execute();
    $stmt2->bind_result($id, $title, $image_landscape, $image_portrait, $lat, $lng, $tags);
    $innerResults = array();
    $i = 0;
    $json .= ', "locations": [';
    while($stmt2->fetch())
    {
        $innerResults[$i]['id'] = $id;
        $innerResults[$i]['title'] = $title;
        $innerResults[$i]['image_landscape'] = $image_landscape;
        $innerResults[$i]['image_portrait'] = $image_portrait;
        $innerResults[$i]['lat'] = $lat;
        $innerResults[$i]['lng'] = $lng;
        $innerResults[$i]['tags'] = $tags;
        $i++;
    }

    $innerNumRows = count($innerResults);
    $innerCount = 0;
    if ($innerNumRows > 0)
    {
        foreach ($innerResults as $innerRow)
        {
            $json .= json_encode($innerRow);
            $innerCount++;
            if ($innerCount < $innerNumRows)
            {
                $json .= ',';
            }
        }
    }

    $stmt2->close();
    $mysqli2->close();


    $json .= ']';
    $json .= ']}';

    $stmt->close();
    $mysqli->close();

    echo $json;

    if (file_exists('../json/'.$last_filename) && !is_null($last_filename))
    {
        unlink('../json/'.$last_filename);
    }

    $file = fopen('../json/'.$filename,'w');
    fwrite($file, $json);
    fclose($file);

    include 'template-builder.php';

    include 'manifest-builder.php';
}
else
{
    if (strlen($error_msg) > 0)
    {
        $error_msg += ',';
    }
    $error_msg += 'Failed to successfully write new json file.';
}

/*
=====================================================
END-PROCESS JSON FILE
=====================================================
*/




if (strlen($error_msg) > 0)
{
    if ($itineraryId > 0)
    {
        header('Location: itineraries-edit.php?id='.$itineraryId.'&error_msg='.$error_msg);
    }
    else
    {
        header('Location: itineraries-list.php?error_msg='.$error_msg);
    }
}
else
{
    if ($itineraryId > 0)
    {
        header('Location: itineraries-list.php?success_msg=Itinerary successfully updated');
    }
    else
    {
        header('Location: itineraries-list.php?new_id='.$new_id.'&success_msg=Itinerary successfully added');
    }
}
?>