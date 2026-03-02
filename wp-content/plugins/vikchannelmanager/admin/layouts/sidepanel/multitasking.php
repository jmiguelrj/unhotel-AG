<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Obtain vars from arguments received in the layout file.
 * This layout file should be called once at most per page.
 * 
 * @var    string  $btn_trigger  The CSS selector of the button that opens the panel.
 * 
 * @since  1.9
 */
extract($displayData);

$app = JFactory::getApplication();

// get the current page and root URIs
$vcm_page     = $app->input->getString('view', $app->input->getString('task'));
$vcm_page_uri = htmlspecialchars((string) JUri::getInstance(), ENT_QUOTES);
$root_uri 	  = htmlspecialchars(JUri::root(), ENT_QUOTES);

if (!method_exists('VikBooking', 'getAdminWidgetsInstance')) {
	// outdated VBO, abort
	return;
}

// we allow to sort the admin widgets
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.sortable.min.js');

// get admin widgets helper
$widgets_helper = VikBooking::getAdminWidgetsInstance();

// get all widgets by preloading their assets (if any)
$admin_widgets = $widgets_helper->getWidgetNames($preload = true);

// theme color preferences
$color_scheme = VikChannelManager::getAppearancePref();
$scheme_name  = JText::_('VCM_APPEARANCE_PREF_AUTO');
$current_mode = 'magic';
if ($color_scheme == 'light') {
	$scheme_name = JText::_('VCM_APPEARANCE_PREF_LIGHT');
	$current_mode = 'sun';
} elseif ($color_scheme == 'dark') {
	$scheme_name = JText::_('VCM_APPEARANCE_PREF_DARK');
	$current_mode = 'moon';
}

// JS lang vars
JText::script('VCM_ADMIN_WIDGET');
JText::script('VCM_APPEARANCE_PREF_AUTO');
JText::script('VCM_APPEARANCE_PREF_LIGHT');
JText::script('VCM_APPEARANCE_PREF_DARK');

?>

<div class="vcm-sidepanel-wrapper vcm-sidepanel-right vcm-sidepanel-close">

	<div class="vcm-sidepanel-container">

		<div class="vcm-sidepanel-layouts">
			<div class="vcm-sidepanel-dismiss">
				<span class="vcm-sidepanel-dismiss-btn"><?php VikBookingIcons::e('times'); ?></span>
			</div>
			<div class="vcm-sidepanel-colorscheme">
				<span class="vcm-tooltip vcm-tooltip-bottom vcm-sidepanel-colorscheme-current" data-tooltiptext="<?php echo JHtml::_('esc_attr', $scheme_name); ?>"><?php VikBookingIcons::e($current_mode); ?></span>
				<div class="vcm-sidepanel-colorscheme-list">
					<div class="vcm-sidepanel-colorscheme-option<?php echo $color_scheme == 'auto' ? ' vcm-sidepanel-colorscheme-option-active' : ''; ?>" data-scheme="auto">
						<span><?php VikBookingIcons::e('magic'); ?> <?php echo JText::_('VCM_APPEARANCE_PREF_AUTO'); ?></span>
					</div>
					<div class="vcm-sidepanel-colorscheme-option<?php echo $color_scheme == 'light' ? ' vcm-sidepanel-colorscheme-option-active' : ''; ?>" data-scheme="light">
						<span><?php VikBookingIcons::e('sun'); ?> <?php echo JText::_('VCM_APPEARANCE_PREF_LIGHT'); ?></span>
					</div>
					<div class="vcm-sidepanel-colorscheme-option<?php echo $color_scheme == 'dark' ? ' vcm-sidepanel-colorscheme-option-active' : ''; ?>" data-scheme="dark">
						<span><?php VikBookingIcons::e('moon'); ?> <?php echo JText::_('VCM_APPEARANCE_PREF_DARK'); ?></span>
					</div>
				</div>
			</div>
		</div>

		<div class="vcm-sidepanel-body-top">

			<div class="vcm-sidepanel-search">
				<?php VikBookingIcons::e('search', 'vcm-sidepanel-search-input-icn'); ?>
				<input id="vcm-sidepanel-search-input" type="text" placeholder="<?php echo htmlspecialchars(JText::_('VBO_SEARCH_ADMIN_WIDGETS')); ?>" value="" autocomplete="off" />
			</div>

			<div class="vcm-sidepanel-add-widgets">
			<?php
			foreach ($admin_widgets as $k => $admin_widget) {
				/**
				 * Add widget container must be focusable with "tabindex=-1" so that via JS we will
				 * have the "relatedTarget" event property set to this element when blurring on search field.
				 */
				?>
				<div class="vcm-sidepanel-add-widget" data-vbowidgetid="<?php echo $admin_widget->id; ?>" tabindex="-1">
					<div class="vcm-sidepanel-widget-info">
						<div class="vcm-sidepanel-widget-info-det">
							<span class="vcm-sidepanel-widget-icn vcm-admin-widget-style-<?php echo $admin_widget->style; ?>"><?php echo $admin_widget->icon; ?></span>
							<span class="vcm-sidepanel-widget-name"><?php echo $admin_widget->name; ?></span>
						</div>
						<div class="vcm-sidepanel-widget-add">
							<span class="vcm-widget-render-modal"><?php echo VikBookingIcons::e('far fa-window-restore'); ?></span>
						</div>
					</div>
					<div class="vcm-sidepanel-widget-tags" style="display: none;"><?php echo strtolower($admin_widget->name . ' ' . $admin_widget->descr); ?></div>
				</div>
				<?php
			}
			?>
				<div class="vcm-sidepanel-add-widgets-nores" style="display: none;">
					<span><?php echo JText::_('VCM_NO_RESULTS'); ?></span>
				</div>
			</div>

		</div>

		<div class="vcm-sidepanel-shortcut">
				<div class="shortcut-keys">
				<span class="mod"></span>
				<span class="key">⏎</span>
			</div>
			<div class="shortcut-desc">
				<?php echo JText::_('VBO_KEYBOARD_SHORTCUT'); ?>
			</div>
			<div class="shortcut-subdesc"></div>
		</div>

	</div>

