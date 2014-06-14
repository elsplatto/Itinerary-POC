<?php
function assessLogin($array = null)
{
    if ($array == null)
    {
        array_push($array,'super');
    }
    if(session_id() == '' || !isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['adminUserId']))
    {
        header('Location: login-fail.php');
    }
    else
    {
        if(!in_array($_SESSION['adminRole'],$array))
        {
            header('Location: dashboard.php');
        }
    }
}
?>