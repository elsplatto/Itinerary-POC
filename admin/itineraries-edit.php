<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

if (!empty($_GET['id']))
{
    $itineraryId = $_GET['id'];
}
else
{
    header('Location: itineraries-list.php?msg=Itinerary not found.');
}

function getItinerary($id, $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT title, page_name, sub_title, intro_text, description, image_landscape, image_portrait, is_live FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($title, $page_name, $sub_title, $intro_text, $description, $image_landscape, $image_portrait,  $is_live);

    $results = array();
    while($stmt->fetch())
    {
        $results['title'] = $title;
        $results['page_name'] = $page_name;
        $results['sub_title'] = $sub_title;
        $results['intro_text'] = $intro_text;
        $results['description'] = $description;
        $results['image_landscape'] = $image_landscape;
        $results['image_portrait'] = $image_portrait;
        $results['is_live'] = $is_live;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}

function getLocations($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT id, title, image_landscape, tags FROM itinerary_locations WHERE is_live = 1');
    $stmt->execute();
    $stmt->bind_result($id, $title, $image_landscape, $tags);

    $results = array();
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['id'] = $id;
        $results[$i]['title'] = $title;
        $results[$i]['image_landscape'] = $image_landscape;
        $results[$i]['tags'] = $tags;
        $i++;
    }
    $stmt->close();
    $mysqli->close();
    return $results;
}

function getSelectedLocation($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT il.id, il.title, il.image_landscape, il.tags FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($id, $title, $image_landscape, $tags);
    $results = array();
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['id'] = $id;
        $results[$i]['title'] = $title;
        $results[$i]['image_landscape'] = $image_landscape;

        $results[$i]['tags'] = $tags;
        $i++;
    }
    $stmt->close();
    $mysqli->close();
    return $results;
}

