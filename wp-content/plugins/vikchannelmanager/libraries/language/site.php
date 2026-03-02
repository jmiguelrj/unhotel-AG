<?php
/** 
 * @package   	VikChannelManager - Libraries
 * @subpackage 	language
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikChannelManager plugin site languages.
 *
 * @since 	1.0
 */
class VikChannelManagerLanguageSite implements JLanguageHandler
{
	/**
	 * Checks if exists a translation for the given string.
	 *
	 * @param 	string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated string, otherwise null.
	 */
	public function translate($string)
	{
		$result = null;

		/**
		 * Translations go here.
		 * @tip Use 'TRANSLATORS:' comment to attach a description of the language.
		 */

		switch ($string)
		{
			case 'VCMOTANEWORDERROOMNOTAVAIL':
				$result = __("VikChannelManager\n\nAn error occurred while processing a new reservation received from the Channel %s.\nThe booking id %s was made for the room id %s that corresponds to the room -%s- of VikBooking. The room is not available from the %s to the %s.\nPlease verify the reservation manually from your Extranet Account on the Channel and check all the reservations saved in VikBooking for those dates.\nThis booking will be automatically saved and confirmed by the Channel even if you do not do anything. This is just an alert message to inform you about the availability situation on your site.\n\nIf you have already received a notification message for this booking id, please ignore this message.", 'vikchannelmanager');
				break;
			case 'VCMOTANEWORDERROOMNOTAVAILSUBJ':
				$result = __('VikChannelManager Error Notification', 'vikchannelmanager');
				break;
			case 'VCMOTACANCORDERNOTFOUND':
				$result = __("VikChannelManager\n\nAn error occurred while processing a cancellation request for a reservation received from the channel %s.\nThe booking id %s does not exist in VikBooking so the system was unable to cancel the reservation. The room id for whom the customer requested the cancellation is %s. The reservation was probably already cancelled in VikBooking or never saved.\n\nPlease ignore this message if you have already received a notification for this booking id. Otherwise check your OTA account because they will automatically proceed with the cancellation of the booking because it was requested by the customer.", 'vikchannelmanager');
				break;
			case 'VCMOTACANCORDERNOTFOUNDSUBJ':
				$result = __('VikChannelManager Notification: Booking not found for cancellation', 'vikchannelmanager');
				break;
			case 'VCMOTAMODORDERNOTFOUND':
				$result = __("VikChannelManager\n\nAn error occurred while processing a modification request for a reservation received from the channel %s.\nThe booking id %s does not exist in VikBooking so the system was unable to modify the reservation. The room id for whom the customer requested the modification is %s. The reservation was probably already cancelled in VikBooking or never saved.\n\nPlease ignore this message if you have already received a notification for the modification of this booking id. Otherwise check your OTA account because they will automatically proceed with the modification of the booking because it was requested by the customer.", 'vikchannelmanager');
				break;
			case 'VCMOTAMODORDERNOTFOUNDSUBJ':
				$result = __('VikChannelManager Notification: Booking not found for modification', 'vikchannelmanager');
				break;
			case 'VCMOTAMODORDERROOMNOTAVAIL':
				$result = __("VikChannelManager\n\nAn error occurred while processing a modification request of an existing reservation received from the channel OTA %s.\nThe booking id %s was made on the room id %s that corresponds to the room -%s- of VikBooking. The room is not available from the %s to the %s.\nPlease verify the reservation manually from the OTA and checks all the reservations saved in VikBooking for those dates.\nThis booking will be automatically saved by the OTA even if you do not do anything, this is just an alert message to inform you that the reservation was not modified in VikBooking.\nIn case you want to manually remove or modify the reservation manually in VikBooking, the reservation id of VikBooking is %s.\n\nIf you have already received a notification message for this booking id, please ignore this message.", 'vikchannelmanager');
				break;
			case 'VCMOTAMODORDERROOMNOTAVAILSUBJ':
				$result = __('VikChannelManager Modification Error Notification', 'vikchannelmanager');
				break;
			case 'VCM_TAC_ERR_PRICE_MISMATCH':
				$result = __('Price Mismatch: the total amount that should be paid for this room in these dates is %s.', 'vikchannelmanager');
				break;
			case 'VCMTACNEWORDERMAILSUBJECT':
				$result = __('TripAdvisor - New Booking Received', 'vikchannelmanager');
				break;
			case 'VCMTACNEWORDERMAILCONTENT':
				$result = __("A new booking was received from TripAdvisor. [%s]\n\n%s\n\nCheckin Date: %s - Checkout Date: %s\n\nCustomer Info:\n\n%s\nThe credit card details were partially stored in the database and are available at the link below.\n\nRemaining Card Number: %s\n\nOrder Details:\n\n%s", 'vikchannelmanager');
				break;
			case 'VCMTACNEWORDSTANDBYSTATUS':
				$result = __('The status is PENDING so you may want to process the payment manually through your virtual terminal or from the order details page.', 'vikchannelmanager');
				break;
			case 'VCMTACNEWORDCONFIRMEDSTATUS':
				$result = __('The status is CONFIRMED and you may want to collect the credit card details from this email and from the administrator section.', 'vikchannelmanager');
				break;
			case 'VCMTRINEWORDERMAILSUBJECT':
				$result = __('Trivago - New Booking Received', 'vikchannelmanager');
				break;
			case 'VCMTRINEWORDERMAILCONTENT':
				$result = __("A new booking was received from Trivago. [%s]\n\n%s\n\nCheckin Date: %s - Checkout Date: %s\n\nCustomer Info:\n\n%s\nThe payment details were partially stored in the database and are available at the link below.\n\nRemaining Card Number/Payment Details: %s\n\nOrder Details:\n\n%s", 'vikchannelmanager');
				break;
			case 'VCMCHANNELNEWORDERMAILSUBJECT':
				$result = __('Booking Sensitive Data: Credit Card', 'vikchannelmanager');
				break;
			case 'VCMCHANNELNEWORDERMAILCONTENT':
				$result = __("The credit card details for the Booking ID %s received from %s, were partially stored in the database and you can see them from the link below.\n\nRemaining Card Number: %s\n\nOrder Details link:\n%s", 'vikchannelmanager');
				break;
			case 'VCMAPPMISSINGCRED':
				$result = __('Missing App Credentials in Vik Channel Manager', 'vikchannelmanager');
				break;
			case 'VCMAPPMISSINGEMAIL':
				$result = __('Missing App Credential Email', 'vikchannelmanager');
				break;
			case 'VCMAPPMISSINGPASS':
				$result = __('Missing App Credential Password', 'vikchannelmanager');
				break;
			case 'VCMAPPINVALIDEMAIL':
				$result = __('Invalid Email for Authentication', 'vikchannelmanager');
				break;
			case 'VCMAPPINVALIDPASS':
				$result = __('Invalid Password for Authentication', 'vikchannelmanager');
				break;
			case 'VCMAPPNOVBINSTALL':
				$result = __('VikBooking is not installed', 'vikchannelmanager');
				break;
			case 'VCMAPPINVALIDDATE':
				$result = __('Invalid Date!', 'vikchannelmanager');
				break;
			case 'VCMAPPREQTYPEINCORRECT':
				$result = __('Invalid request type', 'vikchannelmanager');
				break;
			case 'VCMAPPEMPTYBOOKINGID':
				$result = __('Empty Booking ID', 'vikchannelmanager');
				break;
			case 'VCMAPPSELBOOKINGUNAV':
				$result = __('Requested booking not available', 'vikchannelmanager');
				break;
			case 'VCMAPPNOROOMCONN':
				$result = __('No rooms connected to the selected booking', 'vikchannelmanager');
				break;
			case 'VCMAPPNOROOMAV':
				$result = __('No rooms available', 'vikchannelmanager');
				break;
			case 'VCMAPPGENERICERROR':
				$result = __('Error %s: %s', 'vikchannelmanager');
				break;
			case 'VCMAPPNOROOMFOUND':
				$result = __('No rooms found', 'vikchannelmanager');
				break;
			case 'VCMAPPRQEMPTY':
				$result = __('Empty Request', 'vikchannelmanager');
				break;
			case 'VCMAPPRQINVALID':
				$result = __('Invalid request', 'vikchannelmanager');
				break;
			case 'VCMAPPACCDENIED':
				$result = __('Access Denied', 'vikchannelmanager');
				break;
			case 'VCMAPPERRE4JCSYNC':
				$result = __('Error with channels sync. Check the notifications from your site.', 'vikchannelmanager');
				break;
			case 'VCMAPPCHREQREFUSED':
				$result = __('Channel Mobile App not available', 'vikchannelmanager');
				break;
			case 'VCMAPPCONFBROOMNA':
				$result = __('Some rooms are no longer available for these dates (%s)', 'vikchannelmanager');
				break;
			case 'VCMAPPINVALIDBDATES':
				$result = __('Dates invalid or in the past', 'vikchannelmanager');
				break;
			case 'VCMAPPCANNOTSWITCHROOM':
				$result = __('The room %s cannot be switched to the %s on these dates.', 'vikchannelmanager');
				break;
			case 'VCMAPPPREVROOMMOVED':
				$result = __('Previous room %s was switched on %s', 'vikchannelmanager');
				break;
			case 'VCMAPPNORATESFOUND':
				$result = __('No rates found', 'vikchannelmanager');
				break;
			case 'VCMAPPNOROOMCHANNELS':
				$result = __('No valid channels found for this room', 'vikchannelmanager');
				break;
			case 'VCMAPPINVALIDRPLAN':
				$result = __('Invalid Rate Plans provided', 'vikchannelmanager');
				break;
			case 'VCMAPPVBOMODRATESERR':
				$result = __('Errors while updating the website rates: %s', 'vikchannelmanager');
				break;
			case 'VCMAPPROOMCLOSED':
				$result = __('Room Closed', 'vikchannelmanager');
				break;
			case 'VCMAPPROOMCLOSEDNOTES':
				$result = __('Room Closed via App by %s', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKINGNOTES':
				$result = __('New Booking created via App by %s', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKFNAME':
				$result = __('First Name', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKLNAME':
				$result = __('Last Name', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKEMAIL':
				$result = __('Email', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKPHONE':
				$result = __('Phone', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKADDR':
				$result = __('Address', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKCITY':
				$result = __('City', 'vikchannelmanager');
				break;
			case 'VCMAPPNEWBOOKZIP':
				$result = __('ZIP', 'vikchannelmanager');
				break;
			case 'VCMAPPEXPIREDAPI':
				$result = __('Your API Key is either expired or invalid', 'vikchannelmanager');
				break;
			case 'VCMAPPNOTIFINFO':
				$result = __('News from E4jConnect', 'vikchannelmanager');
				break;
			case 'VCMAPPCHECKEDSTATUS':
				$result = __('Registration', 'vikchannelmanager');
				break;
			case 'VCMAPPCHECKEDSTATUSIN':
				$result = __('Checked-in', 'vikchannelmanager');
				break;
			case 'VCMAPPCHECKEDSTATUSOUT':
				$result = __('Checked-out', 'vikchannelmanager');
				break;
			case 'VCMAPPCHECKEDSTATUSNOS':
				$result = __('No Show', 'vikchannelmanager');
				break;
			case 'VCMAPPCHECKEDSTATUSZERO':
				$result = __('none', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_TODAY':
				$result = __('Today', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_YESTERDAY':
				$result = __('Yesterday', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_TEXTAREA_PLACEHOLDER':
				$result = __('Type your message...', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_NO_THREADS':
				$result = __('No threads found', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_SENDING_ERR':
				$result = __('An error occurred while sending the message. Please, try again.', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_THREAD_TOPIC':
				$result = __('Please enter an optional subject for this thread.', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_THREAD_SUBJECT_DEFAULT':
				$result = __('Information Request', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_MAIL_SUBJECT_HOTEL':
				$result = __('You have a message from %s', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_MAIL_SUBJECT_GUEST':
				$result = __('You have a message from %s', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_SMS_TEXT_HOTEL':
				$result = __('You have a message from %s:\n%s', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_SMS_TEXT_GUEST':
				$result = __('You have a message from %s:\n%s', 'vikchannelmanager');
				break;
			case 'VCMBOOKINGIDNUM':
				$result = __('Reservation ID %s', 'vikchannelmanager');
				break;
			case 'VCMCHATNEWMESSFROM':
				$result = __('You have a new message from %s', 'vikchannelmanager');
				break;
			case 'VCMCHATNEWMESSFROMGUEST':
				$result = __('You have a new message from the guest', 'vikchannelmanager');
				break;
			case 'VCMCHATCLICKTORESP':
				$result = __('Click here to respond', 'vikchannelmanager');
				break;
			case 'VCMBOOKDETAILS':
				$result = __('Reservation Details', 'vikchannelmanager');
				break;
			case 'VCMGUESTNAME':
				$result = __('Guest name', 'vikchannelmanager');
				break;
			case 'VCMCHECKIN':
				$result = __('Check-in', 'vikchannelmanager');
				break;
			case 'VCMCHECKOUT':
				$result = __('Check-out', 'vikchannelmanager');
				break;
			case 'VCMBOOKINGCONFNUM':
				$result = __('Confirmation number %s', 'vikchannelmanager');
				break;
			case 'VCMCHATATTACHDFILES':
				$result = __('Attached files', 'vikchannelmanager');
				break;
			case 'VCMCHATNIGHTS':
				$result = __('Nights', 'vikchannelmanager');
				break;
			case 'VCMCHATROOMSNUM':
				$result = __('Total rooms', 'vikchannelmanager');
				break;
			case 'VCMCHATNOTIFDISCLHOTEL':
				$result = __('This email notification was sent by your Channel Manager because you have received a message from the guest through the chat regarding a specific reservation. Please log in to the back-end section of your website to send a reply to the guest.', 'vikchannelmanager');
				break;
			case 'VCMCHATNOTIFDISCLGUEST':
				$result = __('You have received this email notification because the property has sent you a message through the chat regarding your reservation. Please access the booking details page to reply.', 'vikchannelmanager');
				break;
			case 'VCMAPPNORECORDS':
				$result = __('No records found', 'vikchannelmanager');
				break;


			case 'VCM_WEEKLY_REPORT_TITLE':
				$result = __('Your Weekly Report', 'vikchannelmanager');
				break;
			case 'VCM_BIWEEKLY_REPORT_TITLE':
				$result = __('Your Bi-Weekly Report', 'vikchannelmanager');
				break;
			case 'VCM_MONTHLY_REPORT_TITLE':
				$result = __('Your Monthly Report', 'vikchannelmanager');
				break;
			case 'VCM_TOTAL_INCOME':
				$result = __('Total income', 'vikchannelmanager');
				break;
			case 'VCM_GROSS_REVENUE':
				$result = __('Gross Revenue', 'vikchannelmanager');
				break;
			case 'VCM_TOT_NEW_BOOKINGS':
				$result = __('New Bookings', 'vikchannelmanager');
				break;
			case 'VCM_DIRECT_BOOKINGS_WEBSITE':
				$result = __('Direct Bookings from your Website', 'vikchannelmanager');
				break;
			case 'VCM_PCENT_OFALL_RES':
				$result = __('of all bookings', 'vikchannelmanager');
				break;
			case 'VCM_GLOBAL_OCCUPANCY':
				$result = __('Global Occupancy', 'vikchannelmanager');
				break;
			case 'VCM_RESERVATIONS_COUNT':
				$result = __('Reservations Count', 'vikchannelmanager');
				break;
			case 'VCM_REPORT_CHANNEL':
				$result = __('Channel', 'vikchannelmanager');
				break;
			case 'VCM_RATE_GROWTH':
				$result = __('Rate of Growth', 'vikchannelmanager');
				break;
			case 'VCM_REPORT_YOURWEBSITE':
				$result = __('Your Website', 'vikchannelmanager');
				break;
			case 'VCM_REPORT_DISCLAIMER':
				$result = __('You are receiving this message because you have enabled the periodical Reports from the page Settings in Vik Channel Manager. Log into your website to change the configuration.', 'vikchannelmanager');
				break;
			case 'VCM_REPORT_PLEASENOREPLY':
				$result = __('Please do not reply to this message as it was generated automatically.', 'vikchannelmanager');
				break;
			case 'VCM_REPORT_EMAIL_SUBJECT':
				$result = __('Channel Manager Report', 'vikchannelmanager');
				break;
			case 'VCM_ACCPRR_RS_SUCCESS':
				$result = __('Booking Request Accepted', 'vikchannelmanager');
				break;
			case 'UNKNOWN_ERROR_MAP':
				$result = __('Unknown Error [%s]', 'vikchannelmanager');
				break;
			case 'VCM_OTACONFRES_FROM_PENDING':
				$result = __('Reservation was updated from "Pending" to "Confirmed".', 'vikchannelmanager');
				break;
			case 'VCM_EVALERT_PEND_NO_AV':
				$result = __('The reservation is pending confirmation, but there is no availability at this moment.', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDER_MSUBJ':
				$result = __('Your Channel Manager subscription will expire soon', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDER_MCONT':
				$result = __("This message was generated automatically by your own website at %s.\n\nThis is a reminder to inform you that your E4jConnect subscription for the Channel Manager service will expire in %d days (%s). Do not forget to renew the service before then to not lose the connectivity.", 'vikchannelmanager');
				break;
			case 'VCM_AUTORESPONDER_DEF_MESSAGE':
				$result = __('Thanks for getting in touch. We will get back to you as soon as possible.', 'vikchannelmanager');
				break;
			case 'VCM_ATTACH':
				$result = __('Attach', 'vikchannelmanager');
				break;
			case 'VCM_AI_ASSISTANT':
				$result = __('AI Assistant', 'vikchannelmanager');
				break;
			case 'VCM_TRANSLATE':
				$result = __('Translate', 'vikchannelmanager');
				break;
			case 'VCM_TRANSLATING':
				$result = __('Translating...', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_TOOLTIP':
				$result = __('AI ✨', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_BADGE':
				$result = __('The AI will auto-respond shortly', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_BADGE_STOPPED':
				$result = __('The AI auto-responder is stopped', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_RESUME':
				$result = __('Resume', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_STOP':
				$result = __('Stop', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_DRAFT_TITLE':
				$result = __('The AI generated the following message.', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_DRAFT_SEND':
				$result = __('Send', 'vikchannelmanager');
				break;
			case 'EDIT':
				$result = __('Edit', 'vikchannelmanager');
				break;
			case 'VCM_AI_PAID_SERVICE_REQ':
				$result = __('This function requires the AI service integration!', 'vikchannelmanager');
				break;

			/**
			 * These translation strings are taken directly from WordPress with no textdomain.
			 * 
			 * @since 	1.6.17
			 */
			case 'SUNDAY':
				$result = __('Sunday');
				break;
			case 'MONDAY':
				$result = __('Monday');
				break;
			case 'TUESDAY':
				$result = __('Tuesday');
				break;
			case 'WEDNESDAY':
				$result = __('Wednesday');
				break;
			case 'THURSDAY':
				$result = __('Thursday');
				break;
			case 'FRIDAY':
				$result = __('Friday');
				break;
			case 'SATURDAY':
				$result = __('Saturday');
				break;
			case 'JANUARY':
				$result = __('January');
				break;
			case 'FEBRUARY':
				$result = __('February');
				break;
			case 'MARCH':
				$result = __('March');
				break;
			case 'APRIL':
				$result = __('April');
				break;
			case 'MAY':
				$result = __('May');
				break;
			case 'JUNE':
				$result = __('June');
				break;
			case 'JULY':
				$result = __('July');
				break;
			case 'AUGUST':
				$result = __('August');
				break;
			case 'SEPTEMBER':
				$result = __('September');
				break;
			case 'OCTOBER':
				$result = __('October');
				break;
			case 'NOVEMBER':
				$result = __('November');
				break;
			case 'DECEMBER':
				$result = __('December');
				break;
			//
		}

		return $result;
	}
}
