<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

?>
<html>
<head>
    <title>Add Itinerary</title>
    <?php
    include 'includes/head.php';
    ?>
</head>
<body>
<section>
    <div class="row">
        <div class="large-12 columns">
            <a href="dashboard.php">Home</a>
            <h1>Add Itinerary</h1>
            <a href="itineraries-list.php">< Back to Itinerary List</a>

        </div>
    </div>
</section>

<section>
    <div class="row">
        <div class="large-12 columns">
            <form enctype="multipart/form-data" id="frmItinerary" name="frmItinerary" action="itineraries-process.php" method="post" data-abide>

                <label for="txtTitle">Title:<span class="red">*</span>
                    <input type="text" id="txtTitle" name="txtTitle" autocomplete="off" required />
                    <small class="error">Please enter a title</small>
                </label>

                <label for="txtPageName">Page Name:<span class="red">*</span>
                    <span class="ajaxCheck"></span>
                    <input type="text" id="txtPageName" name="txtPageName" placeholder="Separate words with hyphens" autocomplete="off" required />
                    <small class="error">Please enter a page name</small>
                </label>

                <label for="txtCredit">Credit:
                    <input type="text" id="txtCredit" name="txtCredit" autocomplete="off" />
                </label>

                <label for="txtSubTitle">Sub Title:
                    <input type="text" id="txtSubTitle" name="txtSubTitle" autocomplete="off" />
                </label>

                <label for="txtIntroText">Intro text:</label>
                <textarea class="medium" id="txtIntroText" name="txtIntroText" cols="100" rows="15"></textarea>

                <label for="txtDescription">Description:</label>
                <textarea class="large" id="txtDescription" name="txtDescription" cols="100" rows="15"></textarea>

                <label for="txtImgLandscape">Landscape Image (1200 x 640):
                    <input type="hidden" id="txtImgLandscape" name="txtImgLandscape" />
                    <input type="file" id="landscapeImgUpload" name="landscapeImgUpload" />
                    <inpu type="hidden" name="landscapeDir" id="landscapeDir" value="../img/itineraries/landscape/" />
                </label>

                <label for="txtImgPortrait">Portrait Image (640 x 1200):
                    <input type="hidden" id="txtImgPortrait" name="txtImgPortrait" />
                    <input type="file" id="portraitImgUpload" name="portraitImgUpload" />
                    <inpu type="hidden" name="portraitDir" id="portraitDir" value="../img/itineraries/portrait/" />
                </label>


                <label for="chkIsLive">Live:
                    <input type="checkbox" id="chkIsLive" name="chkIsLive" value="1" />
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


});
</script>
<?php
include 'includes/unique-pagename-js.php';
?>
</body>
</html>