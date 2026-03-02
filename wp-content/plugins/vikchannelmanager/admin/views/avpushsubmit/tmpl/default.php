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
	<div class="vcm-loading-processing"><?php echo JText::_('VCMPROCESSING'); ?> <span class="vcm-loading-progress-text"></span></div>
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-avpush-info vcm-avpush-info-result" style="display: none;">
	<h3><?php echo JText::_('VCMAVPUSHINFORESULTCOMPL'); ?></h3>
	<p><?php echo JText::_('VCMAVPUSHINFORESULT'); ?></p>
	<div class="vcm-avpush-info-result-btns">
		<a href="index.php?option=com_vikchannelmanager&task=ratespush" class="btn vcm-config-btn"><?php VikBookingIcons::e('layer-group'); ?> <?php echo JText::_('VCMMENUBULKACTIONS') . ' - ' . JText::_('VCMMENURATESPUSH'); ?></a>
		<a href="index.php?option=com_vikchannelmanager" class="btn vcm-config-btn"><i class="vboicn-earth"></i> <?php echo JText::_('VCMMENUDASHBOARD'); ?></a>
	</div>
</div>

<div class="vcm-avpush-data">
	<div class="vcm-avpush-request">
<?php
$upd_req_count = 1;
$nodes_count_req = 0;
?>
		<h4><?php echo JText::sprintf('VCMAVPUSHREQNUMB', $upd_req_count); ?> <span><?php echo JText::sprintf('VCMAVPUSHMAXNODES', $this->max_nodes); ?></span></h4>
