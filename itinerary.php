<?php
include 'includes/db.php';


function getItineraryDetail($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT title, sub_title, intro_text, description, image_landscape, image_portrait FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($title, $sub_title, $intro_text, $description, $image_landscape, $image_portrait);

    $results = array();
    while($stmt->fetch())
    {
        $results['title'] = $title;
        $results['sub_title'] = $sub_title;
        $results['intro_text'] = $intro_text;
        $results['description'] = $description;
        $results['image_landscape'] = $image_landscape;
        $results['image_portrait'] = $image_portrait;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}

function getSelectedLocations($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT il.id, il.title, sub_title, il.image_landscape, il.lat, il.lng, il.address, content FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($id, $title, $sub_title, $image_landscape, $lat, $lng, $address, $content);
    $results = array();
    $i = 0;
    while($stmt->fetch())
    {
        $results[$i]['id'] = $id;
        $results[$i]['title'] = $title;
        $results[$i]['sub_title'] = $sub_title;
        $results[$i]['image_landscape'] = $image_landscape;
        $results[$i]['lat'] = $lat;
        $results[$i]['lng'] = $lng;
        $results[$i]['address'] = $address;
        $results[$i]['content'] = $content;
        $i++;
    }
    $stmt->close();
    $mysqli->close();
    return $results;
}

if (!empty($_GET['id']))
{
    $itineraryId = $_GET['id'];
    $itineraryDetail = getItineraryDetail($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $selectedLocations = getSelectedLocations($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>Itinerary - <?=$itineraryDetail['title']?></title>
    <link rel="stylesheet" href="css/idangerous.swiper.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/swiper.css" />
    <script src="js/vendor/modernizr.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcRjvvKaoJuT_-v4op_kWwsV5rwQEIRG8&sensor=true"></script>
</head>
<body>
<div id="mapPreload" class="overlay loading white"></div>
<div class="get-position"><a href="#"></a></div>
<div id="swiperHolder" class="swiper-holder">
    <div id="swiperContainer" class="swiper-container">
        <div id="swiperWrapper" class="swiper-wrapper">
            <?php
            foreach ($selectedLocations as $selectedLocation)
            {
            ?>
            <div class="swiper-slide white-slide">
                <a class="toggle-up-down slide-up"></a>
                <div class="title">
                    <h2><?=$selectedLocation['title']?></h2>
                    <span><?=$selectedLocation['sub_title']?></span>
                </div>
                <div class="img-thmb">
                    <img src="img/itineraries/locations/landscape/med/<?=$selectedLocation['image_landscape']?>" alt="" />
                </div>
                <div class="contentHolder">
                    <?=stripcslashes($selectedLocation['content'])?>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<div class="paginationHolder">
    <div class="pagination"></div>
</div>

<div class="map-large" id="mapCanvas">

</div>



<script src="js/vendor/jquery.js"></script>
<script src="js/vendor/swiper/idangerous.swiper-2.1.min.js"></script>
<script>

 $(function(){

    <?php
    $i = 0;
    echo 'var latLngArray = [';
    foreach ($selectedLocations as $selectedLocation)
    {
        $i++;
        echo '{';
        echo 'lat: '.$selectedLocation['lat'].',';
        echo 'lng: '.$selectedLocation['lng'].',';
        echo 'address: "'.$selectedLocation['address'].'"';
        echo '}';
        if ($i < count($selectedLocations))
        {
           echo ',';
        }
    }
    echo '];';
    ?>

    var activeIndex = 0;
    var activeLat = latLngArray[activeIndex].lat;
    var activeLng = latLngArray[activeIndex].lng;
    var activeLatLng = new google.maps.LatLng(activeLat,activeLng);
    var activeSlide = $('.swiper-slide').eq(activeIndex);
    var slideMode = 'down';

    var mySwiper = new Swiper('.swiper-container',
    {
        pagination: '.pagination',
        paginationClickable: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        resizeReInit: true,
        onSlideChangeEnd: function(swiper)
        {
            activeIndex = swiper.activeIndex;
            activeSlide = $('.swiper-slide').eq(activeIndex);
            activeLat = latLngArray[activeIndex].lat;
            activeLng = latLngArray[activeIndex].lng;
            activeLatLng = new google.maps.LatLng(activeLat,activeLng);
            markerArray[swiper.previousIndex].setAnimation(null);
            markerArray[swiper.previousIndex].setIcon('img/marker-orange-hollow.png');
            markerArray[activeIndex].setIcon('img/marker-orange.png');
            markerArray[activeIndex].setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(function() {
             markerArray[activeIndex].setAnimation(null);
        }, 750);
            if (slideMode == 'up')
            {
                mapRecenterTop(activeLatLng);
            }
            else
            {
                map.panTo(activeLatLng);
            }
        }
    });



    $('.get-position a').click(function(e) {
        e.preventDefault();
        if ($(this).hasClass('active') && markerInBounds(userLocationMarker))
        {
            $(this).removeClass('active');
            if (slideMode == 'up')
            {
                mapRecenterTop(activeLatLng);
            }
            else
            {
                map.panTo(activeLatLng);
            }
            if (!$.isEmptyObject(userLocationMarker))
            {
                //remove user location marker and accuracy circle - empty out objects
                userLocationMarker.setMap(null);
            }
        }
        else
        {
             $(this).addClass('active');
             getUserLocation($(this));
        }
    });

    $('body').on('click', '.toggle-up-down', function() {
        if ($(this).hasClass('slide-up'))
        {
            $('.toggle-up-down').removeClass('slide-up').addClass('slide-down');
            $('#swiperHolder').removeClass('holder-shrink').addClass('holder-grow');
            slideMode = 'up';
            mapRecenterTop(activeLatLng);
        } else if ($(this).hasClass('slide-down'))
        {
            $('.toggle-up-down').removeClass('slide-down').addClass('slide-up');
            $('#swiperHolder').removeClass('holder-grow').addClass('holder-shrink');
            slideMode = 'down';
            map.setCenter(activeLatLng);
        }
    });


    var browserGeoLocationSupport = false;
    var userLocationMarker = {};
    var userLocationAccuracyCircle = {};

    if(navigator.geolocation) {
        browserGeoLocationSupport = true;
    }

    var map;
    var markerArray = [];
    var directionsDisplay;
    var directionsService = new google.maps.DirectionsService();

    initialize();

    function initialize() {
        directionsDisplay = new google.maps.DirectionsRenderer({
            polylineOptions: {
                strokeColor: '#6eb240',
                strokeOpacity: 1.0,
                strokeWeight: 3
            },
            suppressMarkers: true,
            preserveViewport: true
        });
        mapOptions = {
            center: new google.maps.LatLng(latLngArray[0].lat,latLngArray[0].lng),
            zoom: 15,
            zoomControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                position: google.maps.ControlPosition.TOP_CENTER
            }
        };

        map = new google.maps.Map(document.getElementById("mapCanvas"),mapOptions);
        directionsDisplay.setMap(map);

        var styles = [
            {
                "featureType": "transit.line",
                "elementType": "geometry.fill",
                "stylers": [
                    {"visibility": "off" }
                ]
            },
            {
                "featureType": "transit.line",
                "elementType": "labels",
                "stylers": [
                    {"visibility": "off" }
                ]
            },
            {
                "featureType":"water",
                "stylers": [
                    {"visibility":"on"},
                    {"color":"#acbcc9"}
                ]
            },
            {
                "featureType":"landscape",
                "stylers":[
                    {"color":"#f2e5d4"}
                ]
            },
            {
                "featureType":"road.highway",
                "elementType":"geometry",
                "stylers": [
                    {"color":"#c5c6c6"}
                ]
            },
            {
                "featureType":"road.arterial",
                "elementType":"geometry",
                "stylers": [
                    {"color":"#e4d7c6"}
                ]
            },
            {
                "featureType":"road.local",
                "elementType":"geometry",
                "stylers": [
                    {
                        "color":"#fbfaf7"
                    }
                ]
            },
            {
                "featureType":"poi.park",
                "elementType":"geometry",
                "stylers": [
                    {
                        "visibility":"simplicity"
                    },
                    {
                        "color":"#c5dac6"
                    }
                ]
            },
            {
                "featureType":"administrative",
                "stylers": [
                    {
                        "visibility":"simplicity"
                    },
                    {
                        "lightness":33
                    }
                ]
            },
            {
                "featureType":"road"
            },
            {
                "featureType":"poi.park",
                "elementType":"labels",
                "stylers": [
                    {
                        "visibility":"off"
                    },
                    {
                        "lightness":20
                    }
                ]
            },
            {
                "featureType":"poi.business",
                "elementType":"labels",
                "stylers": [
                    {
                        "lightness":20
                    }
                ]
            },
            {

            },
            {
                "featureType":"road",
                "stylers": [
                    {
                        "lightness":20
                    }
                ]
            }
        ]


        var styledMap = new google.maps.StyledMapType(styles, {name: "Map"});

        map.mapTypes.set('map_style', styledMap);
        map.setMapTypeId('map_style');

        var i = 0;

        calcRoute();

        for (i=0; i<latLngArray.length; i++)
        {
            (function(latLngArray){
                var latLng = new google.maps.LatLng(latLngArray.lat,latLngArray.lng);
                var icon;

                if (i === 0)
                {
                    icon = 'img/marker-orange.png';
                }
                else
                {
                    icon = 'img/marker-orange-hollow.png';
                }

                var marker = new google.maps.Marker({
                    index: i,
                    position: latLng,
                    icon: icon,
                    map: map
                });

                markerArray.push(marker);

                google.maps.event.addListener(marker, 'click', function(){
                    //putting true in callback parameter allows icon change and animations to execute at emd of slide animation
                    mySwiper.swipeTo(marker.index, 300,true);
                });

            }(latLngArray[i]));
        }

        google.maps.event.addListener(map, 'tilesloaded', function(evt) {
            $('#mapPreload').hide();
        });

        function calcRoute() {
            var start, end;
            var waypoints = [];
            var endpoint = (latLngArray.length - 2);//remember our array is zero-based
            start = new google.maps.LatLng(latLngArray[0].lat,latLngArray[0].lng);
            end = new google.maps.LatLng(latLngArray[latLngArray.length - 1].lat,latLngArray[latLngArray.length - 1].lng);
            for (var i = 1; i < endpoint; i++)
            {
                waypoints.push({
                    location: new google.maps.LatLng(latLngArray[i].lat,latLngArray[i].lng),
                    stopover: true
                });
            }

            var request = {
                origin: start,
                destination: end,
                waypoints: waypoints,
                optimizeWaypoints: true,
                travelMode: google.maps.TravelMode.WALKING
            };

            directionsService.route(request, function(response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    directionsDisplay.setDirections(response);
                }
            });
        }
    }


     function calculateNewTargetPosY()
     {
         var screenHeight = $(document).height();
         var screenMidY = screenHeight / 2;
         //let's get the spot at 90% up the screen
         var screenTargetY = (90 / 100) * screenHeight;
         var posYOffset =  screenTargetY - screenMidY;
         return posYOffset;
     }

     function mapRecenter(latlng,offsetx,offsety) {
         var point1 = map.getProjection().fromLatLngToPoint(
             (latlng instanceof google.maps.LatLng) ? latlng : map.getCenter()
         );
         var point2 = new google.maps.Point(
             ( (typeof(offsetx) == 'number' ? offsetx : 0) / Math.pow(2, map.getZoom()) ) || 0,
             ( (typeof(offsety) == 'number' ? offsety : 0) / Math.pow(2, map.getZoom()) ) || 0
         );
         map.setCenter(map.getProjection().fromPointToLatLng(new google.maps.Point(
             point1.x - point2.x,
             point1.y + point2.y
         )));
     }

     function mapRecenterTop(latlng) {
         var offsetx = 0;
         var offsety = calculateNewTargetPosY();
         var point1 = map.getProjection().fromLatLngToPoint(
             (latlng instanceof google.maps.LatLng) ? latlng : map.getCenter()
         );
         var point2 = new google.maps.Point(
             ( (typeof(offsetx) == 'number' ? offsetx : 0) / Math.pow(2, map.getZoom()) ) || 0,
             ( (typeof(offsety) == 'number' ? offsety : 0) / Math.pow(2, map.getZoom()) ) || 0
         );
         map.setCenter(map.getProjection().fromPointToLatLng(new google.maps.Point(
             point1.x - point2.x,
             point1.y + point2.y
         )));
     }

    function getUserLocation(el) {
        el.addClass('loading');
        // Try W3C Geolocation (Preferred)
        if (!$.isEmptyObject(userLocationMarker))
        {
            //remove user location marker and accuracy circle - empty out objects
            userLocationMarker.setMap(null);
            userLocationAccuracyCircle.setMap(null);
            userLocationMarker = {};
            userLocationAccuracyCircle = {};
        }
        if(browserGeoLocationSupport) {
            navigator.geolocation.getCurrentPosition(function(position) {
                initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
                accuracy = position.coords.accuracy;
                userLocationMarker = new google.maps.Marker({
                    position: initialLocation,
                    map: map,
                    icon: 'img/location-dot.png'
                });
                userLocationAccuracyCircle = new google.maps.Circle({
                    center: initialLocation,
                    radius: accuracy,
                    map: map,
                    fillColor: '#0000ff',
                    fillOpacity: 0.1,
                    strokeColor: '#0000ff',
                    strokeOpacity: 0.25,
                    strokeWeight: 1
                });

                if (slideMode == 'up')
                {
                    mapRecenterTop(initialLocation);
                }
                else
                {
                    map.setCenter(initialLocation);
                }
                //map.setCenter(initialLocation);
                //map.fitBounds(userLocationAccuracyCircle.getBounds());
                el.removeClass('loading');
            }, function() {
                el.removeClass('loading');
                handleNoGeolocation(browserGeoLocationSupport);
            });
        }
        // Browser doesn't support Geolocation
        else {
            handleNoGeolocation(browserGeoLocationSupport);
            el.removeClass('loading');
        }
    }

    function handleNoGeolocation(errorFlag) {
        if (errorFlag == true) {
            el.removeClass('loading');
            alert("Geolocation service failed.");
            initialLocation = sydney;
        } else {
            el.removeClass('loading');
            alert("Your browser doesn't support geolocation. We've placed you in Sydney.");
            initialLocation = sydney;
        }
        map.setCenter(initialLocation);
    }

    function markerInBounds(marker){
        return map.getBounds().contains(marker.getPosition());
    }


 });
</script>
</body>
</html>
<?php
}
?>