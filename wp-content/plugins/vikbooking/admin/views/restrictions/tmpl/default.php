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

$rows = $this->rows;
$all_rooms = $this->all_rooms;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$app = JFactory::getApplication();
$session = JFactory::getSession();
$vbo_app = VikBooking::getVboApplication();

$updforvcm = $session->get('vbVcmRatesUpd', '');
$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
$vcm_exists = VikBooking::vcmAutoUpdate();

$pidroom = $app->getUserStateFromRequest("vbo.restrictions.idroom", 'idroom', 0, 'int');
$pdatefrom = $app->getUserStateFromRequest("vbo.restrictions.datefrom", 'datefrom', '', 'string');
$pdateto = $app->getUserStateFromRequest("vbo.restrictions.dateto", 'dateto', '', 'string');

$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$cdf = 'd/m/Y';
	$juidf = 'dd/mm/yy';
} elseif ($df == "%m/%d/%Y") {
	$cdf = 'm/d/Y';
	$juidf = 'mm/dd/yy';
} else {
	$cdf = 'Y/m/d';
	$juidf = 'yy/mm/dd';
}
$datesep = VikBooking::getDateSeparator(true);

?>
<div class="vbo-list-form-filters vbo-btn-toolbar">
	<form action="index.php?option=com_vikbooking&task=restrictions" method="post" name="restrictionsform">
		<div class="vbo-list-form-filter-select">
			<div class="vbo-list-form-filter-select-inner">
				<select id="idroom" name="idroom">
					<option value=""><?php echo JText::_('VBAFFANYROOM'); ?></option>
				<?php
				foreach ($this->all_rooms as $rid => $rname) {
					?>
					<option value="<?php echo $rid; ?>"<?php echo $rid == $pidroom ? ' selected="selected"' : ''; ?>><?php echo $rname; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<div class="vbo-list-form-filter-select">
			<div class="vbo-list-form-filter-select-inner">
				<?php echo $vbo_app->getCalendar($pdatefrom, 'datefrom', 'datefrom', $df, ['placeholder' => JText::_('VBNEWRESTRICTIONDFROMRANGE')]); ?>
			</div>
		</div>
		<div class="vbo-list-form-filter-select">
			<div class="vbo-list-form-filter-select-inner">
				<?php echo $vbo_app->getCalendar($pdateto, 'dateto', 'dateto', $df, ['placeholder' => JText::_('VBNEWRESTRICTIONDTORANGE')]); ?>
			</div>
		</div>
		<div class="vbo-list-form-filter-submit">
			<div class="vbo-list-form-filter-submit-inner">
				<button type="submit" class="btn btn-primary"><?php echo JText::_('VBODASHSEARCHKEYS'); ?></button>
				<button type="button" class="btn" onclick="vboRestrictionsClearFilters();"><?php echo JText::_('VBOSIGNATURECLEAR'); ?></button>
			</div>
		</div>
		<div class="vbo-right-btn-container">
		<?php
		if ($vcm_exists > 0 && $updforvcm) {
			?>
			<a class="btn btn-primary hasTooltip" href="index.php?option=com_vikchannelmanager&amp;task=ratespush&amp;vbosess=1" title="<?php echo addslashes(JText::sprintf('VBRATESOVWVCMRCHANGED', $updforvcm['count'])); ?>"><i class="vboicn-notification"></i> <?php echo JText::_('VBRATESOVWVCMRCHANGEDOPEN'); ?></a>
			<?php
		}
		?>
		</div>
		<input type="hidden" name="task" value="restrictions" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
</div>
<?php
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNORESTRICTIONSFOUND'); ?></p>
	<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm" class="vbo-list-form">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title center" width="50" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "id" ? "vbo-list-activesort" : "")); ?>">
					ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="100">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=name&amp;vbordersort=<?php echo ($orderby == "name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWRESTRICTIONSONE').($orderby == "name" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "name" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VBPVIEWRESTRICTIONSTWO' ); ?></th>
			<th class="title center" width="150" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=dfrom&amp;vbordersort=<?php echo ($orderby == "dfrom" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "dfrom" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "dfrom" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBRESTRICTIONSDRANGE').($orderby == "dfrom" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "dfrom" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VBPVIEWRESTRICTIONSTHREE' ); ?></th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=minlos&amp;vbordersort=<?php echo ($orderby == "minlos" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "minlos" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "minlos" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWRESTRICTIONSFOUR').($orderby == "minlos" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "minlos" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=maxlos&amp;vbordersort=<?php echo ($orderby == "maxlos" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "maxlos" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "maxlos" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWRESTRICTIONSFIVE').($orderby == "maxlos" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "maxlos" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="150" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=ctad&amp;vbordersort=<?php echo ($orderby == "ctad" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "ctad" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "ctad" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWRESTRICTIONSCTA').($orderby == "ctad" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "ctad" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="150" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=ctdd&amp;vbordersort=<?php echo ($orderby == "ctdd" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "ctdd" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "ctdd" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWRESTRICTIONSCTD').($orderby == "ctdd" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "ctdd" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=restrictions&amp;vborderby=allrooms&amp;vbordersort=<?php echo ($orderby == "allrooms" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "allrooms" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "allrooms" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBRESTRLISTROOMS').($orderby == "allrooms" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "allrooms" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
		</tr>
		</thead>
	<?php
	$arrmonths = array(
		1 => JText::_('VBMONTHONE'),
		2 => JText::_('VBMONTHTWO'),
		3 => JText::_('VBMONTHTHREE'),
		4 => JText::_('VBMONTHFOUR'),
		5 => JText::_('VBMONTHFIVE'),
		6 => JText::_('VBMONTHSIX'),
		7 => JText::_('VBMONTHSEVEN'),
		8 => JText::_('VBMONTHEIGHT'),
		9 => JText::_('VBMONTHNINE'),
		10 => JText::_('VBMONTHTEN'),
		11 => JText::_('VBMONTHELEVEN'),
		12 => JText::_('VBMONTHTWELVE')
	);
	$arrwdays = array(
		1 => JText::_('VBMONDAY'),
		2 => JText::_('VBTUESDAY'),
		3 => JText::_('VBWEDNESDAY'),
		4 => JText::_('VBTHURSDAY'),
		5 => JText::_('VBFRIDAY'),
		6 => JText::_('VBSATURDAY'),
		0 => JText::_('VBSUNDAY')
	);
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$drange = '-';
		if (!empty($row['dfrom'])) {
			$drange = date(str_replace("/", $datesep, $cdf), $row['dfrom']).' - '.date(str_replace("/", $datesep, $cdf), $row['dto']);
		}
		$room_tips = array();
		$sayrooms = JText::_('VBRESTRALLROOMS');
		if ($row['allrooms'] == 0) {
			$idr = explode(';', (string)$row['idrooms']);
			$sayrooms = (count($idr) - 1);
			foreach ($idr as $tipid) {
				if (!empty($tipid)) {
					$tipidr = (int)str_replace('-', '', $tipid);
					if (array_key_exists($tipidr, $all_rooms)) {
						$room_tips[] = $all_rooms[$tipidr];
					}
				}
			}
		}
		$wdayscta = array();
		$wdaysctd = array();
		if (!empty($row['ctad'])) {
			$wdayscta = explode(',', (string)$row['ctad']);
			foreach ($wdayscta as $ctk => $ctv) {
				$wk = intval(str_replace('-', '', $ctv));
				if (array_key_exists($wk, $arrwdays)) {
					$wdayscta[$ctk] = $arrwdays[$wk];
				} else {
					unset($wdayscta[$ctk]);
				}
			}
		}
		if (!empty($row['ctdd'])) {
			$wdaysctd = explode(',', (string)$row['ctdd']);
			foreach ($wdaysctd as $ctk => $ctv) {
				$wk = intval(str_replace('-', '', $ctv));
				if (array_key_exists($wk, $arrwdays)) {
					$wdaysctd[$ctk] = $arrwdays[$wk];
				} else {
					unset($wdaysctd[$ctk]);
				}
			}
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="center"><a href="index.php?option=com_vikbooking&amp;task=editrestriction&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td class="vbo-highlighted-td"><a href="index.php?option=com_vikbooking&amp;task=editrestriction&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td class="center"><?php echo !empty($row['month']) ? $arrmonths[$row['month']] : '-'; ?></td>
			<td class="center"><?php echo $drange; ?></td>
			<td class="center"><?php echo (strlen((string)$row['wday']) ? $arrwdays[$row['wday']] : '').(strlen((string)$row['wday']) && strlen((string)$row['wdaytwo']) ? '/'.$arrwdays[$row['wdaytwo']] : ''); ?></td>
			<td class="center"><?php echo $row['minlos']; ?></td>
			<td class="center"><?php echo !empty($row['maxlos']) ? $row['maxlos'] : '-'; ?></td>
			<td class="center"><?php echo count($wdayscta) > 0 ? implode(', ', $wdayscta) : '-'; ?></td>
			<td class="center"><?php echo count($wdaysctd) > 0 ? implode(', ', $wdaysctd) : '-'; ?></td>
			<td class="center"><span<?php echo count($room_tips) > 0 ? ' title="'.addslashes(implode(', ', $room_tips)).'" style="padding: 0 3px;"' : ''; ?>><?php echo $sayrooms; ?></span></td>
		</tr>	
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="restrictions" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="idroom" value="<?php echo $pidroom; ?>" />
	<input type="hidden" name="datefrom" value="<?php echo $pdatefrom; ?>" />
	<input type="hidden" name="dateto" value="<?php echo $pdateto; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
?>
<script type="text/javascript">
	function vboRestrictionsClearFilters() {
		jQuery('form[name="restrictionsform"]').find('select[name="idroom"], input[name="datefrom"], input[name="dateto"]').val('');
		document.restrictionsform.submit();
	}
</script>