<?php
foreach ($this->rows as $k => $room) {
	$all_channels = array();
	foreach ($room['channels'] as $ch_id => $channel) {
		if ($ch_id == VikChannelManagerConfig::AIRBNBAPI) {
			$channel['channel'] = 'airbnb';
		} elseif ($ch_id == VikChannelManagerConfig::GOOGLEHOTEL) {
			$channel['channel'] = 'google';
		} elseif ($ch_id == VikChannelManagerConfig::GOOGLEVR) {
			$channel['channel'] = 'google';
		}
		array_push($all_channels, ucwords($channel['channel']));
	}
	$tot_nodes = count($room['availability']);
	$nodes_count = 0;
	while ($nodes_count < $tot_nodes) {
		$nodes_portion = ($this->max_nodes - $nodes_count_req) >= $tot_nodes ? $tot_nodes : ($this->max_nodes - $nodes_count_req);
		$nodes_arr = array();
		$nodes_verb_arr = array();
		for ($i=$nodes_count; $i < ($nodes_portion + $nodes_count); $i++) { 
			if ($i >= $tot_nodes) {
				break;
			}
			$nodes_arr[] = $room['availability'][$i];
			$node_av_parts = explode('_', $room['availability'][$i]);
			$nodes_verb_arr[] = $node_av_parts[0].' - '.$node_av_parts[1].' --&gt; '.$node_av_parts[2];
		}
		$first_node = $nodes_arr[0];
		$last_node = $nodes_arr[(count($nodes_arr) - 1)];
		$first_node_parts = explode('_', $first_node);
		$last_node_parts = explode('_', $last_node);
		?>
		<div class="vcm-avpush-request-node" data-roomid="<?php echo $room['id']; ?>" data-roomname="<?php echo $room['name']; ?>" data-channels="<?php echo implode(',', array_keys($room['channels'])); ?>">
			<div class="vcm-avpush-data-room"><?php echo $room['name']; ?></div>
			<div class="vcm-avpush-data-fromto"><?php echo str_replace('_', ' - ', $room['from_to']); ?></div>
			<div class="vcm-avpush-data-channels"><?php echo implode(', ', $all_channels); ?></div>
			<div class="vcm-avpush-data-fromtoreq"><span class="vcm-avpush-data-fromtoreq-totnodes"><?php echo JText::_('VCMAVPUSHTOTNODES'); ?> <?php echo count($nodes_arr); ?></span><span class="vcm-avpush-data-fromtoreq-dates"><?php echo $first_node_parts[0].' - '.$last_node_parts[1]; ?></span></div>
			<div class="vcm-avpush-data-nodesdetails">
			<?php
			foreach ($nodes_verb_arr as $node_verb) {
				?>
				<span><?php echo $node_verb; ?></span>
				<?php
			}
			?>
			</div>
			<input type="hidden" class="vcm-avpush-data-nodes<?php echo $room['id']; ?>" value="<?php echo implode(';', $nodes_arr); ?>" />
		</div>
		<?php
		if (($nodes_count_req + count($nodes_arr)) >= $this->max_nodes) {
			$upd_req_count++;
			echo '<br clear="all" /></div>'."\n".'<div class="vcm-avpush-request">'."\n".'<h4>'.JText::sprintf('VCMAVPUSHREQNUMB', $upd_req_count).'</h4>'."\n";
			$nodes_count_req = 0;
		} else {
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
	<input type="hidden" name="task" value="avpushsubmit" />
</form>

<script type="text/javascript">
	var req_count = 0;
	var req_length = 1;
	var exec_delay = 10000;
	var req_finalizable = false;
	var vcmExecutionListener;

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

	function vcmCheckRequestExecution() {
		if (req_count > req_length) {
			// complete requests
			jQuery('.vcm-avpush-data-fromtoreq').trigger('click');
			jQuery('.vcm-avpush-info-result').fadeIn();
			if (req_finalizable !== true) {
				jQuery('.vcm-avpush-info-result p').hide();
			}

			// clear loading and interval
			vcmStopLoading();
			clearInterval(vcmExecutionListener);
			vcmExecutionListener = 0;

			// finalize bulk action
			vcmDoAjax(
				"index.php",
				{
					option: "com_vikchannelmanager",
					task: "exec_avpush_finalize",
					res: (req_finalizable === true ? "1" : "0"),
					tmpl: "component"
				}
			);

			if (typeof(vcmRetrieveNotifications) == "function") {
				if (typeof(vcmStopNotifications) !== "undefined") {
					vcmStopNotifications = false;
				}
				setTimeout(function() {
					vcmRetrieveNotifications();
				}, 2000);
			}
		} else {
			jQuery('.vcm-loading-progress-text').text('('+req_count+'/'+req_length+')');
		}
	}

	function vcmDoRequestSubmit(k, rooms_req, channels_req, nodes_req, req_elem) {

		req_elem.fadeIn();

		// make the request using the retry system in case of connection errors
		vcmDoAjax(
			"index.php",
			{
				option: "com_vikchannelmanager",
				task: "exec_avpush",
				r: (k + 1) + "_" + req_length,
				rooms: rooms_req,
				channels: channels_req,
				nodes: nodes_req,
				<?php echo isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1 ? 'e4j_debug: 1,' : ''; ?>
				tmpl: "component"
			},
			function(res) {
				if (res.substr(0, 9) == 'e4j.error') {
					req_elem.addClass("vcm-avpush-request-error");
					req_elem.append("<pre class='vcmpreerror'>" + res.replace("e4j.error.", "") + "</pre>");
				} else {
					req_finalizable = true;
					req_elem.addClass("vcm-avpush-request-success");
					req_elem.append("<div class='vcm-avpush-request-esitnode'>" + res.replace("e4j.OK.", "") + "</div>");
				}
				req_count++;
			},
			function(rq_err) {
				req_elem.addClass("vcm-avpush-request-error");
				alert("Error Performing Ajax Request #" + req_count);
				req_count++;
			}
		);
		
		setTimeout(function() {
			req_elem.insertAfter(jQuery('.vcm-avpush-request').last());
		}, ((k + 1) == req_length ? 1000 : (exec_delay - 1000)) );
		
	}

	function vcmProcessRequests() {

		req_count = 1;
		req_length = jQuery('.vcm-avpush-request').length;
		vcmExecutionListener = setInterval(vcmCheckRequestExecution, 1500);

		jQuery('.vcm-avpush-request').each(function(k, v) {
			var req_elem = jQuery(this);
			var req_delay = exec_delay + (exec_delay * k);

			var rooms_req = new Array;
			var channels_req = new Array;
			var nodes_req = new Array;
			req_elem.find('.vcm-avpush-request-node').each(function(nodek, nodev) {
				var roomid = jQuery(this).attr('data-roomid');
				rooms_req.push(roomid);
				channels_req.push(jQuery(this).attr('data-channels'));
				nodes_req.push(jQuery(this).find('.vcm-avpush-data-nodes'+roomid).val());
			});

			setTimeout(function() {
				vcmDoRequestSubmit(k, rooms_req, channels_req, nodes_req, req_elem);
			}, req_delay);
			
		});

	}

	function vcmSendRequests() {
		
		vcmShowLoading();
		
		vcmDoAjax(
			"index.php",
			{
				option: "com_vikchannelmanager",
				task: "exec_avpush_prepare",
				tmpl: "component"
			}
		);

		setTimeout(function() {
			vcmProcessRequests();
		}, 2500);

		jQuery('.vcm-avpush-request').first().fadeIn();

		if (typeof(vcmStopNotifications) !== "undefined") {
			vcmStopNotifications = true;
		}

	}

	jQuery(document).ready(function() {
		jQuery('.vcm-avpush-request').each(function(k, v) {
			if (!jQuery(v).find('.vcm-avpush-request-node').length) {
				jQuery(this).remove();
			}
		});
		jQuery('.vcm-avpush-data-fromtoreq').click(function() {
			jQuery(this).next('.vcm-avpush-data-nodesdetails').slideToggle();
		});
		vcmSendRequests();
	});
</script>

<?php
if (isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1) {
	echo '<br clear="all"/><pre>'.print_r($this->rows, true).'</pre>';
}
?>