</div>

<script type="text/javascript">
	jQuery(function() {

		// ensure VBO is updated
		if (typeof VBOCore.vcmMultitasking === 'undefined') {
			throw new Error('VikBooking must be updated');
		}

		// inject core properties
		VBOCore.setOptions({
			is_vbo: 			false,
			is_vcm: 			true,
			cms: 				"<?php echo VBOPlatformDetection::isWordPress() ? 'wordpress' : 'joomla'; ?>",
			widget_ajax_uri:    "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=exec_admin_widget'); ?>",
			assets_ajax_uri: 	"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=widgets_get_assets'); ?>",
			multitask_ajax_uri: "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=exec_multitask_widgets'); ?>",
			watchdata_ajax_uri: "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=widgets_watch_data'); ?>",
			current_page: 	    "<?php echo $vcm_page; ?>",
			current_page_uri:   "<?php echo $vcm_page_uri; ?>",
			root_uri:   		"<?php echo $root_uri; ?>",
			tn_texts: 			{
				admin_widget: Joomla.JText._('VCM_ADMIN_WIDGET'),
			},
			default_loading_body: '<?php VikBookingIcons::e('circle-notch', 'fa-spin fa-fw'); ?>',
		});

		// initialize multitasking events
		VBOCore.prepareMultitasking({
			selector: 		 ".vcm-sidepanel-wrapper",
			open_class: 	 "vcm-sidepanel-open",
			close_class: 	 "vcm-sidepanel-close",
			sclass_l_small:  "vcm-sidepanel-right",
			sclass_l_large:  "vcm-sidepanel-large",
			btn_trigger: 	 "<?php echo $btn_trigger; ?>",
			search_selector: "#vcm-sidepanel-search-input",
			search_nores: 	 ".vcm-sidepanel-add-widgets-nores",
			close_selector:  ".vcm-sidepanel-dismiss-btn",
			t_layout_small:	 ".vcm-sidepanel-layout-small",
			t_layout_large:  ".vcm-sidepanel-layout-large",
			wclass_base_sel: ".vcm-admin-widgets-widget-output",
			wclass_l_small:  "vcm-admin-widgets-container-small",
			wclass_l_large:  "vcm-admin-widgets-container-large",
			addws_selector:	 ".vcm-sidepanel-add-widgets",
			addw_selector:	 ".vcm-sidepanel-add-widget",
			addw_modal_cls:	 "vcm-widget-render-modal",
			addwfs_selector: ".vcm-sidepanel-add-widget-focussed",
			wtags_selector:	 ".vcm-sidepanel-widget-tags",
			wname_selector:	 ".vcm-sidepanel-widget-name",
			addw_data_attr:  "data-vbowidgetid",
			actws_selector:  ".vcm-sidepanel-active-widgets",
			editw_selector:  ".vcm-sidepanel-edit-widgets-trig",
			editmode_class:  "vcm-admin-widgets-widget-editing",
			rmwidget_class:  "vcm-admin-widgets-widget-remove",
			rmwidget_icn:  	 '<?php VikBookingIcons::e('times'); ?>',
			dtcwidget_class: "vcm-admin-widgets-widget-detach",
			dtctarget_class: "vcm-admin-widget-head",
			dtcwidget_icn: 	 '<?php VikBookingIcons::e('far fa-window-restore'); ?>',
			notif_selector:  ".vcm-sidepanel-notifications-btn",
			notif_on_class:  "vcm-sidepanel-notifications-on",
			notif_off_class: "vcm-sidepanel-notifications-off",
		});

		// color scheme preferences
		jQuery('.vcm-sidepanel-colorscheme-current').on('click', function() {
			jQuery('.vcm-sidepanel-colorscheme-list').toggleClass('vcm-sidepanel-colorscheme-list-show');
		});

		// color scheme selection
		jQuery('.vcm-sidepanel-colorscheme-option').on('click', function() {
			let set_mode = jQuery(this).attr('data-scheme');

			var vcm_css_base_uri = '<?php echo VCM_ADMIN_URI . 'assets/css/vcm-appearance-%s.css'; ?>';
			var vcm_css_base_id  = 'vcm-css-appearance-';
			let vcm_css_modes 	 = {
				auto: vcm_css_base_uri.replace('%s', 'auto'),
				dark: vcm_css_base_uri.replace('%s', 'dark'),
				light: null,
			};
			let vcm_mode_texts = {
				auto: Joomla.JText._('VCM_APPEARANCE_PREF_AUTO'),
				dark: Joomla.JText._('VCM_APPEARANCE_PREF_DARK'),
				light: Joomla.JText._('VCM_APPEARANCE_PREF_LIGHT'),
			};
			let vcm_mode_icons = {
				auto: '<?php VikBookingIcons::e('magic') ?>',
				dark: '<?php VikBookingIcons::e('moon') ?>',
				light: '<?php VikBookingIcons::e('sun') ?>',
			};

			if (!vcm_css_modes.hasOwnProperty(set_mode)) {
				return false;
			}

			// toggle active class
			jQuery('.vcm-sidepanel-colorscheme-option').removeClass('vcm-sidepanel-colorscheme-option-active');
			jQuery(this).addClass('vcm-sidepanel-colorscheme-option-active');

			// adjust current preference content
			jQuery('.vcm-sidepanel-colorscheme-current')
				.attr('data-tooltiptext', vcm_mode_texts[set_mode])
				.html(vcm_mode_icons[set_mode]);

			// set/unset CSS files from DOM
			for (let app_mode in vcm_css_modes) {
				if (!vcm_css_modes.hasOwnProperty(app_mode) || !vcm_css_modes[app_mode]) {
					continue;
				}
				if (app_mode == set_mode) {
					// set this CSS file
					jQuery('head').append('<link rel="stylesheet" id="' + vcm_css_base_id + app_mode + '" href="' + vcm_css_modes[app_mode] + '" media="all">');
				} else {
					// unset this CSS file
					if (jQuery('link#' + vcm_css_base_id + app_mode).length) {
						jQuery('link#' + vcm_css_base_id + app_mode).remove();
					} else if (jQuery('link#' + vcm_css_base_id + app_mode + '-css').length) {
						// WP framework may add "-css" as suffix to the given ID
						jQuery('link#' + vcm_css_base_id + app_mode + '-css').remove();
					}
				}
			}

			// close menu-list
			jQuery('.vcm-sidepanel-colorscheme-list').removeClass('vcm-sidepanel-colorscheme-list-show');

			// silently update configuration value
			VBOCore.doAjax(
				"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=configuration.update'); ?>",
				{
					settings: {
						appearance_pref: set_mode,
					}
				},
				(success) => {
					// do nothing
				},
				(error) => {
					console.error(error);
				}
			);
		});

		// subscribe to the multitask-panel-close event to dismiss the color scheme selection menu
		document.addEventListener(VBOCore.multitask_close_event, function() {
			if (jQuery('.vcm-sidepanel-colorscheme-list-show').length) {
				jQuery('.vcm-sidepanel-colorscheme-list-show').removeClass('vcm-sidepanel-colorscheme-list-show');
			}
		});

		// dinamycally change the shortcut modifier depending on the OS
		const isMacOs = navigator.platform.toUpperCase().indexOf('MAC') === 0;
		jQuery('.vcm-sidepanel-shortcut .shortcut-keys .mod').text(isMacOs ? '⌘' : '⌃');
		jQuery('.vcm-sidepanel-shortcut .shortcut-subdesc').text(isMacOs ? '(CMD + ENTER)' : '(CTRL + ENTER)');
	});
</script>
