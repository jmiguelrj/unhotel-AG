<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Template file used for the generation of the notification email message to
 * the hotel or to the guest, depending on who sent the message through the chat.
 * 
 * Layout variables
 * ---------------------
 *
 * @var  mixed 	  $user 	The user instance (VCMChatUser).
 * @var  mixed    $message 	The message instance (VCMChatMessage).
 */
extract($displayData);

// get booking object information
$booking = $user->getOrder();

// whether the recipient is the hotel
$mail_for_hotel = (bool) $user->getClient();

// get source channel information
$channel_logo = null;

if (!empty($booking->channel) && !empty($booking->idorderota))
{
	$channel  = $booking->channel;
	$segments = explode('_', $channel);
	
	// we take the first segment, as the second could be the source sub-network (expedia_Hotels.com)
	$channel = $segments[0];

	$channel_logo = VikChannelManager::getLogosInstance($channel)->getLogoURL();
}

if (!$channel_logo && empty($booking->idorderota))
{
	// attempt to get the website logo
	$backlogo = VikBooking::getBackendLogo();

	if (!empty($backlogo)) 
	{
		$channel_logo = VBO_ADMIN_URI . 'resources/' . $backlogo;
	}
}

if (VCMPlatformDetection::isWordPress())
{
	// link to the back-end booking details page
	$backend_link = admin_url('admin.php?option=com_vikbooking&task=editorder&cid[]=' . $booking->id);

	// link to the front-end booking details page
	$frontend_link = JUri::root() . "index.php?option=com_vikbooking&view=booking&sid=" . $booking->sid . "&ts=" .$booking->ts;

	$model 	= JModel::getInstance('vikbooking', 'shortcodes', 'admin');
	$itemid = $model->best('booking');
	if ($itemid)
	{
		$frontend_link = JRoute::_("index.php?option=com_vikbooking&Itemid={$itemid}&view=booking&sid={$booking->sid}&ts={$booking->ts}");
	}
}
else
{
	// link to the back-end booking details page
	$backend_link = JUri::root() . "administrator/index.php?option=com_vikbooking&task=editorder&cid[]=" . $booking->id;

	// link to the front-end booking details page
	$frontend_link = JUri::root() . "index.php?option=com_vikbooking&view=booking&sid=" . $booking->sid . "&ts=" .$booking->ts;

	if (method_exists('VikBooking', 'externalroute'))
	{
		$frontend_link = VikBooking::externalroute("index.php?option=com_vikbooking&view=booking&sid=" . $booking->sid . "&ts=" .$booking->ts);
	}
}

// customer name
$customer = VikBooking::getCPinIstance()->getCustomerFromBooking($booking->id);

$customer_name = count($customer) ? $customer['first_name'] . ' ' . $customer['last_name'] : '';

// use VCM date format (which supports date separators)
$date_format = VikChannelManager::getClearDateFormat(true);

?>

