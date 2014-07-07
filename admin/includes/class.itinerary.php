<?php
require_once '../includes/class.dbConnection.php';
include 'class.upload.php';

class Itinerary {
    public $itineraryId;
    public $title;
    public $page_name;
    public $sub_title;
    public $credit;
    public $intro_text;
    public $description;
    public $image_landscape;
    public $image_portrait;
    public $date_created;
    //insert only - trigger used on update
    public $date_last_modified;
    //insert only - trigger used on update
    public $previous_edit_date_stamp;
    public $is_live;
    public $json_file_prefix;
    public $filename;

    private $newId;

    private $obj_landscape_img;
    private $landscape_img_dir;

    private $obj_portrait_img;
    private $portrait_img_dir;

    private $obj_locations;

    public $baseURL;
    public $landscape_path;
    public $portrait_path;
    public $location_landscape_path;
    public $location_portrait_path_lge;
    public $location_portrait_path_med;
    public $location_portrait_path_sml;

    private $img_list;

    private $slide_html;
    private $slide_js;

    function __construct()
    {
        $this->itineraryId = 0;
        $this->title = '';
        $this->page_name = '';
        $this->sub_title = '';
        $this->credit = '';
        $this->intro_text = '';
        $this->description = '';
        $this->image_landscape = '';
        $this->image_portrait = '';
        $this->date_created = time();
        $this->date_last_modified = time();
        $this->previous_edit_date_stamp = $this->date_last_modified;
        $this->is_live = 0;
        $this->json_file_prefix = 'itinerary-';
        $this->filename = '';
        $this->newId = 0;
        $this->obj_landscape_img = null;
        $this->landscape_img_dir = '../img/itineraries/landscape/';
        $this->obj_portrait_img = null;
        $this->portrait_img_dir = '../img/itineraries/portrait/';
        $this->obj_locations = null;

        $this->baseURL = 'http://localhost/~jasontaikato/itinerary/';
        $this->landscape_path = 'img/itineraries/landscape/';
        $this->portrait_path = 'img/itineraries/portrait/';
        $this->location_landscape_path = 'img/itineraries/locations/landscape/';
        $this->location_landscape_path_lge = 'img/itineraries/locations/landscape/lge/';
        $this->location_landscape_path_med = 'img/itineraries/locations/landscape/med/';
        $this->location_landscape_path_sml = 'img/itineraries/locations/landscape/sml/';

        $this->slide_html = '';
        $this->slide_js = '';

        $this->img_list = array();

    }

    public function get_form_values()
    {
        if (isset($_POST['itineraryId']))
        {
            $this->itineraryId = $_POST['itineraryId'];
        }
        $this->title = $_POST['txtTitle'];
        $this->page_name = $_POST['txtPageName'];
        $this->sub_title = $_POST['txtSubTitle'];
        $this->credit = $_POST['txtCredit'];
        $this->intro_text = $_POST['txtIntroText'];
        $this->description = $_POST['txtDescription'];
        $this->image_landscape = $_POST['txtImgLandscape'];
        $this->image_portrait = $_POST['txtImgPortrait'];
        if (isset($_POST['chkIsLive']))
        {
            $this->is_live = $_POST['chkIsLive'];
        }
        if (isset($_FILES['landscapeImgUpload']))
        {
            $this->obj_landscape_img = new Upload($_FILES['landscapeImgUpload']);
            $this->uploadLandscapeImg();
        }

        if (isset($_FILES['portraitImgUpload']))
        {
            $this->obj_portrait_img = new Upload($_FILES['portraitImgUpload']);
            $this->uploadPortraitImg();
        }
    }

    public function insert_itinerary()
    {
        $dbConnection = new DatabaseConnection();
        //prepare query
        $query = 'INSERT INTO itinerary (title, page_name, sub_title, credit, intro_text, description, image_landscape, image_portrait, date_created, date_last_modified, previous_edit_date_stamp, is_live) ';
        $query .= 'VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
        //prepare type string
        $types = 'ssssssssiiii';
        //prepare bind vars
        $params = array(
            $this->title,
            $this->page_name,
            $dbConnection->escapeString($this->sub_title),
            $this->credit,
            $dbConnection->escapeString($this->intro_text),
            $dbConnection->escapeString($this->description),
            $this->image_landscape,
            $this->image_portrait,
            $this->date_created,
            $this->date_last_modified,
            $this->previous_edit_date_stamp,
            $this->is_live
        );
        $this->newId = $dbConnection->insertRecord($query, $types, $params, true);
    }

