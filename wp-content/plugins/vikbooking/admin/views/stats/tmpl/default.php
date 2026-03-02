<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$bookings = $this->bookings;
$arr_rooms = $this->arr_rooms;
$fromts = $this->fromts;
$tots = $this->tots;
$pstatsmode = $this->pstatsmode;
$arr_months = $this->arr_months;
$arr_channels = $this->arr_channels;
$arr_saleschannels = $this->arr_saleschannels;
$arr_countries = $this->arr_countries;
$arr_totals = $this->arr_totals;
$tot_rooms_units = $this->tot_rooms_units;

JHtml::_('behavior.tooltip');
$vbo_app = VikBooking::getVboApplication();
$vbo_app->prepareJavaScriptCurrency();

$pid_room = VikRequest::getInt('id_room', '', 'request');
$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$currencysymb = VikBooking::getCurrencySymb();
$days_diff = (int)ceil(($tots - $fromts) / 86400);
?>
<form action="index.php?option=com_vikbooking&amp;task=stats" id="vbo-statsform" method="post" style="margin: 0;" class="vbo-stats-topform">
	<div id="filter-bar" class="btn-toolbar vbo-btn-toolbar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<select name="statsmode" onchange="document.getElementById('vbo-statsform').submit();">
				<option value="ts"<?php echo $pstatsmode == 'ts' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOSTATSMODETS'); ?></option>
				<option value="nights"<?php echo $pstatsmode == 'nights' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOSTATSMODENIGHTS'); ?></option>
			</select>
		</div>
		<div class="btn-group pull-right-group">
			<div class="btn-group pull-inline">
				<?php echo $vbo_app->getCalendar(date($usedf, $fromts), 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
			</div>
			<div class="btn-group pull-inline">
				<?php echo $vbo_app->getCalendar(date($usedf, $tots), 'dto', 'dto', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
			</div>
			<div class="btn-group pull-inline">
				<select name="id_room">
					<option value=""><?php echo JText::_('VBOSTATSALLROOMS'); ?></option>
				<?php
				foreach($arr_rooms as $room) {
					?>
					<option value="<?php echo $room['id']; ?>"<?php echo $room['id'] == $pid_room ? ' selected="selected"' : ''; ?>><?php echo $room['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
			<div class="btn-group pull-inline">
				<button type="submit" class="btn"><?php echo JText::_('VBPVIEWORDERSSEARCHSUBM'); ?></button>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">
jQuery(function() {
	jQuery('#dfrom').val('<?php echo date($usedf, $fromts); ?>').attr('data-alt-value', '<?php echo date($usedf, $fromts); ?>');
	jQuery('#dto').val('<?php echo date($usedf, $tots); ?>').attr('data-alt-value', '<?php echo date($usedf, $tots); ?>');
});
</script>

<?php
$months_map = array(
	'1' => JText::_('VBSHORTMONTHONE'),
	'2' => JText::_('VBSHORTMONTHTWO'),
	'3' => JText::_('VBSHORTMONTHTHREE'),
	'4' => JText::_('VBSHORTMONTHFOUR'),
	'5' => JText::_('VBSHORTMONTHFIVE'),
	'6' => JText::_('VBSHORTMONTHSIX'),
	'7' => JText::_('VBSHORTMONTHSEVEN'),
	'8' => JText::_('VBSHORTMONTHEIGHT'),
	'9' => JText::_('VBSHORTMONTHNINE'),
	'10' => JText::_('VBSHORTMONTHTEN'),
	'11' => JText::_('VBSHORTMONTHELEVEN'),
	'12' => JText::_('VBSHORTMONTHTWELVE')
);
if (!(count($bookings) > 0) || !(count($arr_months) > 0)) {
	?>
<p class="warn"><?php echo JText::_('VBNOBOOKINGSTATS'); ?></p>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
</form>
	<?php
} else {
	$datasets = array();
	$donut_datasets = array();
	$nights_datasets = array();
	$nights_donut_datasets = array();
	$months_labels = array_keys($arr_months);
	foreach ($months_labels as $mlbk => $mlbv) {
		$mlb_parts = explode('-', $mlbv);
		$months_labels[$mlbk] = $months_map[$mlb_parts[0]].' '.$mlb_parts[1];
	}
	$tot_months = count($months_labels);
	$tot_channels = count($arr_channels);
	$rooms_pool = array();
	foreach ($bookings as $bk => $bv) {
		if (isset($bv['room_names']) && is_array($bv['room_names'])) {
			foreach ($bv['room_names'] as $r) {
				if (!empty($r) && !in_array($r, $rooms_pool)) {
					$rooms_pool[] = $r;
				}
			}
		}
	}
	$tot_rooms = count($rooms_pool);
	$rand_max = $tot_channels + $tot_rooms;
	$rgb_rand = array();
	for ($z = 0; $z < $rand_max; $z++) { 
		$rgb_rand[$z] = mt_rand(0, 255).','.mt_rand(0, 255).','.mt_rand(0, 255);
	}
	$known_ch_rgb = array(
		JText::_('VBOIBECHANNEL') => '34,72,93',
		'booking.com' => '1,170,233',
		'agoda' => '128,3,205',
		'expedia' => '252,189,6',
		'airbnb' => '255,70,124',
		'hotels.com' => '243,86,64',
		'venere' => '214,109,26',
		'tripconnect' => '75,154,87',
		'homeaway' => '59,113,194'
	);
	//Customers Sales Channels
	if (count($arr_saleschannels) > 0) {
		foreach ($arr_saleschannels as $chk => $chparams) {
			$chk = strtolower($chk);
			if (array_key_exists($chk, $known_ch_rgb) || !array_key_exists('chcolor', $chparams['chdata']) || empty($chparams['chdata']['chcolor'])) {
				continue;
			}
			list($r, $g, $b) = sscanf($chparams['chdata']['chcolor'], "#%02x%02x%02x");
			$known_ch_rgb[$chk] = "$r,$g,$b";
		}
	}
	//
	$ch_dataset = array();
	$ch_donut_dataset = array();
	//commissions (Donuts Chart)
	$ch_donut_dataset[JText::_('VBSTATSOTACOMMISSIONS')] = array(
		'label' => JText::_('VBSTATSOTACOMMISSIONS'),
		'backgroundColor' => "rgba(255,0,0,1)",
		'hoverBorderColor' => "rgba(255,0,0,0.9)",
		'value' => 0
	);
	//
	$ch_map = array();
	foreach ($arr_channels as $chname) {
		$ch_color = $rgb_rand[rand(0, ($tot_channels - 1))];
		if (array_key_exists(strtolower($chname), $known_ch_rgb)) {
			$ch_color = $known_ch_rgb[strtolower($chname)];
		} else {
			foreach ($known_ch_rgb as $kch => $krgb) {
				if (stripos($chname, $kch) !== false) {
					$ch_color = $krgb;
					break;
				}
			}
		}
		$ch_dataset[$chname] = array(
			'label' => $chname,
			'backgroundColor' => "rgba(".$ch_color.",0.2)",
			'borderColor' => "rgba(".$ch_color.",1)",
			'pointBackgroundColor' => "rgba(".$ch_color.",1)",
			'pointBorderColor' => "#fff",
			'pointHoverBackgroundColor' => "#fff",
			'pointHoverBorderColor' => "rgba(".$ch_color.",1)",
			'pointRadius' => 4,
			'pointHoverRadius' => 5,
			'tot_bookings' => 0,
			'data' => array()
		);
		$ch_donut_dataset[$chname] = array(
			'label' => $chname,
			'backgroundColor' => "rgba(".$ch_color.",1)",
			'hoverBorderColor' => "rgba(".$ch_color.",0.9)",
			'value' => 0
		);
		$ch_map[$chname] = $chname;
	}
	$ch_nights_dataset = array(
		'label' => JText::_('VBOGRAPHTOTNIGHTSLBL'),
		'backgroundColor' => "rgba(34,72,93,0.2)",
		'borderColor' => "rgba(34,72,93,1)",
		'pointBackgroundColor' => "rgba(34,72,93,1)",
		'pointBorderColor' => "#fff",
		'pointHoverBackgroundColor' => "#fff",
		'pointHoverBorderColor' => "rgba(34,72,93,1)",
		'pointRadius' => 4,
		'pointHoverRadius' => 5,
		'tot_nights' => 0,
		'data' => array()
	);
	$ch_nights_donut_dataset = array();
	foreach ($rooms_pool as $rpk => $r) {
		$ch_color = $rgb_rand[($tot_channels + $rpk)];
		$ch_nights_donut_dataset[$r] = array(
			'label' => $r,
			'backgroundColor' => "rgba(".$ch_color.",1)",
			'hoverBorderColor' => "rgba(".$ch_color.",0.9)",
			'value' => 0
		);
	}
	foreach ($arr_months as $monyear => $chbookings) {
		$tot_monchannels = count($chbookings);
		$monchannels = array();
		$totnb = 0;
		foreach ($chbookings as $chname => $ords) {
			$monchannels[] = $chname;
			$totchb = 0;
			$totchcomm = 0;
			foreach ($ords as $ord) {
				$totchb += (float)$ord['total'];
				$totchcomm += (float)$ord['cmms'];
				$totnb += (empty($pid_room) ? ($ord['days'] * $ord['roomsnum']) : $ord['days']);
				if (isset($ord['room_names']) && is_array($ord['room_names'])) {
					foreach ($ord['room_names'] as $r) {
						if (array_key_exists($r, $ch_nights_donut_dataset)) {
							$ch_nights_donut_dataset[$r]['value'] += $ord['days'];
						}
					}
				}
			}
			$ch_dataset[$chname]['tot_bookings'] += count($ords);
			$ch_dataset[$chname]['data'][] = $totchb;
			$ch_donut_dataset[$chname]['value'] += $totchb;
			$ch_donut_dataset[JText::_('VBSTATSOTACOMMISSIONS')]['value'] += $totchcomm;
		}
		$ch_nights_dataset['tot_nights'] += $totnb;
		$ch_nights_dataset['data'][] = $totnb;
		if ($tot_monchannels < $tot_channels) {
			$ch_missing = array_diff($ch_map, $monchannels);
			foreach ($ch_missing as $chnk => $chnv) {
				if (array_key_exists($chnv, $ch_dataset)) {
					$ch_dataset[$chnv]['data'][] = 0;
				}
			}
		}
	}
	foreach ($ch_dataset as $chname => $chgraph) {
		$chgraph['label'] = $chgraph['label'].' ('.$chgraph['tot_bookings'].')';
		unset($chgraph['tot_bookings']);
		$datasets[] = $chgraph;
	}
	foreach ($ch_donut_dataset as $chname => $chgraph) {
		$donut_datasets[] = $chgraph;
	}
	$nights_datasets[] = $ch_nights_dataset;
	//Sort the array depending on the number of nights sold per room
	$nights_donut_sortmap = array();
	foreach ($ch_nights_donut_dataset as $rname => $rgraph) {
		// round values
		$rounded_val = round($rgraph['value'], 2);
		$ch_nights_donut_dataset[$rname]['value'] = $rounded_val;
		$rgraph['value'] = $rounded_val;
		//
		$nights_donut_sortmap[$rname] = $rgraph['value'];
	}
	arsort($nights_donut_sortmap);
	$copy_nights_donut = $ch_nights_donut_dataset;
	$ch_nights_donut_dataset = array();
	foreach ($nights_donut_sortmap as $rname => $soldnights) {
		$ch_nights_donut_dataset[$rname] = $copy_nights_donut[$rname];
	}
	unset($copy_nights_donut);
	//end Sort
	foreach ($ch_nights_donut_dataset as $rname => $rgraph) {
		$nights_donut_datasets[] = $rgraph;
	}
	?>
<div class="vbo-stats-wrapper">
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::sprintf('VBOSTATSFOR', count($bookings), $days_diff, $tot_channels); ?></legend>
			<div class="vbo-graph-top-wrapper">
				<div class="vbo-graph-introtitle">
					<span><?php echo JText::_('VBOGRAPHTOTSALES'); ?></span>
				</div>
				<div class="vbo-graph-top-inner">
					<div class="vbo-graph-top-left">
						<div class="vbo-graphstats-left">
							<canvas id="vbo-graphstats-left-canv"></canvas>
						</div>
					</div>
					<div class="vbo-graph-top-right">
						<div class="vbo-graphstats-right">
							<canvas id="vbo-graphstats-right-canv"></canvas>
						</div>
						<div class="vbo-graphstats-secondright">
							<h4><?php echo JText::_('VBOSTATSTOPCOUNTRIES'); ?></h4>
							<div class="vbo-graphstats-countries">
							<?php
							$clisted = 0;
							foreach ($arr_countries as $ccode => $cdata) {
								if ($clisted > 4) {
									break;
								}
								?>
								<div class="vbo-graphstats-country-wrap">
									<span class="vbo-graphstats-country-img"><?php echo $cdata['img']; ?></span>
									<span class="vbo-graphstats-country-name"><?php echo $cdata['country_name']; ?></span>
									<span class="vbo-graphstats-country-totb badge"><?php echo $cdata['tot_bookings']; ?></span>
								</div>
								<?php
								$clisted++;
							}
							?>
							</div>
						</div>
						<div class="vbo-graphstats-thirdright">
							<h4><?php echo JText::_('VBOTOTALS'); ?></h4>
							<p class="vbo-graphstats-income"><span><?php echo JText::_('VBOSTATSTOTINCOME'); ?></span> <?php echo VikBooking::formatCurrencyNumber(VikBooking::numberFormat($arr_totals['total_income']), $currencysymb); ?></p>
							<p class="vbo-graphstats-income-netcmms">
								<span><?php echo $pstatsmode == 'nights' ? $vbo_app->createPopover(array('title' => JText::_('VBOSTATSTOTINCOMELESSCMMS'), 'content' => JText::_('VBOGRAPHAVGVALUES'), 'icon_class' => VikBookingIcons::i('info-circle'))).'&nbsp;' : ''; ?><?php echo JText::_('VBOSTATSTOTINCOMELESSCMMS'); ?></span> <?php echo VikBooking::formatCurrencyNumber(VikBooking::numberFormat($arr_totals['total_income_netcmms']), $currencysymb); ?>
							</p>
							<p class="vbo-graphstats-income-nettax"><span class="hasTooltip" title="<?php echo JText::_('VBOSTATSTOTINCOMELESSTAXHELP'); ?>"><?php echo JText::_('VBOSTATSTOTINCOMELESSTAX'); ?></span> <?php echo VikBooking::formatCurrencyNumber(VikBooking::numberFormat($arr_totals['total_income_nettax']), $currencysymb); ?></p>
						</div>
					</div>
				</div>
			</div>
		<?php
		if ($pstatsmode == 'nights') {
			$tot_occ_pcent = round((100 * $arr_totals['nights_sold'] / ($tot_rooms_units * $days_diff)), 3);
			?>
			<div class="vbo-graph-bottom-wrapper">
				<div class="vbo-graph-introtitle">
					<span><?php echo JText::sprintf('VBOGRAPHTOTNIGHTS', $arr_totals['nights_sold']); ?> - <?php echo JText::sprintf('VBOGRAPHTOTOCCUPANCY', $tot_occ_pcent); ?></span>
				</div>
				<div class="vbo-graph-bottom-inner">
					<div class="vbo-graph-bottom-left">
						<div class="vbo-graphstats-left vbo-graphstats-left-nights">
							<canvas id="vbo-graphstats-left-canv-nights"></canvas>
						</div>
					</div>
					<div class="vbo-graph-bottom-right">
						<?php
						if (count($nights_donut_datasets) > 0) {
						?>
						<div class="vbo-graphstats-right vbo-graphstats-right-nights">
							<canvas id="vbo-graphstats-right-canv-nights"></canvas>
						</div>
						<?php
						}
						?>
						<div class="vbo-graphstats-thirdright vbo-graphstats-thirdright-nights">
							<h4><?php echo JText::_('VBOTOTALS'); ?></h4>
							<div class="vbo-graphstats-mod-containers">
								<div class="vbo-graphstats-mod-container vbo-graphstats-totocc">
									<div class="vbo-graphstats-mod-inner">
										<div class="vbo-graphstats-mod-lbl"><?php echo JText::_('VBOGRAPHTOTOCCUPANCYLBL'); ?></div>
										<div class="vbo-graphstats-mod-val"><?php echo $tot_occ_pcent; ?>%</div>
									</div>
								</div>
								<div class="vbo-graphstats-mod-container vbo-graphstats-totunits">
									<div class="vbo-graphstats-mod-inner">
										<div class="vbo-graphstats-mod-lbl"><?php echo JText::_('VBOGRAPHTOTUNITSLBL'); ?></div>
										<div class="vbo-graphstats-mod-val"><?php echo $tot_rooms_units; ?></div>
									</div>
								</div>
						<?php
						if ($tot_months > 1 && count($nights_datasets[0]['data']) > 1) {
							$remonths_labels = array_keys($arr_months);
							$max_nights = max($nights_datasets[0]['data']);
							$min_nights = min($nights_datasets[0]['data']);
							$max_month_key = array_search($max_nights, $nights_datasets[0]['data']);
							$min_month_key = array_search($min_nights, $nights_datasets[0]['data']);
							$max_monyear = explode('-', $remonths_labels[$max_month_key]);
							$max_month_days = date('t', mktime(0, 0, 0, $max_monyear[0], 1, $max_monyear[1]));
							$min_monyear = explode('-', $remonths_labels[$min_month_key]);
							$min_month_days = date('t', mktime(0, 0, 0, $min_monyear[0], 1, $min_monyear[1]));
							if ($max_month_key !== false && $min_month_key !== false) {
								?>
								<div class="vbo-graphstats-mod-container vbo-graphstats-bestworst vbo-graphstats-best">
									<div class="vbo-graphstats-mod-inner">
										<div class="vbo-graphstats-mod-lbl"><?php echo $months_labels[$max_month_key] . ' - ' . JText::_('VBOGRAPHTOTNIGHTSLBL'); ?></div>
										<div class="vbo-graphstats-mod-val">
											<span class="vbo-graphstats-val-main"><?php echo $max_nights; ?></span>
											<span class="vbo-graphstats-val-descr"><?php echo round((100 * $max_nights / ($tot_rooms_units * $max_month_days)), 3); ?>%</span>
											<?php VikBookingIcons::e('thumbs-up'); ?>
										</div>
									</div>
								</div>
								<div class="vbo-graphstats-mod-container vbo-graphstats-bestworst vbo-graphstats-worst">
									<div class="vbo-graphstats-mod-inner">
										<div class="vbo-graphstats-mod-lbl"><?php echo $months_labels[$min_month_key] . ' - ' . JText::_('VBOGRAPHTOTNIGHTSLBL'); ?></div>
										<div class="vbo-graphstats-mod-val">
											<span class="vbo-graphstats-val-main"><?php echo $min_nights; ?></span>
											<span class="vbo-graphstats-val-descr"><?php echo round((100 * $min_nights / ($tot_rooms_units * $min_month_days)), 3); ?>%</span>
											<?php VikBookingIcons::e('thumbs-down'); ?>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		</fieldset>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikbooking">
	</form>
</div>
<?php
/**
 * Adjust datasets compatibility with Chart.js v2.x.
 * 
 * @since 	1.13
 */
$donut_labels = $nights_donut_labels = array();
$new_donut_datasets = $new_nights_donut_datasets = array(
	'label' 			=> 'Dataset',
	'data' 				=> array(),
	'backgroundColor'  	=> array(),
	'hoverBorderColor' 	=> array(),
);
foreach ($donut_datasets as $k => $v) {
	array_push($donut_labels, $v['label']);
	array_push($new_donut_datasets['data'], $v['value']);
	array_push($new_donut_datasets['backgroundColor'], $v['backgroundColor']);
	array_push($new_donut_datasets['hoverBorderColor'], $v['hoverBorderColor']);
}
foreach ($nights_donut_datasets as $k => $v) {
	array_push($nights_donut_labels, $v['label']);
	array_push($new_nights_donut_datasets['data'], $v['value']);
	array_push($new_nights_donut_datasets['backgroundColor'], $v['backgroundColor']);
	array_push($new_nights_donut_datasets['hoverBorderColor'], $v['hoverBorderColor']);
}
//
?>
<script type="text/javascript">
jQuery(function() {
	var data = {
		labels: <?php echo json_encode($months_labels); ?>,
		datasets: <?php echo json_encode($datasets); ?>,
	};
	var donut_data = {
		labels: <?php echo json_encode($donut_labels); ?>,
		datasets: <?php echo json_encode(array($new_donut_datasets)); ?>,
	};
	var nights_data = {
		labels: <?php echo json_encode($months_labels); ?>,
		datasets: <?php echo json_encode($nights_datasets); ?>
	};
	var nights_donut_data = {
		labels: <?php echo json_encode($nights_donut_labels); ?>,
		datasets: <?php echo json_encode(array($new_nights_donut_datasets)); ?>,
	};

	var options = {
		responsive: true,
		legend: {
			display: false,
		},
		legendCallback: function (chart) {
			// Return the HTML string here.
			var text = [];
			text.push("<ul class=\"chart-line-legend\">");
			for (var i = 0; i < chart.data.datasets.length; i++) {
				text.push("<li>");
				text.push("<span class=\"legend-entry\" style=\"background-color: " + chart.data.datasets[i].backgroundColor + "\"></span>");
				text.push("<span class=\"legend-label\">" + chart.data.datasets[i].label + "</span>");
				text.push("</li>");
			}
			text.push("</ul>");
			return text.join("");
		},
		// tooltip handling
		tooltips: {
			// tooltip callbacks are used to customize default texts
			callbacks: {
				// format the tooltip text displayed when hovering a point
				label: function(tooltipItem, data) {
					// format value as currency with channel name
					var chname = data.datasets[tooltipItem.datasetIndex].label;
					var label = chname + ': ' + VBOCore.getCurrency().format(tooltipItem.value);
					return ' ' + label;
				},
				// change label colors because, by default, the legend background is blank
				labelColor: function(tooltipItem, chart) {
					// get tooltip item meta data
					var meta = chart.data.datasets[tooltipItem.datasetIndex];
					return {
						// use white border
						borderColor: 'rgb(0,0,0)',
						// use same item background color
						backgroundColor: meta.borderColor,
					};
				},
			},
		},
	};

	var pie_options = {
		responsive: true,
		legend: {
			display: false,
		},
		legendCallback: function (chart) {
			// Return the HTML string here.
			var text = [];
			text.push("<ul class=\"chart-line-legend chart-pie-legend\">");
			for (var i = 0; i < chart.data.labels.length; i++) {
				text.push("<li>");
				text.push("<span class=\"legend-entry\" style=\"background-color: " + chart.data.datasets[0].backgroundColor[i] + "\"></span>");
				text.push("<span class=\"legend-label\">" + chart.data.labels[i] + "</span>");
				text.push("</li>");
			}
			text.push("</ul>");
			return text.join("");
		},
		// tooltip handling
		tooltips: {
			// tooltip callbacks are used to customize default texts
			callbacks: {
				// format the tooltip text displayed when hovering a point
				label: function(tooltipItem, data) {
					// keep default label
					var label = data.labels[tooltipItem.index] || '';
					if (label) {
						label += ': ';
					}
					label += ' ' + VBOCore.getCurrency().format(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
					return ' ' + label;
				},
				// change label colors because, by default, the legend background is blank
				labelColor: function(tooltipItem, chart) {
					// get tooltip item meta data
					var meta = chart.data.datasets[tooltipItem.datasetIndex];
					return {
						// use white border
						borderColor: 'rgb(0,0,0)',
						// use same item background color
						backgroundColor: meta.backgroundColor[tooltipItem.index],
					};
				},
			},
		},
		animation: {
			duration: 1000,
		},
	};

	var ctx = document.getElementById("vbo-graphstats-left-canv").getContext("2d");
	var vboLineChart = new Chart(ctx, {
		type: 'line',
		data: data,
		options: options,
	});
	jQuery('#vbo-graphstats-left-canv').parent().append(vboLineChart.generateLegend());

	var donut_ctx = document.getElementById("vbo-graphstats-right-canv").getContext("2d");
	var vboDonutChart = new Chart(donut_ctx, {
		type: 'pie',
		data: donut_data,
		options: pie_options,
	});
	jQuery('#vbo-graphstats-right-canv').parent().append(vboDonutChart.generateLegend());

		<?php
		if ($pstatsmode == 'nights') {
			?>
	var nights_options = options;
	nights_options.tooltips.callbacks.label = function(tooltipItem, data) {
		// format value as nights booked
		var label = tooltipItem.value;
		return ' ' + label + ' <?php echo addslashes(JText::_('VBOGRAPHTOTNIGHTSLBL')); ?>';
	};
	var nights_ctx = document.getElementById("vbo-graphstats-left-canv-nights").getContext("2d");
	var vboNightsLineChart = new Chart(nights_ctx, {
		type: 'line',
		data: nights_data,
		options: nights_options,
	});
	jQuery('#vbo-graphstats-left-canv-nights').parent().append(vboNightsLineChart.generateLegend());
			<?php
		}
		if (count($nights_donut_datasets) > 0) {
			?>
	var nights_pie_options = pie_options;
	nights_pie_options.tooltips.callbacks.label = function(tooltipItem, data) {
		var label = data.labels[tooltipItem.index] || '';
		if (label) {
			label += ': ';
		}
		label += data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + ' <?php echo addslashes(JText::_('VBOGRAPHTOTNIGHTSLBL')); ?>';
		return ' ' + label;
	};
	var nights_donut_ctx = document.getElementById("vbo-graphstats-right-canv-nights").getContext("2d");
	var vboNightsDonutChart = new Chart(nights_donut_ctx, {
		type: 'pie',
		data: nights_donut_data,
		options: nights_pie_options,
	});
	jQuery('#vbo-graphstats-right-canv-nights').parent().append(vboNightsDonutChart.generateLegend());
			<?php
		}
		?>
});
</script>
	<?php
}
