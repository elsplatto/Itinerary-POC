<?php
include 'includes/global-admin-functions.php';
include 'includes/admin-settings.php';
include 'includes/class.itinerary.php';
assessLogin($securityArrAuthor);

$error_msg = '';

if (!isset($_POST['itineraryId']) || intval('0' . $_POST['itineraryId']) === 0)
{
    $itinerary = new Itinerary;
    $itinerary->get_form_values();
    $itinerary->insert_itinerary();

}
else
{
    $itineraryId = $_POST['itineraryId'];
    $itinerary = new Itinerary;
    $itinerary->get_form_values();
    $itinerary->update_itinerary($itineraryId);
}




/*if (strlen($error_msg) > 0)
{
    if ($itineraryId > 0)
    {
        header('Location: itineraries-edit.php?id='.$itineraryId.'&error_msg='.$error_msg);
    }
    else
    {
        header('Location: itineraries-list.php?error_msg='.$error_msg);
    }
}
else
{
    if ($itineraryId > 0)
    {
        header('Location: itineraries-list.php?success_msg=Itinerary successfully updated');
    }
    else
    {
        header('Location: itineraries-list.php?new_id='.$new_id.'&success_msg=Itinerary successfully added');
    }
}*/
?>