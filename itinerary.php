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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Itinerary - <?=$itineraryDetail['title']?></title>
    <link rel="stylesheet" href="css/idangerous.swiper.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/swiper.css" />
    <script src="js/vendor/modernizr.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcRjvvKaoJuT_-v4op_kWwsV5rwQEIRG8&sensor=true"></script>
</head>
<body>

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

    function changeSlideDimensions()
    {
        var swiperCount = $('.swiper-slide').length;
        var screenWidth = $('body').outerWidth();
        var newSlideWidth = ((78.125 / 100) * screenWidth);
        var newWrapperWidth = newSlideWidth*swiperCount;
        var newWrapperPadding_x = (screenWidth - newSlideWidth);
        var toggleBtnWidth = $('.toggle-up-down').outerWidth();

        console.log('screenWidth['+screenWidth+']');
        console.log('newSlideWidth['+newSlideWidth+']');
        console.log('newWrapperWidth['+newWrapperWidth+']');

        $('.swiper-slide').css({
            'width': newSlideWidth
        })
        $('#swiperWrapper').css({
            'width': newWrapperWidth,
            'padding-left': newWrapperPadding_x/2,
            'padding-right': newWrapperPadding_x/2
        });
        $('.toggle-up-down').css({
            'left': (newSlideWidth/2) - (toggleBtnWidth/2)
        })
    }

 $(function(){
    $(window).resize(function() {
        changeSlideDimensions();
    });
    $(window).load(function() {
        mySwiper.reInit();
        //changeSlideDimensions();
    });

    var mySwiper = new Swiper('.swiper-container',{
        pagination: '.pagination',
        paginationClickable: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        resizeReInit: true,
        onSlideChangeEnd: function(swiper) {
            var activeSlide = $('.swiper-slide').eq(swiper.activeIndex);
            var activeLat = latLngArray[swiper.activeIndex].lat;
            var activeLng = latLngArray[swiper.activeIndex].lng;
            /*var activeLat = activeSlide.attr('data-lat');
            var activeLng =  activeSlide.attr('data-lng');*/
            var activeLatLng = new google.maps.LatLng(activeLat,activeLng);
            markerArray[swiper.previousIndex].setAnimation(null);
            markerArray[swiper.previousIndex].setIcon('img/marker-red-hollow.png')
            markerArray[swiper.activeIndex].setIcon('img/marker-red.png');
            markerArray[swiper.activeIndex].setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(function() {
                markerArray[swiper.activeIndex].setAnimation(null);
            }, 750);
            map.panTo(activeLatLng);
            map.setZoom(15);
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
    });
 });
</script>
<script>



    var map;
    var marker;
    var markerArray = [];

    <?php
    $i = 0;
    echo 'var latLngArray = [';
    foreach ($selectedLocations as $selectedLocation)
    {
    $i++;
        echo '{';
        echo 'lat: '.$selectedLocation['lat'].',';
        echo 'lng: '.$selectedLocation['lng'];
        echo '}';
        if ($i < count($selectedLocations))
        {
            echo ',';
        }
    }
    echo '];';
    ?>

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
                    icon = 'img/marker-red.png';
                }
                else
                {
                    icon = 'img/marker-red-hollow.png';
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
</body>
</html>
<?php
}
?>