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

$wdays_map = array(
	JText::_('SUNDAY'),
	JText::_('MONDAY'),
	JText::_('TUESDAY'),
	JText::_('WEDNESDAY'),
	JText::_('THURSDAY'),
	JText::_('FRIDAY'),
	JText::_('SATURDAY'),
);

$months_map = array(
	JText::_('JANUARY'),
	JText::_('FEBRUARY'),
	JText::_('MARCH'),
	JText::_('APRIL'),
	JText::_('MAY'),
	JText::_('JUNE'),
	JText::_('JULY'),
	JText::_('AUGUST'),
	JText::_('SEPTEMBER'),
	JText::_('OCTOBER'),
	JText::_('NOVEMBER'),
	JText::_('DECEMBER'),
);

$all_reports_dates = array();

// build report title and dates
switch ($reports_interval) {
	case 7:
		$report_title = JText::_('VCM_WEEKLY_REPORT_TITLE');
		foreach ($reports as $ind => $report) {
			// in case of multiple reports for two weeks we loop over the dates of each report to get the latest ones
			$from_info = getdate($report->date_from);
			$report_date = $wdays_map[$from_info['wday']] . ', ' . $from_info['mday'] . ' ' . $months_map[($from_info['mon'] - 1)] . ' ' . $from_info['year'];
			$to_info = getdate($report->date_to);
			$report_date .= ' - ';
			$report_date .= $wdays_map[$to_info['wday']] . ', ' . $to_info['mday'] . ' ' . $months_map[($to_info['mon'] - 1)] . ' ' . $to_info['year'];
			$all_reports_dates[$ind] = $report_date;
		}
		break;
	case 14:
		$report_title = JText::_('VCM_BIWEEKLY_REPORT_TITLE');
		foreach ($reports as $ind => $report) {
			// in case of multiple reports for two weeks we loop over the dates of each report to get the latest ones
			$from_info = getdate($report->date_from);
			$report_date = $wdays_map[$from_info['wday']] . ', ' . $from_info['mday'] . ' ' . $months_map[($from_info['mon'] - 1)] . ' ' . $from_info['year'];
			$to_info = getdate($report->date_to);
			$report_date .= ' - ';
			$report_date .= $wdays_map[$to_info['wday']] . ', ' . $to_info['mday'] . ' ' . $months_map[($to_info['mon'] - 1)] . ' ' . $to_info['year'];
			$all_reports_dates[$ind] = $report_date;
		}
		break;
	default:
		// monthly (30)
		$report_title = JText::_('VCM_MONTHLY_REPORT_TITLE');
		foreach ($reports as $ind => $report) {
			// in case of multiple reports for two months we loop over the dates of each report to get the latest ones
			$from_info = getdate($report->date_from);
			$report_date = $months_map[($from_info['mon'] - 1)] . ' ' . $from_info['year'];
			$all_reports_dates[$ind] = $report_date;
		}
		break;
}

// count tot reservations for the last report comparison object
$tot_period_reservations = 0;
$tot_period_earning = 0;
reset($reports);
$last_report = end($reports);
reset($reports);
foreach ($last_report->data as $key => $data) {
	$tot_period_reservations += $data->reservations_count;
	$tot_period_earning += $data->reservations_total;
}

