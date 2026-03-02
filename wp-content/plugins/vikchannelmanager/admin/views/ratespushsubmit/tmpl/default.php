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

$short_wdays_map = array(
	0 => 'Sun',
	1 => 'Mon',
	2 => 'Tue',
	3 => 'Wed',
	4 => 'Thu',
	5 => 'Fri',
	6 => 'Sat'
);

// JS lang def
JText::script('VCMPROCESSING');
JText::script('VCM_PRELOADING_ASSETS');
JText::script('VCM_LOADING');

?>
<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}
function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}
</script>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-processing">
		<span class="vcm-loading-progress-title"><?php echo JText::_('VCMPROCESSING'); ?></span>
		<span class="vcm-loading-progress-text"></span>
	</div>
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-avpush-info vcm-avpush-info-result" style="display: none;">
	<h3><?php echo JText::_('VCMAVPUSHINFORESULTCOMPL'); ?></h3>
	<div class="vcm-avpush-info-result-btns">
		<a href="index.php?option=com_vikchannelmanager&task=avpush" class="btn vcm-config-btn"><?php VikBookingIcons::e('layer-group'); ?> <?php echo JText::_('VCMMENUBULKACTIONS') . ' - ' . JText::_('VCMMENUAVPUSH'); ?></a>
		<a href="index.php?option=com_vikchannelmanager" class="btn vcm-config-btn"><i class="vboicn-earth"></i> <?php echo JText::_('VCMMENUDASHBOARD'); ?></a>
	</div>
</div>

<div class="vcm-preload-assets-result"></div>

<div class="vcm-avpush-data vcm-ratespush-data">
	<div class="vcm-avpush-request vcm-ratespush-request">
<?php
$def_currency = VikChannelManager::getCurrencyName();
$upd_req_count = 1;
$nodes_count_req = 0;
$channels_req_count = 0;
$channels_map = array();
$rooms_map = array();
?>
		<h4><?php echo JText::sprintf('VCMRATESPUSHREQNUMB', $upd_req_count); ?> <span><?php echo JText::sprintf('VCMAVPUSHMAXNODES', $this->max_nodes); ?></span></h4>