    public function update_itinerary($id)
    {
        $dbConnection = new DatabaseConnection();
        $query = 'UPDATE itinerary SET title = ?, page_name = ?, sub_title = ?, credit = ?, intro_text = ?, description = ?, image_landscape = ?, image_portrait = ?, date_last_modified = ?, is_live = ? WHERE id = ?';
        $types = 'ssssssssiii';
        $params = array(
            $this->title,
            $this->page_name,
            $dbConnection->escapeString($this->sub_title),
            $this->credit,
            $dbConnection->escapeString($this->intro_text),
            $dbConnection->escapeString($this->description),
            $this->image_landscape,
            $this->image_portrait,
            $this->date_last_modified,
            $this->is_live,
            $id
        );
        $dbConnection->updateRecord($query, $types, $params);
        unset($dbConnection);
        if ($this->obj_locations === null)
        {
            $this->obj_locations = $this->getLocations($id);
        }

        if ($this->obj_locations !== null)
        {
            $this->buildJSONFile();
            $this->buildManifestFile();
            $this->buildHTMLFile();
        }
    }

    public function get_itinerary_details($id)
    {
        $dbConnection = new DatabaseConnection();
        $query = 'SELECT ';
        $query .= 'title, ';
        $query .= 'page_name, ';
        $query .= 'sub_title, ';
        $query .= 'intro_text, ';
        $query .= 'credit, ';
        $query .= 'description, ';
        $query .= 'image_landscape, ';
        $query .= 'image_portrait, ';
        $query .= 'date_created, ';
        $query .= 'date_last_modified, ';
        $query .= 'previous_edit_date_stamp, ';
        $query .= 'is_live ';
        $query .= 'FROM itinerary WHERE id = ?';
        $types = 'i';
        $params = array(intval($id));
        $results = array(
            $title = '',
            $page_name = '',
            $sub_title = '',
            $intro_text = '',
            $credit = '',
            $description = '',
            $image_landscape = '',
            $image_portrait = '',
            $date_created = 0,
            $date_last_modified = 0,
            $previous_edit_date_stamp = 0,
            $is_live = 0
        );
        $records = $dbConnection->getRecords($query, $types, $params, $results);

        return $records->fetch_array(MYSQLI_ASSOC);//returns associative array - use MYSQLI_NUM to return enumerated array
    }

    private function getLocations($id)
    {
        $dbConnection = new DatabaseConnection();
        $query = 'SELECT il.id, il.title, il.image_landscape, il.image_portrait, il.content, il.lat, il.lng, il.tags FROM itinerary_locations il JOIN itinerary_itinerary_location iil ON iil.itinerary_location_id = il.id WHERE iil.itinerary_id = ? ORDER BY iil.sequence';
        $types = 'i';
        $params = array(intval($id));
        $results = array($locationId = 0, $title = '', $image_landscape = '', $image_portrait = '', $content = '', $lat = 0, $lng = 0, $tags = '');
        $records = $dbConnection->getRecords($query, $types, $params, $results);
        unset($dbConnection);
        return $records;
    }

    private function uploadLandscapeImg()
    {
        if ($this->obj_landscape_img->uploaded)
        {
            // uploaded - process and save to correct directory
            $this->obj_landscape_img->image_resize = false;
            $this->obj_landscape_img->process($this->landscape_img_dir);

            if ($this->obj_landscape_img->processed)
            {
                $this->obj_landscape_img->clean();
            }
            else
            {
                /*if (strlen($error_msg) > 0)
                {
                    $error_msg += ',';
                }
                $error_msg += $landscapeImg->error;*/
                echo($this->obj_landscape_img->error);
                echo('<br />');

            }
            $this->image_landscape = $this->obj_landscape_img->file_dst_name;
        }
    }

    private function uploadPortraitImg()
    {
        if ($this->obj_portrait_img->uploaded)
        {
            // uploaded - process and save to correct directory
            $this->obj_portrait_img->image_resize = false;
            $this->obj_portrait_img->process($this->portrait_img_dir);

            if ($this->obj_portrait_img->processed)
            {
                $this->obj_portrait_img->clean();
            }
            else
            {
                /*if (strlen($error_msg) > 0)
                {
                    $error_msg += ',';
                }
                $error_msg += $portraitImg->error;*/
                echo($this->obj_portrait_img->error);
                echo('<br />');

            }
            $this->image_portrait = $this->obj_portrait_img->file_dst_name;
        }
    }

