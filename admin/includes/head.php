<?php
if (!isset($needsMaps) || is_null($needsMaps))
{
    $needsMaps = false;
}

if (!isset($needsCalendar) || is_null($needsCalendar))
{
    $needsCalendar = false;
}
?>
<link rel="stylesheet" href="../css/foundation.css" />
<link rel="stylesheet" href="css/smoothness/jquery-ui-1.10.4.custom.css" />
<link rel="stylesheet" href="css/style.css" />
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.10.4.custom.js"></script>
<?php
if ($needsCalendar)
{
?>
<script src="js/plugins/jquery-ui-timepicker.js"></script>
<?php
}
if ($needsMaps)
{
?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcRjvvKaoJuT_-v4op_kWwsV5rwQEIRG8&sensor=true"></script>
<?php
}
?>