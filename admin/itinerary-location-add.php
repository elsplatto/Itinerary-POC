<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrAuthor);
$needsMaps = true;
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
            <h1>Add Itinerary Location</h1>
            <a href="itinerary-locations-list.php">< Back to Itinerary Locations List</a>

        </div>
    </div>
</section>

<section>
    <div class="row">
        <div class="large-12 columns">
            <form enctype="multipart/form-data" id="frmItineraryLocation" name="frmItineraryLocation" action="itinerary-locations-process.php" method="post" data-abide>

                <label for="txtTitle">Title:<span class="red">*</span>
                    <input type="text" id="txtTitle" name="txtTitle" autocomplete="off" required />
                    <small class="error">Please enter a title</small>
                </label>

                <label for="txtSubTitle">Sub Title:
                    <input type="text" id="txtSubTitle" name="txtSubTitle" autocomplete="off" />
                </label>

                <a href="#" class="showHide" data-target="#mapArea" data-hideText="Hide Map" data-showText="Show Map">Hide Map</a>
                <div id="mapArea">
                    <div class="large-12">
                        <div class="large-6 left">
                            <label for="txtAddress">Address:
                                <input type="text" id="txtAddress" name="txtAddress" />
                            </label>
                            <button class="small" id="findCoords">Find Location</button>

                            <label for="txtLat">Lat:
                                <input type="text" id="txtLat" name="txtLat" readonly />
                            </label>


                            <label for="txtLng">Lng:
                                <input type="text" id="txtLng" name="txtLng" readonly />
                            </label>

                        </div>
                        <div class="large-6 right">
                            <div id="tile-map-canvas" class="google-maps" style="width: 800px; height: 500px;"></div>
                        </div>
                    </div>
                </div>

                <label for="txtDescription">Content:
                    <textarea class="large" id="txtContent" name="txtContent" cols="100" rows="15"></textarea>
                </label>

                <label for="txtImgLandscape">Landscape Image (1000 x 522):
                    <input type="file" id="landscapeImgUpload" name="landscapeImgUpload" />
                    <inpu type="hidden" name="landscapeDir" id="landscapeDir" value="../img/itineraries/locations/landscape/" />
                    <input type="hidden" id="txtImgLandscape" name="txtImgLandscape" />
                </label>

                <!--label for="txtImgPortrait">Portrait Image (640 x 120):
                    <input type="file" id="portraitImgUpload" name="portraitImgUpload" />
                    <inpu type="hidden" name="portraitDir" id="portraitDir" value="../img/itineraries/locations/portrait/" />
                    <input type="hidden" id="txtImgPortrait" name="txtImgPortrait" />
                </label-->

                <label for="txtTags">Tags:
                    <input type="text" id="txtTags" name="txtTags" placeholder="No # - separate by comma." />
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
    $('#frmItineraryLocation').foundation('abide');

    $(function() {

        $('#findCoords').click(function(e) {
            e.preventDefault();
            var el = $('#txtAddress');
            var btn = $(this);
            var address = el.val();
            if (address.length > 0)
            {
                $.ajax({
                    type: 'POST',
                    url: 'services/geocode.php',
                    dataType: 'json',
                    data: {
                        address: address
                    },
                    beforeSend: function()
                    {
                        beforeFindCoords(btn);
                    },
                    success: function(data)
                    {
                        successFindCoords(data, btn);
                    }
                });
            }
        });

        function beforeFindCoords(btn)
        {
            btn.text('Looking...');
            btn.attr('disabled','disabled');
        }

        function successFindCoords(data, btn)
        {
            btn.text('Find Location');
            btn.removeAttr('disabled');
            if (data.status === 'OK' && data.results.length > 0) {
                $('#txtLat').val(data.results[0].geometry.location.lat);
                $('#txtLng').val(data.results[0].geometry.location.lng);
                var newMapCenter = new google.maps.LatLng(data.results[0].geometry.location.lat,data.results[0].geometry.location.lng);
                marker.setPosition(newMapCenter);
                map.setCenter(newMapCenter);
                map.setZoom(15);
            }
        }

        $('.useMap').click(function(e) {
            e.preventDefault();
            var target = $('#' + $(this).attr('data-target'));

            if (target.is(':hidden'))
            {
                target.show();
                $(this).text('Hide map');
            }
            else
            {
                target.hide();
                $(this).text('Show map');
            }
        });
    });
</script>
<script>
    initialize();
    var map;
    var marker;
    function initialize() {
        var myLatlng = new google.maps.LatLng(-33.836311,151.208267);

        var myOptions = {
            zoom: 12,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById("tile-map-canvas"), myOptions);

        marker = new google.maps.Marker({
            draggable: true,
            position: myLatlng,
            map: map,
            title: "Your location"
        });

        google.maps.event.addListener(marker, 'dragend', function (event) {
            document.getElementById("txtLat").value = this.getPosition().lat();
            document.getElementById("txtLng").value = this.getPosition().lng();
        });

    }
</script>
</body>
</html>