    private function getPreviousEditDateStamp($id)
    {
        $dbConnection = new DatabaseConnection();
        $query = 'SELECT previous_edit_date_stamp FROM itinerary WHERE id = ?';
        $types = 'i';
        $params = array(intval($id));
        $results = array($previous_edit_date_stamp = 0);
        $records = $dbConnection->getRecords($query, $types, $params, $results);
        unset($dbConnection);
        return $records->fetch_array(MYSQLI_ASSOC);
    }

    private function buildManifestFile()
    {
        if ($this->previous_edit_date_stamp === $this->date_last_modified)
        {
            $previous_date_stamp = $this->getPreviousEditDateStamp($this->itineraryId);
            $this->previous_edit_date_stamp = $previous_date_stamp['previous_edit_date_stamp'];
        }
        $manifest_filename = 'itinerary-' . $this->itineraryId . '-' . $this->date_last_modified . '.manifest';
        $previous_manifest_= 'itinerary-' . $this->itineraryId . '-' . $this->previous_edit_date_stamp . '.manifest';
        $latestDate = $this->date_last_modified;
        $manifestCacheBreakString = date("Y-m-d H:i:s", $latestDate);

        $html_filename = $this->page_name . '.html';

        $manifestImgs = '';

        for ($i = 0; $i < count($this->img_list); $i++)
        {
            $manifestImgs .= $this->img_list[$i] ."\n";
        }
        echo($manifestImgs);

$manifest = <<< EOF
CACHE MANIFEST
# {$manifestCacheBreakString}
#static files
CACHE:
{$this->baseURL}itinerary/{$html_filename}
{$this->baseURL}css/idangerous.swiper.css
{$this->baseURL}css/swiper.css
{$this->baseURL}css/style.css
{$this->baseURL}css/img/sprite.png
{$this->baseURL}css/img/preloader-2.gif
{$this->baseURL}css/img/preloader-2-sml.gif
{$this->baseURL}js/vendor/jquery.js
{$this->baseURL}js/vendor/modernizr.js
{$this->baseURL}js/vendor/swiper/idangerous.swiper-2.1.min.js
{$this->baseURL}img/marker-orange-hollow.png
{$this->baseURL}img/marker-orange.png
{$this->baseURL}img/location-dot.png
#dynamic files
{$manifestImgs}

NETWORK:
*

EOF;
        if (file_exists('../manifests/'.$previous_manifest_))
        {
            unlink('../manifests/'.$previous_manifest_);
        }

        $manifestFile = fopen('../manifests/'.$manifest_filename,'w');
        fwrite($manifestFile, $manifest);
        fclose($manifestFile);
    }

