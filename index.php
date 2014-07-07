<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Itinerary POC</title>
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

                    <div class="swiper-slide white-slide" style="width: 250px;" data-lat="-33.860338" data-lng="151.208427">
                        <a class="toggle-up-down slide-up"></a>
                        <div class="title">
                            <h2>Fortune of War</h2>
                            <span>Circular Quay</span>
                        </div>
                        <div class="img-thmb">
                            <img src="img/locations/thmb/fortune-of-war.jpg" width="230" height="120" alt="Fortune of War Pub - The Rocks" />
                        </div>
                    </div>
                    <div class="swiper-slide white-slide" style="width: 250px;" data-lat="-33.858778" data-lng="151.207294">
                        <a class="toggle-up-down slide-up"></a>
                        <div class="title">
                            <h2>The Glenmore Hotel</h2>
                        </div>
                        <div class="img-thmb">
                            <img src="img/locations/thmb/the-glenmore-hotel.jpg" width="230" height="120" alt="The Glenmore Hotel - The Rocks" />
                        </div>
                    </div>
                    <div class="swiper-slide white-slide" style="width: 250px;" data-lat="-33.855894" data-lng="151.180952">
                        <a class="toggle-up-down slide-up"></a>
                        <div class="title">
                            <h2>The Royal Oak</h2>
                        </div>
                        <div class="img-thmb">
                            <img src="img/locations/thmb/the-royal-oak.jpg" width="230" height="120" alt="The Royal Oak - Balmain" />
                        </div>
                    </div>
                    <div class="swiper-slide white-slide" style="width: 250px;" data-lat="-33.853448" data-lng="151.181822">
                        <a class="toggle-up-down slide-up"></a>
                        <div class="title">
                            <h2>Sir William Wallace Hotel</h2>
                        </div>
                        <div class="img-thmb">
                            <img src="img/locations/thmb/sir-william-wallace-hotel.jpg" width="230" height="120" alt="Sir William Wallce Hotel - Balmain" />
                        </div>
                    </div>
                    <div class="swiper-slide white-slide" style="width: 250px;" data-lat="-33.857745" data-lng="151.192529">
                        <a class="toggle-up-down slide-up"></a>
                        <div class="title">
                            <h2>The East Village Hotel</h2>
                        </div>
                        <div class="img-thmb">
                            <img src="img/locations/thmb/the-east-village-hotel.jpg" width="230" height="120" alt="The East Village Hotel - East Balmain" />
                        </div>
                    </div>
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

  </body>
</html>