<center style="background:#fff; color: #666; width: 100%; table-layout: fixed;">
	<div style="max-width: 800px;">
		<table style="margin: 0 auto; width: 100%; max-width: 500px; border-spacing: 0; font-family: sans-serif;">
			<tbody>
				
				<tr>
					<td style="font-size: 0; padding:0;">
						<div style="width: 100%; padding: 20px; display: inline-block; vertical-align: top; text-align: left; background-color: #f2f2f2;">
						<?php
						if ($channel_logo) {
							?>
							<img src="<?php echo $channel_logo; ?>" style="max-width: 100px; float: left;"/>
							<?php
						}
						?>
							<span style="vertical-align: middle; float: right;">
								<a style="font-size: 14px; color: #333; text-decoration: none;" href="<?php echo $mail_for_hotel ? $backend_link : $frontend_link; ?>"><?php echo $mail_for_hotel ? JText::sprintf('VCMBOOKINGIDNUM', $booking->id) : JText::sprintf('VCMBOOKINGCONFNUM', $booking->confirmnumber); ?></a>
							</span>
						</div>
					</td>
				</tr>

				<tr>
					<td style="font-size: 0; padding:0;">
						<div style="width: 100%; display: inline-block; vertical-align: top; text-align: center; padding: 20px 10px;">
							<h3 style="font-size: 16px;">
								<strong><?php echo !$mail_for_hotel ? JText::sprintf('VCMCHATNEWMESSFROM', VikBooking::getFrontTitle()) : (!empty($customer_name) ? JText::sprintf('VCMCHATNEWMESSFROM', $customer_name) : JText::_('VCMCHATNEWMESSFROMGUEST')); ?></strong>
							</h3>
						</div>
					</td>
				</tr>

				<tr>
					<td style="font-size: 0; padding:0;">
						<div style="width: 100%; display: inline-block; vertical-align: top; text-align: left; padding: 10px;">
							<div style="font-size: 14px; margin: 0 10px; background: #eee; border-radius: 8px; padding: 15px; color: #333;"><?php echo nl2br($message->getContent()); ?></div>
						</div>
					</td>
				</tr>

				<tr>
					<td style="font-size: 0; padding:0 0 10px;">
						<div style="width: 100%; display: inline-block; vertical-align: top; text-align: center; padding: 10px;">
							<div style="font-size: 12px; text-align: center;">
								<a style="diplay: block; margin: 0 10px; background: #d3e1e9; border: 1px solid #5a92b1; color: #333; padding: 8px;" href="<?php echo ($mail_for_hotel ? $backend_link : $frontend_link) . '#messaging'; ?>"><?php echo JText::_('VCMCHATCLICKTORESP'); ?></a>
							</div>
						</div>
					</td>
				</tr>

				<tr>
					<td style="border-top: 1px solid #ddd; padding-top: 20px;">
						<div style="width: 100%; display: inline-block; vertical-align: top; text-align: left; padding: 10px;">
							<span style="font-size: 15px; font-weight: bold;"><?php echo JText::_('VCMBOOKDETAILS'); ?></span>
						</div>
					</td>
				</tr>

				<tr>
					<td style="padding:0 0 10px;">
						<div style="width: 100%; display: inline-block; vertical-align: top; text-align: left;">
							<table width="100%" style="padding: 5px; font-size: 16px;">
								<tr>
									<td style="padding: 10px; line-height: 1.4em; vertical-align: middle;">
										<span style="display: block; font-size: 12px;"><?php echo JText::_('VCMGUESTNAME'); ?></span>
										<span style="font-weight: bold; color: #666;"><?php echo $customer_name; ?></span>
									</td>
								</tr>
								<tr>
									<td style="padding: 10px; line-height: 1.4em; vertical-align: middle;">
										<span style="display: block; font-size: 12px;"><?php echo JText::_('VCMCHECKIN'); ?></span>
										<span style="font-weight: bold; color: #666;"><?php echo date($date_format, $booking->checkin); ?></span>
									</td>
									<td style="padding: 10px; line-height: 1.4em; vertical-align: middle;">
										<span style="display: block; font-size: 12px;"><?php echo JText::_('VCMCHECKOUT'); ?></span>
										<span style="font-weight: bold; color: #666;"><?php echo date($date_format, $booking->checkout); ?></span>
									</td>
								</tr>
								<tr>
									<td style="padding: 10px; line-height: 1.4em; vertical-align: middle;">
										<span style="display: block; font-size: 12px;"><?php echo JText::_('VCMCHATNIGHTS'); ?></span>
										<span style="font-weight: bold; color: #666;"><?php echo $booking->days; ?></span>
									</td>
									<td style="padding: 10px; line-height: 1.4em; vertical-align: middle;">
										<span style="display: block; font-size: 12px;"><?php echo JText::_('VCMCHATROOMSNUM'); ?></span>
										<span style="font-weight: bold; color: #666;"><?php echo $booking->roomsnum; ?></span>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>

				<tr>
					<td style="font-size: 10px; padding:20px 0 0; border-top: 1px solid #ddd;">
						<div style="width: 100%; display: inline-block; vertical-align: top; text-align: center;">
							<table width="100%" style="padding: 5px; font-size: 10px;">
								<tr>
									<td style="padding: 10px; line-height: 1.2em; vertical-align: middle;">
										<?php echo $mail_for_hotel ? JText::_('VCMCHATNOTIFDISCLHOTEL') : JText::_('VCMCHATNOTIFDISCLGUEST'); ?>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>

			</tbody>
		</table>
	</div>
</center>