<?php
foreach ($this->rows as $roomIndex => $room) {
	$rooms_map[$room['id']] = $room['name'];
	$all_channels = array();
	foreach ($room['channels'] as $ch_id => $channel) {
		$display_chname = $channel['channel'];
		if ($ch_id == VikChannelManagerConfig::AIRBNBAPI) {
			$display_chname = 'airbnb';
		} elseif ($ch_id == VikChannelManagerConfig::GOOGLEHOTEL) {
			$display_chname = 'google hotel';
		} elseif ($ch_id == VikChannelManagerConfig::GOOGLEVR) {
			$display_chname = 'google vr';
		}
		array_push($all_channels, ucwords($display_chname));
		$channels_map[$ch_id] = $channel['channel'];
	}
	$channels_req_count += count($room['channels']);
	$tot_nodes = count($room['ratesinventory']);
	$nodes_count = 0;
	while ($nodes_count < $tot_nodes) {
		$nodes_portion = ($this->max_nodes - $nodes_count_req) >= $tot_nodes ? $tot_nodes : ($this->max_nodes - $nodes_count_req);
		$nodes_arr = array();
		$nodes_verb_arr = array();
		for ($i=$nodes_count; $i < ($nodes_portion + $nodes_count); $i++) { 
			if ($i >= $tot_nodes) {
				break;
			}
			$nodes_arr[] = $room['ratesinventory'][$i];
			$node_rates_parts = explode('_', $room['ratesinventory'][$i]);
			//CTA/CTD
			$cta_wdays = array();
			$ctd_wdays = array();
			if (strpos($node_rates_parts[2], 'CTA') !== false) {
				//CTA is written before CTD in Min LOS so explode and re-attach the left part if CTD exists too
				$minlos_parts = explode('CTA[', $node_rates_parts[2]);
				$minlos_parts_left = explode(']', $minlos_parts[1]);
				$cta_wdays = explode(',', $minlos_parts_left[0]);
				$node_rates_parts[2] = $minlos_parts[0].(array_key_exists(1, $minlos_parts_left) ? $minlos_parts_left[1] : '');
			}
			if (strpos($node_rates_parts[2], 'CTD') !== false) {
				$minlos_parts = explode('CTD[', $node_rates_parts[2]);
				$ctd_wdays = explode(',', str_replace(']', '', $minlos_parts[1]));
				$node_rates_parts[2] = $minlos_parts[0];
			}
			$cta_ctd_verb = '';
			if (count($cta_wdays) > 0) {
				foreach ($cta_wdays as $ctwk => $ctwv) {
					$ctwv = intval(str_replace('-', '', $ctwv));
					$cta_wdays[$ctwk] = $short_wdays_map[$ctwv];
				}
				$cta_ctd_verb .= 'CTA: '.implode(',', $cta_wdays);
			}
			if (count($ctd_wdays) > 0) {
				foreach ($ctd_wdays as $ctwk => $ctwv) {
					$ctwv = intval(str_replace('-', '', $ctwv));
					$ctd_wdays[$ctwk] = $short_wdays_map[$ctwv];
				}
				$cta_ctd_verb .= (!empty($cta_ctd_verb) ? ' ' : '').'CTD: '.implode(',', $ctd_wdays);
			}
			if (!empty($cta_ctd_verb)) {
				$cta_ctd_verb = ' ['.$cta_ctd_verb.']';
			}
			//end CTA/CTD
			$ratesmod_verb = '';
			if (intval($node_rates_parts[4]) > 0 && intval($node_rates_parts[6]) > 0) {
				$ratesmod_verb = ' --&gt; '.(intval($node_rates_parts[5]) > 0 ? (intval($node_rates_parts[5]) < 2 ? '+' : '') : '-').(float)$node_rates_parts[6].' '.(intval($node_rates_parts[7]) > 0 ? '%' : $def_currency);
				if (isset($room['pushdata']['rmod_channels']) && is_array($room['pushdata']['rmod_channels'])) {
					// display the exact alteration for each channel in this room
					$ch_rmods_details = array();
					foreach ($room['channels'] as $ch_id => $channel) {
						if (!isset($room['pushdata']['rmod_channels'][$ch_id])) {
							continue;
						}
						if (!$room['pushdata']['rmod_channels'][$ch_id]['rmod']) {
							$ch_ratesmod_verb = '+0 %';
						} else {
							$ch_rmodop = $room['pushdata']['rmod_channels'][$ch_id]['rmodop'];
							$ch_rmodamount = $room['pushdata']['rmod_channels'][$ch_id]['rmodamount'];
							$ch_rmodval = $room['pushdata']['rmod_channels'][$ch_id]['rmodval'];
							$ch_ratesmod_verb = (intval($ch_rmodop) > 0 ? (intval($ch_rmodop) < 2 ? '+' : '') : '-').(float)$ch_rmodamount.' '.(intval($ch_rmodval) > 0 ? '%' : $def_currency);
						}
						$ch_rmods_details[] = $ch_ratesmod_verb;
					}
					$ratesmod_verb = count($ch_rmods_details) ? (' --&gt; ' . implode(', ', $ch_rmods_details)) : '';
				}
			}
			$nodes_verb_arr[] = $node_rates_parts[0].' - '.$node_rates_parts[1].(strlen($node_rates_parts[2]) && strlen($node_rates_parts[3]) ? ' --&gt; '.JText::_('VCMRARRESTRMINLOS').' '.$node_rates_parts[2].' '.JText::_('VCMRARRESTRMAXLOS').' '.$node_rates_parts[3] : '').$cta_ctd_verb.$ratesmod_verb;
		}
		$first_node = $nodes_arr[0];
		$last_node = $nodes_arr[(count($nodes_arr) - 1)];
		$first_node_parts = explode('_', $first_node);
		$last_node_parts = explode('_', $last_node);
		//prepare request data
		$channels_ids = array_keys($room['channels']);
		$channels_rplans = array();
		foreach ($channels_ids as $ch_id) {
			$ch_rplan = array_key_exists($ch_id, $room['pushdata']['rplans']) ? $room['pushdata']['rplans'][$ch_id] : '';
			$ch_rplan .= array_key_exists($ch_id, $room['pushdata']['rplanarimode']) ? '='.$room['pushdata']['rplanarimode'][$ch_id] : '';
			$ch_rplan .= array_key_exists($ch_id, $room['pushdata']['cur_rplans']) && !empty($room['pushdata']['cur_rplans'][$ch_id]) ? ':'.$room['pushdata']['cur_rplans'][$ch_id] : '';
			$channels_rplans[] = $ch_rplan;
		}
		$pushdata_str = implode(';', array($room['pushdata']['pricetype'], $room['pushdata']['defrate']));
		//
		?>
		<div class="vcm-avpush-request-node vcm-ratespush-request-node" data-roomid="<?php echo $room['id']; ?>" data-roomname="<?php echo $room['name']; ?>" data-channels="<?php echo implode(',', $channels_ids); ?>" data-chrplans="<?php echo implode(',', $channels_rplans); ?>">
			<div class="vcm-avpush-data-room"><?php echo $room['name']; ?></div>
		<?php
		if (!empty($room['from_to'])) {
		?>
			<div class="vcm-avpush-data-fromto"><?php echo str_replace('_', ' - ', $room['from_to']); ?></div>
		<?php
		}
		?>
			<div class="vcm-avpush-data-channels"><?php echo implode(', ', $all_channels); ?></div>
			<div class="vcm-avpush-data-fromtoreq"><span class="vcm-avpush-data-fromtoreq-totnodes"><?php echo JText::_('VCMAVPUSHTOTNODES'); ?> <?php echo count($nodes_arr); ?></span><span class="vcm-avpush-data-fromtoreq-dates"><?php echo $first_node_parts[0].' - '.$last_node_parts[1]; ?></span></div>
			<div class="vcm-avpush-data-nodesdetails">
			<?php
			foreach ($nodes_verb_arr as $node_verb) {
				$node_verb_parts = explode('--', $node_verb);
				$node_verb_dates = explode(' - ', $node_verb_parts[0]);
				?>
				<span data-nodefromto="<?php echo $room['id'].'-'.trim($node_verb_dates[0]).'-'.trim($node_verb_dates[1]); ?>"><?php echo $node_verb; ?></span>
				<?php
			}
			?>
			</div>
			<input type="hidden" class="vcm-ratespush-data-nodes<?php echo $room['id']; ?>" value="<?php echo implode(';', $nodes_arr); ?>" />
			<input type="hidden" class="vcm-ratespush-data-vars<?php echo $room['id']; ?>" value="<?php echo $pushdata_str; ?>" />
		</div>
		<?php
		if ($channels_req_count > 10 && $roomIndex < (count($this->rows) - 1)) {
			/**
			 * Limit the number of effective channel update XML requests per Bulk Rates Upload execution.
			 * If the channels being updated for this request is greater than the limit, build a new request.
			 * 
			 * @since 	1.9.6
			 */
			// build a new request
			$upd_req_count++;
			echo '<br clear="all" /></div>'."\n".'<div class="vcm-avpush-request">'."\n".'<h4>'.JText::sprintf('VCMRATESPUSHREQNUMB', $upd_req_count).'</h4>'."\n";
			$nodes_count_req = 0;
			// reset channels counter per request
			$channels_req_count = 0;
		} elseif (($nodes_count_req + count($nodes_arr)) >= $this->max_nodes) {
			// build a new request
			$upd_req_count++;
			echo '<br clear="all" /></div>'."\n".'<div class="vcm-avpush-request">'."\n".'<h4>'.JText::sprintf('VCMRATESPUSHREQNUMB', $upd_req_count).'</h4>'."\n";
			$nodes_count_req = 0;
			// reset channels counter per request
			$channels_req_count = 0;
		} else {
			// keep building up the current request
			$nodes_count_req += count($nodes_arr);
		}
		$nodes_count += $nodes_portion;
	}
}
?>
		<br clear="all" />
	</div>
