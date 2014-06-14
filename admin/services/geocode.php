<?php
include '../includes/admin-settings.php';
include '../includes/class.google-geocoder.php';
include '../includes/global-admin-functions.php';
assessLogin($securityArrAuthor);

if (!empty($_POST['address']))
{
    $address = $_POST['address'];

    $geoCoder = new GoogleMapsGeocoder($address);
    $response = $geoCoder->geocode();

    echo json_encode($response);
}
?>