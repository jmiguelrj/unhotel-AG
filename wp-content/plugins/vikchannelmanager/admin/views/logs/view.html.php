<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * File logs list view.
 * 
 * @since 1.9.16
 */
class VikChannelManagerViewLogs extends VCMMvcView
{
	/**
	 * @inheritDoc
	 */
	function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$app = JFactory::getApplication();

		$rootPath = VBO_MEDIA_PATH . '/logs';

		$filters = [];
		$filters['file'] = $app->input->get('selectedfile', '', 'base64');

		$this->logData = null;

		// check whether a file was specified
		if ($filters['file']) {
			// decode file path
			$filters['path'] = base64_decode($filters['file']);

			$this->logData = $this->getLogData($filters['path']);
			$this->filesize = JHtml::_('number.bytes', filesize($filters['path']));
			$this->count = count($this->logData);

			/**
			 * JSON API to download new logs.
			 */
			if ($app->input->getBool('sync')) {
				$response = [
					'filesize' => $this->filesize,
					'count' => $this->count,
					'logs' => [],
				];

				$this->syncing = true;

				for ($i = $app->input->getUint('index', 0); $i < count($this->logData); $i++) {
					$this->loopLog = $this->logData[$i];
					$response['logs'][] = $this->loadTemplate('log');
				}

				VCMHttpDocument::getInstance($app)->json($response);
			}
		} else {
			$filters['path'] = false;
		}

		// load all existing logs
		$this->tree = $this->scanTree($rootPath);

		$this->filters = $filters;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Toolbar setup.
	 * 
	 * @return  void
	 */
	protected function addToolBar()
	{
		// Add menu title and some buttons to the page
		JToolBarHelper::title('VikChannelManager - Logs', 'vikchannelmanager');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_vikchannelmanager');
	}

	/**
	 * Scans all the existing log files.
	 * 
	 * @param   string  $folder  The source folder.
	 * 
	 * @return  array   The files tree.
	 */
	protected function scanTree(string $folder)
	{
		// scan all files under the specified folder
		$files = JFolder::files($folder, '.php$', true, true);

		if ($files === false) {
			return [];
		}

		$tree = [
			'logs' => [
				'name' => 'logs',
				'path' => $folder,
				'files' => [],
			],
		];

		foreach ($files as $file) {
			$rel = trim(str_replace($folder, '', $file), '\\/');
			$parts = explode(DIRECTORY_SEPARATOR, $rel);

			$node = &$tree['logs'];

			$subfolder = rtrim($folder, '\\/');

			while ($parts) {
				$part = array_shift($parts);
				
				if ($parts) {
					$subfolder .= '/' . $part;

					// still parts remaining, we didn't reach the bottom
					if (!isset($node['files'][$part])) {
						$node['files'][$part] = [
							'name' => $part,
							'path' => $subfolder,
							'files' => [],
						];
					}

					$node = &$node['files'][$part];
				} else {
					// we have reached the leaf
					$node['files'][] = [
						'path' => $file,
						'name' => basename($file, '.php'),
					];
				}
			}
		}

		return $tree;
	}

	/**
	 * Helper function used to build a navigator node.
	 *
	 * @param 	array   $node  The tree node.
	 *
	 * @return 	string  The HTML to display.
	 */
	protected function buildNode($node)
	{
		// in case the selected path contains the current node, mark the node as selected
		if ($this->filters['path'] && strpos($this->filters['path'], $node['path']) === 0) {
			$node['selected'] = true;
		} else {
			$node['selected'] = false;
		}

		// set up current node
		$this->currentNode = $node;

		if (isset($node['files'])) {
			// build folder link
			$html = $this->loadTemplate('folder');
		} else {
			// build file link
			$html = $this->loadTemplate('file');
		}

		// check whether the node is a leaf
		if (!empty($node['files'])) {
			// fetch node children visibility
			$style = $node['selected'] ? '' : ' style="display:none;"';

			// display folders first
			usort($node['files'], function($a, $b) {
				$isFolderA = isset($a['files']);
				$isFolderB = isset($b['files']);

				if ($isFolderA ^ $isFolderB) {
					return $isFolderA ? 0 : 1;
				}

				return strcasecmp($a['name'], $b['name']);
			});

			// create children list
			$html .= '<ul' . $style . '>';

			// iterate children
			foreach ($node['files'] as $file) {
				// create child node
				$html .= '<li>';
				// build child HTML
				$html .= $this->buildNode($file);
				// close child node
				$html .= '</li>';
			}

			// close children list
			$html .= '</ul>';
		}

		return $html;
	}

	/**
	 * Reads the log data from the specified file.
	 * 
	 * @param   string  $file
	 * 
	 * @return  object[]
	 */
	protected function getLogData(string $file)
	{
		$logs = [];

		$fp = fopen($file, 'r');

		if (!$fp) {
			throw new Exception('Unable to open file: ' . $file, 500);
		}

		while (!feof($fp)) {
			// read line by line
			$buffer = fgets($fp);

			// attempt to decode line
			$json = json_decode($buffer);

			if (is_object($json)) {
				$logs[] = $json;
			}
		}

		fclose($fp);

		return $logs;
	}
}
