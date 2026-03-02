<?php
/**
 * Template Name: iCalendar Feeds
 */
if( !isset($_GET['iCal'])){
    wp_die('Oooops.. Something went wrong');
}

$allowed_html = array();
$iCal_id = sanitize_text_field(wp_kses($_GET['iCal'], $allowed_html));
$listing_id = homey_get_listing_id_by_ical_id($iCal_id);

$iCalendar ="BEGIN:VCALENDAR\r\n";
$iCalendar.="PRODID:-//Booking Calendar//EN\r\n";
$iCalendar .= "VERSION:2.0";
$iCalendar .= homey_get_booked_dates_for_icalendar($listing_id);
$iCalendar .= homey_get_unavailable_dates_for_icalendar($listing_id);
$iCalendar .= "\r\n";
$iCalendar .= "END:VCALENDAR";

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=icalendar.ics');
print ''.$iCalendar;
exit();