$itineraryDetails = getItinerary($itineraryId, $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
$locations = getLocations($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
$selectedLocations = getSelectedLocation($itineraryId, $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
?>
<html>
<head>
    <title>Edit Itinerary - <?=$itineraryDetails['title']?></title>
    <?php
    include 'includes/head.php';
    ?>
</head>
<body>
<section>
    <div class="row">
        <div class="large-12 columns">
            <a href="dashboard.php">Home</a>
            <h1>Edit Itinerary</h1>
            <a href="itineraries-list.php">< Back to Itinerary List</a>

        </div>
    </div>
</section>

<section>
    <div class="row">
        <div class="large-12 columns">
            <form enctype="multipart/form-data" id="frmItinerary" name="frmItinerary" action="itineraries-process.php" method="post" data-abide>
                <input type="hidden" id="itineraryId" name="itineraryId" value="<?=$itineraryId?>" />
                <label for="txtTitle">Title:<span class="red">*</span>
                    <input type="text" id="txtTitle" name="txtTitle" value="<?=stripcslashes($itineraryDetails['title'])?>" required />
                    <small class="error">Please enter a title</small>
                </label>

                <label for="txtPageName">Page Name:<span class="red">*</span>
                    <span class="ajaxCheck"></span>
                    <input type="text" id="txtPageName" name="txtPageName" value="<?=$itineraryDetails['page_name']?>" placeholder="Separate words with hyphens" autocomplete="off" required />
                    <small class="error">Please enter a page name</small>
                </label>



                <label for="txtSubTitle">Sub Title:
                    <input type="text" id="txtSubTitle" name="txtSubTitle" autocomplete="off" value="<?=stripcslashes($itineraryDetails['sub_title'])?>" />
                </label>

                <label for="txtIntroText">Intro text:</label>
                <textarea class="medium" id="txtIntroText" name="txtIntroText" cols="100" rows="15"><?=stripcslashes($itineraryDetails['intro_text'])?></textarea>

                <label for="txtDescription">Description:</label>
                <textarea class="large" id="txtDescription" name="txtDescription" cols="100" rows="15"><?=stripcslashes($itineraryDetails['description'])?></textarea>

                <label for="txtImgLandscape">Landscape Image (1200 x 640):
                    <input type="file" id="landscapeImgUpload" name="landscapeImgUpload" />
                    <input type="text" id="txtImgLandscape" name="txtImgLandscape" value="<?=$itineraryDetails['image_landscape']?>" readonly />
                    <inpu type="hidden" name="landscapeDir" id="landscapeDir" value="../img/itineraries/landscape/" />
                </label>

                <label for="txtImgPortrait">Portrait Image (640 x 1200):
                    <input type="file" id="portraitImgUpload" name="portraitImgUpload" />
                    <input type="text" id="txtImgPortrait" name="txtImgPortrait" value="<?=$itineraryDetails['image_portrait']?>" readonly />
                    <inpu type="hidden" name="portraitDir" id="portraitDir" value="../img/itineraries/portrait/" />
                </label>



                <div id="locationTiles">
                    <label>Locations</label>
                    <div class="locationTiles large-12">
                        <input type="text" class="tileFilter" data-containment="locationTileList" placeholder="Enter text to filter tiles" />
                        <ul id="locationTileList" class="tileList large-12">
                            <?php
                            foreach ($selectedLocations as $selectedLocation)
                            {
                            ?>
                                <li data-itinerary-location-selected data-location-id="<?=$selectedLocation['id']?>" data-itinerary-id="<?=$itineraryId?>" data-tags="<?=$selectedLocation['tags']?>">
                                    <div class="tile">
                                        <div class="textholder">
                                            <h5><?=$selectedLocation['title']?></h5>
                                        </div>
                                        <div class="imgHolder">
                                            <img src="../img/itineraries/locations/landscape/sml/<?=$selectedLocation['image_landscape']?>" />
                                        </div>
                                    </div>
                                </li>
                            <?php
                            }

                            foreach ($locations as $location)
                            {
                                $tileMatch = false;
                                foreach ($selectedLocations as $selectedLocation)
                                {
                                    if ($location['id'] == $selectedLocation['id'])
                                    {
                                        $tileMatch = true;
                                        break;
                                    }
                                }
                                if (!$tileMatch)
                                {
                                ?>
                                <li data-location-id="<?=$location['id']?>" data-itinerary-id="<?=$itineraryId?>" data-tags="<?=$location['tags']?>">
                                    <div class="tile">
                                        <div class="textholder">
                                            <h5><?=$location['title']?></h5>
                                        </div>
                                        <div class="imgHolder">
                                            <img src="../img/itineraries/locations/landscape/sml/<?=$location['image_landscape']?>" />
                                        </div>
                                    </div>
                                </li>
                                <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>

                </div>



                <label for="chkIsLive">Live:
                    <input type="checkbox" id="chkIsLive" name="chkIsLive" value="1" <?=$itineraryDetails['is_live'] == 1 ? 'checked="checked"' : '' ?>/>
                </label>

                <input type="submit" value="Submit" class="button" />&nbsp;<a href="itineraries-list.php" class="cancel">Cancel</a>
            </form>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
<script src="../js/foundation/foundation.abide.js"></script>
<script>
    $('#frmItinerary').foundation('abide');

$(function() {



    $('.tileFilter').keyup(function(e) {

        var containmentEl = $('#' + $(this).attr('data-containment'));
        var elValue = $(this).val();
        if (elValue.length === 0)
        {
            containmentEl.children('li').each(function(i){
                $(this).attr('style','');
            });
        }
        else
        {
            containmentEl.children('li').each(function(i) {
                var tagAttrStr = $(this).attr('data-tags');
                if (tagAttrStr.indexOf(elValue) < 0)
                {
                    $(this).attr('style','display: none');
                }
                else
                {
                    $(this).attr('style','');
                }
            });
        }
    });



    $('.locationTiles .tileList li').on('click', function(e) {
        e.preventDefault();
        var isSelected = $(this).attr('data-itinerary-location-selected');
        var action;
        var itinerary_id = $(this).attr('data-itinerary-id');
        var location_id = $(this).attr('data-location-id');
        var item_index = $(this).index() + 1; //add on to number as this will be our order
        if (typeof isSelected !== 'undefined' && isSelected !== false)
        {
            $(this).removeAttr('data-itinerary-location-selected');
            action = 'remove';
        }
        else
        {
            $(this).attr('data-itinerary-location-selected','');
            action = 'add';
        }

        //console.log('action['+action+']itinerary_id['+itinerary_id+']location_id['+location_id+']item_index['+item_index+']');
        $.ajax({
            type: 'POST',
            url: 'add-delete-location-tile.php',
            data: {action: action, itinerary_id: itinerary_id, location_id: location_id, sequence: item_index},
            success: function(data)
            {
                //var obj = JSON.parse(data);
                //console.dir(obj);
                //console.dir(data);
            }
        });
    });

    $('.locationTiles .tileList').sortable({
        containment: 'parent',
        update: function (event, ui) {
            //console.dir(event);
            //console.dir(ui);
            var isSelected = ui.item.context.dataset.hasOwnProperty('itineraryLocationSelected');
            if (isSelected)
            {
                //get all selected items and update database
                $(this).children('li[data-itinerary-location-selected]').each(function(i){
                    //console.log('index['+$(this).index()+']');
                    var itinerary_id = $(this).attr('data-itinerary-id');
                    var location_id = $(this).attr('data-location-id');
                    var item_index = $(this).index() + 1;
                    //console.log('itinerary_id['+itinerary_id+']location_id['+location_id+']sequence['+item_index+']');
                    $.ajax({
                        type: 'POST',
                        url: 'update-location-tile-order.php',
                        data: {itinerary_id: itinerary_id, location_id: location_id, sequence: item_index},
                        success: function(data)
                        {
                            //var obj = JSON.parse(data);
                            //console.dir(obj);
                            //console.dir(data);
                        }
                    });
                });
            }
        }
    });

    $('.insertTag').click(function(e) {
        e.preventDefault();
        var target = $('#' + $(this).attr('data-target'));
        var tag = $(this).attr('data-tag');
        var tagHTML = '';
        switch(tag)
        {
            case 'paragraph':
                tagHTML = '\n<p><\/p>';
                break;

            case 'quote':
                tagHTML = '\n<blockquote><br \/><small><\/small><\/blockquote>';
                break;

            case 'image':
                tagHTML = '\n<figure><img src="" alt="" /><figcaption>Caption goes here</figcaption></figure>';
                break;
        }
        target.val(target.val() + tagHTML);
    });
});
</script>
<?php
include 'includes/unique-pagename-js.php';
?>
</body>
</html>