<?php
//include '../includes/db.php';


function getItineraryDetail($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT title, page_name, sub_title, intro_text, description, image_landscape, image_portrait, date_created, date_last_modified, json_filename FROM itinerary WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($title, $page_name, $sub_title, $intro_text, $description, $image_landscape, $image_portrait, $date_created, $date_last_modified, $json_filename);

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

        $results['date_created'] = $date_created;
        $results['date_last_modified'] = $date_last_modified;
        $results['json_filename'] = $json_filename;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}

function getSelectedLocations($id,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT il.id, il.title, sub_title, il.image_landscape, il.lat, il.lng, content FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($id, $title, $sub_title, $image_landscape, $lat, $lng, $content);
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
        $results[$i]['content'] = $content;
        $i++;
    }
    $stmt->close();
    $mysqli->close();
    return $results;
}



if (isset($itineraryId))
{
    $itineraryDetail = getItineraryDetail($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $selectedLocations = getSelectedLocations($itineraryId,$DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

    $filename = $itineraryDetail['page_name'].'.html';

    $slideHTML = '';
    foreach ($selectedLocations as $selectedLocation)
    {
        $slideHTML .= '<div class="swiper-slide white-slide" style="width: 250px;">';
        $slideHTML .= '<a class="toggle-up-down slide-up"></a>';
        $slideHTML .= '<div class="title">';
        $slideHTML .= '<h2>'.$selectedLocation['title'].'</h2>';
        $slideHTML .= '<span>'.$selectedLocation['sub_title'].'</span>';
        $slideHTML .= '</div>';
        $slideHTML .= '<div class="img-thmb">';
        $slideHTML .= '<img src="'.$baseURL.'img/itineraries/locations/landscape/sml/'.$selectedLocation['image_landscape'].'" alt="" />';
        $slideHTML .= '</div>';
        $slideHTML .= '<div class="contentHolder">';
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Itinerary - {$itineraryDetail['title']}</title>
        <link rel="stylesheet" href="{$baseURL}css/idangerous.swiper.css" />
        <link rel="stylesheet" href="{$baseURL}css/style.css" />
        <link rel="stylesheet" href="{$baseURL}css/swiper.css" />
        <script src="{$baseURL}js/vendor/modernizr.js"></script>
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
<script src="{$baseURL}js/vendor/jquery.js"></script>
<script src="{$baseURL}js/vendor/swiper/idangerous.swiper-2.1.min.js"></script>
<script>

    function changeSlideDimensions()
    {
        var swiperCount = $('.swiper-slide').length;
        var screenWidth = $('body').outerWidth();
        var newSlideWidth = ((71.875 / 100) * screenWidth);
        var newWrapperWidth = newSlideWidth*swiperCount;

        var toggleBtnWidth = $('.toggle-up-down').outerWidth();

        console.log('screenWidth['+screenWidth+']');
        console.log('newSlideWidth['+newSlideWidth+']');
        console.log('newWrapperWidth['+newWrapperWidth+']');

        $('.swiper-slide').css({
            'width': newSlideWidth
        });
        $('#swiperWrapper').css({
            'width': newWrapperWidth
        });
        $('.toggle-up-down').css({
            'left': (newSlideWidth/2) - (toggleBtnWidth/2)
        });
    }

    $(window).resize(function() {
        changeSlideDimensions();
    });

    var mySwiper = new Swiper('.swiper-container',{
        pagination: '.pagination',
        paginationClickable: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        onSlideChangeEnd: function(swiper) {
            var activeSlide = $('.swiper-slide').eq(swiper.activeIndex);
            var activeLat = latLngArray[swiper.activeIndex].lat;
            var activeLng = latLngArray[swiper.activeIndex].lng;
            var activeLatLng = new google.maps.LatLng(activeLat,activeLng);
            markerArray[swiper.previousIndex].setAnimation(null);
            markerArray[swiper.previousIndex].setIcon('{$baseURL}img/marker-red-hollow.png')
            markerArray[swiper.activeIndex].setIcon('{$baseURL}img/marker-red.png');
            markerArray[swiper.activeIndex].setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(function() {
                markerArray[swiper.activeIndex].setAnimation(null);
            }, 750);
            map.panTo(activeLatLng);
            map.setZoom(15);
        },
        onFirstInit: function() {
            changeSlideDimensions();
        }
    });


    $(function() {
        $('body').on('click', '.toggle-up-down', function() {
            if ($(this).hasClass('slide-up'))
            {
                $('.toggle-up-down').removeClass('slide-up').addClass('slide-down');
                $('#swiperHolder').removeClass('holder-shrink').addClass('holder-grow');
            }else if ($(this).hasClass('slide-down')) {
                $('.toggle-up-down').removeClass('slide-down').addClass('slide-up');
                $('#swiperHolder').removeClass('holder-grow').addClass('holder-shrink');
            }
        });
    })
</script>
<script>



    var map;
    var marker;
    var markerArray = [];

    {$scriptString}

    initialize();

    function initialize() {
        //console.log('here');
        mapOptions = {
            center: new google.maps.LatLng(-33.860338,151.208427),
            zoom: 15,
            zoomControl: false,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                position: google.maps.ControlPosition.TOP_CENTER
            }
        };

        map = new google.maps.Map(document.getElementById("mapCanvas"),mapOptions);

        for (var i=0; i<latLngArray.length; i++)
        {
            (function(latLngArray){
                var latLng = new google.maps.LatLng(latLngArray.lat,latLngArray.lng);
                var icon;
                if (i === 0)
                {
                    icon = '{$baseURL}img/marker-red.png';
                }
                else
                {
                    icon = '{$baseURL}img/marker-red-hollow.png';
                }

                marker = new google.maps.Marker({
                    position: latLng,
                    icon: icon,
                    map: map
                });
                markerArray.push(marker);
            }(latLngArray[i]));
        }
    }


</script>
EOF;

$html = <<< EOF
<!doctype html>
<html class="no-js" lang="en">
{$head}
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