</div>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="ratespushsubmit" />
</form>

<script type="text/javascript">
	const channels_map = <?php echo json_encode($channels_map); ?>;
	const rooms_map = <?php echo json_encode($rooms_map); ?>;
	const vcm_channels_preloading = <?php echo json_encode($this->preloading_assets); ?>;

	/**
	 * Checks wether a jQuery XHR response object was due to a connection error.
	 * Property readyState 0 = Network Error (UNSENT), 4 = HTTP error (DONE).
	 * Property responseText may not be set in some browsers.
	 * This is what to check to determine if a connection error occurred.
	 * 
	 * @since 	1.7.5
	 */
	function vcmIsConnectionLostError(err) {
		if (!err || !err.hasOwnProperty('status')) {
			return false;
		}

		return (
			err.statusText == 'error'
			&& err.status == 0
			&& (err.readyState == 0 || err.readyState == 4)
			&& (!err.hasOwnProperty('responseText') || err.responseText == '')
		);
	}

	/**
	 * Ensures AJAX requests that fail due to connection errors are retried automatically.
	 * 
	 * @since 	1.7.5
	 */
	function vcmDoAjax(url, data, success, failure, attempt) {
		var VCM_AJAX_MAX_ATTEMPTS = 3;

		if (attempt === undefined) {
			attempt = 1;
		}

		return jQuery.ajax({
			type: 'POST',
			url: url,
			data: data
		}).done(function(resp) {
			if (success !== undefined) {
				// launch success callback function
				success(resp);
			}
		}).fail(function(err) {
			/**
			 * If the error is caused by a site connection lost, and if the number
			 * of retries is lower than max attempts, retry the same AJAX request.
			 */
			if (attempt < VCM_AJAX_MAX_ATTEMPTS && vcmIsConnectionLostError(err)) {
				// delay the retry by half second
				setTimeout(function() {
					// relaunch same request and increase number of attempts
					console.log('Retrying previous AJAX request');
					vcmDoAjax(url, data, success, failure, (attempt + 1));
				}, 500);
			} else {
				// launch the failure callback otherwise
				if (failure !== undefined) {
					failure(err);
				}
			}

			// always log the error in console
			console.log('AJAX request failed' + (err.status == 500 ? ' (' + err.responseText + ')' : ''), err);
		});
	}

	/**
	 * Starts processing the requests to dispatch and execute.
	 */
	function vcmProcessRequests() {
		// set proper title
		document.querySelectorAll('.vcm-loading-progress-title').forEach((el) => {
			el.innerText = Joomla.JText._('VCMPROCESSING');
		});

		// access all request containers
		const requestContainers = document.querySelectorAll('.vcm-avpush-request');

		// count the total number of containers
		const containersCount = requestContainers.length;

		// gather values for dispatching the bulk actions
		let bulkActionsPool = [];
		requestContainers.forEach((req_elem, index) => {
			// prepare request node object
			let reqNode = {
				element: req_elem,
				index: index,
				requestCount: containersCount,
				rooms_req: [],
				channels_req: [],
				chrplans_req: [],
				nodes_req: [],
				vars_req: [],
			};
			// iterate over the current request nodes
			req_elem.querySelectorAll('.vcm-avpush-request-node').forEach((node_elem, node_index) => {
				// access listing id
				let roomid = node_elem.getAttribute('data-roomid');
				// access additional values
				let nodesVal = node_elem.querySelector('.vcm-ratespush-data-nodes' + roomid)?.value || '';
				let varsVal = node_elem.querySelector('.vcm-ratespush-data-vars' + roomid)?.value || '';
				// push values
				reqNode.rooms_req.push(roomid);
				reqNode.channels_req.push(node_elem.getAttribute('data-channels'));
				reqNode.chrplans_req.push(node_elem.getAttribute('data-chrplans'));
				reqNode.nodes_req.push(nodesVal);
				reqNode.vars_req.push(varsVal);
			});
			// push request node object
			bulkActionsPool.push(reqNode);
		});

		// count the total number of requests
		const requestCount = bulkActionsPool.length;

		// start success counter
		let successCounter = 0;

		// dispatch bulk actions
		vcmDispatchBulkActions(
			// list of bulk actions data
			bulkActionsPool,
			(obj_res, successNumber) => {
				// one request was completed
				if (successNumber) {
					successCounter += successNumber;
				}
			},
			() => {
				// dispatching operations completed
				document.querySelectorAll('.vcm-avpush-data-fromtoreq').forEach((datesEl) => {
					// trigger click event
					datesEl.dispatchEvent(new Event('click'));
				});

				document.querySelectorAll('.vcm-avpush-info-result').forEach((resEl) => {
					// display element
					resEl.style.display = 'block';
				});

				// stop loading animation
				vcmStopLoading();

				// finalize bulk action
				vcmDoAjax(
					"<?php echo VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&task=exec_ratespush_finalize'); ?>",
					{
						res: successCounter ? '1' : '0',
						tmpl: 'component',
					}
				);
			},
			(err_mess, unrecoverable) => {
				// request or response error
				if (unrecoverable) {
					// log the error
					console.error(err_mess);
				}
			}
		);
	}

	/**
	 * Dispatches and executes a list of rates update requests one after the other.
	 * 
	 * @param   array       requests    List of bulk action data objects.
	 * @param   function    onProgress  Optional callback when a request is completed.
	 * @param   function    onComplete  Optional callback when all requests have been completed.
	 * @param   function    onError     Optional callback in case of request error.
	 * 
	 * @return  void
	 * 
	 * @since 	1.9.14 adopted recursive-chained method to process all bulk actions one after the other.
	 */
	function vcmDispatchBulkActions(requests, onProgress, onComplete, onError) {
		if (!Array.isArray(requests) || !requests.length) {
			if (typeof onComplete === 'function') {
				onComplete();
			}

			// abort
			return;
		}

		// obtain the request to process
		const request = requests.shift();

		// display current element
		request.element.style.display = 'block';

		if (request.requestCount > 1) {
			// shift current bulk action element to the first position before it's processed
			document.querySelector('.vcm-ratespush-data').prepend(request.element);
		}

		// update process counter text
		document.querySelectorAll('.vcm-loading-progress-text').forEach((progressEl) => {
			progressEl.innerText = '#' + (request.index + 1) + ' / ' + request.requestCount;
		});

		// perform the request by relying on a retry system in case of connection errors
		vcmDoAjax(
			"<?php echo VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&task=exec_ratespush'); ?>",
			{
				r: (request.index + 1) + "_" + request.requestCount,
				rooms: request.rooms_req,
				channels: request.channels_req,
				chrplans: request.chrplans_req,
				nodes: request.nodes_req,
				v: request.vars_req,
				<?php echo isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1 ? 'e4j_debug: 1,' : ''; ?>
				tmpl: "component",
			},
			(res) => {
				if (typeof res === 'string' && res.substr(0, 9) == 'e4j.error') {
					// collect the error message
					let errorMess = res.replace('e4j.error.', '');

					// display error within the request element
					request.element.classList.add('vcm-avpush-request-error');
					let errorElem = document.createElement('pre');
					errorElem.classList.add('vcmpreerror');
					errorElem.innerHTML = errorMess;
					request.element.append(errorElem);

					if (typeof onError === 'function') {
						// register the error
						onError(errorMess);
					}

					// recursively call the same function to process the next request, if any
					vcmDispatchBulkActions(requests, onProgress, onComplete, onError);

					// abort
					return;
				}

				try {
					// decode or obtain the response object
					let vcm_push_obj = typeof res === 'string' ? JSON.parse(res) : res;

					// render the operation result
					request.element.classList.add('vcm-avpush-request-success');

					// register flag to detect a successful response
					let successCounter = 0;

					// parse the response object
					for (const [idroomvb, channels] of Object.entries(vcm_push_obj)) {
						// check if the breakdown is available
						if (channels.hasOwnProperty('breakdown')) {
							// parse the breakdown
							for (const [datenode, daterate] of Object.entries(channels.breakdown)) {
								let verb_node_elem = document.querySelector('span[data-nodefromto="' + idroomvb + '-' + datenode + '"]');
								if (verb_node_elem) {
									verb_node_elem.innerHTML = verb_node_elem.innerHTML + " --&gt; " + daterate;
								}
							}
							// unset this property as already processed
							delete channels.breakdown;
						}
						// parse the channels involved
						for (const [idchannel, chresult] of Object.entries(channels)) {
							if (chresult.substr(0, 9) == 'e4j.error') {
								request.element.insertAdjacentHTML('beforeend', "<div class='vcm-ratespush-request-esitnode vcm-ratespush-request-esitnode-error "+channels_map[idchannel]+"'><div class='vcm-ratespush-request-esitnode-room'>"+rooms_map[idroomvb]+"</div><div class='vcm-ratespush-request-esitnode-text'>" + chresult.replace("e4j.error.", "") + "</div></div>");
							} else if (chresult.substr(0, 11) == 'e4j.warning') {
								successCounter++;
								request.element.insertAdjacentHTML('beforeend', "<div class='vcm-ratespush-request-esitnode vcm-ratespush-request-esitnode-warning "+channels_map[idchannel]+"'><div class='vcm-ratespush-request-esitnode-room'>"+rooms_map[idroomvb]+"</div><div class='vcm-ratespush-request-esitnode-text'>" + chresult.replace("e4j.warning.", "") + "</div></div>");
							} else if (chresult.substr(0, 6) == 'e4j.OK') {
								successCounter++;
								request.element.insertAdjacentHTML('beforeend', "<div class='vcm-ratespush-request-esitnode vcm-ratespush-request-esitnode-success "+channels_map[idchannel]+"'><div class='vcm-ratespush-request-esitnode-room'>"+rooms_map[idroomvb]+"</div><div class='vcm-ratespush-request-esitnode-text'>" + chresult.replace("e4j.OK.", "") + "</div></div>");
							}
						}
					}

					if (typeof onProgress === 'function') {
						// call the given function by passing the operation result object and the success counter
						onProgress(vcm_push_obj, successCounter);
					}

					// recursively call the same function to process the next request
					vcmDispatchBulkActions(requests, onProgress, onComplete, onError);
				} catch(err) {
					// display error
					alert(err);

					if (typeof onError === 'function') {
						// unrecoverable error
						onError(err, true);
					}

					// recursively call the same function to process the next request, if any
					vcmDispatchBulkActions(requests, onProgress, onComplete, onError);
				}
			},
			(err) => {
				// display error
				request.element.classList.add('vcm-avpush-request-error');

				// attempt to get the error message
				let errorContent = err.responseText || '';

				// build the default error message
				let errorMessage = 'Error performing AJAX request #' + (request.index + 1);

				if (errorContent && errorContent.length < 300) {
					errorMessage += "\n" + errorContent;
				}

				// determine the method to display the error
				if (typeof VBOToast !== 'undefined') {
					// use a non-stopping toast message
					let toastDelay = 600000;
					if (typeof VBOToast?.POSITION_CENTER_CENTER !== 'undefined') {
						// change toast position
						VBOToast.changePosition(VBOToast.POSITION_CENTER_CENTER);
						// set supported delay to 0 for infinite message
						toastDelay = 0;
					}
					VBOToast.enqueue({
						text: errorMessage.replace("\n", '<br/>'),
						status: VBOToast.ERROR_STATUS,
						delay: toastDelay,
						action: () => {
							VBOToast.dispose(true);
						},
					});
				} else {
					// fallback to stopping alert
					alert(errorMessage);
				}

				if (typeof onError === 'function') {
					// connection error
					onError(err, true);
				}

				// recursively call the same function to process the next request, if any
				vcmDispatchBulkActions(requests, onProgress, onComplete, onError);
			}
		);
	}

	/**
	 * Dispatches the various requests to preload channel assets.
	 * Originally introduced for Vrbo API to provide updated XML files.
	 * 
	 * @param 	array 	preload 	the list of asset identifiers to preload.
	 * 
	 * @return 	Promise
	 */
	function vcmPreloadChannelAssets(preload) {
		// the object that will be resolved
		let assets_preloaded = {
			success: 0,
			errors: 0,
			messages: [],
		};

		// return the Promise by executing the requests and watching their completion
		return new Promise((resolve, reject) => {
			if (!Array.isArray(preload) || !preload.length) {
				// no channels require preloading
				resolve(assets_preloaded);
				return;
			}

			let completed_requests = 0;
			let total_requests = preload.length;

			// immediately display requests progress
			jQuery('.vcm-loading-progress-title').text(Joomla.JText._('VCM_LOADING') + '...');
			jQuery('.vcm-loading-progress-text').text('#' + completed_requests + ' / ' + total_requests);

			// watch the requests as they complete
			const preloading_interval = setInterval(() => {
				if (completed_requests >= total_requests) {
					// process completed: update progress text
					jQuery('.vcm-loading-progress-text').text('#' + total_requests + ' / ' + total_requests);
					// clear timer interval
					clearInterval(preloading_interval);
					// resolve the Promise and return
					resolve(assets_preloaded);
					return;
				}
				// update progress text
				jQuery('.vcm-loading-progress-text').text('#' + completed_requests + ' / ' + total_requests);
			}, 1000);

			// dispatch all requests
			preload.forEach((asset_sign, rq_index) => {
				VBOCore.doAjax(
					"<?php echo VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&task=bulkaction.rates_preload_channel_asset'); ?>",
					{
						asset: asset_sign,
						tmpl: 'component'
					},
					(res) => {
						// increase requests counter
						completed_requests++;
						try {
							var obj_res = typeof res === 'string' ? JSON.parse(res) : res;
							if (obj_res['status']) {
								// increase success counter
								assets_preloaded['success']++;
							} else {
								// increase error counter
								assets_preloaded['errors']++;
								// push error message
								if (obj_res.hasOwnProperty('error') && obj_res['error']) {
									assets_preloaded['messages'].push(obj_res['error'] + ' (index ' + rq_index + ')');
								} else {
									assets_preloaded['messages'].push('Invalid result for index ' + rq_index);
								}
							}
						} catch(err) {
							// log the error
							console.error('Could not parse JSON response', err, res);
							// increase error counter
							assets_preloaded['errors']++;
							// push error message
							assets_preloaded['messages'].push('Could not parse JSON response for index ' + rq_index);
						}
					},
					(err) => {
						// log the error
						console.error(err);
						// increase counters
						completed_requests++;
						assets_preloaded['errors']++;
						// push error message
						assets_preloaded['messages'].push(err.responseText);
					}
				);
			});
		});
	}

	/**
	 * Begin the actual process of requests submit.
	 */
	function vcmSendRequests() {
		// display loading
		vcmShowLoading();

		// start with a Promise to eventually preload channel assets
		vcmPreloadChannelAssets(vcm_channels_preloading).then((assets_preloaded) => {
			// preloading completed
			if (assets_preloaded['errors']) {
				// some errors occurred, check for messages to be displayed
				let preloading_err_mess = 'Total errors occurred ' + assets_preloaded['errors'];
				if (assets_preloaded['messages'].length) {
					preloading_err_mess = assets_preloaded['messages'].join("\n<br/>\n");
				}
				jQuery('.vcm-preload-assets-result').html('<p class="err">' + Joomla.JText._('VCM_PRELOADING_ASSETS') + '<br/>' + preloading_err_mess + '</p>');
			}

			// start the regular execution process with a bit of a delay
			jQuery('.vcm-avpush-request').first().fadeIn();
			setTimeout(function() {
				vcmProcessRequests();
			}, 1000);
		}).catch((error) => {
			// we should never have the Promise rejected, but we foresee it
			console.error(error);
			alert('The Bulk Action execution could not start. Please try launching it again.');
		});
	}

	jQuery(function() {
		jQuery('.vcm-avpush-request').each(function(k, v) {
			if (!jQuery(v).find('.vcm-avpush-request-node').length) {
				jQuery(this).remove();
			}
		});

		jQuery('.vcm-avpush-data-fromtoreq').click(function() {
			jQuery(this).next('.vcm-avpush-data-nodesdetails').slideToggle();
		});

		jQuery("body").on("click", ".vcm-result-readmore-btn", function() {
			jQuery(this).next('.vcm-result-readmore-cont').show();
			jQuery(this).remove();
		});

		// dispatch the update requests
		vcmSendRequests();
	});
</script>

<?php
if (!empty($_REQUEST['e4j_debug'])) {
	echo '<br clear="all"/><pre>'.print_r($this->rows, true).'</pre><br/>';
	echo '<pre>'.print_r($_POST, true).'</pre><br/>';
}
