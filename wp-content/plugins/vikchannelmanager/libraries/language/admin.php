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
 * Switcher class to translate the VikChannelManager plugin admin languages.
 *
 * @since 	1.0
 */
class VikChannelManagerLanguageAdmin implements JLanguageHandler
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
			/**
			 * Definitions
			 */
			case 'VCMWIZARDAPIKEYTITLE':
				$result = __('Welcome to Vik Channel Manager', 'vikchannelmanager');
				break;
			case 'VCMWIZARDAPIKEYLABEL':
				$result = __('Please insert below your E4jConnect API Key', 'vikchannelmanager');
				break;
			case 'VCMWIZARDINSERTBUTTON':
				$result = __('Validate API Key', 'vikchannelmanager');
				break;
			case 'VCMMENUDASHBOARD':
				$result = __('Dashboard', 'vikchannelmanager');
				break;
			case 'VCMMENUSETTINGS':
				$result = __('Settings', 'vikchannelmanager');
				break;
			case 'VCMMENUIBE':
				$result = __('IBE', 'vikchannelmanager');
				break;
			case 'VCMMENUHOTEL':
				$result = __('Hotel', 'vikchannelmanager');
				break;
			case 'VCMMENUORDERS':
				$result = __('Bookings', 'vikchannelmanager');
				break;
			case 'VCMMENUEXPROOMSREL':
				$result = __('Room Relations', 'vikchannelmanager');
				break;
			case 'VCMMENUEXPROOMSAVRATRESTR':
				$result = __('Availability & Rates', 'vikchannelmanager');
				break;
			case 'VCMMENUEXPSYNCH':
				$result = __('Synchronize Rooms', 'vikchannelmanager');
				break;
			case 'VCMMENUEXPFROMVB':
				$result = __('Bookings List', 'vikchannelmanager');
				break;
			case 'VCMMENUEXPCUSTAV':
				$result = __('Custom Availability', 'vikchannelmanager');
				break;
			case 'VCMMENUTACDETAILS':
				$result = __('Details', 'vikchannelmanager');
				break;
			case 'VCMMENUTACROOMSINV':
				$result = __('Rooms Inventory', 'vikchannelmanager');
				break;
			case 'VCMMENUTACSTATUS':
				$result = __('Status', 'vikchannelmanager');
				break;
			case 'VCMMENUREVEXP':
				$result = __('Review Express', 'vikchannelmanager');
				break;
			case 'VCMMENULISTINGS':
				$result = __('Properties', 'vikchannelmanager');
				break;
			case 'VCMMENUOVERVIEW':
				$result = __('Availability Overview', 'vikchannelmanager');
				break;
			case 'VCMMENUSMARTBALANCER':
				$result = __('Smart Balancer', 'vikchannelmanager');
				break;
			case 'VCMOVERVIEWACMPRQLAUNCH':
				$result = __('Compare 31 days Availability to all Channels', 'vikchannelmanager');
				break;
			case 'VCMMAINTDIAGNOSTIC':
				$result = __('Vik Channel Manager - Diagnostic Test', 'vikchannelmanager');
				break;
			case 'VCMSTARTIODIAGNOSTICBTN':
				$result = __('Launch Input Output Diagnostic Test', 'vikchannelmanager');
				break;
			case 'VCMIODIAGNOSTICGOODTITLE':
				$result = __('IO Traffic Diagnostic Test Successful! Booking retrieval example:', 'vikchannelmanager');
				break;
			case 'VCMIODIAGNOSTICBADTITLE':
				$result = __('IO Traffic Diagnostic Test Failed! Error response:', 'vikchannelmanager');
				break;
			case 'VCMYES':
				$result = __('Yes', 'vikchannelmanager');
				break;
			case 'VCMNO':
				$result = __('No', 'vikchannelmanager');
				break;
			case 'VCMFROMDATE':
				$result = __('From Date', 'vikchannelmanager');
				break;
			case 'VCMTODATE':
				$result = __('To Date', 'vikchannelmanager');
				break;
			case 'VCMIBECOMPARE':
				$result = __('Compare data to IBE', 'vikchannelmanager');
				break;
			case 'VCMWARNINGTEXT':
				$result = __('Warning!', 'vikchannelmanager');
				break;
			case 'VCMPARNOINVLOADEDRESP':
				$result = __('You have no Inventories loaded for this Room Type and Rate Plan for the selected Dates.', 'vikchannelmanager');
				break;
			case 'VCMPARNOINVPUSHSUGGEST':
				$result = __('Use the function &quot;Copy Rates &amp; Inventory&quot; to push your availability and rates through n days in the future.', 'vikchannelmanager');
				break;
			case 'VCMALERTMODALOK':
				$result = __('Okay', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINV':
				$result = __('Copy Rates &amp; Inventory', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVFROM':
				$result = __('Copy availability and rates of the Date', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVFROMOR':
				$result = __('Copy availability and/or rates of the Date', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVNOSKIP':
				$result = __('Availability &amp; Rates', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVNOSKIPEXPL':
				$result = __('Both Availability and Rates will be copied and pushed from this date.', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVSKIPAV':
				$result = __('Rates Only', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVSKIPAVEXPL':
				$result = __('Only the Rates of this date will be copied and pushed. The Availability of this date and the next days, will remain unchanged.', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVWHERE':
				$result = __('Copy Inventory from', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVWHEREOTA':
				$result = __('Current Inventory Grid', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVWHEREIBE':
				$result = __('VikBooking (IBE)', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVTO':
				$result = __('and Apply them on the next n Days', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVERREX':
				$result = __('The values for this Date are already set for copy.', 'vikchannelmanager');
				break;
			case 'VCMRARPARENTSRATEPLANS':
				$result = __('Parent Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMRARANYRATEPLANS':
				$result = __('Any Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOS':
				$result = __('Add Costs per Night', 'vikchannelmanager');
				break;
			case 'VCMRARREMOVEALLLOS':
				$result = __('Remove all Costs per Night', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSNUMNIGHTS':
				$result = __('Number of Nights', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSFROMNIGHTS':
				$result = __('from', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSTONIGHTS':
				$result = __('to', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSAPPLY':
				$result = __('Apply', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSCOSTPNIGHT':
				$result = __('Cost per Night', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSEXPL':
				$result = __('the room type must support the Length Of Stay Pricing', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSOCCUPANCY':
				$result = __('Guests Occupancy', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSGUEST':
				$result = __('%d Guest', 'vikchannelmanager');
				break;
			case 'VCMRARADDLOSGUESTS':
				$result = __('%d Guests', 'vikchannelmanager');
				break;
			case 'VCMRARINVLASTUPDATE':
				$result = __('Last Update to Extranet', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVDONE':
				$result = __('Done', 'vikchannelmanager');
				break;
			case 'VCMCOPYRATESINVAPPLY':
				$result = __('Apply', 'vikchannelmanager');
				break;
			case 'VCMOR':
				$result = __('or', 'vikchannelmanager');
				break;
			case 'VCMRARLOADRATESIBE':
				$result = __('Load Rates from IBE', 'vikchannelmanager');
				break;
			case 'VCMALLROOMTYPES':
				$result = __('Any Room Type', 'vikchannelmanager');
				break;
			case 'VCMALLRATEPLANS':
				$result = __('Any Rate Plan', 'vikchannelmanager');
				break;
			case 'VCMCOMPONIBE':
				$result = __('IBE', 'vikchannelmanager');
				break;
			case 'VCMCOMPONIBEOF':
				$result = __('/', 'vikchannelmanager');
				break;
			case 'VCMRARCOMPNUMNIGHTS':
				$result = __('Nights %d:', 'vikchannelmanager');
				break;
			case 'VCMRARCOPYPRICE':
				$result = __('Use this Price', 'vikchannelmanager');
				break;
			case 'VCMRARCOPYALLPRICES':
				$result = __('Copy All Prices', 'vikchannelmanager');
				break;
			case 'VCMRARDATE':
				$result = __('Day', 'vikchannelmanager');
				break;
			case 'VCMOTAROOMTYPE':
				$result = __('OTA Room Type', 'vikchannelmanager');
				break;
			case 'VCMRAROPEN':
				$result = __('Open', 'vikchannelmanager');
				break;
			case 'VCMRARINVENTORY':
				$result = __('Inventory', 'vikchannelmanager');
				break;
			case 'VCMRARINVSOLD':
				$result = __('Total Inventory Sold: %d', 'vikchannelmanager');
				break;
			case 'VCMRARINVBASEALLOC':
				$result = __('Base Allocation: %d', 'vikchannelmanager');
				break;
			case 'VCMRARINVFLEXALLOC':
				$result = __('Flexible Allocation: %d', 'vikchannelmanager');
				break;
			case 'VCMRARINVLABTOTINVAV':
				$result = __('Total Inventory', 'vikchannelmanager');
				break;
			case 'VCMRARINVLABFLEXALLOC':
				$result = __('Flexible Allocations', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPLAN':
				$result = __('Rate Plan', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPLANTITLE':
				$result = __('%s(%s)', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRICTIONS':
				$result = __('Restrictions', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPERDAY':
				$result = __('Per Day', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPERDAYLOS':
				$result = __('Nights: %d', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPERDAYLOSGUESTS':
				$result = __('Guests: %d', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPERPERSON':
				$result = __('Per Person', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPEROCCUPANCY':
				$result = __('Occupancy %d:', 'vikchannelmanager');
				break;
			case 'VCMRARRATEUNKNOWN':
				$result = __('Unknown Rate Type', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATESINGLE':
				$result = __('Single Rate', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATEDOUBLE':
				$result = __('Double Rate', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATEFULL':
				$result = __('Full Rate', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATEEXTRAPERSON':
				$result = __('Extra Person', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATEEXTRAADULT':
				$result = __('Extra Adult', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATEEXTRACHILD':
				$result = __('Extra Child', 'vikchannelmanager');
				break;
			case 'VCMAGODARARRATEEXTRABED':
				$result = __('Extra Bed', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRMINLOS':
				$result = __('Min LOS:', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRMAXLOS':
				$result = __('Max LOS:', 'vikchannelmanager');
				break;
			case 'VCMRARRATEPLANID':
				$result = __('Rate Plan ID %s', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRCLOSEDARRIVAL':
				$result = __('Closed to Arrival', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRCLOSEDDEPARTURE':
				$result = __('Closed to Departure', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRBREAKFASTINCLUDED':
				$result = __('Breakfast Included', 'vikchannelmanager');
				break;
			case 'VCMRARRESTRPROMOBLACKOUT':
				$result = __('Promotion Blackout', 'vikchannelmanager');
				break;
			case 'VCMTOTINVAVAILABLE':
				$result = __('Units Available', 'vikchannelmanager');
				break;
			case 'VCMRARSETTOCLOSED':
				$result = __('Set to Closed', 'vikchannelmanager');
				break;
			case 'VCMRARSETTOOPEN':
				$result = __('Set to Open', 'vikchannelmanager');
				break;
			case 'VCMRARERRNOSESSION':
				$result = __('Error, no data in session to be sent to the Channel. Fetch the Rates, Availability and Restrictions again.', 'vikchannelmanager');
				break;
			case 'VCMRARERRNODATES':
				$result = __('Error, no dates selected. Please tick the checkbox of some dates and then send the data again.', 'vikchannelmanager');
				break;
			case 'VCMRARRQSUCCESS':
				$result = __('Success! The Update Request was successfully sent to the Channel that returned a positive response.', 'vikchannelmanager');
				break;
			case 'VCMBOOKINGCOMRARRATEPRICEFULL':
				$result = __('Full Occupancy', 'vikchannelmanager');
				break;
			case 'VCMBOOKINGCOMRARRATEPRICESINGLE':
				$result = __('Single Occupancy', 'vikchannelmanager');
				break;
			case 'VCMDESPEGARRARRATEGUEST':
				$result = __('Rate for 1 Adult', 'vikchannelmanager');
				break;
			case 'VCMDESPEGARRARRATEGUESTS':
				$result = __('Rate for %d Adults', 'vikchannelmanager');
				break;
			case 'VCMDESPEGARRARRATEEXTRAGUEST':
				$result = __('Rate for extra Adults', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDGREEN':
				$result = __('Available', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDPURPLE':
				$result = __('Partially Available', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDGREENSELF':
				$result = __('Same Availability', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDRED':
				$result = __('Not Available', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDSKY':
				$result = __('Channel Availability', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDPINK':
				$result = __('Closed on Channel', 'vikchannelmanager');
				break;
			case 'VCMOVERSIGHTLEGENDDASHED':
				$result = __('Missing Data', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMACMPSESSVAL':
				$result = __('There are some comparison values with the channels for the date %s. Would you like to load them?', 'vikchannelmanager');
				break;
			case 'VCMACMPINCLROOM':
				$result = __('Include this Room in the Availability Comparison?', 'vikchannelmanager');
				break;
			case 'VCMACMPEXCLROOM':
				$result = __('Exclude this Room in the Availability Comparison?', 'vikchannelmanager');
				break;
			case 'VCMMAINTWIZARD':
				$result = __('Vik Channel Manager - Wizard', 'vikchannelmanager');
				break;
			case 'VCMMAINTDASHBOARD':
				$result = __('Vik Channel Manager - Dashboard', 'vikchannelmanager');
				break;
			case 'VCMMAINTCONFIG':
				$result = __('Vik Channel Manager - Settings', 'vikchannelmanager');
				break;
			case 'VCMMAINTOVERVIEW':
				$result = __('Vik Channel Manager - Overview', 'vikchannelmanager');
				break;
			case 'VCMMAINTROOMINVENTORY':
				$result = __('Vik Channel Manager - Rooms Inventory', 'vikchannelmanager');
				break;
			case 'VCMMAINTHOTELDETAILS':
				$result = __('Vik Channel Manager - Hotel Details', 'vikchannelmanager');
				break;
			case 'VCMMAINTSTATUSTAC':
				$result = __('Vik Channel Manager - Orders Status', 'vikchannelmanager');
				break;
			case 'REMOVE':
				$result = __('Remove', 'vikchannelmanager');
				break;
			case 'NEW':
				$result = __('New', 'vikchannelmanager');
				break;
			case 'EDIT':
				$result = __('Edit', 'vikchannelmanager');
				break;
			case 'SAVE':
				$result = __('Save', 'vikchannelmanager');
				break;
			case 'SAVECLOSE':
				$result = __('Save & Close', 'vikchannelmanager');
				break;
			case 'VCMSAVECUSTA':
				$result = __('Apply Custom Modifications & Select Channels', 'vikchannelmanager');
				break;
			case 'VCMAPPLYCUSTAV':
				$result = __('Send Custom Availability', 'vikchannelmanager');
				break;
			case 'VCMCUSTOMACHANGES':
				$result = __('Changes to send:', 'vikchannelmanager');
				break;
			case 'CANCEL':
				$result = __('Cancel', 'vikchannelmanager');
				break;
			case 'BACK':
				$result = __('Back', 'vikchannelmanager');
				break;
			case 'VCMDASHSTATUS':
				$result = __('E4jConnect Status', 'vikchannelmanager');
				break;
			case 'VCMDASHEMPTYAPI':
				$result = __('No API Key defined. You will not be able to use the E4jConnect&reg; services. Insert your API key from the settings.', 'vikchannelmanager');
				break;
			case 'VCMDASHEMPTYHOTELID':
				$result = __('Hotel ID is empty. Use the settings to define your Hotel ID.', 'vikchannelmanager');
				break;
			case 'VCMDASHEMPTYEMAILADMIN':
				$result = __('Email is empty. Use the settings to define your Email address for notifications.', 'vikchannelmanager');
				break;
			case 'VCMDASHYOURACTIVECHANNELS':
				$result = __('Active Channels', 'vikchannelmanager');
				break;
			case 'VCMDASHYOURAPI':
				$result = __('API Key', 'vikchannelmanager');
				break;
			case 'VCMDASHYOURHOTELID':
				$result = __('Your Hotel ID', 'vikchannelmanager');
				break;
			case 'VCMDASHYOUREMAILADMIN':
				$result = __('Email for Notifications', 'vikchannelmanager');
				break;
			case 'VCMDASHEMPTYUSERNAME':
				$result = __('The Username for connecting to the OTA is empty. Use the settings.', 'vikchannelmanager');
				break;
			case 'VCMDASHEMPTYPASSWORD':
				$result = __('The Password for connecting to the OTA is empty. Use the settings.', 'vikchannelmanager');
				break;
			case 'VCMDASHYOURUSERNAME':
				$result = __('Your OTA Username', 'vikchannelmanager');
				break;
			case 'VCMDASHYOURPASSWORD':
				$result = __('Your OTA Password', 'vikchannelmanager');
				break;
			case 'VCMCONFEMAIL':
				$result = __('Email for Notifications', 'vikchannelmanager');
				break;
			case 'VCMCONFDATEFORMAT':
				$result = __('Date Format', 'vikchannelmanager');
				break;
			case 'VCMCONFCURSYMB':
				$result = __('Currency Symbol', 'vikchannelmanager');
				break;
			case 'VCMCONFCURNAME':
				$result = __('Currency Code', 'vikchannelmanager');
				break;
			case 'VCMCONFDIAGNOSTICBTN':
				$result = __('Diagnostic Test', 'vikchannelmanager');
				break;
			case 'VCMCONFDEFPAYMENTOPT':
				$result = __('Default Payment Option', 'vikchannelmanager');
				break;
			case 'VCMCONFDEFPAYMENTOPTNONE':
				$result = __('- None -', 'vikchannelmanager');
				break;
			case 'VCMPAYMENTSTATUS0':
				$result = __('Unpublished', 'vikchannelmanager');
				break;
			case 'VCMPAYMENTSTATUS1':
				$result = __('Published', 'vikchannelmanager');
				break;
			case 'VCMAPIKEY':
				$result = __('API Key', 'vikchannelmanager');
				break;
			case 'VCMWIZGETAPIKEY':
				$result = __('Need to get your API Key? Please make a subscription from the link below.', 'vikchannelmanager');
				break;
			case 'VCMHOTELID':
				$result = __('Hotel ID', 'vikchannelmanager');
				break;
			case 'VCMUSERNAME':
				$result = __('OTA Username', 'vikchannelmanager');
				break;
			case 'VCMPASSWORD':
				$result = __('OTA Password', 'vikchannelmanager');
				break;
			case 'VCMAUTOSYNC':
				$result = __('Auto-Sync', 'vikchannelmanager');
				break;
			case 'VCMAUTOSYNCON':
				$result = __('ON', 'vikchannelmanager');
				break;
			case 'VCMAUTOSYNCOFF':
				$result = __('OFF', 'vikchannelmanager');
				break;
			case 'VCMDASHVBSYNCHON':
				$result = __('Channel Manager auto-sync enabled', 'vikchannelmanager');
				break;
			case 'VCMDASHVBSYNCHOFF':
				$result = __('Channel Manager auto-sync disabled', 'vikchannelmanager');
				break;
			case 'VCMSETTINGSUPDATED':
				$result = __('Settings updated successfully', 'vikchannelmanager');
				break;
			case 'VCMNOROOMSASSOCFOUND':
				$result = __('No Relation Records were found between the Rooms of VikBooking and the OTA.', 'vikchannelmanager');
				break;
			case 'VCMROOMNAMEOTA':
				$result = __('Room OTA', 'vikchannelmanager');
				break;
			case 'VCMROOMNAMEVB':
				$result = __('Room VikBooking', 'vikchannelmanager');
				break;
			case 'VCMROOMDESCR':
				$result = __('Description', 'vikchannelmanager');
				break;
			case 'VCMCHANNEL':
				$result = __('Channel', 'vikchannelmanager');
				break;
			case 'VCMROOMCHANNELID':
				$result = __('Channel Room ID', 'vikchannelmanager');
				break;
			case 'VCMACCOUNTCHANNELID':
				$result = __('Account ID', 'vikchannelmanager');
				break;
			case 'VCMROOMVBID':
				$result = __('VikBooking Room ID', 'vikchannelmanager');
				break;
			case 'VCMMAINTROOMSLIST':
				$result = __('Vik Channel Manager - Rooms List - OTA Relations', 'vikchannelmanager');
				break;
			case 'VCMMAINTROOMSYNCH':
				$result = __('Vik Channel Manager - Room Types and Rate Plans Mapping', 'vikchannelmanager');
				break;
			case 'VCMMAINTROOMSRAR':
				$result = __('Vik Channel Manager - Rooms Availability, Rates, Inventory, Restrictions', 'vikchannelmanager');
				break;
			case 'VCMUPDRATESCHANNEL':
				$result = __('Update Rates', 'vikchannelmanager');
				break;
			case 'VCMGOSYNCHROOMS':
				$result = __('Go to the Synchronization Task', 'vikchannelmanager');
				break;
			case 'VCMSTARTSYNCHROOMS':
				$result = __('Synchronize Rooms with %s', 'vikchannelmanager');
				break;
			case 'VCMBASICSETTINGSNOTREADY':
				$result = __('Unable to Synchronize the Rooms. Check your settings and the Status', 'vikchannelmanager');
				break;
			case 'VCMROOMSRETURNEDBYOTA':
				$result = __('Rooms Fetched from the OTA', 'vikchannelmanager');
				break;
			case 'VCMROOMSRETURNEDBYVB':
				$result = __('Rooms Fetched from VikBooking', 'vikchannelmanager');
				break;
			case 'VCMHOTELINFORETURNED':
				$result = __('Hotel Data', 'vikchannelmanager');
				break;
			case 'VCMSELECTOTAROOMTOLINK':
				$result = __('Select Room', 'vikchannelmanager');
				break;
			case 'VCMSELECTEDOTAROOMTOLINK':
				$result = __('Linking this Room', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELATIONS':
				$result = __('Room Relations', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELATIONSOTA':
				$result = __('OTA Rooms', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELATIONSVB':
				$result = __('VikBooking Rooms', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELDETAILS':
				$result = __('Details', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELRPLANS':
				$result = __('Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELDEFRPLAN':
				$result = __('Default', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELDEFRPLANSET':
				$result = __('Set as Default', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELATIONSID':
				$result = __('ID', 'vikchannelmanager');
				break;
			case 'VCMROOMSRELATIONSNAME':
				$result = __('Name', 'vikchannelmanager');
				break;
			case 'VCMSELECTVBROOMTOLINK':
				$result = __('Select', 'vikchannelmanager');
				break;
			case 'VCMSAVERSYNCHERREMPTYVALUES':
				$result = __('Error, some values of the relations are empty. Please try again', 'vikchannelmanager');
				break;
			case 'VCMSAVERSYNCHERRDIFFVALUES':
				$result = __('Error, number of values for relations mismatch. Please try again', 'vikchannelmanager');
				break;
			case 'VCMSAVERSYNCHERRNOROOMSOTA':
				$result = __('Error, no rooms returned by the OTA. Nothing to save', 'vikchannelmanager');
				break;
			case 'VCMRELATIONSSAVED':
				$result = __('%s Relations have been successfully created', 'vikchannelmanager');
				break;
			case 'VCMSTARTROOMSRAR':
				$result = __('Fetch Rates, Availability & Restrictions', 'vikchannelmanager');
				break;
			case 'VCMFIRSTBSUMMTITLE':
				$result = __('Import the active bookings', 'vikchannelmanager');
				break;
			case 'VCMFIRSTBSUMMDESC':
				$result = __('Would you like to import all your active bookings? All the reservations with a check-out date in the future will be imported auotomatically by the system, to lower the availability on your site and on any other channel connected. Please notice that this function can only be used once. You should not import the active bookings if you have already registered them manually in your website. It is important to have the correct availability on your site for the channel manager to be able to do its job.', 'vikchannelmanager');
				break;
			case 'VCMFIRSTBSUMMOK':
				$result = __('Yes, import them', 'vikchannelmanager');
				break;
			case 'VCMFIRSTBSUMMKO':
				$result = __('No, do not import them', 'vikchannelmanager');
				break;
			case 'VCMFIRSTBSUMMREQSENT':
				$result = __('The request was sent successfully. The E4jConnect servers will transmit all your active bookings shortly (if any).', 'vikchannelmanager');
				break;
			case 'VCMDASHLASTSYNCEXPEDIA':
				$result = __('Last Synch', 'vikchannelmanager');
				break;
			case 'VCMDASHLASTNUMROOMSYNCEXPEDIA':
				$result = __('Rooms Fetched', 'vikchannelmanager');
				break;
			case 'VCMDASHNOTIFICATIONS':
				$result = __('Notifications', 'vikchannelmanager');
				break;
			case 'VCMNOORDERSFOUNDVB':
				$result = __('No orders found on VikBooking', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMNUMB':
				$result = __('Confirmation Number', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBSEARCHSUBM':
				$result = __('Search Order', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBONE':
				$result = __('Date', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBTWO':
				$result = __('Customer Info', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBTHREE':
				$result = __('Rooms', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBPEOPLE':
				$result = __('People', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBFOUR':
				$result = __('Check-in', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBFIVE':
				$result = __('Check-out', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBSIX':
				$result = __('Nights', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBSEVEN':
				$result = __('Total', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBEIGHT':
				$result = __('Status', 'vikchannelmanager');
				break;
			case 'VCMPVIEWORDERSVBNINE':
				$result = __('From', 'vikchannelmanager');
				break;
			case 'VCMADULTS':
				$result = __('Adults', 'vikchannelmanager');
				break;
			case 'VCMADULT':
				$result = __('Adult', 'vikchannelmanager');
				break;
			case 'VCMCHILDREN':
				$result = __('Children', 'vikchannelmanager');
				break;
			case 'VCMCHILD':
				$result = __('Child', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMED':
				$result = __('Confirmed', 'vikchannelmanager');
				break;
			case 'VCMSTANDBY':
				$result = __('Pending', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERONE':
				$result = __('Booking Date', 'vikchannelmanager');
				break;
			case 'VBSTATUS':
				$result = __('Status', 'vikchannelmanager');
				break;
			case 'VBCONFIRMED':
				$result = __('Confirmed', 'vikchannelmanager');
				break;
			case 'VBSTANDBY':
				$result = __('Standby', 'vikchannelmanager');
				break;
			case 'VBCANCELLED':
				$result = __('Cancelled', 'vikchannelmanager');
				break;
			case 'VCMVBCONFIRMNUMB':
				$result = __('Confirmation Number', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERTWO':
				$result = __('Purchaser Info', 'vikchannelmanager');
				break;
			case 'VCMVBNOTROOMNUM':
				$result = __('Room #%d', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERFOUR':
				$result = __('Nights', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERFIVE':
				$result = __('Check-in', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERSIX':
				$result = __('Check-out', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERSEVEN':
				$result = __('Rate Plan', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDEREIGHT':
				$result = __('Options', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERNINE':
				$result = __('Total', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERROOMSNUM':
				$result = __('Rooms', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERADULTS':
				$result = __('Adults', 'vikchannelmanager');
				break;
			case 'VCMVBEDITORDERCHILDREN':
				$result = __('Children', 'vikchannelmanager');
				break;
			case 'VCMVBCOUPON':
				$result = __('Coupon', 'vikchannelmanager');
				break;
			case 'VCMVBPAYMENTMETHOD':
				$result = __('Method of Payment', 'vikchannelmanager');
				break;
			case 'VCMDASHNOTSFROM':
				$result = __('From', 'vikchannelmanager');
				break;
			case 'VCMDASHNOTSDATE':
				$result = __('Date', 'vikchannelmanager');
				break;
			case 'VCMDASHNOTSTEXT':
				$result = __('Text', 'vikchannelmanager');
				break;
			case 'VCMNOTIFICATIONTYPE':
				$result = __('Result', 'vikchannelmanager');
				break;
			case 'VCMDASHNOTSRMSELECTED':
				$result = __('Remove Selected', 'vikchannelmanager');
				break;
			case 'VCMMAINTORDERSVB':
				$result = __('Vik Channel Manager - Vik Booking Orders', 'vikchannelmanager');
				break;
			case 'VCMTLBNOTIFYORDERTOOTA':
				$result = __('(Re-)Notify Orders to OTA', 'vikchannelmanager');
				break;
			case 'VCMNOVBVALIDORDFOUND':
				$result = __('No Valid Order Found.', 'vikchannelmanager');
				break;
			case 'VCMRENOTIFYORDSTOOTA':
				$result = __('(Re-)Notify Orders to OTA', 'vikchannelmanager');
				break;
			case 'VCMRENOTIFIABLE':
				$result = __('(Re-)Notifiable', 'vikchannelmanager');
				break;
			case 'VCMNOVALIDORDSTORESENDOTA':
				$result = __('None of the selected bookings can be (re-)notified to the OTA through E4jConnect', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMRENOTIFYORDSTEXT':
				$result = __('Maximum 3 bookings per time can be (re-)notified. The order(s) will be sent to E4jConnect that will try to post your request to the OTA.<br/>In case these bookings were notified before and your VikChannelManager received a notification from E4jConnect, make sure that your request is valid or the bookings could be banned from E4jConnect.<br/>Click on the button below to proceed or on Cancel to close this page.', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMRENOTIFYORDSPROCEED':
				$result = __('Notify the Order(s) to E4jConnect', 'vikchannelmanager');
				break;
			case 'VCMMAINTARQCONFIRM':
				$result = __('Vik Channel Manager - Confirm Orders Notification', 'vikchannelmanager');
				break;
			case 'VCMTOTARQRESENT':
				$result = __('Availability Update Requests sent to E4jConnect: %s.<br/>E4jConnect should process your requests within the next 3 minutes and notify your VikChannelManager with the response received.<br/>Please do not re-send any request during this time to avoid queues.', 'vikchannelmanager');
				break;
			case 'VCMSCHECKAPIEXPDATE':
				$result = __('Check the Expiration Date of your API Key', 'vikchannelmanager');
				break;
			case 'VCMSCHECKAPIEXPDATELOAD':
				$result = __('Checking...', 'vikchannelmanager');
				break;
			case 'VCMAPIEXPRQRSMESS':
				$result = __('Your API Service(s) will last until the following dates (YYYY-MM-DD):<br/>%s<br/><br/>For further information please visit <a href="https://e4jconnect.com" target="_blank">e4jconnect.com</a>', 'vikchannelmanager');
				break;
			case 'VCMNOOTAROOMSFOUNDCUSTA':
				$result = __('No OTA Rooms found. Unable to proceed. Synchronize your VikBooking Rooms with the ones of the OTA first.', 'vikchannelmanager');
				break;
			case 'VCMCUSTADATE':
				$result = __('Date', 'vikchannelmanager');
				break;
			case 'VCMCUSTAAVAILNUM':
				$result = __('Units Available', 'vikchannelmanager');
				break;
			case 'VCMCUSTOMATEXT':
				$result = __('<span>This function should be used only for correcting availability errors or if the Auto-Synch is disabled. In case the Auto-Synch is ON, VikChannelManager will automatically notify the OTA with the units available in the requested dates every time an order is saved from the front site through VikBooking.</span><br/>Select your OTA rooms and set an availability number for specific days of the year. For example, if you want to close a room for the week-end, use one row for each day and set the Units Available to 0. Custom Availability Requests must include the units available for each day, intervals are not allowed.<br/>The maximum number of days allowed per request is 14. Consider using your OTA account for bigger modifications of the availability.', 'vikchannelmanager');
				break;
			case 'VCMROOMWILLBECLOSED':
				$result = __('the room will be closed', 'vikchannelmanager');
				break;
			case 'VCMMAINTCUSTOMA':
				$result = __('Vik Channel Manager - Custom Rooms Availability', 'vikchannelmanager');
				break;
			case 'VCMTLBCUSTOMA':
				$result = __('Notify Custom Availability', 'vikchannelmanager');
				break;
			case 'VCMMAINTCONFIRMCUSTOMA':
				$result = __('Vik Channel Manager - Custom Rooms Availability', 'vikchannelmanager');
				break;
			case 'VCMCUSTOMACONFMESS':
				$result = __('Custom Availability Requests', 'vikchannelmanager');
				break;
			case 'VCMCONFCUSTAONE':
				$result = __('OTA Room ID', 'vikchannelmanager');
				break;
			case 'VCMCONFCUSTATWO':
				$result = __('OTA Room Name', 'vikchannelmanager');
				break;
			case 'VCMCONFCUSTATHREE':
				$result = __('Channel', 'vikchannelmanager');
				break;
			case 'VCMCONFCUSTAFOUR':
				$result = __('Date', 'vikchannelmanager');
				break;
			case 'VCMCONFCUSTAFIVE':
				$result = __('Availability', 'vikchannelmanager');
				break;
			case 'VCMCONFCUSTASIX':
				$result = __('Notifiable', 'vikchannelmanager');
				break;
			case 'VCMNOVALIDCOMBOCUSTA':
				$result = __('No valid Availability of the rooms received', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMCUSTATEXT':
				$result = __('The maximum number of days allowed per request is 14. The Custom Availability Requests will be sent to E4jConnect that will try to post your request to the OTA.<br/>In case these orders were notified before and your VikChannelManager received a notification from E4jConnect, make sure that your request is valid or the custom availability requests could be banned from E4jConnect.<br/>Click on the button below to proceed or on Cancel to close this page.', 'vikchannelmanager');
				break;
			case 'VCMCONFIRMCUSTOMAPROCEED':
				$result = __('Send Custom Availability Request(s) to E4jConnect', 'vikchannelmanager');
				break;
			case 'VCMTOTCUSTARQRESENT':
				$result = __('Custom Availability Update Requests sent to E4jConnect: %s.<br/>E4jConnect should process your requests within the next 10 minutes and notify your VikChannelManager with the response received.<br/>Please do not re-send any requests for the same dates and rooms to avoid queues.', 'vikchannelmanager');
				break;
			case 'VCMCUSTARQOKVBO':
				$result = __('Availability updated for the website', 'vikchannelmanager');
				break;
			case 'VCMNEWAJAXNOTIFICATIONSTEXT':
				$result = __('New Notifications Received (%d)', 'vikchannelmanager');
				break;
			case 'VCMGETCHANNELS':
				$result = __('Activate Channels', 'vikchannelmanager');
				break;
			case 'VCMGETCHANNELSRQRSMESS1':
				$result = __('<span class="vcmactivechsintrospan">These are your active channels:</span><div class="vcmactivechannelscont">%s</div><span class="vcmactivechsoutrospan">Page will be refreshed in 5 seconds...</span>', 'vikchannelmanager');
				break;
			case 'VCMGETCHANNELSRQRSMESS0':
				$result = __('You have no active channels.<br/>For further information please visit <a href="https://e4jconnect.com" target="_blank">e4jconnect.com</a>', 'vikchannelmanager');
				break;
			case 'VCMEXECMAXREQREACHEDERR':
				$result = __('You have reached the maximum number of requests.<br />Please wait, try again later...', 'vikchannelmanager');
				break;
			case 'VCMCONFIGCHASETTINGSTITLE':
				$result = __('Channel Settings', 'vikchannelmanager');
				break;
			case 'VCMREVEXPNOREVIEWS':
				$result = __('No Review currently being processed.', 'vikchannelmanager');
				break;
			case 'VCMSHOWINFOCLICK':
				$result = __('Click to see more details', 'vikchannelmanager');
				break;
			case 'VCMCHANGEACCOUNT':
				$result = __('Change Account', 'vikchannelmanager');
				break;
			case 'VCMMANAGEACCOUNTS':
				$result = __('Manage Accounts', 'vikchannelmanager');
				break;
			case 'VCMSELECTACCOUNT':
				$result = __('Select Account', 'vikchannelmanager');
				break;
			case 'VCMREMOVEACCOUNT':
				$result = __('Remove Account', 'vikchannelmanager');
				break;
			case 'VCMMANAGEACCOUNTNAME':
				$result = __('Account', 'vikchannelmanager');
				break;
			case 'VCMMANAGEACCOUNTNUMRS':
				$result = __('%d Room Types mapped', 'vikchannelmanager');
				break;
			case 'VCMACTIVEACCOUNT':
				$result = __('Active Account', 'vikchannelmanager');
				break;
			case 'VCMREMOVEACCOUNTCONF':
				$result = __('Do you confirm to remove this account and all its mapped room types? The action is irreversible.', 'vikchannelmanager');
				break;
			case 'VCMEXPEDIAUNAMEWARN':
				$result = __('The Username usually starts with "EQC". This Username may be invalid. Expedia should have sent you an email with the credentials to use for the Channel Manager.', 'vikchannelmanager');
				break;
			case 'VCMEXPEDIASHORTUNAMEWARN':
				$result = __('This Username may be too short. It\'s usually similar to "EQC"+Hotel ID+"e4j". Expedia should have sent you an email with the credentials to use for the Channel Manager.', 'vikchannelmanager');
				break;
			case 'VCMENDPOINTWARNPROTOCOL':
				$result = __('Warning: the protocol used the last time for submitting the channels credentials is %s, but now the protocol has changed to %s. The data transmission may not work properly if your website requires HTTP or HTTPS and the correct protocol is not updated on the E4jConnect servers.<br />Click the button below to update your Channel Manager Endpoint.', 'vikchannelmanager');
				break;
			case 'VCMENDPOINTWARNDOMAIN':
				$result = __('Warning: the base domain name used the last time for submitting the channels credentials is %s, but now the domain has changed to %s. The data transmission may not work properly if your website URL is invalid on the E4jConnect servers.<br />Click the button below to update your Channel Manager Endpoint.', 'vikchannelmanager');
				break;
			case 'VCMUPDATEENDPURL':
				$result = __('Update Endpoint URL to: %s', 'vikchannelmanager');
				break;
			case 'VCMUPDATEENDPURLCONF':
				$result = __('Do you really want to update your Channel Manager Endpoint? Make sure the current website address is the right one.', 'vikchannelmanager');
				break;
			case 'VCMUPDATEENDPSUCC':
				$result = __('The Channel Manager Endpoint was updated successfully! This website address will be used for the data transmission.', 'vikchannelmanager');
				break;
			case 'VCMMAINTCONFIRMCUSTOMR':
				$result = __('Vik Channel Manager - Custom Channel Rates', 'vikchannelmanager');
				break;
			case 'VCMAPPLYCUSTRATES':
				$result = __('Send Custom Rates Update Request', 'vikchannelmanager');
				break;
			case 'VCMOSRATEONDATE':
				$result = __('Rates:', 'vikchannelmanager');
				break;
			case 'VCMSEARCHNOTIF':
				$result = __('Search Notification', 'vikchannelmanager');
				break;
			case 'VCMNOCHANNELSACTIVE':
				$result = __('No Channels', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELNAME':
				$result = __('Name', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELSTREET':
				$result = __('Street', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELCITY':
				$result = __('City', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELZIP':
				$result = __('ZIP Code', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELSTATE':
				$result = __('State/Prov.', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELCOUNTRY':
				$result = __('Country', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELLATITUDE':
				$result = __('Latitude', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELLONGITUDE':
				$result = __('Longitude', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELDESCRIPTION':
				$result = __('Description', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELAMENITIES':
				$result = __('Amenities', 'vikchannelmanager');
				break;
			case 'VCMTACROOMAMENITIES':
				$result = __('Room Amenities', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELURL':
				$result = __('URL', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELEMAIL':
				$result = __('E-Mail', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELPHONE':
				$result = __('Phone Number', 'vikchannelmanager');
				break;
			case 'VCMTACHOTELFAX':
				$result = __('Fax', 'vikchannelmanager');
				break;
			case 'VCMHOTELDETAILSUPDATED1':
				$result = __('Hotel details updated successfully!', 'vikchannelmanager');
				break;
			case 'VCMHOTELDETAILSUPDATED2':
				$result = __('Nothing was changed in the hotel details. The request hasn\'t been submitted.', 'vikchannelmanager');
				break;
			case 'VCMHOTELDETAILSUPDATED0':
				$result = __('Some of the Hotel details are missing. Please fill in all the required (*) fields.', 'vikchannelmanager');
				break;
			case 'VCMHOTELDETAILSFIRSTBUILDING':
				$result = __('You have never submitted your hotel details before. Please fill in all the required (*) fields below to become part of the E4jConnect Hotel Inventory.', 'vikchannelmanager');
				break;
			case 'VCMHOTELDETAILSNOTCOMPERR':
				$result = __('Please, fill in all the required (*) fields for your hotel before proceeding with the rooms.', 'vikchannelmanager');
				break;
			case 'VCMINVENTORYNOROOM':
				$result = __('No rooms available. Please create your rooms first from the component VikBooking.', 'vikchannelmanager');
				break;
			case 'VCMTACROOMSHEADTITLE':
				$result = __('Only the published room types will be included in availability responses to TripAdvisor requests.', 'vikchannelmanager');
				break;
			case 'VCMTACROOMDETNAME':
				$result = __('Name', 'vikchannelmanager');
				break;
			case 'VCMTACROOMDETCOST':
				$result = __('Cost per Night', 'vikchannelmanager');
				break;
			case 'VCMTACROOMDETURL':
				$result = __('URL', 'vikchannelmanager');
				break;
			case 'VCMTACROOMDETDESC':
				$result = __('Short Description (Max 1000 characters)', 'vikchannelmanager');
				break;
			case 'VCMTACROOMPUBLISHED':
				$result = __('Published', 'vikchannelmanager');
				break;
			case 'VCMTACROOMUNPUBLISHED':
				$result = __('Unpublished', 'vikchannelmanager');
				break;
			case 'VCMTACROOMBTNPUBALL':
				$result = __('Publish All', 'vikchannelmanager');
				break;
			case 'VCMTACROOMBTNUNPUBALL':
				$result = __('Unpublish All', 'vikchannelmanager');
				break;
			case 'VCMTACROOMSSYNCHMSG':
				$result = __('Rooms on TripAdvisor: %d', 'vikchannelmanager');
				break;
			case 'VCMTACROOMSCREATEDMSG':
				$result = __('Rooms saved successfully: %d', 'vikchannelmanager');
				break;
			case 'VCMTACROOMSREMOVEDMSG':
				$result = __('Rooms removed successfully: %d', 'vikchannelmanager');
				break;
			case 'VCMTACROOMSNOACTIONMSG':
				$result = __('Please fill in all the required fields for the rooms that you want to save.', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSPID':
				$result = __('Partner ID', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSTRIPID':
				$result = __('Trip Advisor ID', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSLASTACTIVEDATE':
				$result = __('Last Active', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSBUSINESSDATE':
				$result = __('Business Listing Since', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSTRIPDATE':
				$result = __('Trip Advisor Since', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSPLATFORMS':
				$result = __('Platforms', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSNUMCLICKS':
				$result = __('Number of Clicks', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSNUMCONVERSIONS':
				$result = __('Number of Conversions', 'vikchannelmanager');
				break;
			case 'VCMTACSTATUSREVIEWEXPRESS':
				$result = __('Review Express Mail', 'vikchannelmanager');
				break;
			case 'VCMREVEXPID':
				$result = __('Request ID', 'vikchannelmanager');
				break;
			case 'VCMREVEXPCONFNUM':
				$result = __('Confirm Number', 'vikchannelmanager');
				break;
			case 'VCMREVEXPMAIL':
				$result = __('E-Mail', 'vikchannelmanager');
				break;
			case 'VCMREVEXPCHECKIN':
				$result = __('Checkin', 'vikchannelmanager');
				break;
			case 'VCMREVEXPCHECKOUT':
				$result = __('Checkout', 'vikchannelmanager');
				break;
			case 'VCMREVEXPCOUNTRY':
				$result = __('Country', 'vikchannelmanager');
				break;
			case 'VCMREVEXPSTATUS':
				$result = __('Status', 'vikchannelmanager');
				break;
			case 'VCMREVEXPSTATUSSENT':
				$result = __('Sent', 'vikchannelmanager');
				break;
			case 'VCMREVEXPSTATUSQUEUED':
				$result = __('Queued', 'vikchannelmanager');
				break;
			case 'VCMREVEXPSTATUSCANCELED':
				$result = __('Canceled', 'vikchannelmanager');
				break;
			case 'VCMTRIROOMSHEADTITLE':
				$result = __('Only the published room types will be included in availability responses to Trivago requests.', 'vikchannelmanager');
				break;
			case 'VCMTRIROOMSSYNCHMSG':
				$result = __('Rooms on Trivago: %d', 'vikchannelmanager');
				break;
			case 'VCMTRIROOMSCREATEDMSG':
				$result = __('Rooms saved successfully: %d', 'vikchannelmanager');
				break;
			case 'VCMTRIROOMSREMOVEDMSG':
				$result = __('Rooms removed successfully: %d', 'vikchannelmanager');
				break;
			case 'VCMTRIROOMSNOACTIONMSG':
				$result = __('Please fill in all the required fields for the rooms that you want to save.', 'vikchannelmanager');
				break;
			case 'VCMENABLED':
				$result = __('Enabled', 'vikchannelmanager');
				break;
			case 'VCMDISABLED':
				$result = __('Disabled', 'vikchannelmanager');
				break;
			case 'VCMPROGRAMBLOCKEDMESSAGE':
				$result = __('A new required version of Vik Channel Manager is available.', 'vikchannelmanager');
				break;
			case 'VCMNEWOPTIONALUPDATEAV':
				$result = __('A new version of Vik Channel Manager is available.', 'vikchannelmanager');
				break;
			case 'VCMUPDATENOWBTN':
				$result = __('Update Now', 'vikchannelmanager');
				break;
			case 'VCMTIPMODALTITLE':
				$result = __('Tip!', 'vikchannelmanager');
				break;
			case 'VCMTIPMODALTEXTAV':
				$result = __('By clicking on a date and selecting an interval, it is possible to adjust the availability for some Room Types and update the Channels.', 'vikchannelmanager');
				break;
			case 'VCMTIPMODALOKREMIND':
				$result = __('Okay, keep reminding.', 'vikchannelmanager');
				break;
			case 'VCMTIPMODALOK':
				$result = __('Okay, do not remind again.', 'vikchannelmanager');
				break;
			case 'VCMRARBCOMANYPMODEL':
				$result = __('Both Pricing Models', 'vikchannelmanager');
				break;
			case 'VCMRARBCOMPERSONPMODEL':
				$result = __('Default Pricing', 'vikchannelmanager');
				break;
			case 'VCMRARBCOMPERSONPMODELTIP':
				$result = __('Fixed Cost per Night for the full occupancy of the room.', 'vikchannelmanager');
				break;
			case 'VCMRARBCOMLOSPMODEL':
				$result = __('Length of Stay Pricing', 'vikchannelmanager');
				break;
			case 'VCMRARBCOMLOSPMODELTIP':
				$result = __('Costs per Night can change depending on the number of nights and also on the number of guests.', 'vikchannelmanager');
				break;
			case 'VCMARIPRMODELEXPL':
				$result = __('<p>This Channel supports two different Pricing Models: <strong>Default Pricing</strong> and <strong>Length of Stay Pricing</strong>. It is important to know which pricing model was configured for your property in order to submit the Rates and Inventory. Please check your Extranet Account if you are not sure.</p><p>If the Rate Plans support the <strong>Default Pricing</strong>, it is sufficient to submit the Full Occupancy price and, optionally, the Single Occupancy Price. Submitting the rates by using the function costs based on Length of Stay, would simply generate a Warning message, no errors.</p><p>If the Rate Plans were configured as <strong>Length of Stay Pricing</strong> then it is required to click the button Add Costs per Night to enter the prices. If this was your current configuration, by submitting the costs with the Default Pricing model (Full Occupancy/Single Occupancy), you would not open your property for booking on those dates because that Model is not supported and it would look like if you had no rates.</p><p>Try to submit the costs for both pricing models to find out what\'s your configuration. If the Length of Stay Pricing (LOS Price Table on the Extranet) is accepted with no Warning messages, it means that your property was configured for that pricing model.</p>', 'vikchannelmanager');
				break;
			case 'VCMPCIDLAUNCH':
				$result = __('Get full Credit Card details', 'vikchannelmanager');
				break;
			case 'VCMPCIDRESPONSETITLE':
				$result = __('Credit Card Details Reconstructed', 'vikchannelmanager');
				break;
			case 'VCMWDAYS':
				$result = __('Week Days', 'vikchannelmanager');
				break;
			case 'VCMDAYS':
				$result = __('Days', 'vikchannelmanager');
				break;
			case 'VCMDESELECT':
				$result = __('Deselect', 'vikchannelmanager');
				break;
			case 'VCMSELECT':
				$result = __('Select', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHINFO':
				$result = __('With this function you can transmit the Availability Inventory of all your room types to all channels connected. The system will read the current availability of the selected room types from your website, for the provided range of dates, and it will push the same Availability to all the selected channels. You can update a maximum dates span of one year, but you can move the From Date to a future date in order to be able to push the information beyond one year from today. Please notice that this function should be used to complete the first configuration of some channels, whenever you want to open up the bookings for dates in the future, or anytime you want to send a full refresh of the availability to some channels. This mass action will not transmit any rates, just the availability.', 'vikchannelmanager');
				break;
			case 'VCMMAINTAVPUSH':
				$result = __('Vik Channel Manager - Bulk Copy Availability Inventory', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHTITLE':
				$result = __('Bulk Copy Availability Inventory', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHSUBMIT':
				$result = __('Submit Availability Inventory Copy', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHERRNODATA':
				$result = __('Invalid data submitted for copying the Availability Inventory', 'vikchannelmanager');
				break;
			case 'VCMPROCESSING':
				$result = __('Processing...', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHTOTNODES':
				$result = __('Update Request Nodes:', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHREQNUMB':
				$result = __('Bulk Availability Update Request #%d', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHMAXNODES':
				$result = __('(Max %d nodes per request)', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHINFORESULTCOMPL':
				$result = __('Process completed', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHINFORESULT':
				$result = __('The results of the Bulk Copy Availability Inventory Request will be available in the Dashboard page within the next 3 minutes. E4jConnect will notify your channel manager with the result of the updates. Please do not resend any requests during this time to avoid queues.', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHRQNODESENTOK':
				$result = __('Request successfully submitted!', 'vikchannelmanager');
				break;
			case 'VCMMAINTRATESPUSH':
				$result = __('Vik Channel Manager - Bulk Upload Rates Inventory', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHTITLE':
				$result = __('Bulk Upload Rates Inventory', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHSUBMIT':
				$result = __('Submit Rates Inventory Upload', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHINFO':
				$result = __('With this function you can transmit the Rates and Restrictions Inventory of all your room types to all channels connected. The system will read the current rates of the selected room types and rate plans from your website, for the provided range of dates, and it will push the information to all the selected channels. You can update a maximum dates span of one year per request, but you can move the From Date to a future date in order to be able to push the information beyond one year from today. Please notice that this function should be used to complete the first configuration of some channels, whenever you want to open up the bookings for dates in the future, or anytime you want to send a full refresh of the rates to some channels. This mass action will only transmit information about rates and restrictions. It is also possible to modify the website rates to be transmitted to the channels, by increasing or decreasing them \'on the fly\'. This alteration will only affect the rates transmitted to the channels, it will not modify the current rates on your website.', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHVBORPLAN':
				$result = __('Copy from website rate plan', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHOVERVBORPLAN':
				$result = __('Modify rates from rate plan', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHRMODNO':
				$result = __('Upload Same Rates as IBE', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHRMODYES':
				$result = __('Upload Modified Rates', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHRMODINCR':
				$result = __('Increase Rates +', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHRMODDECR':
				$result = __('Lower Rates -', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHERRNODATA':
				$result = __('Invalid data submitted for copying the Rates Inventory', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHPERNIGHT':
				$result = __('Default Cost per Night', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHPERNIGHTWARN':
				$result = __('Warning! The rates defined for the website have a default cost per night of %s %s. You should not use a different default cost per night in the channel manager, or the rates transmitted to the channels may not be correct. Unless you know what you are doing, it is recommended to set a default cost per night equal to the one of the website. If you are looking to increase the rates for the channels, then you can use the apposite function next to this field.', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHADVOPT':
				$result = __('Advanced Options', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHRMRATESCACHE':
				$result = __('Delete pricing settings', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHRMRATESCACHEHELP':
				$result = __('Click this button if you wish to reset all pricing settings that are currently saved. Rate plan relations and pricing alteration rules will be reset. The system will update the settings at any bulk action submission.', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHTOGGLEALL':
				$result = __('Toggle Rooms Selection', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHREQNUMB':
				$result = __('Bulk Rates Upload request #%d', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHREADMORE':
				$result = __('Read More', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHOKCHRES':
				$result = __('Rates Upload request was successful!', 'vikchannelmanager');
				break;
			case 'VCMRPUSHBCOMDERIVEDOCCRULES':
				$result = __('Booking.com (Default Pricing only)<br />Send Derived Prices for Occupancy Rules', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHSELALLCH':
				$result = __('Select All Channels', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHUNSELALLCH':
				$result = __('Deselect All Channels', 'vikchannelmanager');
				break;
			case 'VCMCUSTOMRIBEWARN':
				$result = __('New Rate and/or Restrictions', 'vikchannelmanager');
				break;
			case 'VCMCUSTOMRIBEWARNMESS':
				$result = __('If some restrictions have been set for these dates, they will be saved also in VikBooking. If a new exact rate has been set, it will be saved in VikBooking too. Instead, any rates increase or decrease that was set, will be ignored. Use the Rates Overview page in VikBooking to increase or decrease the rates.', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHERRCHIBE':
				$result = __('The channels are currently being updated but some errors occurred while updating the rates and restrictions in VikBooking: %s', 'vikchannelmanager');
				break;
			case 'VCMMENUBULKACTIONS':
				$result = __('Bulk Actions', 'vikchannelmanager');
				break;
			case 'VCMMENUAVPUSH':
				$result = __('Copy Availability', 'vikchannelmanager');
				break;
			case 'VCMMENURATESPUSH':
				$result = __('Rates Upload', 'vikchannelmanager');
				break;
			case 'VCMMENUBCONTAPI':
				$result = __('Manage Contents', 'vikchannelmanager');
				break;
			case 'VCMMENUBCONTAPIHOTEL':
				$result = __('Hotel Contents', 'vikchannelmanager');
				break;
			case 'VCMMENUBCONTAPIROOMS':
				$result = __('Rooms Contents', 'vikchannelmanager');
				break;
			case 'VCMMENUBCONTAPIRPLANS':
				$result = __('Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMMENUBCONTAPIPNOTIF':
				$result = __('Policies & Relations', 'vikchannelmanager');
				break;
			case 'VCMMENUBCONTAPIHOTELSUM':
				$result = __('Hotel Summary', 'vikchannelmanager');
				break;
			case 'VCMMAINTBCAHCONT':
				$result = __('Booking.com - Hotel Descriptive Contents', 'vikchannelmanager');
				break;
			case 'VCMMAINTBCARCONT':
				$result = __('Booking.com - Rooms Contents Management', 'vikchannelmanager');
				break;
			case 'VCMMAINTBCARPLANS':
				$result = __('Booking.com - Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMMAINTBCAPNOTIF':
				$result = __('Booking.com - Rooms/Rate Plans Relations', 'vikchannelmanager');
				break;
			case 'VCMMAINTBCAHSUM':
				$result = __('Booking.com - Hotel Summary', 'vikchannelmanager');
				break;
			case 'VCMMAINTSMARTBALANCER':
				$result = __('Vik Channel Manager - Smart Balancer Rules', 'vikchannelmanager');
				break;
			case 'VCMMAINTSMARTBALANCERNEWRULE':
				$result = __('Vik Channel Manager - New Smart Balancer Rule', 'vikchannelmanager');
				break;
			case 'VCMMAINTSMARTBALANCEREDITRULE':
				$result = __('Vik Channel Manager - Edit Smart Balancer Rule', 'vikchannelmanager');
				break;
			case 'VCMMAINTSMARTBALANCERSTATS':
				$result = __('Vik Channel Manager - Smart Balancer Statistics', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALNORULES':
				$result = __('No active rules defined for the Smart Balancer', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRNAME':
				$result = __('Rule Name', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRTYPE':
				$result = __('Rule Type', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRTYPEAV':
				$result = __('Automatic Allotments for OTAs', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRTYPERATES':
				$result = __('Automatic Rates Adjustments', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRROOMSAFF':
				$result = __('Rooms Affected', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRLOGS':
				$result = __('Execution Logs', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEINTRO':
				$result = __('Smart Balancer Automatic Rule', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEINTROEXPL':
				$result = __('Let the Smart Balancer manage your units available, or increase and reduce the rates automatically to boost your incomes.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULETYPEEXPL':
				$result = __('Choose a type for this Rule', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRTYPEAVEXPL':
				$result = __('The Smart Balancer will automatically close some rooms on the OTAs when certain criterias are met.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRTYPERATESEXPL':
				$result = __('The Smart Balancer will automatically increase or reduce the room rates when certain criterias are met.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEDATESEXPL':
				$result = __('Select the dates when this Rule should be applied', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEDATESLENGTH':
				$result = __('The Rule will be applied on the selected %d days.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERATESUPDOWN':
				$result = __('Choose how the Rates should be adjusted', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERATESUPDOWNEXPL':
				$result = __('Increase or Decrease the rates with absolute or percentage values, when the units remaining are just a few or still a lot.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERATESUP':
				$result = __('Increase Room Rates', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERATESDOWN':
				$result = __('Discount Room Rates', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVHOW':
				$result = __('Choose what availability should be transmitted to the OTAs', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVLIMIT':
				$result = __('Close rooms on the OTAs when the remaining availability is lower than or equal to \'n\' units', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVUNITS':
				$result = __('Close rooms on the OTAs when they have sold \'n\' units on the same dates', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVTYPEEXPL':
				$result = __('The remaining units will always be available for bookings on your own website.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERATESLT':
				$result = __('Adjust the Rates when the number of units left is less than or equal to \'n\' units', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERATESGT':
				$result = __('Adjust the Rates when the number of units left is greater than or equal to \'n\' units', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERTWHEREEXPL':
				$result = __('Choose where to modify the Rates', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERTIBE':
				$result = __('Modify the Rates only on your website', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERTIBEOTA':
				$result = __('Modify the Rates on the website and on the OTAs', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERTDAYSADV':
				$result = __('How many days in advance can the rates be modified?', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVSELROOMS':
				$result = __('Select the rooms affected by this Rule', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVSELROOMSEXPL':
				$result = __('Choose some or all rooms for this Rule and how the system should calculate the remaining units for every day.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEREADYSAVE':
				$result = __('The Rule is ready to be saved!', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALSELALL':
				$result = __('Select All', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERTUNITSSINGLE':
				$result = __('Count the units left on certain dates individually, for each room selected', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULERTUNITSGROUP':
				$result = __('Count the units left on certain dates as a sum of all the selected rooms', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALERRGENERIC':
				$result = __('Invalid data supplied for the Smart Balancer Rule (%s)', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRNAMERTUP':
				$result = __('Increase Rates Automatically', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRNAMERTDOWN':
				$result = __('Discount Rates Automatically', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRNAMEAV':
				$result = __('Automated Availability for OTAs', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRSAVED':
				$result = __('Rule Saved Successfully!', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALCHANNELSUPDLOG':
				$result = __('Channels Rates Upload Result', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALCHANNELSBRKDWN':
				$result = __('Rates Breakdown', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRLASTEXEC':
				$result = __('Last Execution', 'vikchannelmanager');
				break;
			case 'VCMDASHSMBALACTRULES':
				$result = __('Smart Balancer Rules', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRSTATS':
				$result = __('View Bookings Statistics', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALBID':
				$result = __('Booking ID', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALBTS':
				$result = __('Booking Date', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALSAVEAM':
				$result = __('Rates Adjustment', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALBSTATUS':
				$result = __('Status', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALBFROMC':
				$result = __('From', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULENOTFOUND':
				$result = __('Rule not found. The rule may be expired and so it no longer exists.', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALSTATSTOTB':
				$result = __('Total Smart Balancer Bookings: %d', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALOPENWIZARD':
				$result = __('Open Smart Balancer Wizard', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARD':
				$result = __('Smart Balancer Wizard', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALDESCWIZARD':
				$result = __('The wizard will help you set up a Smart Balancer Rule to automatically adjust the rates on some national festivities. Let it guide you!', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDSUGGINCRATES':
				$result = __('Suggestion: you could Increase the rates for the last rooms by %s', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDSUGGDISCRATES':
				$result = __('Suggestion: you could Discount the rates by %s', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDTOTDAYS':
				$result = __('Total Days', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDTOTBOOKS':
				$result = __('Total Bookings', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDNBOOKED':
				$result = __('Nights Booked', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDGLOBOCC':
				$result = __('Global Occupancy', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZARDLOADOCC':
				$result = __('Loading occupancy for these dates', 'vikchannelmanager');
				break;
			case 'VCMFESTCHOOSEHELP':
				$result = __('Select a Region and a Festivity', 'vikchannelmanager');
				break;
			case 'VCMFESTDATESRECOMM':
				$result = __('Dates recommended to increase/decrease the Rates', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALWIZCOMPLETE':
				$result = __('Complete the Rule', 'vikchannelmanager');
				break;
			case 'VCMFESTCHOOSEREGION':
				$result = __('- Select a Region -', 'vikchannelmanager');
				break;
			case 'VCMFESTGLOBAL':
				$result = __('International Holidays', 'vikchannelmanager');
				break;
			case 'VCMFESTITA':
				$result = __('Holidays in Italy', 'vikchannelmanager');
				break;
			case 'VCMFESTFRA':
				$result = __('Holidays in France', 'vikchannelmanager');
				break;
			case 'VCMFESTGER':
				$result = __('Holidays in Germany', 'vikchannelmanager');
				break;
			case 'VCMFESTSPA':
				$result = __('Holidays in Spain', 'vikchannelmanager');
				break;
			case 'VCMFESTUSA':
				$result = __('Holidays in the USA', 'vikchannelmanager');
				break;
			case 'VCMFESTCHOOSEFEST':
				$result = __('- Select a Festivity -', 'vikchannelmanager');
				break;
			case 'VCMFESTNEWYEARSDAY':
				$result = __('New Year\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTEPIPHANY':
				$result = __('Epiphany', 'vikchannelmanager');
				break;
			case 'VCMFESTMLKDAY':
				$result = __('Martin Luther King\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTVALENTINESDAY':
				$result = __('Valentines Day', 'vikchannelmanager');
				break;
			case 'VCMFESTPRESIDENTSDAY':
				$result = __('President\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTROSENMONTAG':
				$result = __('Rosenmontag', 'vikchannelmanager');
				break;
			case 'VCMFESTMARDIGRAS':
				$result = __('Mardi Gras', 'vikchannelmanager');
				break;
			case 'VCMFESTEASTER':
				$result = __('Easter', 'vikchannelmanager');
				break;
			case 'VCMFESTENDOFWARITA':
				$result = __('End of the War', 'vikchannelmanager');
				break;
			case 'VCMFESTWALPURGISNIGHT':
				$result = __('Walpurgis Night', 'vikchannelmanager');
				break;
			case 'VCMFESTDAYOFWORK':
				$result = __('Worker\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTCINCOMAYO':
				$result = __('Cinco de Mayo', 'vikchannelmanager');
				break;
			case 'VCMFESTVEDAYFRA':
				$result = __('Victory Day', 'vikchannelmanager');
				break;
			case 'VCMFESTMEMORIALDAY':
				$result = __('Memorial\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTREPUBLICDAY':
				$result = __('Republic Day', 'vikchannelmanager');
				break;
			case 'VCMFEST4THJULY':
				$result = __('4th of July', 'vikchannelmanager');
				break;
			case 'VCMFESTBASTILLEDAY':
				$result = __('Bastille Day', 'vikchannelmanager');
				break;
			case 'VCMFESTFERRAGOSTO':
				$result = __('Assumption', 'vikchannelmanager');
				break;
			case 'VCMFESTLABORDAY':
				$result = __('Labor\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTCOLUMBUSDAY':
				$result = __('Columbus Day', 'vikchannelmanager');
				break;
			case 'VCMFESTHISPANITYDAY':
				$result = __('Hispanity Day', 'vikchannelmanager');
				break;
			case 'VCMFESTHALLOWEEN':
				$result = __('Halloween', 'vikchannelmanager');
				break;
			case 'VCMFESTSAINTSDAY':
				$result = __('All Saints Day', 'vikchannelmanager');
				break;
			case 'VCMFESTSOULSDAY':
				$result = __('All Souls Day', 'vikchannelmanager');
				break;
			case 'VCMFESTWALLOFBERLIN':
				$result = __('Wall of Berlin Day', 'vikchannelmanager');
				break;
			case 'VCMFESTARMISTICEDAY':
				$result = __('Armistice Day', 'vikchannelmanager');
				break;
			case 'VCMFESTVETERANSDAY':
				$result = __('Veteran\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTTHANKSGIVING':
				$result = __('Thanksgiving', 'vikchannelmanager');
				break;
			case 'VCMFESTIMMACOLATA':
				$result = __('Immaculate Conception', 'vikchannelmanager');
				break;
			case 'VCMFESTCHRISTMASEVE':
				$result = __('Christmas Eve', 'vikchannelmanager');
				break;
			case 'VCMFESTCHRISTMASDAY':
				$result = __('Christmas Day', 'vikchannelmanager');
				break;
			case 'VCMFESTSTSTEPHENSDAY':
				$result = __('St. Stephen\'s Day', 'vikchannelmanager');
				break;
			case 'VCMFESTNEWYEARSEVE':
				$result = __('New Year\'s Eve', 'vikchannelmanager');
				break;
			case 'DFNOW':
				$result = __('Just now', 'vikchannelmanager');
				break;
			case 'DFMINSAGO':
				$result = __('%d min. ago', 'vikchannelmanager');
				break;
			case 'DFMINSAFT':
				$result = __('in %d min.', 'vikchannelmanager');
				break;
			case 'DFHOURAGO':
				$result = __('1 hour ago', 'vikchannelmanager');
				break;
			case 'DFHOURAFT':
				$result = __('in 1 hour', 'vikchannelmanager');
				break;
			case 'DFHOURSAGO':
				$result = __('%d hours ago', 'vikchannelmanager');
				break;
			case 'DFHOURSAFT':
				$result = __('in %s hours', 'vikchannelmanager');
				break;
			case 'DFDAYAGO':
				$result = __('Yesterday', 'vikchannelmanager');
				break;
			case 'DFDAYAFT':
				$result = __('Tomorrow', 'vikchannelmanager');
				break;
			case 'DFDAYSAGO':
				$result = __('%d days ago', 'vikchannelmanager');
				break;
			case 'DFDAYSAFT':
				$result = __('in %d days', 'vikchannelmanager');
				break;
			case 'DFWEEKAGO':
				$result = __('one week ago', 'vikchannelmanager');
				break;
			case 'DFWEEKAFT':
				$result = __('in one week', 'vikchannelmanager');
				break;
			case 'DFWEEKSAGO':
				$result = __('%d weeks ago', 'vikchannelmanager');
				break;
			case 'DFWEEKSAFT':
				$result = __('in %d weeks', 'vikchannelmanager');
				break;
			case 'COM_VIKCHANNELMANAGER_CONFIGURATION':
				$result = __('Vik Channel Manager Configuration', 'vikchannelmanager');
				break;
			case 'VCM_PRICE_COMPARE_TAX':
				$result = __('Price Comparison', 'vikchannelmanager');
				break;
			case 'VCM_PRICE_COMPARE_TAX_EXCL':
				$result = __('Tax Exclusive', 'vikchannelmanager');
				break;
			case 'VCM_PRICE_COMPARE_TAX_INCL':
				$result = __('Tax Inclusive', 'vikchannelmanager');
				break;
			case 'VCM_TA_URL_TYPE':
				$result = __('URL Type', 'vikchannelmanager');
				break;
			case 'VCM_TA_URL_TYPE_ROOM':
				$result = __('Room Page', 'vikchannelmanager');
				break;
			case 'VCM_TA_URL_TYPE_SEARCH':
				$result = __('Search Page', 'vikchannelmanager');
				break;
			case 'VCM_TA_URL_TYPE_HELP':
				$result = __('Choose whether the TripAdvisor button links should redirect to the global search results page (Search) or to the single room details Page (Room)', 'vikchannelmanager');
				break;
			case 'VCM_TA_ACCEPTED_CC':
				$result = __('Accepted Credit Cards', 'vikchannelmanager');
				break;
			case 'VCM_TA_CC_MASTERCARD':
				$result = __('Master Card', 'vikchannelmanager');
				break;
			case 'VCM_TA_CC_VISA':
				$result = __('Visa', 'vikchannelmanager');
				break;
			case 'VCM_TA_CC_AMERICANEXPRESS':
				$result = __('American Express', 'vikchannelmanager');
				break;
			case 'VCM_TA_CC_DISCOVER':
				$result = __('Discover', 'vikchannelmanager');
				break;
			case 'VCM_TA_PAYMENT_STATUS':
				$result = __('Status after Payment', 'vikchannelmanager');
				break;
			case 'VCM_TA_PAYMENT_STATUS_CONFIRMED':
				$result = __('Confirmed', 'vikchannelmanager');
				break;
			case 'VCM_TA_PAYMENT_STATUS_PENDING':
				$result = __('Pending', 'vikchannelmanager');
				break;
			case 'VCM_TA_PAYMENT_POLICY':
				$result = __('Payment Policy', 'vikchannelmanager');
				break;
			case 'VCM_TA_PAYMENT_POLICY_HELP':
				$result = __('A short description (max 1000 characters) of your payment policy', 'vikchannelmanager');
				break;
			case 'VCM_TA_REFUNDS':
				$result = __('Refunds', 'vikchannelmanager');
				break;
			case 'VCM_TA_REFUNDS_NONE':
				$result = __('None', 'vikchannelmanager');
				break;
			case 'VCM_TA_REFUNDS_PARTIAL':
				$result = __('Partial', 'vikchannelmanager');
				break;
			case 'VCM_TA_REFUNDS_FULL':
				$result = __('Full', 'vikchannelmanager');
				break;
			case 'VCM_TA_CANCELLATION_POLICY':
				$result = __('Cancellation Policy', 'vikchannelmanager');
				break;
			case 'VCM_TA_CANCELLATION_POLICY_HELP':
				$result = __('A short description (max 1000 characters) of your cancellation policy', 'vikchannelmanager');
				break;
			case 'VCM_TA_CHECKINOUT_POLICY':
				$result = __('Checkin-out Policy', 'vikchannelmanager');
				break;
			case 'VCM_TA_CHECKINOUT_POLICY_HELP':
				$result = __('A short description (max 1000 characters) of your checkin/checkout policy', 'vikchannelmanager');
				break;
			case 'VCM_TA_TERMS_URL':
				$result = __('Terms & Conditions URL', 'vikchannelmanager');
				break;
			case 'VCM_TRI_STOP_CAMPAIGN':
				$result = __('CAMPAIGN STATUS', 'vikchannelmanager');
				break;
			case 'VCM_TRI_STOP_CAMPAIGN_FALSE':
				$result = __('ACTIVE', 'vikchannelmanager');
				break;
			case 'VCM_TRI_STOP_CAMPAIGN_TRUE':
				$result = __('PAUSED', 'vikchannelmanager');
				break;
			case 'VCM_TRI_STOP_CAMPAIGN_HELP':
				$result = __('Pause the Campaign to stop sending prices-responses. This will not generate any Clicks to your site on Trivago', 'vikchannelmanager');
				break;
			case 'VCM_TRI_URL_TYPE':
				$result = __('URL Type', 'vikchannelmanager');
				break;
			case 'VCM_TRI_URL_TYPE_ROOM':
				$result = __('Room Page', 'vikchannelmanager');
				break;
			case 'VCM_TRI_URL_TYPE_SEARCH':
				$result = __('Search Page', 'vikchannelmanager');
				break;
			case 'VCM_TRI_URL_TYPE_HELP':
				$result = __('Choose whether the Trivago button links should redirect to the global search results page (Search) or to the single room details Page (Room)', 'vikchannelmanager');
				break;
			case 'VCM_TRI_BREAKFAST_PRICE':
				$result = __('Breakfast Price', 'vikchannelmanager');
				break;
			case 'VCM_TRI_BREAKFAST_PRICE_HELP':
				$result = __('Leave the value 0 for this field if the breakfast is included.', 'vikchannelmanager');
				break;
			case 'VCM_TRI_CREDITCARD_REQUIRED':
				$result = __('Credit Cart Required', 'vikchannelmanager');
				break;
			case 'VCM_TRI_CREDITCARD_REQUIRED_HELP':
				$result = __('Set Yes, if the credit cart is required to complete an order on your website', 'vikchannelmanager');
				break;
			case 'VCM_TRI_MEAL_CODE':
				$result = __('Meal Code', 'vikchannelmanager');
				break;
			case 'VCM_TRI_MEAL_CODE_HELP':
				$result = __('Choose the type of stay and the services included', 'vikchannelmanager');
				break;
			case 'VCM_TRI_PAYMENT_TYPE':
				$result = __('Payment Type', 'vikchannelmanager');
				break;
			case 'VCM_TRI_PAYMENT_TYPE_HELP':
				$result = __('Choose if the payment is collected before the arrival, at the check-out or by OTA in several installments (widely used in Latin America)', 'vikchannelmanager');
				break;
			case 'ROOM_SINGLE':
				$result = __('Single', 'vikchannelmanager');
				break;
			case 'ROOM_QUEEN':
				$result = __('Queen', 'vikchannelmanager');
				break;
			case 'ROOM_2_QUEEN':
				$result = __('Double Queen', 'vikchannelmanager');
				break;
			case 'ROOM_KING':
				$result = __('King', 'vikchannelmanager');
				break;
			case 'ROOM_SUITE':
				$result = __('Suite', 'vikchannelmanager');
				break;
			case 'ROOM_SHARED':
				$result = __('Shared', 'vikchannelmanager');
				break;
			case 'ROOM_OTHER':
				$result = __('Other', 'vikchannelmanager');
				break;
			case 'ROOM_DOUBLE':
				$result = __('Double', 'vikchannelmanager');
				break;
			case 'ACTIVITIES_OLDER_CHILDREN':
				$result = __('Activities Older Children', 'vikchannelmanager');
				break;
			case 'ACTIVITIES_YOUNG_CHILDREN':
				$result = __('Activities Young Children', 'vikchannelmanager');
				break;
			case 'ADJOINING_ROOMS':
				$result = __('Ajoining Rooms', 'vikchannelmanager');
				break;
			case 'ALL_INCLUSIVE':
				$result = __('All Inclusive', 'vikchannelmanager');
				break;
			case 'ALL_SUITES':
				$result = __('All Suites', 'vikchannelmanager');
				break;
			case 'APARTMENTS':
				$result = __('Apartments', 'vikchannelmanager');
				break;
			case 'BAR_LOUNGE':
				$result = __('Bar Lounge', 'vikchannelmanager');
				break;
			case 'BATHROOMS':
				$result = __('Bathrooms', 'vikchannelmanager');
				break;
			case 'BEACH':
				$result = __('Beach', 'vikchannelmanager');
				break;
			case 'BED_AND_BREAKFAST':
				$result = __('Bed and Breakfast', 'vikchannelmanager');
				break;
			case 'BUSINESS_SERVICES':
				$result = __('Business Services', 'vikchannelmanager');
				break;
			case 'CAR_RENTAL_DESK':
				$result = __('Car Rental Desk', 'vikchannelmanager');
				break;
			case 'CASTLE':
				$result = __('Castle', 'vikchannelmanager');
				break;
			case 'CONVENTIONS':
				$result = __('Conventions', 'vikchannelmanager');
				break;
			case 'CREDIT_CARDS_ACCEPTED':
				$result = __('Credit Card Accepted', 'vikchannelmanager');
				break;
			case 'DATA_PORT':
				$result = __('Data Port', 'vikchannelmanager');
				break;
			case 'DINING':
				$result = __('Dining', 'vikchannelmanager');
				break;
			case 'DRY_CLEANING':
				$result = __('Dry Cleaning', 'vikchannelmanager');
				break;
			case 'EARLY_ARRIVAL':
				$result = __('Early Arrival', 'vikchannelmanager');
				break;
			case 'ECONOMY':
				$result = __('Economy', 'vikchannelmanager');
				break;
			case 'ELDER_ACCESS':
				$result = __('Elder Access', 'vikchannelmanager');
				break;
			case 'EXTENDED_STAY':
				$result = __('Extended Stay', 'vikchannelmanager');
				break;
			case 'FAMILY_ROOMS':
				$result = __('Family Rooms', 'vikchannelmanager');
				break;
			case 'FARM_RANCH':
				$result = __('Farm Ranch', 'vikchannelmanager');
				break;
			case 'FIRST_CLASS':
				$result = __('First Class', 'vikchannelmanager');
				break;
			case 'FITNESS_CENTER':
				$result = __('Fitness Center', 'vikchannelmanager');
				break;
			case 'FOOD_AVAILABLE':
				$result = __('Food Available', 'vikchannelmanager');
				break;
			case 'FREE_BREAKFAST':
				$result = __('Free Breakfast', 'vikchannelmanager');
				break;
			case 'FREE_CANCELATION':
				$result = __('Free Cancelation', 'vikchannelmanager');
				break;
			case 'FREE_INTERNET':
				$result = __('Free Internet', 'vikchannelmanager');
				break;
			case 'FREE_LOCAL_CALLS':
				$result = __('Free Local Calls', 'vikchannelmanager');
				break;
			case 'FREE_PARKING':
				$result = __('Free Parking', 'vikchannelmanager');
				break;
			case 'FREE_WIFI':
				$result = __('Free WI-FI', 'vikchannelmanager');
				break;
			case 'GAME_ROOM':
				$result = __('Game Room', 'vikchannelmanager');
				break;
			case 'GOLF':
				$result = __('Golf', 'vikchannelmanager');
				break;
			case 'HOT_TUB':
				$result = __('Hot Tub', 'vikchannelmanager');
				break;
			case 'KIDS_ACTIVITIES':
				$result = __('Kids Activities', 'vikchannelmanager');
				break;
			case 'KITCHEN_KITCHENETTE':
				$result = __('Kitchen-Kitchenette', 'vikchannelmanager');
				break;
			case 'KITCHENETTE':
				$result = __('Kitchenette', 'vikchannelmanager');
				break;
			case 'LATE_ARRIVAL':
				$result = __('Late Arrival', 'vikchannelmanager');
				break;
			case 'LATE_CHECK_OUT':
				$result = __('Late Check Out', 'vikchannelmanager');
				break;
			case 'LOCKERS_STORAGE':
				$result = __('Lockers Storage', 'vikchannelmanager');
				break;
			case 'LOYALTY_REWARDS_AVAILABLE':
				$result = __('Loyalty Rewards Available', 'vikchannelmanager');
				break;
			case 'LUXURY':
				$result = __('Luxury', 'vikchannelmanager');
				break;
			case 'MEALS_INCLUDED':
				$result = __('Meals Included', 'vikchannelmanager');
				break;
			case 'MEETING_ROOM':
				$result = __('Meeting Room', 'vikchannelmanager');
				break;
			case 'MOTEL':
				$result = __('Motel', 'vikchannelmanager');
				break;
			case 'NON_SMOKING':
				$result = __('No Smoking', 'vikchannelmanager');
				break;
			case 'PARKING_AVAILABLE':
				$result = __('Parking Available', 'vikchannelmanager');
				break;
			case 'PAID_PARKING':
				$result = __('Paid Parking', 'vikchannelmanager');
				break;
			case 'PETS_ALLOWED':
				$result = __('Pets Allowed', 'vikchannelmanager');
				break;
			case 'PRIVATE_BATH':
				$result = __('Private Bath', 'vikchannelmanager');
				break;
			case 'RESORT':
				$result = __('Resort', 'vikchannelmanager');
				break;
			case 'RESTAURANT':
				$result = __('Restaurant', 'vikchannelmanager');
				break;
			case 'ROOM_SERVICE':
				$result = __('Room Service', 'vikchannelmanager');
				break;
			case 'ROOM_WITH_A_VIEW':
				$result = __('Room with a View', 'vikchannelmanager');
				break;
			case 'SHARED_BATH':
				$result = __('Shared Bath', 'vikchannelmanager');
				break;
			case 'SHUTTLE':
				$result = __('Shuttle', 'vikchannelmanager');
				break;
			case 'STAIRS_ELEVATOR':
				$result = __('Stairs Elevator', 'vikchannelmanager');
				break;
			case 'STROLLER_PARKING':
				$result = __('Stroller Parking', 'vikchannelmanager');
				break;
			case 'SUITES':
				$result = __('Suites', 'vikchannelmanager');
				break;
			case 'SWIMMING_POOL':
				$result = __('Swimming Pool', 'vikchannelmanager');
				break;
			case 'TENNIS_COURT':
				$result = __('Tennis Court', 'vikchannelmanager');
				break;
			case 'VALET_PARKING':
				$result = __('Valet Parking', 'vikchannelmanager');
				break;
			case 'WHEELCHAIR_ACCESS':
				$result = __('Wheelchair Access', 'vikchannelmanager');
				break;
			case 'VCMMAINAIRBNBLISTINGS':
				$result = __('Airbnb - Properties', 'vikchannelmanager');
				break;
			case 'VCMMAINFLIPKEYLISTINGS':
				$result = __('Flipkey - Properties', 'vikchannelmanager');
				break;
			case 'VCMMAINHOLIDAYLETTINGSLISTINGS':
				$result = __('HolidayLettings - Properties', 'vikchannelmanager');
				break;
			case 'VCMMAINWIMDULISTINGS':
				$result = __('Wimdu - Properties', 'vikchannelmanager');
				break;
			case 'VCMMAINHOMEAWAYLISTINGS':
				$result = __('HomeAway - Properties', 'vikchannelmanager');
				break;
			case 'VCMMAINVRBOLISTINGS':
				$result = __('VRBO - Properties', 'vikchannelmanager');
				break;
			case 'VCM_ICS_SKIP_FAKE_BOOKINGS':
				$result = __('Skip Fake Bookings', 'vikchannelmanager');
				break;
			case 'VCMLISTINGLABELDOWNLOADURL':
				$result = __('E4jConnect Download URL', 'vikchannelmanager');
				break;
			case 'VCMLISTINGLABELRETRIEVALURL':
				$result = __('%s Retrieval URL', 'vikchannelmanager');
				break;
			case 'VCMLISTINGSUPDATED':
				$result = __('The properties have been successfully updated.', 'vikchannelmanager');
				break;
			case 'VCMLISTINGSTATUSOK':
				$result = __('The property is ready for SYNC.', 'vikchannelmanager');
				break;
			case 'VCMLISTINGSTATUSBAD':
				$result = __('The URL provided is empty or invalid. The bookings for this property won\'t be updated.', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPAIRBNB':
				$result = __('Copy this link onto the property page of your Airbnb Account', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPAIRBNB':
				$result = __('Insert the Sync URL provided by Airbnb for updating this property', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPFLIPKEY':
				$result = __('Copy this link onto the property page of your Flipkey Account', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPFLIPKEY':
				$result = __('Insert the Sync URL provided by Flipkey for updating this property', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPHOLIDAYLETTINGS':
				$result = __('Copy this link onto the property page of your HolidayLettings Account', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPHOLIDAYLETTINGS':
				$result = __('Insert the Sync URL provided by HolidayLettings for updating this property', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPWIMDU':
				$result = __('Copy this link onto the property page of your Wimdu Account', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPWIMDU':
				$result = __('Insert the Sync URL provided by Wimdu for updating this property', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPHOMEAWAY':
				$result = __('Copy this link onto the property page of your HomeAway Account', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPHOMEAWAY':
				$result = __('Insert the Sync URL provided by HomeAway for updating this property', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPVRBO':
				$result = __('Copy this link onto the property page of your VRBO Account', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPVRBO':
				$result = __('Insert the Sync URL provided by VRBO for updating this property', 'vikchannelmanager');
				break;
			case 'VCMNOCUSTOMAMODS':
				$result = __('Error: no modifications to the availability detected.', 'vikchannelmanager');
				break;
			case 'VCMOSDIALOGTITLE':
				$result = __('Modify or Align Channels Availability', 'vikchannelmanager');
				break;
			case 'VCMOSDIALOGTITLE2':
				$result = __('Modify or Align Channels Rates', 'vikchannelmanager');
				break;
			case 'VCMOSDIALOGAPPLYAVBUTTON':
				$result = __('Apply Current Availability', 'vikchannelmanager');
				break;
			case 'VCMOSDIALOGAPPLYRATESBUTTON':
				$result = __('Apply Current Rates', 'vikchannelmanager');
				break;
			case 'VCMOSDIALOGAPPLYBUTTON':
				$result = __('Apply', 'vikchannelmanager');
				break;
			case 'VCMOSDIALOGCANCBUTTON':
				$result = __('Cancel', 'vikchannelmanager');
				break;
			case 'VCMOSTAB1':
				$result = __('Availability', 'vikchannelmanager');
				break;
			case 'VCMOSTAB2':
				$result = __('Rates - Restrictions', 'vikchannelmanager');
				break;
			case 'VCMOSFROMDATE':
				$result = __('From:', 'vikchannelmanager');
				break;
			case 'VCMOSTODATE':
				$result = __('To:', 'vikchannelmanager');
				break;
			case 'VCMOSSINGDATE':
				$result = __('Day:', 'vikchannelmanager');
				break;
			case 'VCMOSUNITSONDATE':
				$result = __('Units:', 'vikchannelmanager');
				break;
			case 'VCMOSUNDEFINEDROOM':
				$result = __('Undefined Room', 'vikchannelmanager');
				break;
			case 'VCMOSUNDEFINEDDESC':
				$result = __('This room will be ignored. It is not assigned to any room of your channels.', 'vikchannelmanager');
				break;
			case 'VCMOSCLOSEUNITS':
				$result = __('Lower the Availability by:', 'vikchannelmanager');
				break;
			case 'VCMOSOPENUNITS':
				$result = __('Increase the Availability by:', 'vikchannelmanager');
				break;
			case 'VCMOSUNITSLABEL':
				$result = __('units', 'vikchannelmanager');
				break;
			case 'VCMOSCLOSEALL':
				$result = __('Close all units', 'vikchannelmanager');
				break;
			case 'VCMOSOPENALL':
				$result = __('Open all units', 'vikchannelmanager');
				break;
			case 'VCMOSCHECKALL':
				$result = __('Check All', 'vikchannelmanager');
				break;
			case 'VCMOSUNCHECKALL':
				$result = __('Uncheck All', 'vikchannelmanager');
				break;
			case 'VCMOSINCREASERATES':
				$result = __('Increase', 'vikchannelmanager');
				break;
			case 'VCMOSDECREASERATES':
				$result = __('Decrease', 'vikchannelmanager');
				break;
			case 'VCMOSSYNCRATESBY':
				$result = __('rates by', 'vikchannelmanager');
				break;
			case 'VCMUPDATEVBOBOOKINGS':
				$result = __('Website/IBE', 'vikchannelmanager');
				break;
			case 'VCMDESCRORDVBO':
				$result = __('Booking Generated by VCM', 'vikchannelmanager');
				break;
			case 'VCMVBORDERFROMVCM':
				$result = __('Channel Manager', 'vikchannelmanager');
				break;
			case 'VCMOSSETNEWRATE':
				$result = __('Set New Rate', 'vikchannelmanager');
				break;
			case 'VCMOSSETRESTR':
				$result = __('Set Restrictions', 'vikchannelmanager');
				break;
			case 'VCMJQCALDONE':
				$result = __('Done', 'vikchannelmanager');
				break;
			case 'VCMJQCALPREV':
				$result = __('Prev', 'vikchannelmanager');
				break;
			case 'VCMJQCALNEXT':
				$result = __('Next', 'vikchannelmanager');
				break;
			case 'VCMJQCALTODAY':
				$result = __('Today', 'vikchannelmanager');
				break;
			case 'VCMJQCALSUN':
				$result = __('Sunday', 'vikchannelmanager');
				break;
			case 'VCMJQCALMON':
				$result = __('Monday', 'vikchannelmanager');
				break;
			case 'VCMJQCALTUE':
				$result = __('Tuesday', 'vikchannelmanager');
				break;
			case 'VCMJQCALWED':
				$result = __('Wednesday', 'vikchannelmanager');
				break;
			case 'VCMJQCALTHU':
				$result = __('Thursday', 'vikchannelmanager');
				break;
			case 'VCMJQCALFRI':
				$result = __('Friday', 'vikchannelmanager');
				break;
			case 'VCMJQCALSAT':
				$result = __('Saturday', 'vikchannelmanager');
				break;
			case 'VCMJQCALWKHEADER':
				$result = __('Wk', 'vikchannelmanager');
				break;
			case 'VCMMONTHONE':
				$result = __('January', 'vikchannelmanager');
				break;
			case 'VCMMONTHTWO':
				$result = __('February', 'vikchannelmanager');
				break;
			case 'VCMMONTHTHREE':
				$result = __('March', 'vikchannelmanager');
				break;
			case 'VCMMONTHFOUR':
				$result = __('April', 'vikchannelmanager');
				break;
			case 'VCMMONTHFIVE':
				$result = __('May', 'vikchannelmanager');
				break;
			case 'VCMMONTHSIX':
				$result = __('June', 'vikchannelmanager');
				break;
			case 'VCMMONTHSEVEN':
				$result = __('July', 'vikchannelmanager');
				break;
			case 'VCMMONTHEIGHT':
				$result = __('August', 'vikchannelmanager');
				break;
			case 'VCMMONTHNINE':
				$result = __('September', 'vikchannelmanager');
				break;
			case 'VCMMONTHTEN':
				$result = __('October', 'vikchannelmanager');
				break;
			case 'VCMMONTHELEVEN':
				$result = __('November', 'vikchannelmanager');
				break;
			case 'VCMMONTHTWELVE':
				$result = __('December', 'vikchannelmanager');
				break;
			case 'VCMJQFIRSTDAY':
				$result = __('1', 'vikchannelmanager');
				break;
			case 'VCMDOUPDATEPERMISSIONERR':
				$result = __('Impossible to write files on your server! Please check the file permissions.', 'vikchannelmanager');
				break;
			case 'VCMDOUPDATEUNZIPERROR':
				$result = __('Impossible to unzip the updater!', 'vikchannelmanager');
				break;
			case 'VCMDOUPDATEPACKAGENOTFOUND':
				$result = __('The installer package doesn\'t exist on your server.', 'vikchannelmanager');
				break;
			case 'VCMDOUPDATECOMPLETED':
				$result = __('The Update has been installed Successfully!', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFO':
				$result = __('Contact Info', 'vikchannelmanager');
				break;
			case 'VCMBCAHHINFO':
				$result = __('Hotel Info', 'vikchannelmanager');
				break;
			case 'VCMBCAHFINFO':
				$result = __('Facility Info', 'vikchannelmanager');
				break;
			case 'VCMBCAHAINFO':
				$result = __('Area Info', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLICIES':
				$result = __('Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHMULTIMEDIA':
				$result = __('Multimedia', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE':
				$result = __('Contact Info Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHHINFODET':
				$result = __('Hotel Info Details', 'vikchannelmanager');
				break;
			case 'VCMBCAHFINFODET':
				$result = __('Facility Info Details', 'vikchannelmanager');
				break;
			case 'VCMBCAHAINFODET':
				$result = __('Area Info Details', 'vikchannelmanager');
				break;
			case 'VCMBCAHPEOPLE':
				$result = __('People', 'vikchannelmanager');
				break;
			case 'VCMBCAHADDRESSES':
				$result = __('Addresses', 'vikchannelmanager');
				break;
			case 'VCMBCAHADDRESS':
				$result = __('Address', 'vikchannelmanager');
				break;
			case 'VCMBCAHEMAILS':
				$result = __('Emails', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONES':
				$result = __('Phones', 'vikchannelmanager');
				break;
			case 'VCMBCAHFIRSTNAME':
				$result = __('First Name', 'vikchannelmanager');
				break;
			case 'VCMBCAHSURNAME':
				$result = __('Surname', 'vikchannelmanager');
				break;
			case 'VCMBCAHGENDER':
				$result = __('Gender', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOBTITLE':
				$result = __('Job Title', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANGUAGE':
				$result = __('Language', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANGUAGES':
				$result = __('Languages', 'vikchannelmanager');
				break;
			case 'VCMBCAHMALE':
				$result = __('Male', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEMALE':
				$result = __('Female', 'vikchannelmanager');
				break;
			case 'VCMBCAHOTHER':
				$result = __('Other', 'vikchannelmanager');
				break;
			case 'VCMBCAHSTATEPROV':
				$result = __('State/Province Code', 'vikchannelmanager');
				break;
			case 'VCMBCAHCITYNAME':
				$result = __('City Name', 'vikchannelmanager');
				break;
			case 'VCMBCAHADDLINE':
				$result = __('Address Line', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOSCODE':
				$result = __('Postal Code', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTELNAME':
				$result = __('Hotel Name', 'vikchannelmanager');
				break;
			case 'VCMBCAHCOUNTRY':
				$result = __('Country', 'vikchannelmanager');
				break;
			case 'VCMBCAHEMAIL':
				$result = __('Email', 'vikchannelmanager');
				break;
			case 'VCMBCAHDELETE':
				$result = __('Delete', 'vikchannelmanager');
				break;
			case 'VCMBCAHADD':
				$result = __('Add', 'vikchannelmanager');
				break;
			case 'VCMBCAHSUBMIT':
				$result = __('Submit', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONE':
				$result = __('Phone', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONENUMB':
				$result = __('Phone Number', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONETYPE':
				$result = __('Phone Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONEEXT':
				$result = __('Phone Extension', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERVICE':
				$result = __('Service', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERVICES':
				$result = __('Services', 'vikchannelmanager');
				break;
			case 'VCMBCAHLEGALID':
				$result = __('Legal ID', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERVTYPE':
				$result = __('Service Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERVINCL':
				$result = __('Service Included?', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFPRICE':
				$result = __('Breakfast Price', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE':
				$result = __('Breakfast Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENITY':
				$result = __('Amenity', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENITIES':
				$result = __('Amenities', 'vikchannelmanager');
				break;
			case 'VCMBCAHQUANTITY':
				$result = __('Quantity', 'vikchannelmanager');
				break;
			case 'VCMBCAHGUAPAYPOL':
				$result = __('Guaranteed Payment Policy', 'vikchannelmanager');
				break;
			case 'VCMBCAHGUAPAYPOLS':
				$result = __('Guaranteed Payment Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHCANCPOL':
				$result = __('Cancel Policy', 'vikchannelmanager');
				break;
			case 'VCMBCAHCANCPOLS':
				$result = __('Cancel Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPE':
				$result = __('Upload Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAX':
				$result = __('Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXES':
				$result = __('Taxes', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE':
				$result = __('Tax Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMOUNT':
				$result = __('Amount', 'vikchannelmanager');
				break;
			case 'VCMBCAHDECIMALPLACES':
				$result = __('Decimal Places', 'vikchannelmanager');
				break;
			case 'VCMBCAHPRICETYPE':
				$result = __('Price Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHGFRQ':
				$result = __('Charge Frequency', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEE':
				$result = __('Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEES':
				$result = __('Fees', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE':
				$result = __('Fee Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH':
				$result = __('Payment Method', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETHS':
				$result = __('Payment Methods', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTRACTION':
				$result = __('Attraction', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTRACTIONS':
				$result = __('Attractions', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTNAME':
				$result = __('Attraction Name', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE':
				$result = __('Attraction Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHDISTANCE':
				$result = __('Distance', 'vikchannelmanager');
				break;
			case 'VCMBCAHDISTMSR':
				$result = __('Distance Measure', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGURL':
				$result = __('Image URL', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG':
				$result = __('Image Tag', 'vikchannelmanager');
				break;
			case 'VCMBCAHSORTORD':
				$result = __('Sorting Order', 'vikchannelmanager');
				break;
			case 'VCMBCAHMAINIMAGE':
				$result = __('Main Image', 'vikchannelmanager');
				break;
			case 'VCMBCAHSHHIDETAILS':
				$result = __('Show/Hide Details', 'vikchannelmanager');
				break;
			case 'VCMBCAHGROOMQ':
				$result = __('Guest Room Quantity', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTCAT':
				$result = __('Hotel Categories', 'vikchannelmanager');
				break;
			case 'VCMBCAHEXISTS':
				$result = __('Exists?', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE':
				$result = __('Hotel Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOSITION':
				$result = __('Position', 'vikchannelmanager');
				break;
			case 'VCMBCAHLATITUDE':
				$result = __('Latitude', 'vikchannelmanager');
				break;
			case 'VCMBCAHLONGITUDE':
				$result = __('Longitude', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHECKINFROM':
				$result = __('Check-in from:', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHECKOUTFROM':
				$result = __('Check-out from:', 'vikchannelmanager');
				break;
			case 'VCMBCAHTO':
				$result = __('to', 'vikchannelmanager');
				break;
			case 'VCMBCAHKIDSPOLI':
				$result = __('Kids Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHKIDSFREE':
				$result = __('Kids Stay Free', 'vikchannelmanager');
				break;
			case 'VCMBCAHKIDSCUTOFF':
				$result = __('Usually Stay Free Cutoff Age', 'vikchannelmanager');
				break;
			case 'VCMBCAHKIDPERADULT':
				$result = __('Usually Stay Free Child Per Adult', 'vikchannelmanager');
				break;
			case 'VCMBCAHPETSPOLI':
				$result = __('Pets Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHPETSENTRANCE':
				$result = __('Pets Entrance Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHPETSALLOW':
				$result = __('Pets Allowed', 'vikchannelmanager');
				break;
			case 'VCMBCAHPETSNALLOW':
				$result = __('Pets Not Allowed', 'vikchannelmanager');
				break;
			case 'VCMBCAHPETSNARRANGE':
				$result = __('Pets By Arrangements', 'vikchannelmanager');
				break;
			case 'VCMBCAHNONREFFEE':
				$result = __('Non Refundable Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADDESC':
				$result = __('Upload your image...', 'vikchannelmanager');
				break;
			case 'VCMBCAHTIMEPOL':
				$result = __('Time Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAHPROPLICNUM':
				$result = __('Property License Number', 'vikchannelmanager');
				break;
			case 'VCMBCAHPROPLICNUMPPVT':
				$result = __('What\'s my Property License Number?', 'vikchannelmanager');
				break;
			case 'VCMBCAHPROPLICNUMPPVC':
				$result = __('This is a number provided by your state that is required for some types of properties in certain nations, such as Greece and some regions of Italy and Spain. Please consult with your account manager to know if this number is required.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONETYPE1':
				$result = __('Voice', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONETYPE2':
				$result = __('Fax', 'vikchannelmanager');
				break;
			case 'VCMBCAHPHONETYPE3':
				$result = __('Mobile', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPEDESC1':
				$result = __('All - Replaces all hotel photos with the new ones (this is the default setting)', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPEDESC2':
				$result = __('Manual - Replaces only the photos uploaded by the hotel itself, and leaves the ones uploaded by Content API', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPEDESC3':
				$result = __('ContentAPI - Replaces only the photos uploaded by the Content API and leaves the ones uploaded by the hotel itself', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPE1':
				$result = __('All', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPE2':
				$result = __('Manual', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPLOADTYPE3':
				$result = __('ContentAPI', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE1':
				$result = __('Apartment', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE2':
				$result = __('Bed and Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE3':
				$result = __('Cabin or bungalow (Holiday Home)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE4':
				$result = __('Campground (Camping)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE5':
				$result = __('Chalet', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE6':
				$result = __('Condominium (Holiday Home)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE7':
				$result = __('Cruise (Boat)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE8':
				$result = __('Ferry (Boat)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE9':
				$result = __('Guest Farm (Farm Stay)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE10':
				$result = __('Guest House Limited Service (Guest House)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE11':
				$result = __('Holiday Resort (Resort)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE12':
				$result = __('Hostel', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE13':
				$result = __('Hotel', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE14':
				$result = __('Inn', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE15':
				$result = __('Lodge', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE16':
				$result = __('Meeting Resort (Resort)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE17':
				$result = __('Mobile-Home (Holiday Home)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE18':
				$result = __('Monastery (Homestay)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE19':
				$result = __('Motel', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE20':
				$result = __('Ranch (Farm Stay)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE21':
				$result = __('Residential Apartment (Apartment)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE22':
				$result = __('Resort (Resort)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE23':
				$result = __('Sailing Ship (Boat)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE24':
				$result = __('Self Catering Accomodation (Apartment)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE25':
				$result = __('Tent (Tented)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE26':
				$result = __('Vacation Home (Holiday Home)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE27':
				$result = __('Villa (Villa)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE28':
				$result = __('Wildlife Reserve (Resort)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE29':
				$result = __('Castle (Holiday Home)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE30':
				$result = __('Pension (Guest House)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE31':
				$result = __('Boatel (Boat)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE32':
				$result = __('Boutique (Hotel)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE33':
				$result = __('Studio (Apartment)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE34':
				$result = __('Recreational Vehicle Park (Camping)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE35':
				$result = __('Charm Hotel (Hotel)', 'vikchannelmanager');
				break;
			case 'VCMBCAHHOTTYPE36':
				$result = __('Manor (Holiday Home)', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE1':
				$result = __('General', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE2':
				$result = __('Contract', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE3':
				$result = __('Reservations', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE4':
				$result = __('Invoices', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE5':
				$result = __('Availability', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE6':
				$result = __('Site Content', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE7':
				$result = __('Pricing', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE8':
				$result = __('Special Requests', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE9':
				$result = __('Central Reservations', 'vikchannelmanager');
				break;
			case 'VCMBCAHCINFOTYPE10':
				$result = __('Physical Location', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG1':
				$result = __('Shower', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG2':
				$result = __('Toilet', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG3':
				$result = __('Property Building', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG4':
				$result = __('Patio', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG5':
				$result = __('Nearby Landmark', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG6':
				$result = __('Staff', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG7':
				$result = __('Restaurant/places to eat', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG8':
				$result = __('Communal lounge/ TV room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG9':
				$result = __('Facade/entrance', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG10':
				$result = __('Spring', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG11':
				$result = __('Bed', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG12':
				$result = __('Off Site', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG13':
				$result = __('Food close-up', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG14':
				$result = __('Day', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG15':
				$result = __('Night', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG16':
				$result = __('People', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG17':
				$result = __('Property logo or sign', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG18':
				$result = __('Neighbourhood', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG19':
				$result = __('Natural landscape', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG20':
				$result = __('Activities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG21':
				$result = __('Bird\'s eye view', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG22':
				$result = __('Winter', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG23':
				$result = __('Summer', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG24':
				$result = __('BBQ facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG25':
				$result = __('Billiard', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG26':
				$result = __('Bowling', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG27':
				$result = __('Casino', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG28':
				$result = __('Place of worship', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG29':
				$result = __('Children play ground', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG30':
				$result = __('Darts', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG31':
				$result = __('Fishing', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG32':
				$result = __('Game Room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG33':
				$result = __('Garden', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG34':
				$result = __('Golfcourse', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG35':
				$result = __('Horse-riding', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG36':
				$result = __('Hot Spring Bath', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG37':
				$result = __('Hot Tub', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG38':
				$result = __('Karaoke', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG39':
				$result = __('Library', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG40':
				$result = __('Massage', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG41':
				$result = __('Minigolf', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG42':
				$result = __('Nightclub/DJ', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG43':
				$result = __('Sauna', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG44':
				$result = __('On-site shops', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG45':
				$result = __('Ski School', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG46':
				$result = __('Skiing', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG47':
				$result = __('Snorkeling', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG48':
				$result = __('Solarium', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG49':
				$result = __('Squash', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG50':
				$result = __('Table Tennis', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG51':
				$result = __('Steam Room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG52':
				$result = __('Bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG53':
				$result = __('TV and Multimedia', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG54':
				$result = __('Coffe/Tea facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG55':
				$result = __('View (from property/room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG56':
				$result = __('Balcony/Terrace', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG57':
				$result = __('Kitchen or kitchenette', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG58':
				$result = __('Living room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG59':
				$result = __('Lobby or reception', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG60':
				$result = __('Lounge or bar', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG61':
				$result = __('Spa and wellness centre/facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG62':
				$result = __('Fitness centre/facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG63':
				$result = __('Food and drinks', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG64':
				$result = __('Other', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG65':
				$result = __('Photo of the whole room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG66':
				$result = __('Business facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG67':
				$result = __('Banquet/function facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG68':
				$result = __('Decorative detail', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG69':
				$result = __('Seating area', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG70':
				$result = __('Floor plan', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG71':
				$result = __('Dining area', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG72':
				$result = __('Beach', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG73':
				$result = __('Aqua Park', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG74':
				$result = __('Tennis Court', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG75':
				$result = __('Windsurfing', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG76':
				$result = __('Canoeing', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG77':
				$result = __('Hiking', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG78':
				$result = __('Cycling', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG79':
				$result = __('Diving', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG80':
				$result = __('Kid\'s club', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG81':
				$result = __('Evening entertainment', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG82':
				$result = __('Logo/Certificate/Sign', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG83':
				$result = __('Animals', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG84':
				$result = __('Bedroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG85':
				$result = __('Communal Kitchen', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG86':
				$result = __('Autumn', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG87':
				$result = __('On Site', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG88':
				$result = __('Meeting/conference room', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG89':
				$result = __('Food', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG90':
				$result = __('Text overlay', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG91':
				$result = __('Pets', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG92':
				$result = __('Guests', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG93':
				$result = __('City View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG94':
				$result = __('Garden View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG95':
				$result = __('Lake View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG96':
				$result = __('Landmark View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG97':
				$result = __('Mountain View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG98':
				$result = __('Pool View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG99':
				$result = __('River View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG100':
				$result = __('Sea View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG101':
				$result = __('Street View', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG102':
				$result = __('Area and facilities', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG103':
				$result = __('Supermarket/grocery shop', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG104':
				$result = __('Shopping Area', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG105':
				$result = __('Swimming Pool', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG106':
				$result = __('Sports', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG107':
				$result = __('Entertainment', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG108':
				$result = __('Meals', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG109':
				$result = __('Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG110':
				$result = __('Continental Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG111':
				$result = __('Buffet Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG112':
				$result = __('Asian Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG113':
				$result = __('Italian Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG114':
				$result = __('English/Irish Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG115':
				$result = __('American Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG116':
				$result = __('Lunch', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG117':
				$result = __('Dinner', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG118':
				$result = __('Drinks', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG119':
				$result = __('Alcoholic Drinks', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG120':
				$result = __('Non Alcoholic Drinks', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG121':
				$result = __('Seasons', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG122':
				$result = __('Time of Day', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG123':
				$result = __('Location', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG124':
				$result = __('Sunrise', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG125':
				$result = __('Sunset', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG126':
				$result = __('Children', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG127':
				$result = __('Young Children', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG128':
				$result = __('Older Children', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG129':
				$result = __('Group of Guests', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG130':
				$result = __('Cot', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG131':
				$result = __('Bunk Bed', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG132':
				$result = __('Certificate/Award', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG133':
				$result = __('ADAM', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG134':
				$result = __('Open Air Bath', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG135':
				$result = __('Public Bath', 'vikchannelmanager');
				break;
			case 'VCMBCAHIMGTAG136':
				$result = __('Family', 'vikchannelmanager');
				break;
			case 'VCMBCAHDISTMSR1':
				$result = __('Miles', 'vikchannelmanager');
				break;
			case 'VCMBCAHDISTMSR2':
				$result = __('Meters', 'vikchannelmanager');
				break;
			case 'VCMBCAHDISTMSR3':
				$result = __('Kilometers', 'vikchannelmanager');
				break;
			case 'VCMBCAHDISTMSR4':
				$result = __('Feet', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE1':
				$result = __('Beach', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE2':
				$result = __('Lake', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE3':
				$result = __('Market', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE4':
				$result = __('Mountain', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE5':
				$result = __('Sea/Ocean', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE6':
				$result = __('Restaurant', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE7':
				$result = __('River', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE8':
				$result = __('Skilift', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE9':
				$result = __('General Supplies', 'vikchannelmanager');
				break;
			case 'VCMBCAHATTTYPE10':
				$result = __('Cafe Bar', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH1':
				$result = __('American Express', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH2':
				$result = __('Visa', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH3':
				$result = __('Euro/MasterCard', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH4':
				$result = __('Carte Bleue', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH5':
				$result = __('Diners Club', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH6':
				$result = __('JCB', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH7':
				$result = __('PIN', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH8':
				$result = __('Red 6000', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH9':
				$result = __('Maestro', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH10':
				$result = __('Discover', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH11':
				$result = __('Bancontact', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH12':
				$result = __('Solo', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH13':
				$result = __('Switch', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH14':
				$result = __('Carte Blanche', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH15':
				$result = __('NICOS', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH16':
				$result = __('UC', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH17':
				$result = __('No Credit Cards Accepted, Only Cash', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH18':
				$result = __('Bankcard', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH19':
				$result = __('CartaSi', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH20':
				$result = __('Argencard', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH21':
				$result = __('Cabak', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH22':
				$result = __('Red Compra', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH23':
				$result = __('Other Cards', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH24':
				$result = __('Greatwall', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH25':
				$result = __('Peony', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH26':
				$result = __('Dragon', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH27':
				$result = __('Pacific', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH28':
				$result = __('Jin Sui', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH29':
				$result = __('Eftpos', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH30':
				$result = __('Hipercard', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH31':
				$result = __('UnionPay debit card', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH32':
				$result = __('UnionPay credit card', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH33':
				$result = __('EC-Card', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH34':
				$result = __('BC-Card', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH35':
				$result = __('Booking virtual card (MasterCard', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH36':
				$result = __('MasterCard Google Wallet', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH37':
				$result = __('KH Széchényi Pihenõkártya', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH38':
				$result = __('MKB Széchényi Pihenõkártya', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH39':
				$result = __('OTP Széchényi Pihenõkártya', 'vikchannelmanager');
				break;
			case 'VCMBCAHPAYMETH40':
				$result = __('UnionPay Credit Card', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE1':
				$result = __('Resort Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE2':
				$result = __('Service Charge', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE3':
				$result = __('Tourism Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE4':
				$result = __('Destination Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE5':
				$result = __('Enviroment Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE6':
				$result = __('Municipality Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE7':
				$result = __('Public Transit Day Ticket', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE8':
				$result = __('Heritage Charge', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE9':
				$result = __('Cleaning Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE10':
				$result = __('Towel Charge', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE11':
				$result = __('Electricity Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE12':
				$result = __('Bed linen Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE13':
				$result = __('Gas Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE14':
				$result = __('Oil Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE15':
				$result = __('Wood Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE16':
				$result = __('Water Usage Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE17':
				$result = __('Transfer Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE18':
				$result = __('Linen Package Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE19':
				$result = __('Heating Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE20':
				$result = __('Air Conditioning Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE21':
				$result = __('Kitchen Linen Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE22':
				$result = __('Housekeeping Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE23':
				$result = __('Airport Shuttle Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE24':
				$result = __('Shuttle Boat Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE25':
				$result = __('Sea Plane Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE26':
				$result = __('Ski Pass', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE27':
				$result = __('Final Cleaning Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE28':
				$result = __('Wristband Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE29':
				$result = __('Visa Support Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE30':
				$result = __('Water Park Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE31':
				$result = __('Club Card Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE32':
				$result = __('Conservation Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE33':
				$result = __('Credit Card Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE34':
				$result = __('Pet Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE35':
				$result = __('Internet Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHFEETYPE36':
				$result = __('Parking Fee', 'vikchannelmanager');
				break;
			case 'VCMBCAHINCLUS':
				$result = __('Inclusive', 'vikchannelmanager');
				break;
			case 'VCMBCAHEXCLUS':
				$result = __('Exclusive', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHGFRQ1':
				$result = __('Per stay', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHGFRQ2':
				$result = __('Per room per night', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHGFRQ3':
				$result = __('Per person per stay', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHGFRQ4':
				$result = __('Per person per night', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHGFRQ5':
				$result = __('Applicable, charges may vary', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE1':
				$result = __('City Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE2':
				$result = __('TAX', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE3':
				$result = __('Goods And Services Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE4':
				$result = __('VAT (Value Added Tax)', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE5':
				$result = __('Government Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE6':
				$result = __('Spa Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE7':
				$result = __('Hot Spring Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE8':
				$result = __('Residential Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE9':
				$result = __('Sauna/fitness Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHTAXTYPE10':
				$result = __('Local Council Tax', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE1':
				$result = __('The guest will be charged the total price if they cancel.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE2':
				$result = __('The guest can cancel free of charge until 42 days before arrival. The guest will be charged the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE3':
				$result = __('The guest can cancel free of charge until 14 days before arrival. The guest will be charged the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE4':
				$result = __('The guest can cancel free of charge until 7 days before arrival. The guest will be charged the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE5':
				$result = __('The guest can cancel free of charge until 5 days before arrival. The guest will be charged the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE6':
				$result = __('The guest can cancel free of charge until 2 days before arrival. The guest will be charged the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE7':
				$result = __('The guest can cancel free of charge until 14 days before arrival. The guest will be charged the first night if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE8':
				$result = __('The guest can cancel free of charge until 3 days before arrival. The guest will be charged the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE9':
				$result = __('The guest can cancel free of charge until 3 days before arrival. The guest will be charged the first night if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE10':
				$result = __('The guest can cancel free of charge until 2 days before arrival. The guest will be charged the first night if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE11':
				$result = __('The guest can cancel free of charge until 1 day before arrival. The guest will be charged the first night if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE12':
				$result = __('The guest can cancel free of charge until 7 days before arrival. The guest will be charged the first night if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE13':
				$result = __('The guest can cancel free of charge until 1 day before arrival. The guest will be charged the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE14':
				$result = __('The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged the total price if they cancel after 6 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE15':
				$result = __('The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged the first night if they cancel after 6 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE16':
				$result = __('The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged the first night if they cancel after 2 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE17':
				$result = __('The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged 30% of the total price if they cancel after 2 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE18':
				$result = __('The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged 50% of the total price if they cancel after 2 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE19':
				$result = __('The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged 70% of the total price if they cancel after 2 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE20':
				$result = __('The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged the total price if they cancel after 2 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE21':
				$result = __('The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged 30% of the total price if they cancel after 6 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE22':
				$result = __('The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged 50% of the total price if they cancel after 6 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE23':
				$result = __('The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged 70% of the total price if they cancel after 6 pm on the day of arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE24':
				$result = __('The guest can cancel free of charge until 1 day before arrival. The guest will be charged 30% of the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE25':
				$result = __('The guest can cancel free of charge until 1 day before arrival. The guest will be charged 50% of the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE26':
				$result = __('The guest can cancel free of charge until 1 day before arrival. The guest will be charged 70% of the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE27':
				$result = __('The guest can cancel free of charge until 2 days before arrival. The guest will be charged 30% of the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE28':
				$result = __('The guest can cancel free of charge until 2 days before arrival. The guest will be charged 50% of the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE29':
				$result = __('The guest can cancel free of charge until 2 days before arrival. The guest will be charged 70% of the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE30':
				$result = __('The guest can cancel free of charge until 3 days before arrival. The guest will be charged 30% of the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE31':
				$result = __('The guest can cancel free of charge until 3 days before arrival. The guest will be charged 50% of the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE32':
				$result = __('The guest can cancel free of charge until 3 days before arrival. The guest will be charged 70% of the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE33':
				$result = __('The guest can cancel free of charge until 5 days before arrival. The guest will be charged the first night if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE34':
				$result = __('The guest can cancel free of charge until 5 days before arrival. The guest will be charged 30% of the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE35':
				$result = __('The guest can cancel free of charge until 5 days before arrival. The guest will be charged 50% of the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE36':
				$result = __('The guest can cancel free of charge until 5 days before arrival. The guest will be charged 70% of the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE37':
				$result = __('The guest can cancel free of charge until 7 days before arrival. The guest will be charged 30% of the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE38':
				$result = __('The guest can cancel free of charge until 7 days before arrival. The guest will be charged 50% of the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE39':
				$result = __('The guest can cancel free of charge until 7 days before arrival. The guest will be charged 70% of the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE40':
				$result = __('The guest can cancel free of charge until 14 days before arrival. The guest will be charged 30% of the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE41':
				$result = __('The guest can cancel free of charge until 14 days before arrival. The guest will be charged 50% of the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE42':
				$result = __('The guest can cancel free of charge until 14 days before arrival. The guest will be charged 70% of the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE43':
				$result = __('The guest can cancel free of charge until 30 days before arrival. The guest will be charged the first night if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE44':
				$result = __('The guest can cancel free of charge until 30 days before arrival. The guest will be charged 30% of the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE45':
				$result = __('The guest can cancel free of charge until 30 days before arrival. The guest will be charged 50% of the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE46':
				$result = __('The guest can cancel free of charge until 30 days before arrival. The guest will be charged 70% of the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE47':
				$result = __('The guest can cancel free of charge until 30 days before arrival. The guest will be charged the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE48':
				$result = __('The guest can cancel free of charge until 42 days before arrival. The guest will be charged the first night if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE49':
				$result = __('The guest can cancel free of charge until 42 days before arrival. The guest will be charged 30% of the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE50':
				$result = __('The guest can cancel free of charge until 42 days before arrival. The guest will be charged 50% of the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE51':
				$result = __('The guest can cancel free of charge until 42 days before arrival. The guest will be charged 70% of the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE52':
				$result = __('The guest can cancel free of charge until 60 days before arrival. The guest will be charged the first night if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE53':
				$result = __('The guest can cancel free of charge until 60 days before arrival. The guest will be charged 30% of the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE54':
				$result = __('The guest can cancel free of charge until 60 days before arrival. The guest will be charged 50% of the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE55':
				$result = __('The guest can cancel free of charge until 60 days before arrival. The guest will be charged 70% of the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE56':
				$result = __('The guest can cancel free of charge until 60 days before arrival. The guest will be charged the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE57':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE58':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE59':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE60':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE61':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE62':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE63':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE64':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE65':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE66':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE67':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE68':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE69':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE70':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE71':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE72':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE73':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE74':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE75':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE76':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE77':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE78':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE79':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE80':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 5 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE81':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE82':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE83':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE84':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 7 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE85':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE86':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE87':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE88':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 14 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE89':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE90':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE91':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE92':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE93':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE94':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel within 1 day before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE95':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE96':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 2 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE97':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE98':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 3 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE99':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE100':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 30 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE101':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE102':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE103':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE104':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 42 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE105':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE106':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and a further 40% of the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE107':
				$result = __('The guest will be charged 30% of the total price if they cancel after reservation and the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE108':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and a further 20% of the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE109':
				$result = __('The guest will be charged 50% of the total price if they cancel after reservation and the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHPOLTYPE110':
				$result = __('The guest will be charged 70% of the total price if they cancel after reservation and the total price if they cancel in the 60 days before arrival.', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE1':
				$result = __('Adjoining Rooms', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE2':
				$result = __('Air Conditioning', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE3':
				$result = __('Alarm Clock', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE4':
				$result = __('AM/FM Radio', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE5':
				$result = __('Barbeque Grills', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE6':
				$result = __('Bathtub with spray jets', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE7':
				$result = __('Bathrobe', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE8':
				$result = __('Bathroom Amenities', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE9':
				$result = __('Bathtub', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE10':
				$result = __('Bathtub Only', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE11':
				$result = __('Bidet', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE12':
				$result = __('Cable Television', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE13':
				$result = __('Coffe/Tea Maker', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE14':
				$result = __('Color Television', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE15':
				$result = __('Computer', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE16':
				$result = __('Connecting Rooms', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE17':
				$result = __('Cordless Phone', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE18':
				$result = __('Cribs', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE19':
				$result = __('Desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE20':
				$result = __('Desk with lamp', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE21':
				$result = __('Dishwasher', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE22':
				$result = __('Double Bed', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE23':
				$result = __('Fax Machine', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE24':
				$result = __('Fireplace', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE25':
				$result = __('Free movies/video', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE26':
				$result = __('Full Kitchen', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE27':
				$result = __('Grecian Tub', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE28':
				$result = __('Hairdryer', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE29':
				$result = __('Internet Access', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE30':
				$result = __('Iron (ironing facilities)', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE31':
				$result = __('Ironing Board', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE32':
				$result = __('Whirlpool', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE33':
				$result = __('Extra Large Double', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE34':
				$result = __('Kitchen', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE35':
				$result = __('Kitchen supplies', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE36':
				$result = __('Kitchenette', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE37':
				$result = __('Laptop', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE38':
				$result = __('Large Desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE39':
				$result = __('Microwave', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE40':
				$result = __('Minibar', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE41':
				$result = __('Multi-line Phone', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE42':
				$result = __('Oven', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE43':
				$result = __('Pay per view movies on TV', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE44':
				$result = __('Phone in bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE45':
				$result = __('Plates and bowls', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE46':
				$result = __('Private bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE47':
				$result = __('Large Double', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE48':
				$result = __('Refrigerator', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE49':
				$result = __('Refrigerator with ice maker', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE50':
				$result = __('Extra Bed', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE51':
				$result = __('Safe', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE52':
				$result = __('Separate closet', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE53':
				$result = __('Shower only', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE54':
				$result = __('Silverware/utensils', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE55':
				$result = __('Sitting area', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE56':
				$result = __('Speaker phone', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE57':
				$result = __('Stove', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE58':
				$result = __('Telephone for hearing impaired', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE59':
				$result = __('Twin (Bed)', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE60':
				$result = __('VCR Movies', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE61':
				$result = __('Videogames', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE62':
				$result = __('Wake-up calls', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE63':
				$result = __('Wireless Internet', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE64':
				$result = __('Air conditioning individually controlled in room', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE65':
				$result = __('Bathtub & whirlpool', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE66':
				$result = __('CD player', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE67':
				$result = __('Desk with electrical outlet', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE68':
				$result = __('Marble bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE69':
				$result = __('List of movie channels available', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE70':
				$result = __('Oversized bathtub', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE71':
				$result = __('Shower', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE72':
				$result = __('Soundproofed room', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE73':
				$result = __('Tables and chairs', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE74':
				$result = __('Two-line phone', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE75':
				$result = __('Washer/dryer', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE76':
				$result = __('Separate tub and shower', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE77':
				$result = __('Ceiling fan', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE78':
				$result = __('CNN available', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE79':
				$result = __('Closets in room', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE80':
				$result = __('DVD player', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE81':
				$result = __('Mini-refrigerator', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE82':
				$result = __('Self-controlled heating/cooling system', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE83':
				$result = __('Toaster', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE84':
				$result = __('Shared bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE85':
				$result = __('Telephone TDD/Textphone', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE86':
				$result = __('Futon Mat', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE87':
				$result = __('Single (Bed)', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE88':
				$result = __('Satellite Television', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE89':
				$result = __('iPod docking station', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE90':
				$result = __('Satellite radio', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE91':
				$result = __('Video on demand', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE92':
				$result = __('Gulf view', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE93':
				$result = __('Mountain view', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE94':
				$result = __('Ocean view', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE95':
				$result = __('Slippers', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE96':
				$result = __('Chair provided with desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE97':
				$result = __('Luxury linen type', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE98':
				$result = __('Private pool', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE99':
				$result = __('High Definition (HD) Flat Panel Television - 32 inches or greater', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE100':
				$result = __('Double (Bed)', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE101':
				$result = __('TV', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE102':
				$result = __('Video Game Player', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE103':
				$result = __('Dining Room Seats', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE104':
				$result = __('Mobile/Cellular Phones', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE105':
				$result = __('Movies', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE106':
				$result = __('Multiple Closets', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE107':
				$result = __('Safe large enough to accommodate a laptop', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE108':
				$result = __('Bluray player', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE109':
				$result = __('Non-allergenic room', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE110':
				$result = __('Seating area with sofa/chair', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE111':
				$result = __('Separate toilet area', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE112':
				$result = __('Widescreen TV', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE113':
				$result = __('Separate tub or shower', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE114':
				$result = __('Coffe/Tea maker', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE115':
				$result = __('Wake Up Service/Alarm-clock', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE116':
				$result = __('Clothing Iron', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE117':
				$result = __('Balcony', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE118':
				$result = __('Washing Machine', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE119':
				$result = __('Patio', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE120':
				$result = __('Extra long beds', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE121':
				$result = __('Dressing room', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE122':
				$result = __('Shared toilet', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE123':
				$result = __('Carpeted floor', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE124':
				$result = __('Additional toilet', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE125':
				$result = __('Private entrance', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE126':
				$result = __('Sofa', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE127':
				$result = __('Tiled/Marble floor', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE128':
				$result = __('View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE129':
				$result = __('Wooden/Parquet floor', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE130':
				$result = __('Electric Kettle', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE131':
				$result = __('Executive Lounge Access', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE132':
				$result = __('Mosquito Net', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE133':
				$result = __('Towels/Linens at surcharge', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE134':
				$result = __('Sauna', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE135':
				$result = __('iPad', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE136':
				$result = __('Game Console - Xbox 360', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE137':
				$result = __('Game Console - PS2', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE138':
				$result = __('Game Console - PS3', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE139':
				$result = __('Game Console - Nintendo Wii', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE140':
				$result = __('Lake View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE141':
				$result = __('Garden View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE142':
				$result = __('Pool View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE143':
				$result = __('Landmark View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE144':
				$result = __('Cleaning Products', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE145':
				$result = __('Electric Blankets', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE146':
				$result = __('Additional Bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE147':
				$result = __('City View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE148':
				$result = __('River View', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE149':
				$result = __('Towels', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE150':
				$result = __('Dining Table', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE151':
				$result = __('Children Highchair', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE152':
				$result = __('Outdoor Furniture', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE153':
				$result = __('Outdoor Dining Area', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE154':
				$result = __('Entire property on ground floor', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE155':
				$result = __('Upper floor reachable by lift', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE156':
				$result = __('Upper floor reachable by stairs only', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE157':
				$result = __('Entire unit whellchair accessible', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE158':
				$result = __('Detached', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE159':
				$result = __('Semi-detached', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE160':
				$result = __('Private flat in block of flats', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE161':
				$result = __('Clothes rack', 'vikchannelmanager');
				break;
			case 'VCMBCAHAMENTYPE162':
				$result = __('Sofa Bed', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE1':
				$result = __('Continental', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE2':
				$result = __('Italian', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE3':
				$result = __('Full English', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE4':
				$result = __('Vegetarian', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE5':
				$result = __('Vegan', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE6':
				$result = __('Halal', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE7':
				$result = __('Gluten Free', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE8':
				$result = __('Kosher', 'vikchannelmanager');
				break;
			case 'VCMBCAHBRKFTYPE9':
				$result = __('Asian', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV1':
				$result = __('24-hour front desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV2':
				$result = __('Air conditioning', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV3':
				$result = __('ATM/Cash machine', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV4':
				$result = __('Baby sitting', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV5':
				$result = __('BBQ/Picnic area', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV6':
				$result = __('Business library', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV7':
				$result = __('Car rental desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV8':
				$result = __('Casino', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV9':
				$result = __('Concierge desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV10':
				$result = __('Currency exchange', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV11':
				$result = __('Elevators', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV12':
				$result = __('Exercise gym', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV13':
				$result = __('Express check-in', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV14':
				$result = __('Airport shuttle (free)', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV15':
				$result = __('Gift/News stand', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV16':
				$result = __('Heated pool', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV17':
				$result = __('Indoor pool', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV18':
				$result = __('Live entertainment', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV19':
				$result = __('Massage services', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV20':
				$result = __('Nightclub', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV21':
				$result = __('Restaurant', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV22':
				$result = __('Room service', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV23':
				$result = __('Safe deposit box', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV24':
				$result = __('Sauna', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV25':
				$result = __('Shoe shine stand', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV26':
				$result = __('Solarium', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV27':
				$result = __('Steam bath', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV28':
				$result = __('Tour/sightseeing desk', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV29':
				$result = __('Dry cleaning', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV30':
				$result = __('Valet parking', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV31':
				$result = __('Vending machines', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV32':
				$result = __('Shops and commercial services', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV33':
				$result = __('Continental breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV34':
				$result = __('Lounges/bars', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV35':
				$result = __('Onsite laundry', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV36':
				$result = __('Breakfast Service', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV37':
				$result = __('Children\'s play area', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV38':
				$result = __('Locker room', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV39':
				$result = __('Non-smoking rooms', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV40':
				$result = __('Bicycle rentals', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV41':
				$result = __('Business center', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV42':
				$result = __('Tennis court', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV43':
				$result = __('Water sports', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV44':
				$result = __('Golf', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV45':
				$result = __('Horseback riding', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV46':
				$result = __('Beachfront', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV47':
				$result = __('Heated guest rooms', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV48':
				$result = __('Kitchenette (communal)', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV49':
				$result = __('Meeting rooms', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV50':
				$result = __('Snow skiing', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV51':
				$result = __('Airport shuttle service (surcharge)', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV52':
				$result = __('Luggage service', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV53':
				$result = __('Newspaper', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV54':
				$result = __('Hypoallergenic rooms', 'vikchannelmanager');
				break;
			case 'VCMBCAHSERV55':
				$result = __('Smoke-free property', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG1':
				$result = __('Arabic', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG2':
				$result = __('Azerbaijani', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG3':
				$result = __('Bulgarian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG4':
				$result = __('Catalan', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG5':
				$result = __('Czech', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG6':
				$result = __('Danish', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG7':
				$result = __('German', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG8':
				$result = __('Greek', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG9':
				$result = __('English (UK)', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG10':
				$result = __('Spanish', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG11':
				$result = __('Estonian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG12':
				$result = __('French', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG13':
				$result = __('Finnish', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG14':
				$result = __('Hebrew', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG15':
				$result = __('Hindi', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG16':
				$result = __('Croatian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG17':
				$result = __('Hungarian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG18':
				$result = __('Indonesian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG19':
				$result = __('Icelandic', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG20':
				$result = __('Italian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG21':
				$result = __('Japanese', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG22':
				$result = __('Khmer', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG23':
				$result = __('Korean', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG24':
				$result = __('Lao', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG25':
				$result = __('Lithuanian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG26':
				$result = __('Latvian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG27':
				$result = __('Malay', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG28':
				$result = __('Dutch', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG29':
				$result = __('Norwegian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG30':
				$result = __('Polish', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG31':
				$result = __('Portuguese', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG32':
				$result = __('Romanian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG33':
				$result = __('Russian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG34':
				$result = __('Slovak', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG35':
				$result = __('Slovenian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG36':
				$result = __('Serbian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG37':
				$result = __('Swedish', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG38':
				$result = __('Tagalog', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG39':
				$result = __('Thai', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG40':
				$result = __('Turkish', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG41':
				$result = __('Ukranian', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG42':
				$result = __('Vietnamese', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG43':
				$result = __('Portuguese (Brazilian)', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG44':
				$result = __('Spanish (South American)', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG45':
				$result = __('Chinese (Cantonese)', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG46':
				$result = __('English (American)', 'vikchannelmanager');
				break;
			case 'VCMBCAHLANG47':
				$result = __('Chinese (Mandarin)', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION1':
				$result = __('Afghanistan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION2':
				$result = __('Åland Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION3':
				$result = __('Albania', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION4':
				$result = __('Algeria', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION5':
				$result = __('American Samoa', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION6':
				$result = __('Andorra', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION7':
				$result = __('Angola', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION8':
				$result = __('Anguilla', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION9':
				$result = __('Antarctica', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION10':
				$result = __('Antigua and Barbuda', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION11':
				$result = __('Argentina', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION12':
				$result = __('Armenia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION13':
				$result = __('Aruba', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION14':
				$result = __('Australia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION15':
				$result = __('Austria', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION16':
				$result = __('Azerbaijan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION17':
				$result = __('Bahamas', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION18':
				$result = __('Bahrain', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION19':
				$result = __('Bangladesh', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION20':
				$result = __('Barbados', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION21':
				$result = __('Belarus', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION22':
				$result = __('Belgium', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION23':
				$result = __('Belize', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION24':
				$result = __('Benin', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION25':
				$result = __('Bermuda', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION26':
				$result = __('Bhutan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION27':
				$result = __('Bolivia, Plurinational State of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION28':
				$result = __('Bonaire, Sint Eustatius and Saba', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION29':
				$result = __('Bosnia and Herzegovina', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION30':
				$result = __('Botswana', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION31':
				$result = __('Bouvet Island', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION32':
				$result = __('Brazil', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION33':
				$result = __('British Indian Ocean Territory', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION34':
				$result = __('Brunei Darussalam', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION35':
				$result = __('Bulgaria', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION36':
				$result = __('Burkina Faso', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION37':
				$result = __('Burundi', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION38':
				$result = __('Cambodia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION39':
				$result = __('Cameroon', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION40':
				$result = __('Canada', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION41':
				$result = __('Cape Verde', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION42':
				$result = __('Cayman Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION43':
				$result = __('Central African Republic', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION44':
				$result = __('Chad', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION45':
				$result = __('Chile', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION46':
				$result = __('China', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION47':
				$result = __('Christmas Island', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION48':
				$result = __('Cocos (Keeling) Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION49':
				$result = __('Colombia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION50':
				$result = __('Comoros', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION51':
				$result = __('Congo', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION52':
				$result = __('Congo, the Democratic Republic of the', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION53':
				$result = __('Cook Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION54':
				$result = __('Costa Rica', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION55':
				$result = __('Côte d\'Ivoire', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION56':
				$result = __('Croatia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION57':
				$result = __('Cuba', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION58':
				$result = __('Curaçao', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION59':
				$result = __('Cyprus', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION60':
				$result = __('Czech Republic', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION61':
				$result = __('Denmark', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION62':
				$result = __('Djibouti', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION63':
				$result = __('Dominica', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION64':
				$result = __('Dominican Republic', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION65':
				$result = __('Ecuador', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION66':
				$result = __('Egypt', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION67':
				$result = __('El Salvador', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION68':
				$result = __('Equatorial Guinea', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION69':
				$result = __('Eritrea', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION70':
				$result = __('Estonia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION71':
				$result = __('Ethiopia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION72':
				$result = __('Falkland Islands (Malvinas)', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION73':
				$result = __('Faroe Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION74':
				$result = __('Fiji', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION75':
				$result = __('Finland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION76':
				$result = __('France', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION77':
				$result = __('French Guiana', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION78':
				$result = __('French Polynesia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION79':
				$result = __('French Southern Territories', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION80':
				$result = __('Gabon', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION81':
				$result = __('Gambia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION82':
				$result = __('Georgia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION83':
				$result = __('Germany', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION84':
				$result = __('Ghana', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION85':
				$result = __('Gibraltar', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION86':
				$result = __('Greece', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION87':
				$result = __('Greenland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION88':
				$result = __('Grenada', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION89':
				$result = __('Guadeloupe', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION90':
				$result = __('Guam', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION91':
				$result = __('Guatemala', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION92':
				$result = __('Guernsey', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION93':
				$result = __('Guinea', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION94':
				$result = __('Guinea-Bissau', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION95':
				$result = __('Guyana', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION96':
				$result = __('Haiti', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION97':
				$result = __('Heard Island and McDonald Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION98':
				$result = __('Holy See (Vatican City State)', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION99':
				$result = __('Honduras', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION100':
				$result = __('Hong Kong', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION101':
				$result = __('Hungary', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION102':
				$result = __('Iceland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION103':
				$result = __('India', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION104':
				$result = __('Indonesia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION105':
				$result = __('Iran, Islamic Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION106':
				$result = __('Iraq', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION107':
				$result = __('Ireland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION108':
				$result = __('Isle of Man', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION109':
				$result = __('Israel', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION110':
				$result = __('Italy', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION111':
				$result = __('Jamaica', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION112':
				$result = __('Japan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION113':
				$result = __('Jersey', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION114':
				$result = __('Jordan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION115':
				$result = __('Kazakhstan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION116':
				$result = __('Kenya', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION117':
				$result = __('Kiribati', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION118':
				$result = __('Korea, Democratic People\'s Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION119':
				$result = __('Korea, Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION120':
				$result = __('Kuwait', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION121':
				$result = __('Kyrgyzstan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION122':
				$result = __('Lao People\'s Democratic Republic', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION123':
				$result = __('Latvia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION124':
				$result = __('Lebanon', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION125':
				$result = __('Lesotho', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION126':
				$result = __('Liberia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION127':
				$result = __('Libya', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION128':
				$result = __('Liechtenstein', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION129':
				$result = __('Lithuania', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION130':
				$result = __('Luxembourg', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION131':
				$result = __('Macao', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION132':
				$result = __('Macedonia, the former Yugoslav Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION133':
				$result = __('Madagascar', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION134':
				$result = __('Malawi', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION135':
				$result = __('Malaysia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION136':
				$result = __('Maldives', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION137':
				$result = __('Mali', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION138':
				$result = __('Malta', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION139':
				$result = __('Marshall Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION140':
				$result = __('Martinique', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION141':
				$result = __('Mauritania', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION142':
				$result = __('Mauritius', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION143':
				$result = __('Mayotte', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION144':
				$result = __('Mexico', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION145':
				$result = __('Micronesia, Federated States of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION146':
				$result = __('Moldova, Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION147':
				$result = __('Monaco', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION148':
				$result = __('Mongolia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION149':
				$result = __('Montenegro', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION150':
				$result = __('Montserrat', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION151':
				$result = __('Morocco', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION152':
				$result = __('Mozambique', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION153':
				$result = __('Myanmar', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION154':
				$result = __('Namibia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION155':
				$result = __('Nauru', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION156':
				$result = __('Nepal', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION157':
				$result = __('Netherlands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION158':
				$result = __('New Caledonia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION159':
				$result = __('New Zealand', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION160':
				$result = __('Nicaragua', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION161':
				$result = __('Niger', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION162':
				$result = __('Nigeria', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION163':
				$result = __('Niue', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION164':
				$result = __('Norfolk Island', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION165':
				$result = __('Northern Mariana Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION166':
				$result = __('Norway', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION167':
				$result = __('Oman', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION168':
				$result = __('Pakistan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION169':
				$result = __('Palau', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION170':
				$result = __('Palestinian Territory, Occupied', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION171':
				$result = __('Panama', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION172':
				$result = __('Papua New Guinea', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION173':
				$result = __('Paraguay', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION174':
				$result = __('Peru', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION175':
				$result = __('Philippines', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION176':
				$result = __('Pitcairn', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION177':
				$result = __('Poland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION178':
				$result = __('Portugal', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION179':
				$result = __('Puerto Rico', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION180':
				$result = __('Qatar', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION181':
				$result = __('Réunion', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION182':
				$result = __('Romania', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION183':
				$result = __('Russian Federation', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION184':
				$result = __('Rwanda', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION185':
				$result = __('Saint Barthélemy', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION186':
				$result = __('Saint Helena, Ascension and Tristan da Cunha', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION187':
				$result = __('Saint Kitts and Nevis', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION188':
				$result = __('Saint Lucia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION189':
				$result = __('Saint Martin (French part)', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION190':
				$result = __('Saint Pierre and Miquelon', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION191':
				$result = __('Saint Vincent and the Grenadines', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION192':
				$result = __('Samoa', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION193':
				$result = __('San Marino', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION194':
				$result = __('Sao Tome and Principe', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION195':
				$result = __('Saudi Arabia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION196':
				$result = __('Senegal', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION197':
				$result = __('Serbia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION198':
				$result = __('Seychelles', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION199':
				$result = __('Sierra Leone', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION200':
				$result = __('Singapore', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION201':
				$result = __('Sint Maarten (Dutch part)', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION202':
				$result = __('Slovakia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION203':
				$result = __('Slovenia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION204':
				$result = __('Solomon Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION205':
				$result = __('Somalia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION206':
				$result = __('South Africa', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION207':
				$result = __('South Georgia and the South Sandwich Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION208':
				$result = __('South Sudan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION209':
				$result = __('Spain', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION210':
				$result = __('Sri Lanka', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION211':
				$result = __('Sudan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION212':
				$result = __('Suriname', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION213':
				$result = __('Svalbard and Jan Mayen', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION214':
				$result = __('Swaziland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION215':
				$result = __('Sweden', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION216':
				$result = __('Switzerland', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION217':
				$result = __('Syrian Arab Republic', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION218':
				$result = __('Taiwan, Province of China', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION219':
				$result = __('Tajikistan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION220':
				$result = __('Tanzania, United Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION223':
				$result = __('Thailand', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION224':
				$result = __('Timor-Leste', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION225':
				$result = __('Togo', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION226':
				$result = __('Tokelau', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION227':
				$result = __('Tonga', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION228':
				$result = __('Trinidad and Tobago', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION229':
				$result = __('Tunisia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION230':
				$result = __('Turkey', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION231':
				$result = __('Turkmenistan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION232':
				$result = __('Turks and Caicos Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION233':
				$result = __('Tuvalu', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION234':
				$result = __('Uganda', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION235':
				$result = __('Ukraine', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION236':
				$result = __('United Arab Emirates', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION237':
				$result = __('United Kingdom', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION238':
				$result = __('United States', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION239':
				$result = __('United States Minor Outlying Islands', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION240':
				$result = __('Uruguay', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION241':
				$result = __('Uzbekistan', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION242':
				$result = __('Vanuatu', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION243':
				$result = __('Venezuela, Bolivarian Republic of', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION244':
				$result = __('Viet Nam', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION245':
				$result = __('Virgin Islands, British', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION246':
				$result = __('Virgin Islands, U.S.', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION247':
				$result = __('Wallis and Futuna', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION248':
				$result = __('Western Sahara', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION249':
				$result = __('Yemen', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION250':
				$result = __('Zambia', 'vikchannelmanager');
				break;
			case 'VCMBCAHNATION251':
				$result = __('Zimbabwe', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB1':
				$result = __('Administration Employee', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB2':
				$result = __('Director of Business Development', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB3':
				$result = __('E-Commerce Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB4':
				$result = __('Finance Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB5':
				$result = __('Front Office Employee', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB6':
				$result = __('Front Office Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB7':
				$result = __('General Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB8':
				$result = __('Marketing Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB9':
				$result = __('Owner', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB10':
				$result = __('Reservations Employee', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB11':
				$result = __('Reservations Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB12':
				$result = __('Revenue Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB13':
				$result = __('Rooms Division Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB14':
				$result = __('Sales & Marketing Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB15':
				$result = __('Sales Executive', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB16':
				$result = __('Sales Manager', 'vikchannelmanager');
				break;
			case 'VCMBCAHJOB17':
				$result = __('Unknown', 'vikchannelmanager');
				break;
			case 'VCMBCAHNODATAERROR':
				$result = __('Error! Please insert some data!', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHCKINTIMEERROR':
				$result = __('Error! Please check the Check-In time!', 'vikchannelmanager');
				break;
			case 'VCMBCAHCHCKOUTTIMEERROR':
				$result = __('Error! Please check the Check-Out time!', 'vikchannelmanager');
				break;
			case 'VCMBCAHNEWVALIDATE':
				$result = __('Error! To create a new property, fill in the Physical Location and Invoices Contact Infos!', 'vikchannelmanager');
				break;
			case 'VCMBCAHNEWSUCCESS':
				$result = __('New Hotel Successfully Saved. New Hotel ID: %s', 'vikchannelmanager');
				break;
			case 'VCMBCAHNEWWARNING':
				$result = __('Request was successful but the following errors were returned: %s', 'vikchannelmanager');
				break;
			case 'VCMBCAHPERSON':
				$result = __('Person', 'vikchannelmanager');
				break;
			case 'VCMBCAHINSERTTYPE':
				$result = __('Insert Type', 'vikchannelmanager');
				break;
			case 'VCMBCAHUPDATEPROP':
				$result = __('Update Property: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHNEWPROP':
				$result = __('New Property', 'vikchannelmanager');
				break;
			case 'VCMBCAHUSERID':
				$result = __('User ID: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHACCOUNTNAME':
				$result = __('Hotel Name: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHCOPYTEXT':
				$result = __('Copy data from: ', 'vikchannelmanager');
				break;
			case 'VCMBCARPTITLE':
				$result = __('Rates Plans', 'vikchannelmanager');
				break;
			case 'VCMBCARCTITLE':
				$result = __('Rooms Contents Management', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMINFO':
				$result = __('Room Info', 'vikchannelmanager');
				break;
			case 'VCMBCARCMAXOCCUP':
				$result = __('Max Occupancy', 'vikchannelmanager');
				break;
			case 'VCMBCARCCRIBS':
				$result = __('Children Allowed', 'vikchannelmanager');
				break;
			case 'VCMBCARCMAXROLL':
				$result = __('Number of Rollaway Beds', 'vikchannelmanager');
				break;
			case 'VCMBCARCADDGUESTS':
				$result = __('Additional Guests Allowed', 'vikchannelmanager');
				break;
			case 'VCMBCARCNONSMOKING':
				$result = __('No Smoking', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE':
				$result = __('Room Type', 'vikchannelmanager');
				break;
			case 'VCMBCARCSIZEMEASUREMENT':
				$result = __('Room Size', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMAMENITIES':
				$result = __('Room Amenities', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMDESC':
				$result = __('Room Description', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMNAME':
				$result = __('Room Name', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMIMAGES':
				$result = __('Images', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMIMAGETITLE':
				$result = __('Image', 'vikchannelmanager');
				break;
			case 'VCMBCARCSUBROOMS':
				$result = __('Sub Rooms', 'vikchannelmanager');
				break;
			case 'VCMBCARCPRIVATEBATHROOM':
				$result = __('Private Bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCARCMAXGUESTS':
				$result = __('Max Guests', 'vikchannelmanager');
				break;
			case 'VCMBCARCBEDDINGTYPE':
				$result = __('Bedding Type', 'vikchannelmanager');
				break;
			case 'VCMBCARPDELETEMESSAGE':
				$result = __('Scheduled for deletion. </br> Please save to confirm.', 'vikchannelmanager');
				break;
			case 'VCMBCARCSIZEMEASUREMENTUNIT':
				$result = __('sqm', 'vikchannelmanager');
				break;
			case 'VCMBCARCEDITACT':
				$result = __('Edit room %1$s - %2$s - Hotel %3$s', 'vikchannelmanager');
				break;
			case 'VCMBCARCSELECTACT':
				$result = __('Select Action', 'vikchannelmanager');
				break;
			case 'VCMBCARCNEWACT':
				$result = __('New Room', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE1':
				$result = __('Apartment', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE2':
				$result = __('Quadruple', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE3':
				$result = __('Suite', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE4':
				$result = __('Triple', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE5':
				$result = __('Twin', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE6':
				$result = __('Double', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE7':
				$result = __('Single', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE8':
				$result = __('Studio', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE9':
				$result = __('Family', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE10':
				$result = __('Dormitory room', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE11':
				$result = __('Bed in Dormitory', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE12':
				$result = __('Bungalow', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE13':
				$result = __('Chalet', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE14':
				$result = __('Holiday home', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE15':
				$result = __('Villa', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE16':
				$result = __('Mobile home', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMTYPE17':
				$result = __('Tent', 'vikchannelmanager');
				break;
			case 'VCMBCARCNOROOMTYPE':
				$result = __('Please select a room type', 'vikchannelmanager');
				break;
			case 'VCMBCARCPRIVBATHROOM':
				$result = __('Private Bathroom', 'vikchannelmanager');
				break;
			case 'VCMBCARCMAXGUESTS':
				$result = __('Max Guests', 'vikchannelmanager');
				break;
			case 'VCMBCARCBEDDINGTYPE':
				$result = __('Bedding Type', 'vikchannelmanager');
				break;
			case 'VCMBCARCAMENITYVAL1':
				$result = __('Not Specified', 'vikchannelmanager');
				break;
			case 'VCMBCARCAMENITYVAL2':
				$result = __('Free of Charge', 'vikchannelmanager');
				break;
			case 'VCMBCARCAMENITYVAL3':
				$result = __('Surcharge', 'vikchannelmanager');
				break;
			case 'VCMBCARCVALUE':
				$result = __('Value', 'vikchannelmanager');
				break;
			case 'VCMBCARCSUBROOMTYPE1':
				$result = __('Living Room', 'vikchannelmanager');
				break;
			case 'VCMBCARCSUBROOMTYPE2':
				$result = __('Bedroom', 'vikchannelmanager');
				break;
			case 'VCMBCARCWRONGSIZE':
				$result = __('Wrong Image Resolution! Must be 2048x1536 or higher.', 'vikchannelmanager');
				break;
			case 'VCMBCARCUNKNOWNERROR':
				$result = __('Unknown Error. Please try again.', 'vikchannelmanager');
				break;
			case 'VCMBCARCACTIVATE':
				$result = __('Activate Room', 'vikchannelmanager');
				break;
			case 'VCMBCARCDEACTIVATE':
				$result = __('Deactivate Room', 'vikchannelmanager');
				break;
			case 'VCMBCARCROOMDETAILS':
				$result = __('Room ID: %1$s - Hotel ID: %2$s', 'vikchannelmanager');
				break;
			case 'VCMBCAHEMPTYVALERR':
				$result = __('Please insert some values before submitting.', 'vikchannelmanager');
				break;
			case 'VCMBCAPNRATEPLANVAL':
				$result = __('Rate Plan ID: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN1':
				$result = __('None', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN2':
				$result = __('All inclusive', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN3':
				$result = __('Breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN4':
				$result = __('Lunch', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN5':
				$result = __('Dinner', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN6':
				$result = __('American', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN7':
				$result = __('Bed & breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN8':
				$result = __('Buffet breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN9':
				$result = __('Caribbean breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN10':
				$result = __('Continental breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN11':
				$result = __('English breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN12':
				$result = __('European plan', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN13':
				$result = __('Family plan', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN14':
				$result = __('Full board', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN15':
				$result = __('Full breakfast', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN16':
				$result = __('Half board/modified American plan', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN17':
				$result = __('Room only', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN18':
				$result = __('Self catering', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN19':
				$result = __('Bermuda', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN20':
				$result = __('Dinner bed and breakfast plan', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN21':
				$result = __('Family American', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN22':
				$result = __('Modified', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLAN23':
				$result = __('Breakfast & lunch', 'vikchannelmanager');
				break;
			case 'VCMBCAPNTIME1':
				$result = __('Years', 'vikchannelmanager');
				break;
			case 'VCMBCAPNTIME2':
				$result = __('Months', 'vikchannelmanager');
				break;
			case 'VCMBCAPNTIME3':
				$result = __('Days', 'vikchannelmanager');
				break;
			case 'VCMBCAPNTIME4':
				$result = __('Hours', 'vikchannelmanager');
				break;
			case 'VCMBCAPNROOMRATES':
				$result = __('Rooms/Rates', 'vikchannelmanager');
				break;
			case 'VCMBCAPNRATEPLANS':
				$result = __('Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMBCAPNPOLICIES':
				$result = __('Policies', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLANS':
				$result = __('Meal Plans', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMINOFF':
				$result = __('Minimum Advance Booking Offset: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMINOFFDESC':
				$result = __('The value is relative to 24:00 of the day of check-in - EX: 14 Days means bookings start in 14 days from today', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMAXOFF':
				$result = __('Maximum Advance Booking Offset: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMAXOFFDESC':
				$result = __('The value is relative to 24:00 of the day of check-in - EX: 5 Hours means bookings until 19:00)', 'vikchannelmanager');
				break;
			case 'VCMBCAPNOCCUPANCY':
				$result = __('Max Occupancy', 'vikchannelmanager');
				break;
			case 'VCMBCAPNOCCUPANCYVAL':
				$result = __('Max Occupancy: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMEALPLANSVAL':
				$result = __('Meal Plans: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNPOLICYVAL':
				$result = __('Cancel Policy: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMINOFFVAL':
				$result = __('Min Offset: ', 'vikchannelmanager');
				break;
			case 'VCMBCAPNMAXOFFVAL':
				$result = __('Max Offset: ', 'vikchannelmanager');
				break;
			case 'VCMBCAUPDATESUCC':
				$result = __('Information Updated Successfully', 'vikchannelmanager');
				break;
			case 'VCMBCARCNEWSUCC':
				$result = __('Room created correctly. Please create a connection between rooms and rate plans.', 'vikchannelmanager');
				break;
			case 'VCMBCAPNNEWSUCC':
				$result = __('Room Relation created correctly. Please synchronize rooms.', 'vikchannelmanager');
				break;
			case 'VCMBCAPNRNAME':
				$result = __('Room: %s', 'vikchannelmanager');
				break;
			case 'VCMBCAPNRID':
				$result = __('ID: %s', 'vikchannelmanager');
				break;
			case 'VCMBCAPNPRODFOR':
				$result = __('Products for %s', 'vikchannelmanager');
				break;
			case 'VCMBCAPNDELPROD':
				$result = __('Delete Product', 'vikchannelmanager');
				break;
			case 'VCMBCAHSTITLE':
				$result = __('Hotel Summary', 'vikchannelmanager');
				break;
			case 'VCMBCAHSAJAXERR':
				$result = __('Error Performing Ajax Request', 'vikchannelmanager');
				break;
			case 'VCMBCAHSCHECK':
				$result = __('Check Integrity', 'vikchannelmanager');
				break;
			case 'VCMBCAHSOPEN':
				$result = __('Open Accomodation', 'vikchannelmanager');
				break;
			case 'VCMBCAHSCLOSED':
				$result = __('Close Accomodation', 'vikchannelmanager');
				break;
			case 'VCMBCAHSSTATUS':
				$result = __('Verify Status', 'vikchannelmanager');
				break;
			case 'VCMBCAPNEMPTY':
				$result = __('Please select a Rate Plan, Max Occupancy, Policy and Meal Plan', 'vikchannelmanager');
				break;
			case 'VCMBCARPPRODUCTEXISTS':
				$result = __('A product using rate plan \'%1$s\' and room \'%2$s\' exists. Please delete it before deleting the Rate Plan.', 'vikchannelmanager');
				break;
			case 'VCMBCAIMAGETAGS':
				$result = __('Image Tags', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPHRASES':
				$result = __('Standard Phrases', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPGUESTID':
				$result = __('Guests have to show identification on arrival: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPINFORMARRIVAL':
				$result = __('Guests inform property in advance of their expected arrival time: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPPAYBEFORESTAY':
				$result = __('Guests will pay the booking before stay: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPTATOORESTRICTION':
				$result = __('Guests with tattoos may not be permitted to use some public facilities: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPKEYCOLLECTION':
				$result = __('Check-in and key collection happens at another address: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPKCADDRESS':
				$result = __('Address Line: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPKCCITY':
				$result = __('City: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPKCPOSTAL':
				$result = __('Postal Code: ', 'vikchannelmanager');
				break;
			case 'VCMBCAHSPRENOVATION':
				$result = __('The facility is undergoing renovations: ', 'vikchannelmanager');
				break;
			case 'VCMUPASUCC':
				$result = __('Action was successful!', 'vikchannelmanager');
				break;
			case 'VCMUPAFAILNOEM':
				$result = __('Action Failed! No Email!', 'vikchannelmanager');
				break;
			case 'VCMUPAFAILNOAC':
				$result = __('Action Failed! No Action Specified!', 'vikchannelmanager');
				break;
			case 'VCMUPAFAILNOPS':
				$result = __('Action Failed! No New Password Given!', 'vikchannelmanager');
				break;
			case 'VCMUPAFAILNODB':
				$result = __('Action Failed! No Records Found!', 'vikchannelmanager');
				break;
			case 'VCMUPAFAILRESERR':
				$result = __('Action Failed! E4jConnect response error! Please contact e4j.com', 'vikchannelmanager');
				break;
			case 'VCMAPPACCTITLE':
				$result = __('App Accounts', 'vikchannelmanager');
				break;
			case 'VCMREMOVECONFIRM':
				$result = __('Some records will be removed. Proceed?', 'vikchannelmanager');
				break;
			case 'VCMAPPPASSWORD':
				$result = __('New Password', 'vikchannelmanager');
				break;
			case 'VCMAPPCONFPASSWORD':
				$result = __('Confirm Password', 'vikchannelmanager');
				break;
			case 'VCMAPPPASSERR':
				$result = __('The passwords don\'t match!', 'vikchannelmanager');
				break;
			case 'VCMAPPCHANGEPASS':
				$result = __('Change Password', 'vikchannelmanager');
				break;
			case 'VCMMENUAPPCONFTIT':
				$result = __('App Config', 'vikchannelmanager');
				break;
			case 'VCMMENUAPPGENSET':
				$result = __('General Settings', 'vikchannelmanager');
				break;
			case 'VCMAPPCONFTIT':
				$result = __('Vik Channel Manager - Mobile App Settings', 'vikchannelmanager');
				break;
			case 'VCMAPPNOTIF':
				$result = __('Notifications', 'vikchannelmanager');
				break;
			case 'VCMAPPVBRESERV':
				$result = __('Do you wish to receive notifications</br>for the reservations of VikBooking?', 'vikchannelmanager');
				break;
			case 'VCMAPPTOTACC':
				$result = __('Total App Accounts', 'vikchannelmanager');
				break;
			case 'VCMAPPREPORTS':
				$result = __('Reports', 'vikchannelmanager');
				break;
			case 'VCMAPPWEEKREPORTS':
				$result = __('Do you wish to receive weekly reports?', 'vikchannelmanager');
				break;
			case 'VCMAPPWEEKREPDAY':
				$result = __('On which day do you wish to receive reports?', 'vikchannelmanager');
				break;
			case 'VCMAPPREPMAINEMAIL':
				$result = __('Reports Account', 'vikchannelmanager');
				break;
			case 'VCMAPPSETTINGSUCCESSUPDATE':
				$result = __('Update Successful', 'vikchannelmanager');
				break;
			case 'VCMAPPACCOUNTSACL':
				$result = __('Accounts Access Levels', 'vikchannelmanager');
				break;
			case 'VCMBCOMREPORTINVCARD':
				$result = __('Report credit card as invalid', 'vikchannelmanager');
				break;
			case 'VCMBCOMREPORTINVCARDCONF':
				$result = __('Do you want to report the credit card to the OTA as invalid?', 'vikchannelmanager');
				break;
			case 'VCMBCOMREPORTSUCC':
				$result = __('The request was executed successfully', 'vikchannelmanager');
				break;
			case 'UNKNOWN_ERROR_MAP':
				$result = __('Unknown Error [%s]', 'vikchannelmanager');
				break;
			case 'MSG_BASE':
				$result = __('Simple Response from E4jConnect', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR':
				$result = __('Generic error caught! Please report to e4jconnect.com', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AUTH':
				$result = __('Authentication Error! Your API Key is either invalid or expired.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_NOCHANNELS':
				$result = __('No valid Channels found for updating the availability.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AUTH_TRIPCONNECT':
				$result = __('TripConnect Authorization Error! One or more required fields are missing, please check them from the configuration.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CURL':
				$result = __('Fatal Error: cURL is not installed on your server. Please contact your hosting provider.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CURL_REQUEST':
				$result = __('cURL Request Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CURL_CONNECTION':
				$result = __('cURL connection failed! Error caught: %s', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CURL_BROKEN':
				$result = __('Data Transmission Failed: %s', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS':
				$result = __('Generic Error from Channels.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_NOSYNCHROOMS':
				$result = __('No relations between the rooms of the channel and the ones of the IBE', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_BOOKINGDOWNLOAD':
				$result = __('Error saving the new booking', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_INVALIDBOOKING':
				$result = __('Invalid Booking Data', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_BOOKINGMODIFICATION':
				$result = __('Error with the Booking Modification', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_ACMPBUSY':
				$result = __('Server will be busy for the next %s minutes. Please use last retrieval for %s', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_AVPUSHBUSY':
				$result = __('Server will be busy for the next %s hours for this request type. Please use the page Availability Overview for manual updates.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_CHANNELS_RATESPUSHBUSY':
				$result = __('Server will be busy for the next %s hours for this request type. Please use the page Rates Overview for manual updates.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_EXPEDIA':
				$result = __('Expedia reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_EXPEDIA_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_EXPEDIA_BC_RS':
				$result = __('Booking Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_EXPEDIA_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_EXPEDIA_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AGODA':
				$result = __('Agoda reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AGODA_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AGODA_BC_RS':
				$result = __('Booking Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AGODA_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AGODA_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BOOKING':
				$result = __('Booking reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BOOKING_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BOOKING_BC_RS':
				$result = __('Booking Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BOOKING_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BOOKING_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_DESPEGAR':
				$result = __('Despegar reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_DESPEGAR_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_DESPEGAR_BC_RS':
				$result = __('Despegar Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_DESPEGAR_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_DESPEGAR_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_OTELZ':
				$result = __('Otelz.com reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_OTELZ_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_OTELZ_BC_RS':
				$result = __('Otelz.com Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_OTELZ_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_OTELZ_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_GARDAPASS':
				$result = __('Gardapass reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_GARDAPASS_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_GARDAPASS_BC_RS':
				$result = __('Gardapass Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_GARDAPASS_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_GARDAPASS_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BEDANDBREAKFASTIT':
				$result = __('Gateway reported a generic Error.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BEDANDBREAKFASTIT_RAR':
				$result = __("The Update request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BEDANDBREAKFASTIT_BC_RS':
				$result = __('Booking Confirmation Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BEDANDBREAKFASTIT_CUSTAR_RS':
				$result = __('Custom Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_BEDANDBREAKFASTIT_AR_RS':
				$result = __('Availability Upd. Error', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_MAX31DAYSREQ':
				$result = __('Requests should not be sent for more than 31 days.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_PAR_RR':
				$result = __('Products Availability and Rates Response Error: %s.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_QUERY':
				$result = __('Query Failed! Please report this error to e4jconnect.com', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_REQUEST':
				$result = __('Request Integrity Error! Please fill in all the required fields.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_SCHEMA':
				$result = __('Schema Error! Please report this error to e4jconnect.com', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_SETTINGS':
				$result = __('Setting Incompleted! Check you API Key and the global settings.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_FILE':
				$result = __('Generic File Error caught!', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_FILE_PERMISSIONS':
				$result = __('File Permissions Error caught!', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_FILE_PERMISSIONS_WRITE':
				$result = __('VikChannelManager is not allowed to create/write files!', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_FILE_NOTFOUND':
				$result = __('The file VikChannelManager is looking for, has not been found!', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_PCIDATA':
				$result = __("The request returned the following errors:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING':
				$result = __('Generic warning caught! Please report to e4jconnect.com', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_NOUPD':
				$result = __('No update found for the version %s.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_EXPEDIA':
				$result = __('Expedia reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_EXPEDIA_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_EXPEDIA_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_EXPEDIA_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_EXPEDIA_BC_RS':
				$result = __('Booking Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_AGODA':
				$result = __('Agoda reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_AGODA_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_AGODA_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_AGODA_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_AGODA_BC_RS':
				$result = __('Booking Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BOOKING':
				$result = __('Booking reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BOOKING_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BOOKING_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BOOKING_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BOOKING_BC_RS':
				$result = __('Booking Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_DESPEGAR':
				$result = __('Despegar reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_DESPEGAR_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_DESPEGAR_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_DESPEGAR_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_DESPEGAR_BC_RS':
				$result = __('Despegar Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_OTELZ':
				$result = __('Otelz.com reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_OTELZ_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_OTELZ_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_OTELZ_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_OTELZ_BC_RS':
				$result = __('Otelz.com Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_GARDAPASS':
				$result = __('Gardapass reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_GARDAPASS_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_GARDAPASS_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_GARDAPASS_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_GARDAPASS_BC_RS':
				$result = __('Gardapass Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BEDANDBREAKFASTIT':
				$result = __('Gateway reported a generic Warning but the update request was successful.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BEDANDBREAKFASTIT_RAR':
				$result = __("The update request was successful but the following warnings were returned:\n%s", 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BEDANDBREAKFASTIT_CUSTAR_RS':
				$result = __('Custom Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BEDANDBREAKFASTIT_AR_RS':
				$result = __('Availability Upd. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_WARNING_BEDANDBREAKFASTIT_BC_RS':
				$result = __('Booking Confirm. Warning', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS':
				$result = __('Success', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_EXPEDIA_CUSTAR_RS':
				$result = __('Custom Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_EXPEDIA_AR_RS':
				$result = __('Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_EXPEDIA_BC_RS':
				$result = __('Booking Confirmation', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_AGODA_CUSTAR_RS':
				$result = __('Custom Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_AGODA_AR_RS':
				$result = __('Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_AGODA_BC_RS':
				$result = __('Booking Confirmation', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS':
				$result = __('Custom Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_BOOKING_AR_RS':
				$result = __('Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_BOOKING_BC_RS':
				$result = __('Booking Confirmation', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS':
				$result = __('Custom Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_DESPEGAR_AR_RS':
				$result = __('Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_DESPEGAR_BC_RS':
				$result = __('Booking Confirmation', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_CUSTAR_RS':
				$result = __('Custom Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_AR_RS':
				$result = __('Availability Update Response', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_BC_RS':
				$result = __('Booking Confirmation', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_CUSTAR_RQ':
				$result = __('Custom Availability Update Request', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_AVPUSHCUSTAR_RQ':
				$result = __('Bulk Copy Availability Inventory', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_AR_RQ':
				$result = __('Availability Update Request Sent', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_NEWBOOKINGDOWNLOADED':
				$result = __('New Booking Downloaded', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_BOOKINGMODIFIED':
				$result = __('Booking Modification', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_BOOKINGCANCELLED':
				$result = __('Booking Cancelled', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHCLOSEALL':
				$result = __('Close All Rooms', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHCLOSEALLCONF':
				$result = __('All the selected rooms will be closed on the specified dates.', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHCLOSEALLCONS':
				$result = __('The selected rooms will be closed on the specified dates and channels. To re-open the rooms, launch the Bulk Action again to transmit the real availability inventory.', 'vikchannelmanager');
				break;
			case 'VCMLOGTYPENBW':
				$result = __('New Booking from Website', 'vikchannelmanager');
				break;
			case 'VCMLOGTYPEMBW':
				$result = __('Booking Modified via Website', 'vikchannelmanager');
				break;
			case 'VCMLOGTYPECBW':
				$result = __('Booking Cancelled via Website', 'vikchannelmanager');
				break;
			case 'VCMLOGTYPENBO':
				$result = __('New Booking from OTA', 'vikchannelmanager');
				break;
			case 'VCMLOGTYPEMBO':
				$result = __('Booking Modified by OTA', 'vikchannelmanager');
				break;
			case 'VCMLOGTYPECBO':
				$result = __('Booking Cancelled by OTA', 'vikchannelmanager');
				break;
			case 'VCMRESLOGSBTN':
				$result = __('Reservation Logs', 'vikchannelmanager');
				break;
			case 'VCMMAINTRESLOGS':
				$result = __('Vik Channel Manager - Reservation Logs', 'vikchannelmanager');
				break;
			case 'VCMRESLOGSDT':
				$result = __('Date', 'vikchannelmanager');
				break;
			case 'VCMRESLOGSIDORDOTA':
				$result = __('OTA Booking ID', 'vikchannelmanager');
				break;
			case 'VCMRESLOGSDAYAFF':
				$result = __('Night Updated', 'vikchannelmanager');
				break;
			case 'VCMRESLOGSTYPE':
				$result = __('Type', 'vikchannelmanager');
				break;
			case 'VCMRESLOGSDESCR':
				$result = __('Details', 'vikchannelmanager');
				break;
			case 'VCMBCAHINODATA':
				$result = __('No data received!', 'vikchannelmanager');
				break;
			case 'VCMBPROMPTYPE':
				$result = __('What type of promotion is it?', 'vikchannelmanager');
				break;
			case 'VCMBPROMPTYPEDESC':
				$result = __('Select a promotion that matches your business goals.', 'vikchannelmanager');
				break;
			case 'VCMBPROMBSDEALTIT':
				$result = __('Basic Deal', 'vikchannelmanager');
				break;
			case 'VCMBPROMBSDEALSUB':
				$result = __('Give your guests a discount', 'vikchannelmanager');
				break;
			case 'VCMBPROMBSDEALDSC1':
				$result = __('Immediately apply discounts to certain dates', 'vikchannelmanager');
				break;
			case 'VCMBPROMBSDEALDSC2':
				$result = __('Customizable to your needs', 'vikchannelmanager');
				break;
			case 'VCMBPROMLMDEALTIT':
				$result = __('Last Minute', 'vikchannelmanager');
				break;
			case 'VCMBPROMLMDEALSUB':
				$result = __('Sell unbooked rooms', 'vikchannelmanager');
				break;
			case 'VCMBPROMLMDEALDSC1':
				$result = __('Max out your occupancy', 'vikchannelmanager');
				break;
			case 'VCMBPROMLMDEALDSC2':
				$result = __('Increase your visibility on mobile devices', 'vikchannelmanager');
				break;
			case 'VCMBPROMLMDEALDSC3':
				$result = __('Attract last-minute bookers', 'vikchannelmanager');
				break;
			case 'VCMBPROMEBDEALTIT':
				$result = __('Early Booker', 'vikchannelmanager');
				break;
			case 'VCMBPROMEBDEALSUB':
				$result = __('Get a head start', 'vikchannelmanager');
				break;
			case 'VCMBPROMEBDEALDSC1':
				$result = __('Attract early-bird bookers', 'vikchannelmanager');
				break;
			case 'VCMBPROMEBDEALDSC2':
				$result = __('Fill your low-season rooms early', 'vikchannelmanager');
				break;
			case 'VCMBPROMQWHERE':
				$result = __('When can guests book this promotion?', 'vikchannelmanager');
				break;
			case 'VCMBPROMWITHIND':
				$result = __('Guests can book this promotion within %s days of their check-in day.', 'vikchannelmanager');
				break;
			case 'VCMBPROMWITHINH':
				$result = __('Guests can book this promotion within %s hours of their check-in day.', 'vikchannelmanager');
				break;
			case 'VCMBPROMQHOWEARLY':
				$result = __("How early can guests book this promotion?", 'vikchannelmanager');
				break;
			case 'VCMBPROMBEFORED':
				$result = __("Guests can book this promotion %s days before check-in day.", 'vikchannelmanager');
				break;
			case 'VCMBPROMQWHO':
				$result = __("Who will see this promotion?", 'vikchannelmanager');
				break;
			case 'VCMBPROMAMEMBERSSUB':
				$result = __("Members and newsletter subscribers only", 'vikchannelmanager');
				break;
			case 'VCMBPROMASECRETD':
				$result = __("Secret Deal", 'vikchannelmanager');
				break;
			case 'VCMBPROMAEVERYONE':
				$result = __("Everyone", 'vikchannelmanager');
				break;
			case 'VCMBPROMQSTAY':
				$result = __("How long do guests need to stay to get this promotion?", 'vikchannelmanager');
				break;
			case 'VCMBPROMAMINNIGHTS':
				$result = __("Minimum Nights", 'vikchannelmanager');
				break;
			case 'VCMBPROMAMINNIGHTSAUTO':
				$result = __("Matched with your chosen rate", 'vikchannelmanager');
				break;
			case 'VCMBPROMQMUCH':
				$result = __("How much of a discount do you want to give?", 'vikchannelmanager');
				break;
			case 'VCMBPROMQRATES':
				$result = __("Which rates?", 'vikchannelmanager');
				break;
			case 'VCMBPROMQRATESDESC':
				$result = __("The discount will be deducted from the below selected rates.", 'vikchannelmanager');
				break;
			case 'VCMBPROMQROOMS':
				$result = __("Which rooms?", 'vikchannelmanager');
				break;
			case 'VCMBPROMQROOMSDESC':
				$result = __("The discount will be applied to the rooms you select.", 'vikchannelmanager');
				break;
			case 'VCMBPROMQWHEN':
				$result = __("When can guests stay using the discounted rate?", 'vikchannelmanager');
				break;
			case 'VCMBPROMQWHENDESC1':
				$result = __("Your discount will apply to stays on the following dates.", 'vikchannelmanager');
				break;
			case 'VCMBPROMQWHENDESC2':
				$result = __("Use the calendar below if you wish to exclude some dates from the above specified range of dates.", 'vikchannelmanager');
				break;
			case 'VCMBPROMQNAME':
				$result = __("What's the name for this promotion?", 'vikchannelmanager');
				break;
			case 'VCMBPROMQNAMEDESC1':
				$result = __("This name is just for your reference. It won't be displayed to customers on Booking.com.", 'vikchannelmanager');
				break;
			case 'VCMBPROMONONREF':
				$result = __("Non-refundable Promotion", 'vikchannelmanager');
				break;
			case 'VCMBPROMONONREFDESC':
				$result = __("Add a non-refundable policy to your promotion and decrease cancellations.", 'vikchannelmanager');
				break;
			case 'VCMBPROMONOCRED':
				$result = __("No Credit Card Required", 'vikchannelmanager');
				break;
			case 'VCMBPROMONOCREDDESC':
				$result = __("A credit card will not be required for this promotion (this helps increase conversion by making bookings easier and faster).", 'vikchannelmanager');
				break;
			case 'VCMBPROMQVIS':
				$result = __("When can guests see this promotion on Booking.com?", 'vikchannelmanager');
				break;
			case 'VCMBPROMQVISDESC':
				$result = __("Guests can see this promotion on the following range of dates.", 'vikchannelmanager');
				break;
			case 'VCMBPROMOTIME':
				$result = __("Set the timing for your Promotion", 'vikchannelmanager');
				break;
			case 'VCMBPROMOTIMEDESC':
				$result = __("Limit your promotion to the following hours, based on your local time.", 'vikchannelmanager');
				break;
			case 'VCMBPROMBOOKFROMTO':
				$result = __("Bookable from %s to %s", 'vikchannelmanager');
				break;
			case 'VCMBPROMACTIV':
				$result = __("Activate", 'vikchannelmanager');
				break;
			case 'VCMBPROMACTIVCONF':
				$result = __("Do you wish to activate this promotion?", 'vikchannelmanager');
				break;
			case 'VCMBPROMDEACTIV':
				$result = __("Deactivate", 'vikchannelmanager');
				break;
			case 'VCMBPROMDEACTIVCONF':
				$result = __("Do you wish to deactivate this promotion?", 'vikchannelmanager');
				break;
			case 'VCMBPROMEVERYWDAY':
				$result = __("For every day", 'vikchannelmanager');
				break;
			case 'VCMBPROMEXCLDATES':
				$result = __("Some dates excluded", 'vikchannelmanager');
				break;
			case 'VCMBPROMOHNAME':
				$result = __("Name", 'vikchannelmanager');
				break;
			case 'VCMBPROMOHDISC':
				$result = __("Discount", 'vikchannelmanager');
				break;
			case 'VCMBPROMOHDET':
				$result = __("Details", 'vikchannelmanager');
				break;
			case 'VCMBPROMOHROOMRATES':
				$result = __("Rooms and rates", 'vikchannelmanager');
				break;
			case 'VCMBPROMOHDATES':
				$result = __("Dates", 'vikchannelmanager');
				break;
			case 'VCMBPROMERRNOACTIVE':
				$result = __('No Active Promotions Found.', 'vikchannelmanager');
				break;
			case 'VCMMENUBPROMOTIONS':
				$result = __('Promotions', 'vikchannelmanager');
				break;
			case 'VCMMAINTBPROMOTIONS':
				$result = __('Booking.com - Promotions', 'vikchannelmanager');
				break;
			case 'VCMCONFDEFLANG':
				$result = __('Bookings Default Language', 'vikchannelmanager');
				break;
			case 'VCMCONFDEFLANGOPTNONE':
				$result = __('- not specified -', 'vikchannelmanager');
				break;
			case 'VCMMAINTBNEWPROMOTION':
				$result = __('Booking.com - New Promotion', 'vikchannelmanager');
				break;
			case 'VCMMAINTBEDITPROMOTION':
				$result = __('Booking.com - Edit Promotion', 'vikchannelmanager');
				break;
			case 'VCMBPROMOERRNOACTIVATE':
				$result = __('You probably cannot re-activate this promotion that was previously deactivated. You should create a new Promotion.', 'vikchannelmanager');
				break;
			case 'VCMBPROMUPDSUCCRQ':
				$result = __('Update request was successful for promotion %s.', 'vikchannelmanager');
				break;
			case 'VCMBPROMLOADALL':
				$result = __('Load all promotions from Booking.com', 'vikchannelmanager');
				break;
			case 'VCMBCAHISUCCESS':
				$result = __('Booking.com content successfully recieved and stored!', 'vikchannelmanager');
				break;
			case 'VCMBCAHIROOMUPDATEFAIL':
				$result = __('Failed to update Room %s data', 'vikchannelmanager');
				break;
			case 'VCMBCAHIROOMUPDATESUCCESS':
				$result = __('Room %s data properly updated', 'vikchannelmanager');
				break;
			case 'VCMBCAHIROOMINSERTFAIL':
				$result = __('Failed to insert Room %s data', 'vikchannelmanager');
				break;
			case 'VCMBCAHIROOMINSERTSUCCESS':
				$result = __('Room %s data properly inserted', 'vikchannelmanager');
				break;
			case 'VCMBCAHIDOWNLOADDATA':
				$result = __('Download Booking.com Hotel Content', 'vikchannelmanager');
				break;
			case 'VCMBCAHICONFIRM':
				$result = __('Download your Hotel Content from Booking.com? This will delete all of the content you currently have on your website.', 'vikchannelmanager');
				break;
			case 'VCMBRTWOACT':
				$result = __('Re-transmit Booking', 'vikchannelmanager');
				break;
			case 'VCMBRTWOCONFIRM':
				$result = __('Do you want the servers to re-transmit this booking to your Channel Manager? Please make sure to have availability for the booked dates in Vik Booking. Max 3 attempts.', 'vikchannelmanager');
				break;
			case 'VCMBRTWOWAITOK':
				$result = __('The request has been sent to the servers correctly. If the booking can be re-transmitted, it will be visible in your Channel Manager within a few seconds. If nothing happens, then it means that the booking is no longer available. If you get another erroneous notification, then it means that there is no availability on your website, and so the booking cannot be saved until there is enough space for the booked rooms.', 'vikchannelmanager');
				break;
			case 'VCMSAVERSYNCHERRSUBSCRLIM':
				$result = __('Error: you selected for your subscription a maximum number of room types of %d units, while the rooms mapping you tried to save had %d units in total for the rooms of your website. Please update your subscription by adding more room types if necessary, in order to not face limitations with the connection. The relations between the rooms will not be saved if your subscription limit is exceeded.', 'vikchannelmanager');
				break;
			case 'VCMRATESPUSHCNWARNLAUNCH':
				$result = __('Warning! You have changed the room basic prices from the page Rates Table so it is fundamental to submit this Bulk Action to update the information in the Channel Manager. The new basic cost was automatically calculated, and by submitting this action, the system will automatically update the room basic cost for all the other functions (App, Rates Overview etc..). If you do not launch this Bulk Action you may risk to submit invalid prices to the channels because the starting price is different in the Channel Manager from the one you have right now on your website.', 'vikchannelmanager');
				break;
			case 'VCMMAINTREVIEWS':
				$result = __('Vik Channel Manager - Guest reviews', 'vikchannelmanager');
				break;
			case 'VCMMENUREVIEWS':
				$result = __('Reviews', 'vikchannelmanager');
				break;
			case 'VCMNOREVIEWSFOUND':
				$result = __('No reviews found. You can import the reviews only from some specific channels. If your currently selected channel supports this feature, a button to download the reviews will be displayed on this page.', 'vikchannelmanager');
				break;
			case 'VCMREVFILTBYCH':
				$result = __('Filter by channel', 'vikchannelmanager');
				break;
			case 'VCMREVFILTBYLANG':
				$result = __('Filter by language', 'vikchannelmanager');
				break;
			case 'VCMREVFILTBYCOUNTRY':
				$result = __('Filter by country', 'vikchannelmanager');
				break;
			case 'VCMREVFILTBYPROPNAME':
				$result = __('Filter by property', 'vikchannelmanager');
				break;
			case 'VCMREVIEWID':
				$result = __('Review ID', 'vikchannelmanager');
				break;
			case 'VCMREVIEWSCORE':
				$result = __('Score', 'vikchannelmanager');
				break;
			case 'VCMREVIEWDOWNLOAD':
				$result = __('Download Reviews', 'vikchannelmanager');
				break;
			case 'VCMREVIEWDOWNLOADFROMD':
				$result = __('Download reviews from %s?', 'vikchannelmanager');
				break;
			case 'VCMNEWTOTREVIEWS':
				$result = __('Total new reviews downloaded: %d', 'vikchannelmanager');
				break;
			case 'VCMPROPNAME':
				$result = __('Property name', 'vikchannelmanager');
				break;
			case 'VCMREVDOWNLIMEXCEED':
				$result = __('Limit exceeded for the number of download requests. Please try again later.', 'vikchannelmanager');
				break;
			case 'VCMREVGLOBSCORES':
				$result = __('Global Scores', 'vikchannelmanager');
				break;
			case 'VCMREVNOGLOBSCORES':
				$result = __('No global scores available', 'vikchannelmanager');
				break;
			case 'VCMREVBASEDONTOT':
				$result = __('based on %d reviews', 'vikchannelmanager');
				break;
			case 'VCMREVREPLYREV':
				$result = __('Reply to review', 'vikchannelmanager');
				break;
			case 'VCMREVREPLYSUCCESS':
				$result = __('Reply to review successfully submitted!', 'vikchannelmanager');
				break;
			case 'VCMDERIVEDRATEPLANS':
				$result = __('Derived Rate Plans', 'vikchannelmanager');
				break;
			case 'VCMRMAPCONFLRELOTA':
				$result = __('The OTA room (%s) %s is linked to more than one website room. This configuration is invalid because the room relations should be one-to-one. One room of a channel should be linked to just one corresponding room of the website or the availability may not be calculated properly.', 'vikchannelmanager');
				break;
			case 'VCMRMAPCONFLRELIBE':
				$result = __('The website room %s is linked to more than one room of the channel %s. This configuration is invalid because the room relations should be one-to-one. One room of your website should be linked to just one corresponding room of the channel or the availability may not be calculated properly.', 'vikchannelmanager');
				break;
			case 'VCMMAINTOPPORTUNITIES':
				$result = __('Vik Channel Manager - Opportunities', 'vikchannelmanager');
				break;
			case 'VCMNOOPPSFOUND':
				$result = __('No opportunities found', 'vikchannelmanager');
				break;
			case 'VCMOPPORTUNITIES':
				$result = __('Opportunities', 'vikchannelmanager');
				break;
			case 'VCMOPPORTUNITIESDESCR':
				$result = __('This is the list of opportunities recommended by various channels, specifically for your property, to improve your global score and revenue. Turning off certain settings is always possible, so you should try to follow what was advised for your account and see what benefits it brings!', 'vikchannelmanager');
				break;
			case 'VCMMOREINFO':
				$result = __('More information', 'vikchannelmanager');
				break;
			case 'VCMDISMISS':
				$result = __('Dismiss', 'vikchannelmanager');
				break;
			case 'VCMDISMISSCONF':
				$result = __('Do you really want to dismiss this opportunity?', 'vikchannelmanager');
				break;
			case 'VCMENABLEOPP':
				$result = __('Enable feature', 'vikchannelmanager');
				break;
			case 'VCMOPPSETDONE':
				$result = __('Done', 'vikchannelmanager');
				break;
			case 'VCMCONFREPORTSINTERV':
				$result = __('Reports Interval', 'vikchannelmanager');
				break;
			case 'VCMCONFREPORTSINTERVDESC':
				$result = __('Choose if and how often you would like to receive (via email or through the App) the reports for your reservations.', 'vikchannelmanager');
				break;
			case 'VCMCONFREPORTSWEEK':
				$result = __('Weekly', 'vikchannelmanager');
				break;
			case 'VCMCONFREPORTS2WEEK':
				$result = __('2-Weeks', 'vikchannelmanager');
				break;
			case 'VCMCONFREPORTSMONTH':
				$result = __('Monthly', 'vikchannelmanager');
				break;
			case 'VCMRPUSHALTEROCCRULES':
				$result = __('Modify Occupancy Pricing Rules', 'vikchannelmanager');
				break;
			case 'VCMRPUSHALTEROCCRULESHELP':
				$result = __('By turning on this function, the charge/discount rules for the various numbers of adults occupancy will be altered by the same rules defined in this page of the Channel Manager to upload modified room rates onto the channels (if any). This function does not support occupancy pricing rules with percent values.', 'vikchannelmanager');
				break;
			case 'VCMICALMANUALPULL':
				$result = __('Import bookings from Calendars', 'vikchannelmanager');
				break;
			case 'VCMICALMANUALPULLHELP':
				$result = __('New reservations are always imported automatically by the Channel Manager. Would you like to import them now?', 'vikchannelmanager');
				break;
			case 'VCMMAINICALLISTINGS':
				$result = __('%s iCal - Properties', 'vikchannelmanager');
				break;
			case 'VCMICALMANUALPULLINFO':
				$result = __('The usage of this function is limited to 3 times per day because all calendars are always synced automatically, and so all new bookings will be transmitted to your Channel Manager without needing to use this function. This is useful only to force the synchronisation to run immediately.', 'vikchannelmanager');
				break;
			case 'VCMBPROMGEODEALTIT':
				$result = __('Geo Rate Promotion', 'vikchannelmanager');
				break;
			case 'VCMBPROMGEODEALSUB':
				$result = __('Apply discounts to guests from a specific region', 'vikchannelmanager');
				break;
			case 'VCMBPROMGEODEALDSC1':
				$result = __('Focus your business on a certain geographical area', 'vikchannelmanager');
				break;
			case 'VCMBPROMGEOWHICHAREA':
				$result = __('Choose the region to be used as target channel', 'vikchannelmanager');
				break;
			case 'VCMBPROMMOBILEDEALTIT':
				$result = __('Mobile Rate Promotion', 'vikchannelmanager');
				break;
			case 'VCMBPROMMOBILEDEALSUB':
				$result = __('Offer discounts to guests who book through mobile (App and Smartphone)', 'vikchannelmanager');
				break;
			case 'VCMBPROMMOBILEDEALDSC1':
				$result = __('Target a particular type of guests', 'vikchannelmanager');
				break;
			case 'VCMBCOMACCDENIEDWARN':
				$result = __('Your connection with Booking.com is probably not completely active - please log into your Booking.com account to make the final activation of the connection with the Channel Manager.', 'vikchannelmanager');
				break;
			case 'VCM_ICS_ONLY_FULL_NIGHTS':
				$result = __('Export fully booked nights only', 'vikchannelmanager');
				break;
			case 'VCMMENUICALCHANNELS':
				$result = __('Channels', 'vikchannelmanager');
				break;
			case 'VCMMENUTITLEICALCHANNELS':
				$result = __('Vik Channel Manager - iCal Channels', 'vikchannelmanager');
				break;
			case 'VCMADDNEWICALCH':
				$result = __('Add new iCal channel', 'vikchannelmanager');
				break;
			case 'VCMEDITICALCH':
				$result = __('Edit iCal channel', 'vikchannelmanager');
				break;
			case 'VCMTOGGLEICALSUBUNITS':
				$result = __('Calendar sync URLs for sub-units', 'vikchannelmanager');
				break;
			case 'VCMTOGGLEICALSUBUNITSHELP':
				$result = __('The main calendar of the room will include all of its bookings. This is sufficient to synchronize the availability with any third party system. However, it is also possible to obtain the availability calendar for every specific sub-unit in case an external system or a different configuration requires individual calendars.', 'vikchannelmanager');
				break;
			case 'VCMLISTINGDURLTIPICAL':
				$result = __('Copy this link and use it on the external system for them to import your reservations', 'vikchannelmanager');
				break;
			case 'VCMLISTINGRURLTIPICAL':
				$result = __('Insert the calendar Sync URL provided by the external system for importing their reservations', 'vikchannelmanager');
				break;
			case 'VCM_HW_DIVIDE_COST_PRROOMS':
				$result = __('Divide price for private rooms', 'vikchannelmanager');
				break;
			case 'VCM_HW_DIVIDE_COST_PRROOMS_HELP':
				$result = __('At Hostelworld, the prices for private rooms must be specified ber bed, and not per room. The price transmitted by the Channel Manager will be multiplied by the number of beds of each Private Room. This means that if you set up the pricing on your website per-private-room, this parameter should be set to Yes in order to obtain the same price at Hostelworld. This is because they expect the Channel Manager to transmit the price of one bed in a private room, but for your website this would not be a good configuration. That\'s why this parameter is helpeful when turned on.', 'vikchannelmanager');
				break;
			case 'VCM_INVMAXDATE_MESSDATE':
				$result = __('The rooms of your property are bookable on the various OTAs until %s', 'vikchannelmanager');
				break;
			case 'VCM_INVMAXDATE_MESSDATE_HELP':
				$result = __('In order for your rooms to be available for bookings on the various channels, it is necessary to periodically transmit the availability and rates inventory through your Channel Manager for the future dates. This way, you open up the possibility of making reservations for your property. You can do this by launching the two Bulk Actions, to transmit respectively the availability and the rates/restrictions. It is strongly recommended to always push this information for at least one year ahead. Alternatively, you can increase the Availability Window from the apposite configuration setting to automatize this process.', 'vikchannelmanager');
				break;
			case 'VCM_PUSH_AVAILABILITY_INV':
				$result = __('Push Availability Inventory', 'vikchannelmanager');
				break;
			case 'VCM_PUSH_RATES_INV':
				$result = __('Push Rates Inventory', 'vikchannelmanager');
				break;
			case 'VCMBPROMBUSINESSDEALTIT':
				$result = __('Business Booker Promotion', 'vikchannelmanager');
				break;
			case 'VCMBPROMBUSINESSDEALSUB':
				$result = __('Offer discounts to guests who travel for business', 'vikchannelmanager');
				break;
			case 'VCMBPROMBUSINESSDEALDSC1':
				$result = __('Target business travellers', 'vikchannelmanager');
				break;
			case 'VCMLOADPROMO':
				$result = __('Load promotions', 'vikchannelmanager');
				break;
			case 'VCMWEBSITE':
				$result = __('Website', 'vikchannelmanager');
				break;
			case 'VCMREVIEWHASREPLY':
				$result = __('Replied', 'vikchannelmanager');
				break;
			case 'VCMREVIEWNOREPLY':
				$result = __('No reply', 'vikchannelmanager');
				break;
			case 'VCMGREVVALUE':
				$result = __('Value for money', 'vikchannelmanager');
				break;
			case 'VCMGREVLOCATION':
				$result = __('Location', 'vikchannelmanager');
				break;
			case 'VCMGREVSTAFF':
				$result = __('Staff', 'vikchannelmanager');
				break;
			case 'VCMGREVCLEAN':
				$result = __('Clean', 'vikchannelmanager');
				break;
			case 'VCMGREVCOMFORT':
				$result = __('Comfort', 'vikchannelmanager');
				break;
			case 'VCMGREVFACILITIES':
				$result = __('Facilities', 'vikchannelmanager');
				break;
			case 'VCMTOTALSCORE':
				$result = __('Total score', 'vikchannelmanager');
				break;
			case 'VCMGREVCONTENT':
				$result = __('Content', 'vikchannelmanager');
				break;
			case 'VCMGREVMESSAGE':
				$result = __('Message', 'vikchannelmanager');
				break;
			case 'VCMGREVNEGATIVE':
				$result = __('Negative', 'vikchannelmanager');
				break;
			case 'VCMGREVPOSITIVE':
				$result = __('Positive', 'vikchannelmanager');
				break;
			case 'VCMMENUBPHOTOS':
				$result = __('Photos', 'vikchannelmanager');
				break;
			case 'VCMMAINTBPHOTOS':
				$result = __('Booking.com - Property and Rooms Photos', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOGALLYPROP':
				$result = __('Property Gallery', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOGALLYROOM':
				$result = __('%s Gallery', 'vikchannelmanager');
				break;
			case 'VCMBPHOTONOPFOUNDING':
				$result = __('No photos found in this gallery', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOCONFRMIG':
				$result = __('Do you want to remove this photo from the gallery?', 'vikchannelmanager');
				break;
			case 'VCMUPLOADPHOTOS':
				$result = __('Upload Photos', 'vikchannelmanager');
				break;
			case 'VCMMEDIAMANAGER':
				$result = __('Media Manager', 'vikchannelmanager');
				break;
			case 'VCMMANUALUPLOAD':
				$result = __('Upload File', 'vikchannelmanager');
				break;
			case 'VCMDROPFILES':
				$result = __('or DRAG FILES HERE', 'vikchannelmanager');
				break;
			case 'VCMDROPFILESSTOPREMOVING':
				$result = __('Press ESC from keyboard to stop deleting the files', 'vikchannelmanager');
				break;
			case 'VCMDROPFILESHINT':
				$result = __('Drag & drop some images here to upload them. It is possible to remove the uploaded images by clicking and keeping them pressed.', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOFILESUPQUEUED':
				$result = __('Total files uploaded', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOUPLOADTOBCOM':
				$result = __('Upload Photos on Booking.com', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOUPLOADTOBCOMHELP':
				$result = __('Booking.com will have to approve the photos you upload before being able to add them to your galleries. You should first upload the photos on Booking.com and wait for their verification process to complete. At this point, the photos can be added to your galleries.', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOERRLASTSTATUPD':
				$result = __('Last update was made just a few minutes ago. Please wait until %s to request a new update of the status.', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOACTIVEPHOTOS':
				$result = __('Published photos in this gallery', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOQUEUEDPHOTOS':
				$result = __('Uploaded photos for processing', 'vikchannelmanager');
				break;
			case 'VCMBPHOTONOQUEUED':
				$result = __('No photos waiting to be processed. Upload new photos to add them to the galleries.', 'vikchannelmanager');
				break;
			case 'VCMFROMMEDIAMANAGER':
				$result = __('From Media Manager', 'vikchannelmanager');
				break;
			case 'VCMFROMCOMPUTERDEVICE':
				$result = __('From your computer/device', 'vikchannelmanager');
				break;
			case 'VCMPHOTOADDCONFIRM':
				$result = __('Do you want to publish this photo? The photo will be instantly visible to everyone', 'vikchannelmanager');
				break;
			case 'VCMPHOTOADDTOGALLERY':
				$result = __('Add to gallery', 'vikchannelmanager');
				break;
			case 'VCMPHOTOSSAVEORDERBCOM':
				$result = __('Save gallery order on Booking.com', 'vikchannelmanager');
				break;
			case 'VCMPHOTORELOADCONFIRM':
				$result = __('Some photos have been accepted and added to the gallery by Booking.com!', 'vikchannelmanager');
				break;
			case 'VCMBPHOTOQUEUEDPHOTOSHELP':
				$result = __('The approval status of these photos will be automatically monitored every few minutes and will update in case of changes. It usually takes a couple of minutes to get an update on the uploaded photos.', 'vikchannelmanager');
				break;
			case 'VCMPCTS':
				$result = __('Property Class Types', 'vikchannelmanager');
				break;
			case 'VCMPCTSPLCHLD':
				$result = __('Choose the appropriate categories for your property', 'vikchannelmanager');
				break;
			case 'VCMWIZARDCONFIGCHANNEL':
				$result = __('Please enter your account information for the currently active channel to begin the configuration.', 'vikchannelmanager');
				break;
			case 'VCMCONFIGIMPORTTITLE':
				$result = __('Import Configuration', 'vikchannelmanager');
				break;
			case 'VCMCONFIGIMPORTDESC':
				$result = __('Your website still needs to be set up. Would you like to import some configuration settings from your channel account? The system will try to import rooms, rate plans, mapping information, and future reservations to set up your website. However, a full import of the rates and restrictions may not be possible, the system will import from the channel any data available. You can always choose to manually set up your website without importing anything.', 'vikchannelmanager');
				break;
			case 'VCMCONFIGIMPORTOK':
				$result = __('Yes, import data from channel', 'vikchannelmanager');
				break;
			case 'VCMCONFIGIMPORTKO':
				$result = __('No, do no import any data', 'vikchannelmanager');
				break;
			case 'VCMCONFIGIMPORTWARNTIME':
				$result = __('This process may need a few minutes to complete. Continue?', 'vikchannelmanager');
				break;
			case 'VCMCONFIGIMPSUCCESS':
				$result = __('Data imported correctly! Please double check from the IBE the configuration of your rooms, rate plans and rates before proceeding.', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZPROP':
				$result = __('Data fetched from account %s', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZROOMS':
				$result = __('Room Types Configuration', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZNEXTSTEP':
				$result = __('Next step', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZRPLANS':
				$result = __('Rate Plans Configuration', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZRPHOTOS':
				$result = __('Import Rooms Photos', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZDWNEXTRAPHOTOS':
				$result = __('Download %d extra photos for the various rooms', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZDTOTPHOTOSDLD':
				$result = __('%d photos have been downloaded.', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZDTOTRPLANSIMP':
				$result = __('%d rate plans have been imported.', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZDTOTROOMSIMP':
				$result = __('%d room types have been imported.', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZBOOKINGS':
				$result = __('Import Active Bookings', 'vikchannelmanager');
				break;
			case 'VCMCFGIMPWIZBOOKINGSNONE':
				$result = __('No active bookings with a check-in date in the future found for this account.', 'vikchannelmanager');
				break;
			case 'VCMDASHCHSCORECARD':
				$result = __('%s %s - Scorecard', 'vikchannelmanager');
				break;
			case 'VCMDASHCHSCORECARDHELP':
				$result = __('This is the scorecard for your currently active account. Scores are updated automatically every day by the Channel Manager, and they are only visible to you. Sometimes you can improve your scores by following the guidelines returned by the channel.', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_REVIEW_SCORE':
				$result = __('Review Score', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_REPLY_SCORE':
				$result = __('Reply Score', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_CONTENT_SCORE':
				$result = __('Content Score', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_AREA_AVERAGE_SCORE':
				$result = __('Area Average Score', 'vikchannelmanager');
				break;
			case 'VCMSHOWMORE':
				$result = __('Show more', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETS':
				$result = __('Property Check-in Methods', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETADDINFOTXT':
				$result = __('Instructions for guests', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETADDINFOBRANDNM':
				$result = __('Brand Name', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETADDINFOOFFLOC':
				$result = __('Off Location', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETADDINFOHOW':
				$result = __('How', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETADDINFOWHEN':
				$result = __('When', 'vikchannelmanager');
				break;
			case 'VCMVRESSPCINMETADDINFOEXPL':
				$result = __('Explanation', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPPROFILE':
				$result = __('Property Profile', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPCOMPANY':
				$result = __('Company name', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPBUILTDATE':
				$result = __('Built Date', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPRENOVATINGDATE':
				$result = __('Renovating Date', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPHOSTLOC':
				$result = __('Host Location', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPHOSTLOCOFF':
				$result = __('Off site', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPHOSTLOCON':
				$result = __('On site', 'vikchannelmanager');
				break;
			case 'VCMVRESSPROPRENTINGDATE':
				$result = __('Renting Date', 'vikchannelmanager');
				break;
			case 'VCMVRESSISCOMPANYPROFILE':
				$result = __('Is company profile?', 'vikchannelmanager');
				break;
			case 'VCMVRESSINFOUPDATED':
				$result = __('Information updated correctly on the channel!', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_CATEGORY':
				$result = __('Misconduct category', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_SUBCATEGORY':
				$result = __('Misconduct sub-category', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_DETAILSTEXT':
				$result = __('Misconduct details', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_ESCALATEREPORT':
				$result = __('Follow up on this incident?', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_ESCALATEREPORT_NO':
				$result = __('Do not escalate the report to Booking.com', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_ESCALATEREPORT_YES':
				$result = __('Escalate the report to Booking.com', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_REBOOKINGALLOWED':
				$result = __('Allow guest from re-booking?', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_REBOOKINGALLOWED_NO':
				$result = __('Do not allow this guest to book your property again', 'vikchannelmanager');
				break;
			case 'VCMGUESTMISCONDUCT_REBOOKINGALLOWED_YES':
				$result = __('Allow this guest to book your property in the future', 'vikchannelmanager');
				break;
			case 'VCMDMGDEPCOLLECTNUMDAYS':
				$result = __('Days before check-in for collection of deposit', 'vikchannelmanager');
				break;
			case 'VCMDMGDEPRETMETH':
				$result = __('Deposit return method', 'vikchannelmanager');
				break;
			case 'VCMDMGDEPRETWHEN':
				$result = __('Deposit will be returned', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHHEADRTYPES':
				$result = __('Room-types/Accommodations', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHHEADRANGEDT':
				$result = __('Date Range', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHHEADCHTOUPD':
				$result = __('Channels to update', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHHEADCHRPTOUPD':
				$result = __('Channels and Rate Plans to update', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHHEADWEBRPLAN':
				$result = __('Website Rate Plan', 'vikchannelmanager');
				break;
			case 'VCMAVPUSHHEADRATESMOD':
				$result = __('Rates modification', 'vikchannelmanager');
				break;
			case 'VCMMAINTCHPROMOTIONS':
				$result = __('%s - Promotions', 'vikchannelmanager');
				break;
			case 'VCMCHPROMLOADALL':
				$result = __('Load all promotions from %s', 'vikchannelmanager');
				break;
			case 'VCMMAINTCHNEWPROMOTION':
				$result = __('%s - New Promotion', 'vikchannelmanager');
				break;
			case 'VCMMAINTCHEDITPROMOTION':
				$result = __('%s - Edit Promotion', 'vikchannelmanager');
				break;
			case 'VCMPROMMULTINDEALTIT':
				$result = __('Multiple Nights', 'vikchannelmanager');
				break;
			case 'VCMPROMMULTINDEALSUB':
				$result = __('Apply discounts to guests staying longer', 'vikchannelmanager');
				break;
			case 'VCMPROMMULTINDSC1':
				$result = __('Incentivize your guests to stay longer', 'vikchannelmanager');
				break;
			case 'VCMPROMMULTINDSC2':
				$result = __('Offer discounts after N nights', 'vikchannelmanager');
				break;
			case 'VCMPROMAPPLYDOW':
				$result = __('Apply different discounts depending on the week-day', 'vikchannelmanager');
				break;
			case 'VCMMAXNIGHTS':
				$result = __('Maximum Nights', 'vikchannelmanager');
				break;
			case 'VCMPROMQMEMBEXTRADISC':
				$result = __('Apply additional discount to members?', 'vikchannelmanager');
				break;
			case 'VCMPROMQMEMBEXTRADISCDESC':
				$result = __('If you set a value greater than zero, this will be summed to the base discount, but only to members.', 'vikchannelmanager');
				break;
			case 'VCMPROMQHMANYMULTIN':
				$result = __('Which number of night should be discounted?', 'vikchannelmanager');
				break;
			case 'VCMPROMQHMANYMULTINDESC':
				$result = __('If you wish to discount the 3rd night of stay, enter 3. If also the 6th or 9th night should be discounted, enable the recurring option.', 'vikchannelmanager');
				break;
			case 'VCMPROMMULTINRECUR':
				$result = __('Discount recurring numbers of nights', 'vikchannelmanager');
				break;
			case 'VCMPROMSTATUS':
				$result = __('Promotion Status', 'vikchannelmanager');
				break;
			case 'VCMPROMSTATUSACTIVE':
				$result = __('Active', 'vikchannelmanager');
				break;
			case 'VCMPROMSTATUSINACTIVE':
				$result = __('Inactive', 'vikchannelmanager');
				break;
			case 'VCMBPROMQVISCH':
				$result = __('When can guests see this promotion on %s?', 'vikchannelmanager');
				break;
			case 'VCMBPROMQNAMEDESC1CH':
				$result = __('This name is just for your reference. It won\'t be displayed to customers on %s.', 'vikchannelmanager');
				break;
			case 'VCM_TIP_CONF_NEWACCOUNT':
				$result = __('Need to add another account for %s? Just enter the new Property ID by replacing the current value (this action will not make you lose any data) and click on Save. Then use the page &quot;Hotel - Synchronize Rooms&quot; to map the rooms of the new account.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBAPI_WARNPARAM_EMPTY':
				$result = __('Warning: this value will be automatically populated as soon as you connect your Airbnb account', 'vikchannelmanager');
				break;
			case 'VCM_AIRBAPI_WARNPARAM_FULL':
				$result = __('Warning: changing this value may break your connection, and you may need to restart it', 'vikchannelmanager');
				break;
			case 'VCM_TIP_NEWACCOUNT_AIRBNBAPI':
				$result = __('Need to add another Airbnb account? Click again the button &quot;Connect with Airbnb&quot; to enable the connection with a new host account (any previously connected host account will remain active). Then use the page &quot;Hotel - Synchronize Rooms&quot; to map the listings of the new account. Please notice that if you created new listings under the same host account (user ID) on Airbnb, then you do not need to connect a new account, you can simply use the page &quot;Hotel - Synchronize Rooms&quot; to map the new listings.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DOSYNC':
				$result = __('Synchronize Listings with Airbnb', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_BTAXID':
				$result = __('Business tax id', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_BTAXID_HELP':
				$result = __('The business tax id identifies the business as a taxpayer. For example, in the US, it would be the EIN. For other countries it would be the Company VAT Number. Leave the field empty if you do not have a business tax id.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_REGID':
				$result = __('Registration id', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_REGID_HELP':
				$result = __('Represents the ID number that a host receives from a jurisdiction when it registers to collect, remit, and report the applicable taxes. In some cases, this can be the same as the Business tax id.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_SECDEP':
				$result = __('Security Deposit', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_SECDEP_HELP':
				$result = __('A security deposit held by Airbnb and refunded to the guest unless the host makes a claim within 48 hours after guest checks out. Set it to 0 to remove the security deposit.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_CLEANFEE':
				$result = __('Cleaning Fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_CLEANFEE_HELP':
				$result = __('Cleaning fee applied on top of the reservations. Set it to 0 to remove the cleaning fees.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEES':
				$result = __('Standard Fees', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEES_HELP':
				$result = __('Fees that are applied on top of the nightly price for all reservations. The supported online standard fees are: Resort, Community, Management and Linen fees.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_LISTINGS':
				$result = __('Listings', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE':
				$result = __('Standard Fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE':
				$result = __('Fee type', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_OFFLINE':
				$result = __('Offline', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_OFFLINE_HELP':
				$result = __('If enabled, the fee will be collected when the guest checks in or out. If disabled, the fee will be collected at the time of booking', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_ONLINE':
				$result = __('Online', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_UNIT_TYPE':
				$result = __('Unit type', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_UNIT_TYPE_KWH':
				$result = __('Per kilowatt hour', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_UNIT_TYPE_LT':
				$result = __('Per liter', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_UNIT_TYPE_CM':
				$result = __('Per cubic meter', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_CHARGE_TYPE':
				$result = __('Charge type', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_CHARGE_TYPE_GROUP':
				$result = __('Per group', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_CHARGE_TYPE_PERSON':
				$result = __('Per person', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_RESORT':
				$result = __('Resort fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_MNG':
				$result = __('Management fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_COMM':
				$result = __('Community Fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_LINEN':
				$result = __('Linen fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_ELEC':
				$result = __('Electricity', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_WATER':
				$result = __('Water', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_HEATING':
				$result = __('Heating', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_AIRCOND':
				$result = __('Air Conditioning', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_UTILITY':
				$result = __('Utility', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_MAPPING_CRITICAL':
				$result = __('There was an error while communicating the listings information. Please repeat the sync operation until no errors are displayed, or the future reservations for these listings/rooms may not be transmitted to your Channel Manager. Contact us for any help.', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AIRBNB_ACCPRR_RS':
				$result = __('Error accepting pending reservation request:\n%s', 'vikchannelmanager');
				break;
			case 'MSG_BASE_ERROR_AIRBNB_DENPRR_RS':
				$result = __('Error declining pending reservation request:\n%s', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_AIRBNB_ACCPRR_RS':
				$result = __('Booking Request Accepted', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_AIRBNB_DENPRR_RS':
				$result = __('Booking Request Declined', 'vikchannelmanager');
				break;
			case 'VCMMAINTDECLINEBOOKING':
				$result = __('Vik Channel Manager - Decline and cancel reservation', 'vikchannelmanager');
				break;
			case 'VCM_DECLINE_BOOKING_TITLE':
				$result = __('Decline and cancel reservation', 'vikchannelmanager');
				break;
			case 'VCM_DECLINE_BOOKING_DESCR':
				$result = __('The channel %s requires a reason for declining the reservation request.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DECLINE_REASON':
				$result = __('Decline reason', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DECLINE_REASON_DESCR':
				$result = __('An Airbnb-provided reason for declining the reservation request.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DECLINE_GUESTMESS':
				$result = __('Message to guest', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DECLINE_GUESTMESS_DESCR':
				$result = __('A reply message to the guest that explains the need to decline.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DECLINE_AIRBNBMESS':
				$result = __('Message to Airbnb', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_DECLINE_AIRBNBMESS_DESCR':
				$result = __('An optional private message sent to Airbnb. The guest does not see this message.', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_DATES_NOT_AVAILABLE':
				$result = __('Dates not available', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_NOT_A_GOOD_FIT':
				$result = __('Not a good fit', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_WAITING_FOR_BETTER_RESERVATION':
				$result = __('Waiting for better reservation', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_NOT_COMFORTABLE':
				$result = __('Not comfortable', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNBICAL_DEPRECATED_ALERT':
				$result = __('You are using a deprecated integration for Airbnb in iCal format. The new powerful and complete API integration is now available. Upgrade to the API version of Airbnb!', 'vikchannelmanager');
				break;
			case 'VCM_SEND_SPECOFFER_TITLE':
				$result = __('Send %s a special offer', 'vikchannelmanager');
				break;
			case 'VCM_SEND_SPECOFFER_DESCR':
				$result = __('%s: %s will have 24 hours to book. In the meantime, your calendars will remain open.', 'vikchannelmanager');
				break;
			case 'VCM_SEND_SPECIAL_OFFER':
				$result = __('Send special offer', 'vikchannelmanager');
				break;
			case 'VCM_TOTAL_BEFORE_TAX_SP':
				$result = __('Subtotal inclusive of any cleaning or extra guest fees. Taxes are exclusive and will be applied automatically later, if necessary.', 'vikchannelmanager');
				break;
			case 'VCM_SPOFFER_SENT_GUEST':
				$result = __('Special offer sent to guest', 'vikchannelmanager');
				break;
			case 'VCMMAINTHOSTGUESTREVIEW':
				$result = __('Vik Channel Manager - Write a review for your guest', 'vikchannelmanager');
				break;
			case 'VCM_REVIEW_GUEST_TITLE':
				$result = __('Write review', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_PUBLIC':
				$result = __('Leave a public review', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_PUBLIC_HELP':
				$result = __('Write a fair, honest review about your guest\'s stay so future hosts know what to expect.', 'vikchannelmanager');
					break;
			case 'VCM_HTGREVIEW_PRIVATE':
				$result = __('Add a private note', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_PRIVATE_HELP':
				$result = __('Offer suggestions, or say thanks for being a great guest. Your note won\'t be published on the guest\'s profile.', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_CLEAN':
				$result = __('Cleanliness', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_CLEAN_HELP':
				$result = __('Did your guest leave your space clean?', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_COMM':
				$result = __('Communication', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_COMM_HELP':
				$result = __('How clearly did the guest communicate their plans, questions, and concerns?', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_HRULES':
				$result = __('Observance of house rules', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_HRULES_HELP':
				$result = __('Did the guest observe your house rules?', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_WAGAIN':
				$result = __('Would you host this guest again?', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_WAGAIN_HELP':
				$result = __('Your answer won\'t be published anywhere, and the guest won\'t see it.', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_COMMENT':
				$result = __('Leave a comment', 'vikchannelmanager');
				break;
			case 'VCM_HOST_TO_GUEST_REVIEW':
				$result = __('Host-to-guest review submitted', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_OVERALL_RATING':
				$result = __('Overall rating', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_TOT_REVIEWS':
				$result = __('Total reviews', 'vikchannelmanager');
				break;
			case 'VCM_PREAPPROVAL_SENT_GUEST':
				$result = __('Pre-approval sent to guest', 'vikchannelmanager');
				break;
			case 'VCM_SPOFFER_WITHDRAWN':
				$result = __('Special offer withdrawn', 'vikchannelmanager');
				break;
			case 'VCM_PREAPPROVAL_WITHDRAWN':
				$result = __('Pre-approval withdrawn', 'vikchannelmanager');
				break;
			case 'VCMMENUAIRBMNGLST':
				$result = __('Manage Listings', 'vikchannelmanager');
				break;
			case 'VCM_LOAD_DETAILS':
				$result = __('Load details', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_NOLIST_FOUND':
				$result = __('No listings found. Try to load their details if no errors previously occurred.', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_NEW':
				$result = __('Create New Listing', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_EDIT':
				$result = __('Edit Listing', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_LOCDESCRS':
				$result = __('Localized Descriptions', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_LOCDESCRS_ONLYEDIT':
				$result = __('The descriptions for the listing in various languages will be available after creating the listing itself.', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_PHOTOS':
				$result = __('Upload/Manage Photos', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_PHOTOS_ONLYEDIT':
				$result = __('The photos for the listing will be available after creating the listing itself.', 'vikchannelmanager');
				break;
			case 'VCM_PHOTO_CAPTION':
				$result = __('Photo caption', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_ROOMSDESCR':
				$result = __('Rooms Description', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_ROOMSDESCR_ONLYEDIT':
				$result = __('The descriptions for the bedrooms will be available after creating the listing.', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_BOOKSETTINGS':
				$result = __('Booking Settings', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_BOOKSETTINGS_ONLYEDIT':
				$result = __('The listing booking settings will be available after creating the listing.', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_AVRULES':
				$result = __('Availability Rules', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_AVRULES_ONLYEDIT':
				$result = __('The listing availability rules will be available after creating the listing.', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_PRSETTINGS':
				$result = __('Pricing Settings', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_PRSETTINGS_ONLYEDIT':
				$result = __('The listing pricing settings will be available after creating the listing.', 'vikchannelmanager');
				break;
			case 'VCMMAINTCANCACTIVEBOOKING':
				$result = __('Vik Channel Manager - Cancel active reservation', 'vikchannelmanager');
				break;
			case 'VCM_CANCACTIVE_BOOKING_TITLE':
				$result = __('Cancel active reservation', 'vikchannelmanager');
				break;
			case 'VCM_CANCACTIVE_BOOKING_DESCR':
				$result = __('The channel %s requires a reason for cancelling the active reservation. Please notice that cancellation penalties may be applied by the channel when the host cancels an active reservation.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_CANCACTIVE_REASON':
				$result = __('Cancellation reason', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_CANCACTIVE_REASON_DESCR':
				$result = __('An Airbnb-provided reason for cancelling the active reservation.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_CANCACTIVE_GUESTMESS_DESCR':
				$result = __('A message to the guest that explains the need to cancel.', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_NEEDCHANGE':
				$result = __('Need to change', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_BADREV_SPARSEPROF':
				$result = __('Bad reviews or sparse profile', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_UNAUTHORIZED_PARTY':
				$result = __('Unauthorized party', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_BEHAVIOR':
				$result = __('Bad behavior', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_ASKED':
				$result = __('Asked to cancel', 'vikchannelmanager');
				break;
			case 'VCM_DECL_REASON_OTHER':
				$result = __('Other reasons', 'vikchannelmanager');
				break;
			case 'VCM_APP_API_PWD':
				$result = __('API Password', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_ERR1':
				$result = __('The system has detected that you are using an outdated and deprecated connection with the channel <strong>Airbnb</strong>. This connection is based on iCal calendars, which generate a lot of traffic with a poor sync service. Due to the recent partnership with <strong>Airbnb</strong>, all iCal calendars should be upgraded to the new <strong>API connection method</strong> as soon as possible, to ensure the best connection. Vik Channel Manager needs to on-board to the Airbnb API platform all listings that are currently connected through iCal, and so this message won\'t be dismissed until the new configuration will be complete.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_FINDMORE':
				$result = __('Click here to find out more', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_ERR2':
				$result = __('You can activate the new Airbnb API channel <strong>for free</strong><sup>1</sup> by clicking the button below. However, please make sure to read the following conditions that will be applied:', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT1':
				$result = __('The activation of the new Airbnb API channel is free for all active users until the expiration date of their subscription.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT2':
				$result = __('The Airbnb API channel has got slightly different (higher) costs per year than the old iCal version. The fees are exactly the same as for any other existing API-based channel.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT3':
				$result = __('Those who have an active recurring subscription with automated renewal will pay the same fee for this new channel as the subscription cost is frozen, and the old iCal version no longer exists. If the renewal gets suspended or terminated due to any reasons, the following points will apply.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT4':
				$result = __('Those who DON\'T have a recurring subscription and wish to keep using the Airbnb channel, will have to pay a slightly different amount for the new Airbnb API version at their next renewal.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT5':
				$result = __('In accordance with Airbnb, the old iCal integration will no longer be available to renewals or new connections, as this channel will be dismissed. However, it will keep working until the expiration date of your API Key.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT6':
				$result = __('Those who don\'t want to upgrade to the new Airbnb API channel, will have to choose (or switch to) the &quot;Generic iCal&quot; service from our channels list, because the only channel with the name &quot;Airbnb&quot; will have to be based on API connections.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_POINT7':
				$result = __('Once the new API version will be activated, it will need to be configured. When the account(s) and listings will be synced, you will be asked to delete the old iCal calendar links with Airbnb to completely dismiss this message.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_ERR3':
				$result = __('Thousands of properties have been waiting for this new connection between Vik Channel Manager and Airbnb to be available! Real-time sync with full control over any possible API service, from Guest Reviews to Messaging, Promotions, Scorecards, Booking Inquiries, Request to Book reservations, Special Offers and much more, is what your Channel Manager will be capable of doing!', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_ERR4':
				$result = __('Please notice that keeping both services alive with the same name was not possible for our company after the partnership with Airbnb. For this reason, the old and consuming iCal integration will be terminated in accordance with Airbnb. The difference with the subscription fees applied for this new channel is given by the fact that Airbnb, Booking.com and Expedia will need to have the same costs. Please visit our website for more details.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_ACTIVATE':
				$result = __('Click here to activate Airbnb API', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_WARN1':
				$result = __('The new channel <strong>Airbnb API</strong> has been activated! However, the old iCal integration is still active on your Channel Manager, and this will need to be dismissed as soon as possible. Please make sure to complete the configuration of the new Airbnb API channel so that you will be able to delete the old iCal channels and get rid of this message.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_WARN2':
				$result = __('Please make sure to read (again) the following conditions that will be applied:', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_WARN3':
				$result = __('Please visit our website if you have any questions.', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_WARN4':
				$result = __('Have you connected all the necessary accounts and listings with the new channel?', 'vikchannelmanager');
				break;
			case 'AIRBNB_UPGNOTICE_DEACTIVATE':
				$result = __('Click here to de-activate Airbnb iCal', 'vikchannelmanager');
				break;
			case 'VCM_CONNECTIVITY':
				$result = __('Connectivity', 'vikchannelmanager');
				break;
			case 'VCM_RECOVERYTOOLS':
				$result = __('Recovery tools', 'vikchannelmanager');
				break;
			case 'VCM_DOWNLOAD_OTABOOKING':
				$result = __('Download reservation ID', 'vikchannelmanager');
				break;
			case 'VCM_ASK_CONTINUE':
				$result = __('Continue?', 'vikchannelmanager');
				break;
			case 'VCM_DOWNLOAD':
				$result = __('Download', 'vikchannelmanager');
				break;
			case 'VCM_DOWNOTABOOK_SENT':
				$result = __('The request was sent successfully! However, this is an asynchronous process, and if the reservation you requested is available, it will be transmitted shortly to your Channel Manager. If no reservation comes in within the next 60 seconds, please do not retry again, because it means the reservation is not available.', 'vikchannelmanager');
				break;
			case 'VCM_AV_WINDOW':
				$result = __('Channels Availability Window', 'vikchannelmanager');
				break;
			case 'VCM_AV_WINDOW_DESCR':
				$result = __('Choose how far in advance your rooms should be bookable on the channels. If manual, you will need to periodically launch the Bulk Actions to open up the availability in the future months. Otherwise, rates and availability will be automatically transmitted to the channels for the selected booking window.', 'vikchannelmanager');
				break;
			case 'VCM_AV_WINDOW_MANUAL':
				$result = __('Manual', 'vikchannelmanager');
				break;
			case 'VCM_AV_WINDOW_XMONTHS':
				$result = __('%d months', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_AUTOBULKCUSTAR_RQ':
				$result = __('Auto Bulk Availability Window', 'vikchannelmanager');
				break;
			case 'MSG_BASE_SUCCESS_CHANNELS_AUTOBULKRAR_RQ':
				$result = __('Auto Bulk Rates Window', 'vikchannelmanager');
				break;
			case 'VCM_SUBSCR_APIKEY':
				$result = __('Subscription API Key', 'vikchannelmanager');
				break;
			case 'VCM_CHECK_EXPDATE':
				$result = __('Check expiration date', 'vikchannelmanager');
				break;
			case 'VCM_EXPORT_ROOM_ICAL':
				$result = __('Export iCal calendar', 'vikchannelmanager');
				break;
			case 'VCM_EXPORT_ROOM_ICAL_DESCR':
				$result = __('This link can be used to download the real-time updated iCal calendar with the bookings (availability) information for this room to sync third party calendars. Please notice that this URL will only work to export the availability of the room.', 'vikchannelmanager');
				break;
			case 'VCM_SUGGEST_BULKACTIONS':
				$result = __('If this is the first time you are configuring an account for this channel, do not forget to launch the two Bulk Actions to complete the configuration process. Make sure the rates and availability on your website are ready and up to date to push back the information to the channel. These steps are necessary to put back online for bookings your rooms on the various channels after their first connection.', 'vikchannelmanager');
				break;
			case 'VCM_CALENDARS':
				$result = __('Calendars', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_CHECK_CALENDARS':
				$result = __('This tool will read the current calendar information for this listing on Airbnb. Calendars include the pricing details, the nightly availability, and any booking restrictions. Use the Bulk Actions to update the calendar information on Airbnb.', 'vikchannelmanager');
				break;
			case 'VCM_UNITS':
				$result = __('Units', 'vikchannelmanager');
				break;
			case 'VCM_PRICE':
				$result = __('Price', 'vikchannelmanager');
				break;
			case 'VCM_RELOAD':
				$result = __('Reload', 'vikchannelmanager');
				break;
			case 'VCM_ACCESSIBILITY':
				$result = __('Accessibility', 'vikchannelmanager');
				break;
			case 'VCM_CATEGORY':
				$result = __('Category', 'vikchannelmanager');
				break;
			case 'VCM_LISTING':
				$result = __('Listing', 'vikchannelmanager');
				break;
			case 'VCM_NOAMENITIES_ACCESS':
				$result = __('Please select one or more amenities of type Accessibility', 'vikchannelmanager');
				break;
			case 'VCM_LOSPRICES':
				$result = __('LOS Prices', 'vikchannelmanager');
				break;
			case 'VCM_DIFF_ALTER_PERCHANNEL':
				$result = __('Different alterations per channel', 'vikchannelmanager');
				break;
			case 'VCM_APPLY_ALL_ROOMS':
				$result = __('Apply to all rooms', 'vikchannelmanager');
				break;
			case 'VCM_ADOPTION_PCENTAGE':
				$result = __('Adoption percentage', 'vikchannelmanager');
				break;
			case 'VCM_APPLICABLE_ROOMS_TOT':
				$result = __('Applicable rooms: %d', 'vikchannelmanager');
				break;
			case 'VCM_APPEARANCE_PREF':
				$result = __('Appearance', 'vikchannelmanager');
				break;
			case 'VCM_APPEARANCE_PREF_LIGHT':
				$result = __('Light', 'vikchannelmanager');
				break;
			case 'VCM_APPEARANCE_PREF_AUTO':
				$result = __('Auto', 'vikchannelmanager');
				break;
			case 'VCM_APPEARANCE_PREF_DARK':
				$result = __('Dark', 'vikchannelmanager');
				break;
			case 'VCMRPUSHMINMAXADVRES':
				$result = __('Transmit min/max advance booking time', 'vikchannelmanager');
				break;
			case 'VCMRPUSHMINMAXADVRESHELP':
				$result = __('If enabled, the Channel Manager will transmit the minimum and maximum booking time in advance to all channels supporting this feature. This serves to define how early or how late at most bookings can be placed for a specific rate plan. For example, a refundable rate plan could have a minimum advance booking time to a few hours or days, so that it can be excluded for last-minute reservations. Such settings will be read from the IBE.', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDERS':
				$result = __('Expiration Reminders', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDERS_HELP':
				$result = __('If enabled, the Channel Manager will display alert messages when the expiration date of the service is near. This will also trigger email reminders to be sent to the specified email address. If disabled, the expiration date of the subscription can be manually checked from the apposite button in the page Dashboard.', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_ROOM':
				$result = __('Listing Room', 'vikchannelmanager');
				break;
			case 'VCM_PHOTO_CATEGORY_HELP':
				$result = __('Every photo you upload must be assigned to one <strong>category</strong> to specify what\'s that photo about. Available categories are:<br/><ul><li><strong>Listing</strong> - a generic photo of the main listing.</li><li><strong>Listing Accessibility Amenity</strong> - a photo describing an accessibility amenity of the main listing (i.e. Disabled Parking Spot).</li><li><strong>Listing Room</strong> - a photo describing a room (previously created) of the listing (i.e. the kitchen or the bathroom).</li><li><strong>Listing Room Accessibility Amenity</strong> - a photo describing an accessibility amenity of a room (previously created) of the main listing (i.e. Grab rails in toilet).</li></ul>', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_HASNO_ROOMS':
				$result = __('The listing has got no rooms defined. Please create one first.', 'vikchannelmanager');
				break;
			case 'VCM_WARN_SUBSCR_EXPIRED':
				$result = __('Warning: your subscription is expired. The Channel Manager will not be able to communicate with any channels until the service will be renewed.', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDER_DAYS':
				$result = __('Warning: your subscription will expire in %d days (%s). Do not forget to renew the service before then to not lose the connectivity.', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDER_MSUBJ':
				$result = __('Your Channel Manager subscription will expire soon', 'vikchannelmanager');
				break;
			case 'VCM_EXPIRATION_REMINDER_MCONT':
				$result = __("This message was generated automatically by your own website at %s.\n\nThis is a reminder to inform you that your E4jConnect subscription for the Channel Manager service will expire in %d days (%s). Do not forget to renew the service before then to not lose the connectivity.", 'vikchannelmanager');
				break;
			case 'VCM_CURRENCY_CONVERTER':
				$result = __('Currency Converter', 'vikchannelmanager');
				break;
			case 'VCM_DEFCURRENCY':
				$result = __('Default currency', 'vikchannelmanager');
				break;
			case 'VCM_CONVTOCURRENCY':
				$result = __('Convert to currency', 'vikchannelmanager');
				break;
			case 'VCM_GETCONVRATE':
				$result = __('Get conversion', 'vikchannelmanager');
				break;
			case 'VCM_EXCHRATECURR':
				$result = __('The exchange rate between %s and %s is: %s', 'vikchannelmanager');
				break;
			case 'VCM_CURRCONV_SUGG_PCENT':
				$result = __('The rates should be modified by %s in order to have your rates in %s converted to %s.', 'vikchannelmanager');
				break;
			case 'VCM_PLEASE_SELECT':
				$result = __('Please make a selection', 'vikchannelmanager');
				break;
			case 'VCM_CURRCONV_ERR_GENERIC':
				$result = __('An error occurred while getting the exchange rate. Your server may not be able to fetch the remote exchanging data due to some limitations. Check the full error from your browser console.', 'vikchannelmanager');
				break;
			case 'VCM_RAR_PREVERR_MAPPING':
				$result = __('This request was not executed to prevent API errors due to outdated mapping information, please relaunch the Bulk Action - Rates Upload and make sure to select the proper rate plan to update', 'vikchannelmanager');
				break;
			case 'VCMSMARTBALRULEAVBLOCK':
				$result = __('Close all rooms no matter what\'s the real remaining availability (Block dates)', 'vikchannelmanager');
				break;
			case 'VCM_BLOCK_DATES':
				$result = __('Block dates', 'vikchannelmanager');
				break;
			case 'VCM_DATE_BLOCKED':
				$result = __('Date blocked', 'vikchannelmanager');
				break;
			case 'VCM_EXCLUDED_DATES':
				$result = __('Excluded dates', 'vikchannelmanager');
				break;
			case 'VCM_TOTROOM_UNITS_TYPES':
				$result = __('Total room units: %d - Total room types: %d', 'vikchannelmanager');
				break;
			case 'VCM_SMBAL_AVTYPE_LIMIT':
				$result = __('Limited units', 'vikchannelmanager');
				break;
			case 'VCM_SMBAL_AVTYPE_UNITS':
				$result = __('Units threshold', 'vikchannelmanager');
				break;
			case 'VCM_DATE_BLOCKED_HELP':
				$result = __('Some previously defined block-date rules are making this day not bookable on the OTAs. You can remove the rule that is currently blocking the room on this date, or you can exclude this day from the rule to allow again bookings on all OTAs.', 'vikchannelmanager');
				break;
			case 'VCM_UNLOCK_DAY_OPEN':
				$result = __('Unlock date %s for all rooms', 'vikchannelmanager');
				break;
			case 'VCM_NO_HOTELDETAILS':
				$result = __('You need to submit the hotel details first, by filling all the required information about your property.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_24H':
				$result = __('Google may take up to 1 business day to process your property details, which were submitted on %s. Please wait at least 24 hours before choosing the room-types to sync with Google Hotel.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_WARNPARAM_EMPTY':
				$result = __('Warning: this value will be automatically populated as soon as you submit your Hotel details', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_WARNPARAM_EMPTYSAVE':
				$result = __('Click the Save button to use your given Hotel ID', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_WARNPARAM_FULL':
				$result = __('Warning: changing this value may break your connection, do not change it', 'vikchannelmanager');
				break;
			case 'VCM_MAP':
				$result = __('Map', 'vikchannelmanager');
				break;
			case 'VCM_LATLNG_MUSTBENUMS':
				$result = __('Latitude and longitude must be coordinates expressed as decimal numbers like 43.7734385', 'vikchannelmanager');
				break;
			case 'VCM_YOUR_CURR_LOCATION':
				$result = __('Use your current location', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLE_PROPDATA_ERR':
				$result = __('Google must accept your rooms inventory or it won\'t be possible to transmit availability and rates. This process must complete with success so that the configuration of this channel will be ready.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLE_NOHINV':
				$result = __('Google requires you to submit your property details before being able to use their services. Please make sure to provide all the required information.', 'vikchannelmanager');
				break;
			case 'VCM_LBL_SUCCESS':
				$result = __('Success', 'vikchannelmanager');
				break;
			case 'VCM_LBL_WARNING':
				$result = __('Warning', 'vikchannelmanager');
				break;
			case 'VCM_LBL_ERROR':
				$result = __('Error', 'vikchannelmanager');
				break;
			case 'VCMSCORECARD_HOTEL_STATUS':
				$result = __('Hotel Status', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_ONFEED':
				$result = __('On Feed', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_MATCHMAPS':
				$result = __('Matched on Maps', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_LIVEOG':
				$result = __('Live on Google', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_LIVEOG_TOGGLE':
				$result = __('Do you want to change your Live on Google status?');
				break;
			case 'VCM_GOOGLEHOTEL_FBLR_CLICKS':
				$result = __('Booking link clicks', 'vikchannelmanager');
				break;
			case 'VCM_DEVICE':
				$result = __('Device', 'vikchannelmanager');
				break;
			case 'VCM_REGION':
				$result = __('Region', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_NO_LOS':
				$result = __('Airbnb - Ignore LOS rates', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_NO_LOS_HELP':
				$result = __('If enabled, the system will ignore any pricing configuration for your rooms on your website based on LOS (Length of Stay). If disabled, the system will detect if LOS rates have been defined for the rooms through your website and will transmit to Airbnb the same exact settings. Those who prefer to define weekly or monthly discounts for each Airbnb listing, rather than pushing LOS rates to Airbnb, can choose to ignore LOS rates defined for the website.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLE_MULTI_HOTEL_ASK':
				$result = __('Do you manage multiple hotels/properties under the same website? If your hotels/properties have a separate Google Business Account you can add a new hotel account.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLE_MULTI_HOTEL_ADD':
				$result = __('Add new hotel account', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLE_MULTI_HOTEL_NO':
				$result = __('No, do not remind me again', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_PET':
				$result = __('Pet fee', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_TYPE_SHORTCLEANING':
				$result = __('Short Term Cleaning Fees', 'vikchannelmanager');
				break;
			case 'VCM_HOTEL_MAIN_PIC':
				$result = __('Main picture', 'vikchannelmanager');
				break;
			case 'VCM_HOTEL_MAIN_PIC_DESCR':
				$result = __('Channels like Google Hotel may display this picture (photo or logo) in the landing page for the Free Booking Links. Do not use big or heavy images.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLE_PROPDATA_3DAYS':
				$result = __('Please notice that Google may take up to 3 business days (Monday to Friday) to process and accept your property information. Please try again later until no errors will be displayed.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEHOTEL_NOSCORECARD_HELP':
				$result = __('Google must process and accept your property details first (process may take up to 3 business days), and then you should sync the rooms you want to share with the Google Hotel services by using the apposite page from the top menu. At that point, your property scorecard will be available. Please ignore this message and try again later if you have already completed the configuration.', 'vikchannelmanager');
				break;
			case 'VCM_ICAL_CANCELLATIONS':
				$result = __('Allow iCal Cancellations', 'vikchannelmanager');
				break;
			case 'VCM_ICAL_CANCELLATIONS_HELP':
				$result = __('If enabled, the iCal calendars that will no longer contain previously and active reservations downloaded, will allow the system to automatically cancel such missing reservations. If disabled, booking cancellations from iCal calendars will have to be performed manually from your website.', 'vikchannelmanager');
				break;
			case 'VCM_TOT_LISTINGS_UNPUBLISHED':
				$result = __('Total listings unpublished: %d', 'vikchannelmanager');
				break;
			case 'VCM_RETRANSMIT_UNPUBLISHED_STATUS':
				$result = __('Re-transmit unpublished status', 'vikchannelmanager');
				break;
			case 'VCM_RETRANSMIT_UNPUBLISHED_STATUS_HELP':
				$result = __('This is helpful only if you recently disconnected and re-connected this Host Account ID, because the process may have published all of your listings again. Try to not disconnect and reconnect your Host Account to avoid discrepancies with the information available.', 'vikchannelmanager');
				break;
			case 'VCM_CH_NOROOMS_FOUND':
				$result = __('No rooms found. Try to load their details if no errors previously occurred.', 'vikchannelmanager');
				break;
			case 'VCM_BEDROOMS':
				$result = __('Bedrooms', 'vikchannelmanager');
				break;
			case 'VCM_BED_SIZE':
				$result = __('Size', 'vikchannelmanager');
				break;
			case 'VCM_EXTRA_BEDS':
				$result = __('Extra Beds', 'vikchannelmanager');
				break;
			case 'VCM_ATTRIBUTES':
				$result = __('Attributes', 'vikchannelmanager');
				break;
			case 'VCM_TYPEOFROOM':
				$result = __('Type of room', 'vikchannelmanager');
				break;
			case 'VCM_OPTIONAL':
				$result = __('Optional', 'vikchannelmanager');
				break;
			case 'VCM_QUICK_ACTIONS':
				$result = __('Quick Actions', 'vikchannelmanager');
				break;
			case 'VCMMENURATESOVERV':
				$result = __('Rates Overview', 'vikchannelmanager');
				break;
			case 'VCM_MNGPRODUCT_NEW':
				$result = __('Create new room-type', 'vikchannelmanager');
				break;
			case 'VCM_MNGPRODUCT_EDIT':
				$result = __('Edit room-type', 'vikchannelmanager');
				break;
			case 'VCM_VRBO_WARNPARAM_EMPTY':
				$result = __('Warning: this value will be automatically populated as soon as you submit your listing details', 'vikchannelmanager');
				break;
			case 'VCM_VRBO_NO_LISTINGS':
				$result = __('No listings found. Create your listings manually, or generate them from the website listings (if any).', 'vikchannelmanager');
				break;
			case 'VCM_VRBO_GEN_FROM_WEBSITE':
				$result = __('Generate listings from website', 'vikchannelmanager');
				break;
			case 'VCM_VRBO_LISTING_CONTVALIDATION_STATUS':
				$result = __('Listing content validation status (before saving)', 'vikchannelmanager');
				break;
			case 'VCM_RESET_DATA':
				$result = __('Reset data', 'vikchannelmanager');
				break;
			case 'VCM_VRBO_LISTING_CONTVALIDATION_OK':
				$result = __('Listing content is valid', 'vikchannelmanager');
				break;
			case 'VCM_MAPPING_STATUS':
				$result = __('Mapping status', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_SYNCED':
				$result = __('Listing synced', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_NOT_SYNCED':
				$result = __('Listing not synced', 'vikchannelmanager');
				break;
			case 'VCM_ELIGIBLE_LISTINGS':
				$result = __('Eligible listings', 'vikchannelmanager');
				break;
			case 'VCM_ACCEPTED_PAYM_FORMS':
				$result = __('Accepted payment forms', 'vikchannelmanager');
				break;
			case 'VCM_PRELOADING_ASSETS':
				$result = __('Preloading channel assets', 'vikchannelmanager');
				break;
			case 'VCM_LOADING':
				$result = __('Loading', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_PRCSETTINGS_HELP':
				$result = __('If you need to define additional types of fees for your listings, such as linen fees, pet fees, electricity fees etc.. then you should use the page Settings and leave these settings unchanged.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_PRCSETTINGS_HELP2':
				$result = __('Important notice: the following Pricing Settings and Standard Fees will be transmitted to Airbnb only when launching the &quot;Bulk Action - Rates Upload&quot; function. No matter what dates or rates you will be updating with the Bulk Action, this information will be transmitted to Airbnb for all listings involved in the request.', 'vikchannelmanager');
				break;
			case 'VCM_APPLY_CURRENCY_CONVERSION':
				$result = __('Apply Currency Conversion', 'vikchannelmanager');
				break;
			case 'VCM_ACCOUNT_ROOMS_MAPPED':
				$result = __('Total rooms mapped for this account: %d.', 'vikchannelmanager');
				break;
			case 'VCM_ACCOUNT_NO_MAPPED_ROOMS_WARN':
				$result = __('The account ID must be valid and the connection with the Channel Manager must be enabled. This will allow you to map/synchronize the rooms of this account from the page %s.', 'vikchannelmanager');
				break;
			case 'VCM_NEW_ACCOUNT_SAVE_WARN':
				$result = __('Make sure to click the Save button before visiting the page to synchronize the rooms for this new account.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_ERR1':
				$result = __('The system has detected that you are using an outdated and deprecated connection with the channel <strong>Vrbo</strong>. This connection is based on iCal calendars, which generate a lot of traffic with a poor sync service. Due to the recent partnership with <strong>Vrbo</strong>, all iCal calendars should be upgraded to the new <strong>API connection method</strong> as soon as possible, to ensure the best connection. Vik Channel Manager needs to on-board to the Vrbo API platform all listings that are currently connected through iCal, and so this message won\'t be dismissed until the new configuration will be complete.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_ERR2':
				$result = __('You can activate the new Vrbo API channel <strong>for free</strong><sup>1</sup> by clicking the button below. However, please make sure to read the following conditions that will be applied:', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT1':
				$result = __('The activation of the new Vrbo API channel is free for all active users until the expiration date of their subscription.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT2':
				$result = __('The Vrbo API channel has got slightly different (sometimes higher) costs per year than the old iCal version. The fees are exactly the same as for any other existing API-based channel.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT3':
				$result = __('Those who have an active recurring subscription with automated renewal will pay the same fee for this new channel because the subscription cost is frozen, and because the old iCal version no longer exists. If the renewal gets suspended or terminated due to any reasons, the following points will apply.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT4':
				$result = __('Those who DON\'T have a recurring subscription and wish to keep using the Vrbo channel, will have to pay a slightly different amount for the new Vrbo API version at their next renewal.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT5':
				$result = __('In accordance with Vrbo, the old iCal integration will no longer be available to renewals or new connections, as this channel will be dismissed. However, it will keep working until the expiration date of your API Key is reached.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT6':
				$result = __('Those who don\'t want to upgrade to the new Vrbo API channel, will have to choose (or switch to) the &quot;Generic iCal&quot; service from our channels list, because the only channel with the name &quot;Vrbo&quot; offered by our service will have to be based on API connections.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_POINT7':
				$result = __('Once the new API version will be activated, it will need to be configured. When the listings will be synced with Vrbo, you will be asked to delete the old iCal calendar URLs related to Vrbo to completely dismiss this message.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_ERR3':
				$result = __('Thousands of property managers have been waiting for this new connection between Vik Channel Manager and Vrbo to be available! Real-time sync with full control over any possible API service, from listing contents, photos, policies to reservations with full customer details and a complete management of rates and restrictions is what your Channel Manager will be capable of doing!', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_ERR4':
				$result = __('Please notice that keeping both services alive with the same name was not possible for our company after the partnership with Vrbo. For this reason, the old and consuming iCal integration will be terminated in accordance with Vrbo. The difference with the subscription fees applied for this new channel is given by the fact that Vrbo, Expedia, Booking.com and Airbnb will need to have the same costs. Please visit our website for more details.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_ACTIVATE':
				$result = __('Click here to activate Vrbo API', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_WARN1':
				$result = __('The new channel <strong>Vrbo API</strong> has been activated! However, the old iCal integration is still active on your Channel Manager, and this will need to be dismissed as soon as possible. Please make sure to complete the configuration of the new Vrbo API channel so that you will be able to delete the old iCal channels and get rid of this message. Also, once your account will be onboarded on Vrbo, they will automatically remove any previously configured iCal calendar, and so it\'s important to do the same operation on your Channel Manager once you are ready.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_WARN2':
				$result = __('Please make sure to read (again) the following conditions that will be applied:', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_WARN3':
				$result = __('Please visit our website if you have any questions.', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_WARN4':
				$result = __('Have you provided the information for your Vrbo API listings and synced them?', 'vikchannelmanager');
				break;
			case 'VRBO_UPGNOTICE_DEACTIVATE':
				$result = __('Click here to de-activate Vrbo iCal', 'vikchannelmanager');
				break;
			case 'VCMAUTOSYNC_HELP':
				$result = __('If disabled, no reservations will be imported from any channels. On a production website, this must be always enabled.', 'vikchannelmanager');
				break;
			case 'VCM_BCOM_PROP_DETAILS':
				$result = __('Property Details', 'vikchannelmanager');
				break;
			case 'VCM_CH_NODETAILS_FOUND':
				$result = __('No details found. Try to load them if no errors previously occurred.', 'vikchannelmanager');
				break;
			case 'VCM_HOUSE_RULES':
				$result = __('House Rules', 'vikchannelmanager');
				break;
			case 'VCM_SMOKING_ALLOWED':
				$result = __('Smoking Allowed', 'vikchannelmanager');
				break;
			case 'VCM_PARTIES_ALLOWED':
				$result = __('Parties Allowed', 'vikchannelmanager');
				break;
			case 'VCM_ON_REQUEST':
				$result = __('On request', 'vikchannelmanager');
				break;
			case 'VCM_CHARGES_MAY_APPLY':
				$result = __('Charges may apply', 'vikchannelmanager');
				break;
			case 'VCM_QUIET_HOURS':
				$result = __('Quiet Hours', 'vikchannelmanager');
				break;
			case 'VCM_ROOM':
				$result = __('Room', 'vikchannelmanager');
				break;
			case 'VCM_ACCEPT_LONG_STAYS':
				$result = __('Accept long stays', 'vikchannelmanager');
				break;
			case 'VCM_ACCEPT_LONG_STAYS_DESCR':
				$result = __('Whether the property accepts bookings for stays longer than 30 nights', 'vikchannelmanager');
				break;
			case 'VCM_MAXIMUM_LOS':
				$result = __('Maximum length of stay', 'vikchannelmanager');
				break;
			case 'VCM_ACCEPT_GUEST_TYPE':
				$result = __('Accepted guest type', 'vikchannelmanager');
				break;
			case 'VCM_MIN_GUEST_AGE':
				$result = __('Minimum guest age', 'vikchannelmanager');
				break;
			case 'VCM_MIN_AGE':
				$result = __('Minimum age', 'vikchannelmanager');
				break;
			case 'VCM_MAX_AGE':
				$result = __('Maximum age', 'vikchannelmanager');
				break;
			case 'VCM_EXPAND':
				$result = __('Expand', 'vikchannelmanager');
				break;
			case 'VCM_COLLAPSE':
				$result = __('Collapse', 'vikchannelmanager');
				break;
			case 'VCM_TOTAL_COUNT':
				$result = __('Total count', 'vikchannelmanager');
				break;
			case 'VCM_DERIVED_RPLAN_DESCR':
				$result = __('Derived rate plan automatically updated by %s', 'vikchannelmanager');
				break;
			case 'VCM_PARENT_RPLAN_DESCR':
				$result = __('Parent rate plan managed through your Channel Manager', 'vikchannelmanager');
				break;
			case 'VCM_CREATE_NEW':
				$result = __('Create New', 'vikchannelmanager');
				break;
			case 'VCM_MODELEM_AFTER_SAVE':
				$result = __('The element will be modified after saving', 'vikchannelmanager');
				break;
			case 'VCM_ROOMRATE_RELATIONS':
				$result = __('Room-Rate Relations', 'vikchannelmanager');
				break;
			case 'VCM_UNDO_CHANGES':
				$result = __('Undo Changes', 'vikchannelmanager');
				break;
			case 'VCM_ASK_RELOAD':
				$result = __('Would you like to reload the page? All changes will go lost.', 'vikchannelmanager');
				break;
			case 'VCM_NOTHING_TO_SAVE':
				$result = __('Nothing to save', 'vikchannelmanager');
				break;
			case 'VCM_DERIVED_RP_FROM_RP':
				$result = __('Derived rate plan from %s', 'vikchannelmanager');
				break;
			case 'VCM_EXISTING_CANC_POLICIES':
				$result = __('Existing Cancellation Policies', 'vikchannelmanager');
				break;
			case 'VCM_LICENSES':
				$result = __('Licenses', 'vikchannelmanager');
				break;
			case 'VCM_NO_LICENSE_REQUIREMENTS':
				$result = __('No license requirements found for this property.', 'vikchannelmanager');
				break;
			case 'VCM_SYNC_PENDING':
				$result = __('Occupy rooms for pending reservations', 'vikchannelmanager');
				break;
			case 'VCM_SYNC_PENDING_TIP':
				$result = __('The rooms will be temporarily kept occupied on the channels for the number of minutes defined in the configuration settings of VikBooking for &quot;Minutes of Waiting for the Payment&quot;, if greater than 0.', 'vikchannelmanager');
				break;
			case 'VCM_SYNC_PENDING_HELP':
				$result = __('If enabled, stand-by reservations waiting to be confirmed or paid will keep the room(s) occupied on the various channels for the booked dates. In case the reservations are not confirmed on time, the system will automatically release the rooms on the various channels.', 'vikchannelmanager');
				break;
			case 'VCM_CHECK_STATUS':
				$result = __('Check Status', 'vikchannelmanager');
				break;
			case 'VCM_ROOMS_TMP_LOCK_RELEASED':
				$result = __('Previously occupied rooms were released due to expired payment terms', 'vikchannelmanager');
				break;
			case 'VCM_PENDING_PAYMENT':
				$result = __('Pending Payment', 'vikchannelmanager');
				break;
			case 'VCM_PENDING_LOCK':
				$result = __('Temporary hold applied', 'vikchannelmanager');
				break;
			case 'VCM_PAYMENT_EXPIRED':
				$result = __('Payment Terms Expired', 'vikchannelmanager');
				break;
			case 'VCM_WEEKLY_DISCOUNTS':
				$result = __('Weekly Discounts', 'vikchannelmanager');
				break;
			case 'VCM_MONTHLY_DISCOUNTS':
				$result = __('Monthly Discounts', 'vikchannelmanager');
				break;
			case 'VCM_WEBAPP_PUSH_REGISTRATIONS':
				$result = __('Web App Registrations', 'vikchannelmanager');
				break;
			case 'VCM_THIS_BROWSER':
				$result = __('It\'s this browser!', 'vikchannelmanager');
				break;
			case 'VCM_COPY':
				$result = __('Copy', 'vikchannelmanager');
				break;
			case 'VCM_COPIED':
				$result = __('Copied', 'vikchannelmanager');
				break;
			case 'VCM_AUTORESPONDER_DEF_MESSAGE':
				$result = __('Thanks for getting in touch. We will get back to you as soon as possible.', 'vikchannelmanager');
				break;
			case 'VCM_AUTORESPONDER_MESS':
				$result = __('Channels Autoresponder Message', 'vikchannelmanager');
				break;
			case 'VCM_AUTORESPONDER_MESS_HELP':
				$result = __('Configure the autoresponder for those channels supporting the Guest Messaging API to send an automatic message in reply to the guests\' first message. An empty message will turn off the autoresponder.', 'vikchannelmanager');
				break;
			case 'VCM_ICAL_PRIVACY_FIELDS':
				$result = __('iCal Privacy Protected Fields', 'vikchannelmanager');
				break;
			case 'VCM_ICAL_PRIVACY_FIELDS_HELP':
				$result = __('If you share iCal calendars with third-party systems, select all the information that you would like to be omitted for privacy reasons.', 'vikchannelmanager');
				break;
			case 'VCM_CANC_RES_INV_CC':
				$result = __('Cancel reservation for invalid credit card', 'vikchannelmanager');
				break;
			case 'VCM_MENU_AI_CHATBOT':
				$result = __('Chatbot', 'vikchannelmanager');
				break;
			case 'VCM_MENU_AI_TRAININGS':
				$result = __('Trainings', 'vikchannelmanager');
				break;
			case 'VCM_AI_TRAININGS_TITLE':
				$result = __('AI Assistant - Trainings', 'vikchannelmanager');
				break;
			case 'VCM_AI_TRAININGS_TITLE_EDIT':
				$result = __('AI Assistant - Trainings: Edit', 'vikchannelmanager');
				break;
			case 'VCM_AI_TRAININGS_TITLE_NEW':
				$result = __('AI Assistant - Trainings: New', 'vikchannelmanager');
				break;
			case 'VCM_TITLE':
				$result = __('Title', 'vikchannelmanager');
				break;
			case 'VCM_TRANSLATE':
				$result = __('Translate', 'vikchannelmanager');
				break;
			case 'VCM_TRANSLATING':
				$result = __('Translating...', 'vikchannelmanager');
				break;
			case 'VCM_CREATED_DATE':
				$result = __('Created', 'vikchannelmanager');
				break;
			case 'VCM_MODIFIED_DATE':
				$result = __('Modified', 'vikchannelmanager');
				break;
			case 'VCM_CREATED_BY':
				$result = __('Created By', 'vikchannelmanager');
				break;
			case 'VCM_MODIFIED_BY':
				$result = __('Modified By', 'vikchannelmanager');
				break;
			case 'VCM_TOPIC':
				$result = __('Topic', 'vikchannelmanager');
				break;
			case 'VCM_HITS':
				$result = __('Hits', 'vikchannelmanager');
				break;
			case 'VCM_PUBLISHING':
				$result = __('Publishing', 'vikchannelmanager');
				break;
			case 'VCM_ATTACH':
				$result = __('Attach', 'vikchannelmanager');
				break;
			case 'VCM_ATTACHMENTS':
				$result = __('Attachments', 'vikchannelmanager');
				break;
			case 'VCM_SELECT_LISTING_FILTER':
				$result = __('- Select Listing -', 'vikchannelmanager');
				break;
			case 'VCM_SELECT_ALL_LISTINGS':
				$result = __('- All Listings -', 'vikchannelmanager');
				break;
			case 'VCM_SELECT_STATUS_FILTER':
				$result = __('- Select Status -', 'vikchannelmanager');
				break;
			case 'VCM_SELECT_LANG_FILTER':
				$result = __('- Select Language -', 'vikchannelmanager');
				break;
			case 'VCM_TRANSLATE_TRAINING_HELP':
				$result = __('Select all languages for which you would like to translate this training set.', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_TITLE_DESC':
				$result = __('Only for administrative purposes.', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_LISTING_DESC':
				$result = __('Choose whether the training set applies to all listings, only the selected ones, or all listings except the selected ones.', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_LANGUAGE_DESC':
				$result = __('Select the language matching the content of this training set. The more languages you use, the more accurate the AI will be.', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_CONTENT_DESC':
				$result = __('Not sure what to write? Think of a frequently asked question. Write here the answer you would give. Try to be concise because you cannot write more than 1500 characters.', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_ATTACHMENTS_DESC':
				$result = __('The uploaded attachments will be automatically sent by the AI whenever an answer is generated by using the contents described by this training set.', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_PUBLISHED_DESC':
				$result = __('Unpublished training sets won\'t be used by the AI while elaborating an answer for the customer.', 'vikchannelmanager');
				break;
			case 'VCM_FILLED_ON_SAVE':
				$result = __('Auto-filled after saving', 'vikchannelmanager');
				break;
			case 'VCM_GUIDELINES':
				$result = __('Guidelines', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_GUIDELINES_BTN_OPEN':
				$result = __('Open Examples', 'vikchannelmanager');
				break;
			case 'VCM_TRAINING_GUIDELINES_DESC':
				$result = __('Click this button to see some pre-built examples. They will give you a better idea of how the content should be structured.', 'vikchannelmanager');
				break;
			case 'VCM_AI_PLAYGROUND':
				$result = __('AI Playground', 'vikchannelmanager');
				break;
			case 'VCM_AI_PLAYGROUND_TEST_BTN':
				$result = __('Ask AI to answer', 'vikchannelmanager');
				break;
			case 'VCM_AI_PLAYGROUND_QUESTION':
				$result = __('Enter here the question for the AI.', 'vikchannelmanager');
				break;
			case 'VCM_AI_PLAYGROUND_ANSWER':
				$result = __('An answer will be reported here.', 'vikchannelmanager');
				break;
			case 'VCM_AI_PLAYGROUND_PROCESSING':
				$result = __('Processing', 'vikchannelmanager');
				break;
			case 'VCM_MENU_AI_MESSAGING_FAQS':
				$result = __('Most Frequent Topics', 'vikchannelmanager');
				break;
			case 'VCM_AI_MESSAGING_FAQS_TITLE':
				$result = __('AI Assistant - Most Frequent Topics', 'vikchannelmanager');
				break;
			case 'VCM_AI_MESSAGING_FAQS_WARN':
				$result = __('The AI runs automatically in background to extract the topics from each guest conversation.', 'vikchannelmanager');
				break;
			case 'VCM_AI_MESSAGING_FAQS_PROCESSED':
				$result = __('%d/%d processed threads', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_TOOLTIP':
				$result = __('AI ✨', 'vikchannelmanager');
				break;
			case 'VCM_AI_GEN_THROUGH':
				$result = __('Generate through AI ✨', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_BADGE':
				$result = __('The AI will auto-respond shortly', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_BADGE_STOPPED':
				$result = __('The AI auto-responder is stopped', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_RESUME':
				$result = _x('Resume', 'Resume the AI auto-responder.', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_AUTOREPLY_STOP':
				$result = _x('Stop', 'Stop the AI auto-responder.', 'vikchannelmanager');
				break;
			case 'VCM_AI_PAID_SERVICE_REQ':
				$result = __('This function requires the AI service integration!', 'vikchannelmanager');
				break;
			case 'VCM_GUEST_REVIEW':
				$result = __('Guest review', 'vikchannelmanager');
				break;
			case 'VCM_SUBMIT_REVIEW_CONF':
				$result = __('The review will be submitted. Continue?', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_MESSAGES_LABEL':
				$result = __('Messaging Auto-Responder', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_MESSAGES_DESC':
				$result = __('The AI will automatically answer to your guest messages. Answers will be generated according to the available trainings.<br /><br />Drafts will be saved for the first 20 automatic replies, rather than actually sending the message.', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_MESSAGES_FOOTER_LABEL':
				$result = __('Auto-Responder Signature', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_MESSAGES_FOOTER_DESC':
				$result = __('An optional text to be added to the end of the message whenever the AI automatically replies to the guests, so that they can understand the message was generated by AI.', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_MESSAGES_FOOTER_HINT':
				$result = __('- Generated by AI', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_REVIEW_REPLY_LABEL':
				$result = __('Automatic Review Replies', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_REVIEW_REPLY_DESC':
				$result = __('The AI will automatically reply to the reviews left by your guests.', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_IGNORE_NEGATIVE_REVIEWS_LABEL':
				$result = __('Exclude Negative Reviews', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_IGNORE_NEGATIVE_REVIEWS_DESC':
				$result = __('When enabled, the AI will not automatically reply to reviews with an overall score lower than 6 points.', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_REVIEW_GUEST_LABEL':
				$result = __('Automatic Guest Reviews', 'vikchannelmanager');
				break;
			case 'VCM_AI_SETTING_AUTORESPONDER_REVIEW_GUEST_DESC':
				$result = __('The AI will automatically review your guests (Airbnb only). Reviews will be automatically submitted the day after the check-out.', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_DRAFT_TITLE':
				$result = __('The AI generated the following message.', 'vikchannelmanager');
				break;
			case 'VCM_AI_CHAT_DRAFT_SEND':
				$result = __('Send', 'vikchannelmanager');
				break;
			case 'VCM_ADMIN_WIDGET':
				$result = __('Admin widget', 'vikchannelmanager');
				break;
			case 'VCM_NO_RESULTS':
				$result = __('No results', 'vikchannelmanager');
				break;
			case 'VCM_SEARCH_ADMIN_WIDGETS':
				$result = __('Search widgets', 'vikchannelmanager');
				break;
			case 'VCM_BUNDLED_VALUE_ADDS':
				$result = __('Bundled Value Adds', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_CHARGE_PERIOD':
				$result = __('Charge period', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_CHARGE_PERIOD_BOOKING':
				$result = __('Per booking', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_STDFEE_CHARGE_PERIOD_NIGHT':
				$result = __('Per night', 'vikchannelmanager');
				break;
			case 'VCM_AI_ASSISTANT_VIEW_SOURCES':
				$result = __('View sources', 'vikchannelmanager');
				break;
			case 'VCM_AI_ASSISTANT_VIEW_SOURCES_1':
				$result = __('View source', 'vikchannelmanager');
				break;
			case 'VCM_MNGLISTING_QUALITY':
				$result = __('Quality', 'vikchannelmanager');
				break;
			case 'VCM_HTGREVIEW_CAT_TAGS_HELP':
				$result = __('You can choose some category tags to provide meaningful reviews.', 'vikchannelmanager');
				break;
			case 'VCM_ONBOARD_LIST_NEW_OR_EXIST':
				$result = __('New property or existing host account?', 'vikchannelmanager');
				break;
			case 'VCM_ONBOARD_LIST_NEW_OR_EXIST_HELP':
				$result = __('Choose to create a new property if the new listing address was never used on any other account. Otherwise, add the listing to an existing account.', 'vikchannelmanager');
				break;
			case 'VCM_ONBOARD_LIST_EXIST_ACCOUNT':
				$result = __('Add listing to Host account', 'vikchannelmanager');
				break;
			case 'VCM_ONBOARD_LIST_NEW_ACCOUNT':
				$result = __('Create new property', 'vikchannelmanager');
				break;
			case 'VCM_ONBOARD_PROPERTY_CATEGORY':
				$result = __('Property category', 'vikchannelmanager');
				break;
			case 'VCM_ONBOARD_LIST_CURRENT_PROGRESS':
				$result = __('Onboarding progress', 'vikchannelmanager');
				break;
			case 'VCM_CHECKIN_START':
				$result = __('Check-in start', 'vikchannelmanager');
				break;
			case 'VCM_CHECKIN_END':
				$result = __('Check-in end', 'vikchannelmanager');
				break;
			case 'VCM_CHECKOUT_START':
				$result = __('Check-out start', 'vikchannelmanager');
				break;
			case 'VCM_CHECKOUT_END':
				$result = __('Check-out end', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_NAME':
				$result = __('Listing name', 'vikchannelmanager');
				break;
			case 'VCM_PROP_TYPE_GROUP':
				$result = __('Property type group', 'vikchannelmanager');
				break;
			case 'VCM_PROP_TYPE_CAT':
				$result = __('Property type category', 'vikchannelmanager');
				break;
			case 'VCM_ROOM_TYPE_CAT':
				$result = __('Room type category', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_PRIVATE_ROOM':
				$result = __('Private room', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_ENTIRE_HOME':
				$result = __('Entire home', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_SHARED_ROOM':
				$result = __('Shared room', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_BEDS':
				$result = __('Beds', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_FIELDSET_TITLE':
				$result = __('AI Permissions', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_FIELDSET_HELP':
				$result = __('Define the tools that the AI assistant can access to fulfill the requests made by an administrator.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_FETCH_AVAILABILITY_LABEL':
				$result = __('Fetch Availability', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_FETCH_AVAILABILITY_DESC':
				$result = __('Fetches the availability and restrictions inventory for the optionally requested room and for a specific period.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_DELETE_BOOKING_LABEL':
				$result = __('Delete Booking', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_DELETE_BOOKING_DESC':
				$result = __('Deletes a specific reservation by updating its status to cancelled and by freeing the previously booked room(s) and dates.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_MODIFY_BOOKING_LABEL':
				$result = __('Modify Booking', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_MODIFY_BOOKING_DESC':
				$result = __('Modifies a specific reservation by updating stay dates, rooms, number of guests, extra services and total cost.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_MODIFY_ROOM_RATES_LABEL':
				$result = __('Modify Room Rates', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_MODIFY_ROOM_RATES_DESC':
				$result = __('Modifies the rates and/or restrictions for a given room on a specific range of dates.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_NOTIFY_CUSTOMER_LABEL':
				$result = __('Notify Customer', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_NOTIFY_CUSTOMER_DESC':
				$result = __('Notifies a customer that made a reservation, either via email or by sending a message through the OTA messaging APIs.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_ROOM_BOOKING_LABEL':
				$result = __('Room Booking', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_ROOM_BOOKING_DESC':
				$result = __('Books the requested room for a given customer and stay dates.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_ROOM_CLOSURE_LABEL':
				$result = __('Room Closure', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_ROOM_CLOSURE_DESC':
				$result = __('Closes the requested room for a specific period.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_ROOMS_QUOTATION_LABEL':
				$result = __('Rooms Quotation', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_ROOMS_QUOTATION_DESC':
				$result = __('Calculates the total cost for the requested stay dates and guests, eventually for a specific room.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_SEARCH_BOOKINGS_LABEL':
				$result = __('Search Bookings', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_SEARCH_BOOKINGS_DESC':
				$result = __('Searches for reservations through various filters and returns the booking details.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_SET_REMINDER_LABEL':
				$result = __('Set Reminder', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_SET_REMINDER_DESC':
				$result = __('Sets reminders and notes for a specific date.', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_STATISTICS_FETCH_LABEL':
				$result = __('Statistics Fetch', 'vikchannelmanager');
				break;
			case 'VCM_AI_ACL_RULE_STATISTICS_FETCH_DESC':
				$result = __('Gets financial and booking statistics according to various metrics.', 'vikchannelmanager');
				break;
			case 'VCM_BCOM_LEGAL_ID_EXAMPLE':
				$result = __('For example, the property "%s" belongs to the legal ID %s.', 'vikchannelmanager');
				break;
			case 'VCM_BCOM_LEGAL_ID_HELP':
				$result = __('The legal entity ID will determine the Booking.com group account to which the new property will be assigned.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEVR_WAIT':
				$result = __('Google may take up to 3 business days to process your listing details, which were submitted on %s. Please wait at least 24 hours before transmitting the mandatory listing details to Google Vacation Rentals.', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEVR_SEND_DATA':
				$result = __('Send data to Google', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEVR_UPDATE_DATA':
				$result = __('Update data on Google', 'vikchannelmanager');
				break;
			case 'VCM_GOOGLEVR_UPDATE_DATA_HELP':
				$result = __('In case of changes to photos, rate plans, mandatory fees, taxes or other lodging settings, you should update the data on Google. Otherwise, rates, restrictions, availability and other settings are always synced automatically with Google.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_PRCSETTINGS_HELP3':
				$result = __('Tourist tax details will be automatically transmitted to Airbnb when launching the Bulk Action "Rates Upload", as long as tourist taxes were configured for your website listings.', 'vikchannelmanager');
				break;
			case 'VCMMENUPMS':
				$result = __('PMS', 'vikchannelmanager');
				break;
			case 'VCM_N_LISTINGS':
				$result = __('%d listings', 'vikchannelmanager');
				break;
			case 'VCM_ALL_EXCEPT':
				$result = __('All except', 'vikchannelmanager');
				break;
			case 'VCM_ALL_LISTINGS':
				$result = __('All listings', 'vikchannelmanager');
				break;
			case 'VCM_ALL_LISTINGS_SELECTED':
				$result = __('Only selected listings', 'vikchannelmanager');
				break;
			case 'VCM_ALL_LISTINGS_EXCEPT':
				$result = __('All except selected listings', 'vikchannelmanager');
				break;
			case 'VCM_ALL_LISTINGS_EXCEPT':
				$result = __('All except selected listings', 'vikchannelmanager');
				break;
			case 'VCM_FILTERS':
				$result = __('Filters', 'vikchannelmanager');
				break;
			case 'VCM_FILTER_LISTINGS':
				$result = __('Filter listings', 'vikchannelmanager');
				break;
			case 'VCM_FILTER_BY_CATEGORY':
				$result = __('Filter by category', 'vikchannelmanager');
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
			case 'VCM_CHAT_SENDING_ERR':
				$result = __('An error occurred while sending the message. Please, try again.', 'vikchannelmanager');
				break;
			case 'VCM_NO_OTHER_CHANNELS':
				$result = __('No other active channels.', 'vikchannelmanager');
				break;
			case 'VCM_CHAT_THREAD_TOPIC':
				$result = __('Please enter an optional subject for this thread.', 'vikchannelmanager');
				break;
			case 'VCM_GEN_CONTENT':
				$result = __('Generate content', 'vikchannelmanager');
				break;
			case 'VCM_AI_GEN_CONTENT_INFO_HELP':
				$result = __('Enter the information to help the AI generate a proper content.', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_NO_RATES_TABLE_RESTR':
				$result = __('Airbnb - Ignore Rates Table Restrictions', 'vikchannelmanager');
				break;
			case 'VCM_AIRBNB_NO_RATES_TABLE_RESTR_HELP':
				$result = __('Those who use multiple rate plans with rates defined only for certain number of nights of stay, should enable this setting to disable the calculation of the default minimum and maximum stay based on the rates table configuration. If disabled, minimum and maximum stay restrictions will be taken from the rate plan costs defined in the Rates Table.', 'vikchannelmanager');
				break;
			case 'VCM_HOSTING_QUALITY':
				$result = __('Hosting Quality', 'vikchannelmanager');
				break;
			case 'VCM_RATINGS':
				$result = __('Ratings', 'vikchannelmanager');
				break;
			case 'VCM_BEST_RATING':
				$result = __('Best rating', 'vikchannelmanager');
				break;
			case 'VCM_WORST_RATING':
				$result = __('Worst rating', 'vikchannelmanager');
				break;
			case 'VCM_MAXIMUM':
				$result = __('Maximum', 'vikchannelmanager');
				break;
			case 'VCM_MINIMUM':
				$result = __('Minimum', 'vikchannelmanager');
				break;
			case 'VCM_CATEGORY_RATINGS':
				$result = __('Category ratings', 'vikchannelmanager');
				break;
			case 'VCM_GUEST_FEEDBACK':
				$result = __('Guest feedback', 'vikchannelmanager');
				break;
			case 'VCM_MOST_FREQUENT':
				$result = __('Most frequent', 'vikchannelmanager');
				break;
			case 'VCM_LEAST_FREQUENT':
				$result = __('Least frequent', 'vikchannelmanager');
				break;
			case 'VCM_TIMES':
				$result = _x('times', 'How many times something occurred.', 'vikchannelmanager');
				break;
			case 'VCM_SUMMARY':
				$result = __('Summary', 'vikchannelmanager');
				break;
			case 'VCM_LISTING_RATINGS_RANKING':
				$result = __('Listings ratings ranking', 'vikchannelmanager');
				break;
			case 'VCM_PENALTY_EVENTS':
				$result = __('Penalty events', 'vikchannelmanager');
				break;
			case 'VCM_RESERVATION':
				$result = __('Reservation', 'vikchannelmanager');
				break;
			case 'VCM_DEVICES':
				$result = __('Devices', 'vikchannelmanager');
				break;
			case 'VCM_AI_TRAINING_NEEDS_REVIEW_WARNING':
				$result = __('This training set needs to be manually reviewed. You still have %d days to review it before this record is automatically deleted.', 'vikchannelmanager');
				break;
			case 'VCM_AI_TRAINING_NEEDS_REVIEW_WARNING_1':
				$result = __('This training set needs to be manually reviewed. You still have 1 day to review it before this record is automatically deleted.', 'vikchannelmanager');
				break;
			case 'VCM_AI_TRAINING_NEEDS_REVIEW_WARNING_0':
				$result = __('This training set needs to be manually reviewed. Please hurry, this record may be deleted soon.', 'vikchannelmanager');
				break;
			case 'VCM_EDIT_BULK_RATES_CACHE':
				$result = __('Edit pricing settings', 'vikchannelmanager');
				break;
			case 'VCM_FULL_SYNC':
				$result = __('Full Sync', 'vikchannelmanager');
				break;
			case 'VCM_MESSAGE_ACCSEC_PHISHING':
				$result = __('This message has been flagged as suspicious - please interact with caution.', 'vikchannelmanager');
				break;
			case 'VCM_BCOM_CONNECTION_AID_TITLE':
				$result = __('How Booking.com handles new connections', 'vikchannelmanager');
				break;
			case 'VCM_BCOM_CONNECTION_AID_TXT':
				$result = __('<p>When you connect your Hotel ID(s) to any Channel Manager software, Booking.com automatically resets calendar data (availability and rates). This is expected behavior: Booking.com waits for a full update from the Channel Manager to prevent data discrepancies.<br/>Once the connection is active, you are expected to complete the Channel Manager configuration and promptly push availability and rates back to Booking.com.</p><p>If this information is not received in time, your property status will change to "<strong>Auto-Closed</strong>", making it unavailable for booking.<br/>To bring the property back online, you must submit <strong>both Bulk Actions</strong> for availability and rates.<br/>Although updates are sent in real time, if the property was auto-closed it may take a <strong>few hours</strong> for Booking.com to restore its online status.</p><p>If the configuration is completed immediately after the first connection, <strong>no downtime will occur</strong>.<br/>Please note that <strong>disconnecting the Channel Manager does not restore availability or rates</strong>, and will not bring the property back online.</p><p><strong>Correct Booking.com setup – required steps</strong><br/><ol><li>Ensure pricing is correctly configured in VikBooking for all listings.</li><li>Connect your Booking.com account(s) to the Channel Manager.</li><li>Enter the Booking.com Hotel ID to configure.</li><li>Complete the room mapping by using the Synchronize function.</li><li>Let the Channel Manager import the active reservations from Booking.com.</li><li>Submit the bulk actions from the Channel Manager to push back inventory details and become bookable again.</li></ol></p>', 'vikchannelmanager');
				break;
			case 'VCM_PROBLEMS_WITH_OTA':
				$result = __('Problems with %s?', 'vikchannelmanager');
				break;
		}

		return $result;
	}
}