?>
<div style="background:#f6f6f6; color: #444; width: 100%; table-layout: fixed; font-family: Century Gothic, Arial, Sans-Serif;">
	<div style="max-width: 800px; margin:0 auto; padding: 10px;">

		<table style="width: 98%;">
			<tr>
				<td style="padding: 20px; text-align: center;">
					<img src="<?php echo VikChannelManager::getBackendLogoFullPath(); ?>" alt="Your logo" title="Your logo" />
					<h3 style="color: #444; padding: 0; margin: 20px 0 0; font-size: 24px;"><?php echo $report_title; ?></h3>
					<p style="margin: 5px 0 0; padding: 0; color: #666; text-transform: uppercase;"><?php echo $report_date; ?></p>
				</td>
			</tr>
		</table>

		<!--[if (gte mso 9)|(IE)]>
		<table width="800" align="center">
		<tr>
		<td>
		<![endif]-->
		<table style="margin: 0 0 10px; width: 98%; background: #fff; border-bottom: 1px solid #e7e7e7;">
			<tr>
				<td colspan="2">
					<h3 style="text-transform: uppercase; margin: 0; padding: 20px 20px 15px; color: #666; font-weight: bold;"><?php echo JText::_('VCM_TOTAL_INCOME'); ?></h3>
				</td>
			</tr>
			<tr>
				<td style="text-align: left; font-size: 0; padding:0; width: 100%;">
					<!--[if (gte mso 9)|(IE)]>
					<table width="100%">
					<tr>
					<td width="50%" valign="top">
					<![endif]-->
					<div style="width: 100%; max-width: 380px; display: inline-block; vertical-align: top; text-align: left;">
						<table width="100%" style="margin: 0; padding: 0;">
							<tr>
								<td style="padding: 0 20px;">
									<div style="padding: 0px 0 10px; border-right: 1px solid #eee;">
										<h3 style="color: #23b973; font-weight: bold; font-size:32px; margin: 0; padding: 0;"><?php echo $currency . ' ' . number_format($tot_period_earning, 2); ?></h3>
										<span style="font-weight: bold; text-transform: uppercase; color: #666; font-size: 14px;"><?php echo JText::_('VCM_GROSS_REVENUE'); ?></span>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<!--[if (gte mso 9)|(IE)]>
					</td><td width="50%" valign="top">
					<![endif]-->
					<div style="width: 100%; max-width: 380px; display: inline-block; vertical-align: top; text-align: left;">
						<table width="100%" style="margin: 0; padding: 0 0 20px;">
							<tr>
								<td style="padding: 0 20px 20px;">
									<div style="padding: 0;">
										<h3 style="color: #239db9; font-weight: bold; font-size:32px; margin: 0; padding: 0;"><?php echo $tot_period_reservations; ?></h3>
										<span style="font-weight: bold; text-transform: uppercase; color: #666; font-size: 14px;"><?php echo JText::_('VCM_TOT_NEW_BOOKINGS'); ?></span>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				</td>
			</tr>
		</table>
		<!--[if (gte mso 9)|(IE)]>
		</td>
		</tr>
		</table>
		<![endif]-->

		<?php
		$ibe_earn = 0;
		$ibe_pcent = 0;
		if (isset($last_report->data['VBO'])) {
			$ibe_earn = $last_report->data['VBO']->reservations_total;
			$pcent_val = $tot_period_earning > 0 ? ($ibe_earn * 100 / $tot_period_earning) : 0;
			$ibe_pcent = round($pcent_val, ($pcent_val > 10 ? 0 : 2));
		}
		if ($ibe_pcent >= 10) {
			// show the IBE information only if percent is greater than or equal to 10% of all bookings
			?>

			<!--[if (gte mso 9)|(IE)]>
			<table width="800" align="center">
			<tr>
			<td>
			<![endif]-->
			<table style="margin: 0 0 30px; width: 98%; background: #fff; border-bottom: 1px solid #e7e7e7;">
				<tr>
					<td colspan="2">
						<h3 style="text-transform: uppercase; margin: 0; padding: 20px 20px 15px; color: #666; font-weight: bold;"><?php echo JText::_('VCM_DIRECT_BOOKINGS_WEBSITE'); ?></h3>
					</td>
				</tr>
				<tr>
					<td style="text-align: left; font-size: 0; padding:0; width: 100%;">
						<!--[if (gte mso 9)|(IE)]>
						<table width="100%">
						<tr>
						<td width="50%" valign="top">
						<![endif]-->
						<div style="width: 100%; max-width: 380px; display: inline-block; vertical-align: top; text-align: left;">
							<table width="100%" style="margin: 0; padding: 0;">
								<tr>
									<td style="padding: 0 20px;">
										<div style="padding: 0px 0 10px; border-right: 1px solid #eee;">
											<h3 style="color: #23b973; font-weight: bold; font-size:32px; margin: 0; padding: 0;"><?php echo $currency . ' ' . number_format($ibe_earn, 2); ?></h3>
											<span style="font-weight: bold; text-transform: uppercase; color: #666; font-size: 14px;"><?php echo JText::_('VCM_GROSS_REVENUE'); ?></span>
										</div>
									</td>
								</tr>
							</table>
						</div>
						 <!--[if (gte mso 9)|(IE)]>
						</td><td width="50%" valign="top">
						<![endif]-->
						<div style="width: 100%; max-width: 380px; display: inline-block; vertical-align: top; text-align: left;">
							<table width="100%" style="margin: 0; padding: 0 0 20px;">
								<tr>
									<td style="padding: 0 20px 20px;">
										<div style="padding: 0;">
											<h3 style="color: #239db9; font-weight: bold; font-size:32px; margin: 0; padding: 0;"><?php echo $ibe_pcent; ?>%</h3>
											<span style="font-weight: bold; text-transform: uppercase; color: #666; font-size: 14px;"><?php echo JText::_('VCM_PCENT_OFALL_RES'); ?></span>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				<!--[if (gte mso 9)|(IE)]>
				</td>
				</tr>
				</table>
				<![endif]-->
			</table>
			<!--[if (gte mso 9)|(IE)]>
			</td>
			</tr>
			</table>
			<![endif]-->
			<?php
		}
		?>

		<!--[if (gte mso 9)|(IE)]>
		<table width="800" align="center">
		<tr>
		<td>
		<![endif]-->

		<table style="width: 98%; background: #fff; border-bottom: 1px solid #e7e7e7;">
			<tr>
				<td>
					<h3 style="text-transform: uppercase; margin: 0; padding: 20px 20px 5px; color: #666; font-weight: bold;"><?php echo JText::_('VCM_GLOBAL_OCCUPANCY'); ?></h3>
				</td>
			</tr>
			<?php 
		foreach ($reports as $ind => $report) {
			$precision = $occupancy_stats[$ind] < 1 ? 2 : 0;
			$bgcolor = '#df3e17';
			if ($occupancy_stats[$ind] >= 10 && $occupancy_stats[$ind] <= 50) {
				$bgcolor = '#df8017';
			} elseif ($occupancy_stats[$ind] > 50) {
				$bgcolor = '#23b973';
			}
			?>
			<tr>
				<td style="padding: 10px 20px 20px;">
					<div>
						<span style="text-transform: uppercase;font-weight: bold;color: #666;display: block;margin-bottom: 5px;font-size: 14px;"><?php echo $all_reports_dates[$ind]; ?></span>
						<div style="font-weight: bold; font-size: 18px;">
							<div style="display: inline-block; width: 70%; background: #eee; height: 60px;float: left;">
								<span style="display:inline-block; background: <?php echo $bgcolor; ?>; height: 100%; width: <?php echo round($occupancy_stats[$ind], 2); ?>%;">
								</span>
							</div>
							<div style="display:inline-block; float: left; padding: 18px 0 0 20px;">
								<span><?php echo round($occupancy_stats[$ind], $precision); ?></span><span>%</span>
							</div>                  
						</div>
					</div>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<!--[if (gte mso 9)|(IE)]>
		</td>
		</tr>
		</table>
		<![endif]-->

		<table style="width: 98%;background: #fff;margin: 10px 0; border-bottom: 1px solid #e7e7e7; color: #666;">
			<tr>
				<td>
					<h3 style="margin: 0 20px;padding: 15px 0px 0; text-transform: uppercase; font-weight: bold;"><?php echo JText::_('VCM_RESERVATIONS_COUNT'); ?></h3>
				</td>
			</tr>
			<tr>
				<td>
					<div style="margin: 10px 20px;">
						<table style="width: 100%; font-size: 15px;">
							<tr>
								<td style="border-bottom: 1px solid #eee;padding: 10px;font-weight: bold;font-size: 14px; color: #444; text-transform: uppercase;"><?php echo JText::_('VCM_REPORT_CHANNEL'); ?></td>
							<?php
							foreach ($reports as $report) {
								$dfinfo = getdate($report->date_from);
								$dtinfo = getdate($report->date_to);
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;font-weight: bold;font-size: 14px; color: #444; text-transform: uppercase;"><?php echo $dfinfo['month'] . ' ' . $dfinfo['year']; ?></td>
								<?php
							}
							if (count($reports) > 1) {
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;font-weight: bold;font-size: 14px; color: #444; text-transform: uppercase;"><?php echo JText::_('VCM_RATE_GROWTH'); ?></td>
								<?php
							}
							?>
							</tr>
						<?php
						foreach ($channels_reports->reservations_count as $chname => $data) {
							?>
							<tr>
								<td style="width: 150px; border-bottom: 1px solid #eee;padding: 10px;">
									<div style="font-size: 13px; font-weight: bold;">
										<?php echo $chname == 'VBO' ? JText::_('VCM_REPORT_YOURWEBSITE') : $chname; ?>
									</div>
								</td>
							<?php
							$period_vals = array();
							foreach ($data as $intk => $rep_data) {
								array_unshift($period_vals, $rep_data->reservations_count);
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;"><?php echo $rep_data->reservations_count; ?></td>
								<?php
							}
							if (count($period_vals) > 1) {
								$prev_val = $period_vals[1] != 0 ? $period_vals[1] : 1;
								$rog = round((($period_vals[0] - $period_vals[1]) / $prev_val * 100), 2);
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px; font-weight: bold;">
									<span style="color: <?php echo $rog <= 0 ? 'red' : 'green'; ?>;"><?php echo $rog . '%'; ?></span>
								</td>
								<?php
							}
							?>
							</tr>
							<?php
						}
						?>
						</table>
					</div>
				</td>
			</tr>
		</table>

		
		<table style="width: 98%;background: #fff;margin: 10px 0; border-bottom: 1px solid #e7e7e7; color: #666;">
			<tr>
				<td>
					<h3 style="margin: 0 20px;padding: 15px 0px 0; text-transform: uppercase; font-weight: bold;"><?php echo JText::_('VCM_GROSS_REVENUE'); ?></h3>
				</td>
			</tr>
			<tr>
				<td>
					<div style="margin: 10px 20px;">
						<table style="width: 100%; font-size: 15px;">
							<tr>
								<td style="border-bottom: 1px solid #eee;padding: 10px;font-weight: bold;font-size: 14px; color: #444; text-transform: uppercase;"><?php echo JText::_('VCM_REPORT_CHANNEL'); ?></td>
							<?php
							foreach ($reports as $report) {
								$dfinfo = getdate($report->date_from);
								$dtinfo = getdate($report->date_to);
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;font-weight: bold;font-size: 14px; color: #444; text-transform: uppercase;"><?php echo $dfinfo['month'] . ' ' . $dfinfo['year']; ?></td>
								<?php
							}
							if (count($reports) > 1) {
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;font-weight: bold;font-size: 14px; color: #444; text-transform: uppercase;"><?php echo JText::_('VCM_RATE_GROWTH'); ?></td>
								<?php
							}
							?>
							</tr>
						<?php
						foreach ($channels_reports->reservations_total as $chname => $data) {
							?>
							<tr>
								<td style="width: 150px; border-bottom: 1px solid #eee;padding: 10px;">
									<div style="font-size: 13px; font-weight: bold;">
										<?php echo $chname == 'VBO' ? JText::_('VCM_REPORT_YOURWEBSITE') : $chname; ?>
									</div>
								</td>
							<?php
							$period_vals = array();
							foreach ($data as $intk => $rep_data) {
								array_unshift($period_vals, $rep_data->reservations_total);
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;"><?php echo $currency . ' ' . number_format($rep_data->reservations_total, 2); ?></td>
								<?php
							}
							if (count($period_vals) > 1) {
								if ($period_vals[1] != 0) {
									$rog = ($period_vals[0] - $period_vals[1]) / $period_vals[1] * 100;
								} else {
									$rog = ($period_vals[0] - $period_vals[1]);
								}
								$rog_formatted = round($rog, 0);
								if ($rog_formatted < 2 && $rog_formatted > -2 && ($rog - intval(abs($rog))) > 0) {
									// small numbers are rounded to two decimals
									$rog_formatted = round($rog, 2);
								}
								?>
								<td style="border-bottom: 1px solid #eee;padding: 10px;">
									<span style="font-weight: bold; color: <?php echo $rog <= 0 ? 'red' : 'green'; ?>;"><?php echo $rog_formatted . '%'; ?></span>
								</td>
								<?php
							}
							?>
							</tr>
							<?php
						}
						?>
						</table>
					</div>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-spacing: 0; margin: 15px auto 0; padding: 15px; font-size: 12px;">
							<tr>
								<td style="padding: 0 10px 0; line-height: 1.4em; text-align: left;">
									
									<div style="display: inline-block;">
										<a style="text-decoration:none;" href="https://e4jconnect.com" alt="e4jConnect official page"><img width="100" src="https://extensionsforjoomla.com/mailing/images/logo_e4jconnect.png" alt="e4jConnect Logo" title="e4jConnect Logo" border="0" style="float: left; margin-right: 15px;" /></a>
									</div>

								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="padding: 0; text-align: center;">
					<table style="border-spacing: 0; margin: 10px auto 0; padding: 15px; font-size: 14px; border-top: 2px solid #eee;" width="100%">
						<tbody>
							<tr>
								<td style="padding: 0; line-height: 1.4em; text-align: left;">
								<p style="margin-top: 0;"><small><?php echo JText::_('VCM_REPORT_PLEASENOREPLY'); ?></small></p>

								<p style="font-size: 11px; line-height: 14px;"><?php echo JText::_('VCM_REPORT_DISCLAIMER'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					</td>
				</tr>
		</table>
	</div>
</div>
