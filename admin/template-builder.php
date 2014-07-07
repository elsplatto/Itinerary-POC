<?php
//include '../includes/db.php';

$filePrePath = '../';


if (isset($itineraryId))
{
    $itineraryDetail = getItineraryDetail($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $selectedLocations = getSelectedLocations($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

    $filename = $itineraryDetail['page_name'].'.html';



    if (is_null($itineraryDetail['date_last_modified']) || empty($itineraryDetail['date_last_modified']))
    {
        $latestDate = $itineraryDetail['date_created'];
    }
    else
    {
        $latestDate = $itineraryDetail['date_last_modified'];
    }

    $manifestFilename = 'itinerary-'.$itineraryId.'-'.$latestDate.'.manifest';


    $slideHTML = '';
    foreach ($selectedLocations as $selectedLocation)
    {
        $slideHTML .= '<div class="swiper-slide white-slide">';
        //$slideHTML .= '<a class="toggle-up-down slide-up"></a>';
        $slideHTML .= '<div class="title">';
        $slideHTML .= '<h2>'.$selectedLocation['title'].'</h2>';
        //$slideHTML .= '<span>'.$selectedLocation['sub_title'].'</span>';
        $slideHTML .= '</div>';
        //$slideHTML .= '<div class="img-thmb">';
        //$slideHTML .= '</div>';
        $slideHTML .= '<div class="contentHolder">';
        $slideHTML .= '<img src="'.$filePrePath.'img/itineraries/locations/landscape/med/'.$selectedLocation['image_landscape'].'" alt="" />';
        $slideHTML .= stripcslashes($selectedLocation['content']);
        $slideHTML .= '</div>';
        $slideHTML .= '</div>';
    }


    $scriptString = '';
    $i = 0;
    $scriptString .= 'var latLngArray = [';
    foreach ($selectedLocations as $selectedLocation)
    {
        $i++;
        $scriptString .= '{';
        $scriptString .= 'lat: '.$selectedLocation['lat'].',';
        $scriptString .= 'lng: '.$selectedLocation['lng'];
        $scriptString .= '}';
        if ($i < count($selectedLocations))
        {
            $scriptString .= ',';
        }
    }
    $scriptString .= '];';



$head = <<< EOF
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
        <title>Itinerary - {$itineraryDetail['title']}</title>
        <link rel="stylesheet" href="{$filePrePath}css/idangerous.swiper.css" />
        <link rel="stylesheet" href="{$filePrePath}css/style.css" />
        <link rel="stylesheet" href="{$filePrePath}css/swiper.css" />
        <script src="{$filePrePath}js/vendor/modernizr.js"></script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcRjvvKaoJuT_-v4op_kWwsV5rwQEIRG8&sensor=true"></script>
    </head>
EOF;

$swiper = <<< EOF
    <div id="swiperHolder" class="swiper-holder">
        <div id="swiperContainer" class="swiper-container">
            <div id="swiperWrapper" class="swiper-wrapper">
            {$slideHTML}
            </div>
        </div>
    </div>
    <div class="paginationHolder">
        <div class="pagination"></div>
    </div>
EOF;

$mapCanvas = <<< EOF
    <div class="map-large" id="mapCanvas">
    </div>
EOF;



$jsScript = <<< EOF
<script src="{$filePrePath}js/vendor/jquery.js"></script>
<script src="{$filePrePath}js/vendor/swiper/idangerous.swiper-2.1.min.js"></script>
<script>


 $(function(){

     var dragStartTime = 0;
     var dragStartY = 0;
     var dragHolderDiff = 0;
     var originalDistanceFromBottom = 0;
     var holderHeight = $('#swiperHolder').outerHeight();
     var screenHeight = $(document).height();
     var distanceFromBottom = 0;
     var upperThreshold = (80 / 100) * screenHeight;
     var lowerThreshold = $('.title').outerHeight() + 40;
     var distanceCovered = 0;

     setContentHeight();

    $('body').on('mousedown touchstart', '.title', function(e)
    {
        e.stopPropagation();
        holderHeight = $('#swiperHolder').outerHeight();
        dragStartTime = e.timeStamp;
        dragStartY = e.originalEvent.pageY;
        distanceFromBottom = (screenHeight - dragStartY);
        dragHolderDiff = (holderHeight - distanceFromBottom);
        originalDistanceFromBottom = distanceFromBottom;

    }).on('mousemove touchmove', '.title', function(e){
        e.stopPropagation();
        e.preventDefault();
        var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
        holderHeight = $('#swiperHolder').outerHeight();
        distanceFromBottom = (screenHeight - touch.pageY);
        if (dragStartY > 0 && ((distanceFromBottom + dragHolderDiff) < upperThreshold) && ((distanceFromBottom + dragHolderDiff) > lowerThreshold))
        {
            $('#swiperHolder').css({
                height: ((screenHeight - touch.pageY) + dragHolderDiff)
            })
        }
    }).on('touchend', '.title', function(e){

        var timeSpan = (e.timeStamp - dragStartTime);
        var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
        distanceFromBottom = (screenHeight - touch.pageY);
        distanceCovered = (distanceFromBottom - originalDistanceFromBottom);
        holderHeight = $('#swiperHolder').outerHeight();

        var velocity = (timeSpan/Math.abs(distanceCovered));

        if (velocity < 5)
        {
            if ((holderHeight + distanceCovered) < lowerThreshold)
            {
                $('#swiperHolder').animate({
                    height: lowerThreshold
                }, timeSpan, function() {
                    slidesDown();
                })
            }
            else if ((holderHeight + distanceCovered) > upperThreshold)
            {
                $('#swiperHolder').animate({
                    height: upperThreshold
                }, timeSpan, function() {
                    slidesUp();
                })
            }
            else if ((holderHeight + distanceCovered) > lowerThreshold)
            {
                $('#swiperHolder').animate({
                    height: (holderHeight + distanceCovered)
                }, timeSpan, function() {
                    slidesMiddled();
                })
            }
        }
        else
        {
            if ((holderHeight - lowerThreshold) < 40)
            {
                slidesDown();
            }

            else if ((upperThreshold - holderHeight) < 40)
            {
                slidesUp();
            }
            else
            {
                slidesMiddled();
            }

        }
    });

    {$scriptString}

    var activeIndex = 0;
    var activeLat = latLngArray[activeIndex].lat;
    var activeLng = latLngArray[activeIndex].lng;
    var activeLatLng = new google.maps.LatLng(activeLat,activeLng);
    var activeSlide = $('.swiper-slide').eq(activeIndex);
    var slideMode = 'down';

    var mainSwiper = new Swiper('.swiper-container',
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
            markerArray[swiper.previousIndex].setIcon('{$filePrePath}img/marker-orange-hollow.png');
            markerArray[activeIndex].setIcon('{$filePrePath}img/marker-orange.png');
            markerArray[activeIndex].setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(function() {
             markerArray[activeIndex].setAnimation(null);
        }, 750);
            if (slideMode === 'up')
            {
                mapRecenterTop(activeLatLng);
            }
            else if (slideMode === 'middled')
            {
                mapRecenterTop(activeLatLng, calculateTargetPercent());
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
            if (slideMode === 'up')
            {
                mapRecenterTop(activeLatLng);
            }
            else if (slideMode === 'middled')
            {
                mapRecenterTop(activeLatLng,calculateTargetPercent());
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

    function slidesUp()
    {
        $('.title').removeClass('up');
        $('.title').addClass('down');
        slideMode = 'up';
        setContentHeight();
        mapRecenterTop(activeLatLng);
    }

    function slidesDown()
    {
        $('.title').removeClass('down');
        $('.title').addClass('up');
        slideMode = 'down';
        setContentHeight();
        map.setCenter(activeLatLng);
    }

     function slidesMiddled()
     {
        $('.title').removeClass('down');
        $('.title').removeClass('up');
        slideMode = 'middled';
        setContentHeight();
        var percentage = calculateTargetPercent();
        if ($('#swiperHolder').outerHeight() > (screenHeight/2))
        {
            mapRecenterTop(activeLatLng, percentage);
        }
        else
        {
           map.setCenter(activeLatLng);
        }
     }

     function calculateTargetPercent(){
        var heightPercentage = ($('#swiperHolder').outerHeight() / screenHeight) * 100;
        var remainderHalved = (100 - heightPercentage) / 2;
        var targetPercentage =  heightPercentage + remainderHalved;
        return targetPercentage;
     }

      function setContentHeight() {
         var contentHeight = ($('#swiperHolder').outerHeight() - $('.title').outerHeight()) - 30;
         $('.contentHolder').css({
             height: contentHeight
         });
     }

    $('.contentHolder').scroll(function(e) {
        //console.dir(e)
        //console.log('scroll Top: ' + $(this).scrollTop());
        var pos = $(this).scrollTop();
        var titleEl = $(this).prev('.title');

        if (pos > 0 && !titleEl.hasClass('shadowed'))
        {
            titleEl.addClass('shadowed');
        }
        else if (pos === 0 && titleEl.hasClass('shadowed'))
        {
            titleEl.removeClass('shadowed')
        }
    })

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
                    icon = '{$filePrePath}img/marker-orange.png';
                }
                else
                {
                    icon = '{$filePrePath}img/marker-orange-hollow.png';
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
                    mainSwiper.swipeTo(marker.index, 300,true);
                });

            }(latLngArray[i]));
        }

        google.maps.event.addListener(map, 'tilesloaded', function(evt) {
            $('#mapPreload').hide();
        });

        function calcRoute() {
            var start, end;
            var waypoints = [];
            var endpoint = (latLngArray.length - 1);//remember our array is zero-based
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


     function calculateNewTargetPosY(p)
     {
         var screenHeight = $(document).height();
         var screenMidY = screenHeight / 2;
         //p is percentage of screenheight
         var screenTargetY = (p / 100) * screenHeight;
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

     function mapRecenterTop(latlng, p) {

         p = typeof p !== 'undefined' ? p : 85;

         var offsetx = 0;
         var offsety = calculateNewTargetPosY(p);
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
                    icon: '{$filePrePath}img/location-dot.png'
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

                if (slideMode === 'up')
                {
                    mapRecenterTop(initialLocation);
                }
                else if (slideMode === 'middled')
                {
                    mapRecenterTop(initialLocation, calculateTargetPercent());
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
EOF;

$html = <<< EOF
<!doctype html>
<html manifest="{$filePrePath}manifests/{$manifestFilename}" class="no-js" lang="en">
{$head}
<body>
<div id="mapPreload" class="overlay loading white"></div>
<div class="get-position"><a href="#"></a></div>
{$swiper}
{$mapCanvas}
{$jsScript}
</body>
</html>
EOF;

//echo $html;

$file = fopen('../itinerary/'.$filename,'w');
fwrite($file, $html);
fclose($file);
}
?>