    private function buildJSONFile()
    {
        if ($this->previous_edit_date_stamp === $this->date_last_modified)
        {
            $previous_date_stamp = $this->getPreviousEditDateStamp($this->itineraryId);
            $this->previous_edit_date_stamp = $previous_date_stamp['previous_edit_date_stamp'];
        }
        $json_filename = 'itinerary-' . $this->itineraryId . '-' . $this->date_last_modified . '.json';
        $previous_json_filename = 'itinerary-' . $this->itineraryId . '-' . $this->previous_edit_date_stamp . '.json';
        $json = '';
        $json .= '{"itinerary": [';

        $results = array();
        $results[0]['title'] = $this->title;
        $results[0]['page_name'] = $this->page_name;
        $results[0]['sub_title'] = $this->sub_title;
        $results[0]['intro_text'] = $this->intro_text;
        $results[0]['description'] = $this->description;
        $results[0]['image_landscape'] = $this->baseURL . $this->landscape_path . $this->image_landscape;
        $results[0]['image_portrait'] = $this->baseURL . $this->portrait_path . $this->image_portrait;
        $results[0]['date_created'] = $this->date_created;
        $results[0]['date_last_modified'] = $this->date_last_modified;
        $results[0]['json_filename'] = $json_filename;
        $results[0]['last_json_filename'] = $previous_json_filename;

        array_push($this->img_list, $this->baseURL . $this->landscape_path . $this->image_landscape);
        array_push($this->img_list, $this->baseURL . $this->portrait_path . $this->image_portrait);

        foreach ($results as $result)
        {
            $json .= json_encode($result);
        }

        $rowCount = $this->obj_locations->num_rows;
        $i = 0;
        $json .= ', "locations": [';

        $this->slide_js .= 'var latLngArray = [';

        while($row = $this->obj_locations->fetch_array(MYSQLI_ASSOC))
        {
            $json .= '{';
            $json .= '"id":' . $row['id'] . ',';
            $json .= '"title":"' . $row['title'] . '",';
            $json .= '"images [{';
            $json .= '"large:"' . json_encode($this->baseURL . $this->location_landscape_path_lge . $row['image_landscape']) . '",';
            $json .= '"medium:"' . json_encode($this->baseURL . $this->location_landscape_path_med . $row['image_landscape']) . '",';
            $json .= '"small:"' . json_encode($this->baseURL . $this->location_landscape_path_sml . $row['image_landscape']) . '"';
            $json .= '}],';
            $json .= '"content:"'.json_encode($row['content']).'",';
            $json .= '"lat":' . $row['lat'] . ',';
            $json .= '"lng":' . $row['lng'] . ',';
            $json .= '"tags":"' . $row['tags'] . '"';
            $json .= '}';

            array_push($this->img_list, $this->baseURL . $this->location_landscape_path_med . $row['image_landscape']);

            $this->slide_html .= '<div class="swiper-slide white-slide">';
            $this->slide_html .= '<div class="title">';
            $this->slide_html .= '<h2>'.$row['title'].'</h2>';
            $this->slide_html .= '</div>';
            $this->slide_html .= '<div class="contentHolder">';
            $this->slide_html .= '<img src="'.$this->baseURL .'img/itineraries/locations/landscape/med/'.$row['image_landscape'].'" alt="" />';
            $this->slide_html .= stripcslashes($row['content']);
            $this->slide_html .= '</div>';
            $this->slide_html .= '</div>';

            $this->slide_js .= '{';
            $this->slide_js .= 'lat: '.$row['lat'].',';
            $this->slide_js .= 'lng: '.$row['lng'];
            $this->slide_js .= '}';

            $i++;
            if ($i < $rowCount)
            {
                $json .= ',';
                $this->slide_js .= ',';
            }
        }

        $this->slide_js .= '];';

        $json .= ']';
        $json .= ']}';

        if (file_exists('../json/'.$previous_json_filename) && !is_null($previous_json_filename))
        {
            unlink('../json/'.$previous_json_filename);
        }

        $file = fopen('../json/'.$json_filename,'w');
        fwrite($file, $json);
        fclose($file);
    }

    private function buildHTMLFile()
    {
        $manifest_filename = 'itinerary-' . $this->itineraryId . '-' . $this->date_last_modified . '.manifest';


$head = <<< EOF
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>Itinerary - {$this->title}</title>
    <link rel="stylesheet" href="{$this->baseURL}css/idangerous.swiper.css" />
    <link rel="stylesheet" href="{$this->baseURL}css/style.css" />
    <link rel="stylesheet" href="{$this->baseURL}css/swiper.css" />
    <script src="{$this->baseURL}js/vendor/modernizr.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcRjvvKaoJuT_-v4op_kWwsV5rwQEIRG8&sensor=true"></script>
</head>
EOF;

$swiper = <<< EOF
<div id="swiperHolder" class="swiper-holder">
    <div id="swiperContainer" class="swiper-container">
        <div id="swiperWrapper" class="swiper-wrapper">
        {$this->slide_html}
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
<script src="{$this->baseURL}js/vendor/jquery.js"></script>
<script src="{$this->baseURL}js/vendor/swiper/idangerous.swiper-2.1.min.js"></script>
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

{$this->slide_js}

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
        markerArray[swiper.previousIndex].setIcon('{$this->baseURL}img/marker-orange-hollow.png');
        markerArray[activeIndex].setIcon('{$this->baseURL}img/marker-orange.png');
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
                icon = '{$this->baseURL}img/marker-orange.png';
            }
            else
            {
                icon = '{$this->baseURL}img/marker-orange-hollow.png';
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

    fitBoundsToMarkers();

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
                icon: '{$this->baseURL}img/location-dot.png'
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

function fitBoundsToMarkers() {
    var bounds = new google.maps.LatLngBounds();
    for (var i=0;i<markerArray.length;i++)
    {
        bounds.extend( markerArray[i].getPosition() );
    }

    map.fitBounds(bounds);
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
<html manifest="{$this->baseURL}manifests/{$manifest_filename}" class="no-js" lang="en">
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


        $file = fopen('../itinerary/'.$this->page_name.'.html' ,'w');
        fwrite($file, $html);
        fclose($file);


    }
}
?>