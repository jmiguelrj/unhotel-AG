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
 * Usage Example
 * 
 * date_default_timezone_set('UTC');
 * $x = new VCMFestivities;
 * $x->translate = false;
 * $y = $x->loadFestivities();
 * foreach ($y as $key => $value) {
 * 	echo "$key: ".date('Y-m-d H:i:s', $value['from_ts'])." - ".date('Y-m-d H:i:s', $value['to_ts'])." (original next date: {$value['wday']}, ".date('Y-m-d', $value['next_ts']).")\n";
 * }
 */

/**
 * This class is used by the Smart Balancer to get
 * information about the next festivities for a
 * certain region, to help set up a rule to automatically
 * adjust the rates depending on the remaining availability.
 * Since VCM 1.6.13 and VBO 1.12.0 (1.2.0 for WP) this class
 * is also used by Vik Booking to store festivities information.
 */
class VCMFestivities
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var VCMFestivities
	 */
	protected static $instance = null;

	/**
	 * The (current) timestamp from which the class
	 * should calculate the closest festivities.
	 *
	 * @var int
	 */
	protected $now;

	/**
	 * Whether the class should translate the names
	 * of the festivities.
	 *
	 * @var boolean
	 */
	public $translate = true;

	/**
	 * The list of all the supported regions for the festivities.
	 *
	 * @var array
	 */
	protected $regions = array(
		'global' 	=> 'GLOBAL',
		'ita' 		=> 'ITA',
		'fra' 		=> 'FRA',
		'ger' 		=> 'GER',
		'spa' 		=> 'SPA',
		'usa' 		=> 'USA'
	);

	/**
	 * The list of all the supported festivities.
	 *
	 * @var array
	 */
	protected $festivities = array(
		'newYearsDay' 	=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 1,
			'mday' 		=> 1
		),
		'epiphany' 		=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 1,
			'mday' 		=> 6
		),
		'mlkDay' 		=> array(
			'regions' 	=> array('usa'),
			'func' 		=> 'getMLKDate' //3rd monday of January
		),
		'valentinesDay'	=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 2,
			'mday' 		=> 14
		),
		'presidentsDay'	=> array(
			'regions' 	=> array('usa'),
			'func' 		=> 'getPresidentsDate' //3rd monday of February
		),
		'rosenmontag' 	=> array(
			'regions' 	=> array('ger'),
			'func' 		=> 'getRosenmontagDate' //monday before Ash Wednesday (7th week before Easter)
		),
		'mardiGras' 	=> array(
			'regions' 	=> array('global'),
			'func' 		=> 'getMardigrasDate' //tuesday before Ash Wednesday (7th week before Easter)
		),
		'easter' 		=> array(
			'regions' 	=> array('global'),
			'func' 		=> 'getEasterDate'
		),
		'endofwarita'	=> array(
			'regions' 	=> array('ita'),
			'mon' 		=> 4,
			'mday' 		=> 25
		),
		'walpurgisNight'=> array(
			'regions' 	=> array('ger'),
			'mon' 		=> 4,
			'mday' 		=> 30
		),
		'dayOfWork'		=> array(
			'regions' 	=> array('ita', 'fra', 'ger'),
			'mon' 		=> 5,
			'mday' 		=> 1
		),
		'cincomayo'		=> array(
			'regions' 	=> array('usa'),
			'mon' 		=> 5,
			'mday' 		=> 5
		),
		'vedayfra'		=> array(
			'regions' 	=> array('fra'),
			'mon' 		=> 5,
			'mday' 		=> 8
		),
		'memorialDay'	=> array(
			'regions' 	=> array('usa'),
			'func' 		=> 'getMemorialDate' //last monday of May
		),
		'republicDay'	=> array(
			'regions' 	=> array('ita'),
			'mon' 		=> 6,
			'mday' 		=> 2
		),
		'4thJuly'		=> array(
			'regions' 	=> array('usa'),
			'mon' 		=> 7,
			'mday' 		=> 4
		),
		'bastilleDay'	=> array(
			'regions' 	=> array('fra'),
			'mon' 		=> 7,
			'mday' 		=> 14
		),
		'ferragosto'	=> array(
			'regions' 	=> array('ita'),
			'mon' 		=> 8,
			'mday' 		=> 15
		),
		'laborDay'	=> array(
			'regions' 	=> array('usa'),
			'func' 		=> 'getLaborDate' //1st Monday of September
		),
		'columbusDay'	=> array(
			'regions' 	=> array('usa'),
			'func' 		=> 'getColumbusDate' //2nd Monday of October
		),
		'hispanityDay'	=> array(
			'regions' 	=> array('spa'),
			'mon' 		=> 10,
			'mday' 		=> 12,
		),
		'halloween'	=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 10,
			'mday' 		=> 31,
			'bridge' 	=> array('saintsDay', 'soulsDay')
		),
		'saintsDay'	=> array(
			'regions' 	=> array('ita'),
			'mon' 		=> 11,
			'mday' 		=> 1,
			'bridge' 	=> array('soulsDay')
		),
		'soulsDay'	=> array(
			'regions' 	=> array('ita'),
			'mon' 		=> 11,
			'mday' 		=> 2
		),
		'wallOfBerlin'	=> array(
			'regions' 	=> array('ger'),
			'mon' 		=> 11,
			'mday' 		=> 9
		),
		'armisticeDay'	=> array(
			'regions' 	=> array('fra'),
			'mon' 		=> 11,
			'mday' 		=> 11
		),
		'veteransDay'	=> array(
			'regions' 	=> array('usa'),
			'mon' 		=> 11,
			'mday' 		=> 11
		),
		'thanksgiving'	=> array(
			'regions' 	=> array('usa'),
			'func' 		=> 'getThanksgivingDate' //4th Thursday of November
		),
		'immacolata'	=> array(
			'regions' 	=> array('ita', 'spa'),
			'mon' 		=> 12,
			'mday' 		=> 8
		),
		'christmasEve'	=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 12,
			'mday' 		=> 24,
			'bridge' 	=> array('christmasDay', 'stStephensDay', 'newYearsEve', 'newYearsDay')
		),
		'christmasDay'	=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 12,
			'mday' 		=> 25,
			'bridge' 	=> array('stStephensDay', 'newYearsEve', 'newYearsDay')
		),
		'stStephensDay' => array(
			'regions' 	=> array('global'),
			'mon' 		=> 12,
			'mday' 		=> 26,
			'bridge' 	=> array('newYearsEve', 'newYearsDay')
		),
		'newYearsEve'	=> array(
			'regions' 	=> array('global'),
			'mon' 		=> 12,
			'mday' 		=> 31,
			'bridge' 	=> array('newYearsDay')
		),
	);

	/**
	 * Class constructor is still public, even though
	 * it is possible to access the Singletone instance
	 * of the class through the method getInstance().
	 *
	 * @see 	getInstance()
	 * @since 	1.6.13
	 */
	public function __construct()
	{
		$this->now = time();
	}

	/**
	 * Returns the global class object, either
	 * a new instance or the existing instance
	 * if the class was already instantiated.
	 * This method was introduced in the v 1.6.13 for
	 * VBO to check whether the class can be extended.
	 * It used to have private vars and methods that
	 * have now been changed to protected.
	 *
	 * @return 	self 	A new instance of the class.
	 *
	 * @since 	1.6.13
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Method to set the current timestamp from which
	 * the class should find the closest festivities.
	 *
	 * @param 	int  		$from_ts
	 *
	 * @return 	self
	 */
	public function setFromTimestamp($from_ts)
	{
		if (!empty($from_ts)) {
			$this->now = $from_ts;
		}

		return $this;
	}

	/**
	 * Returns all the supported regions with
	 * the corresponding translated names.
	 *
	 * @return 	array
	 */
	public function getTranslatedRegions()
	{
		$trans_regions = $this->regions;
		foreach ($trans_regions as $k => $v) {
			$trans_regions[$k] = $this->translate ? JText::_('VCMFEST'.strtoupper($v)) : $k;
		}

		return $trans_regions;
	}

	/**
	 * Main method to get a list of the closest festivities
	 * for the given region, starting from the current timestamp.
	 *
	 * @param 	string  		[$region]
	 *
	 * @return 	array
	 */
	public function loadFestivities($region = 'global')
	{
		return $this->calculateFestivititesDates(
			$this->getFestivitiesByRegion(strtolower($region))
		);
	}

	/**
	 * Method that filters the festivities by the
	 * requested region or the global ones.
	 *
	 * @param 	string  		$region
	 *
	 * @return 	array
	 */
	protected function getFestivitiesByRegion($region)
	{
		$fests = array();
		foreach ($this->festivities as $k => $v) {
			if ($v['regions'][0] == 'global' || in_array($region, $v['regions'])) {
				$fests[$k] = $v;
			}
		}

		return $fests;
	}

	/**
	 * Method that parses all the region-filtered festivities to
	 * calculate the next timestamp for each fest and the end date,
	 * by setting the properties 'from_ts' and 'to_ts' for each fest.
	 * Ex. If a holiday is on Monday, the method sets the dates to Sat-Mon.
	 * Ex. If a holiday is on Thursday, the method sets the dates to Thu-Sat.
	 * Ex. If a holiday is on Friday, the method sets the dates to Fri-Sat.
	 * The method returns a sorted and filtered array of key-value pairs with
	 * some new properties: 'next_ts', 'from_ts', 'to_ts', 'wday', 'trans_name'.
	 *
	 * @param 	array  		$regions_festivities (global festivities + region's festivities)
	 *
	 * @return 	array
	 */
	protected function calculateFestivititesDates($regions_festivities)
	{
		$fests = array();
		$info_from = getdate($this->now);

		//calculate the next timestamp from today of each festivity ('from_ts')
		foreach ($regions_festivities as $k => $v) {
			if (isset($v['func'])) {
				//custom function
				if (!method_exists($this, $v['func']) || !is_callable(array($this, $v['func']))) {
					continue;
				}
				$next_ts = $this->{$v['func']}();
			} else {
				//fixed month and month-day
				$next_ts = mktime(0, 0, 0, $v['mon'], $v['mday'], $info_from['year']);
				if ($next_ts < $this->now) {
					$next_ts = mktime(0, 0, 0, $v['mon'], $v['mday'], ($info_from['year'] + 1));
				}
			}
			if (empty($next_ts)) {
				continue;
			}
			$info_next = getdate($next_ts);
			$v['wday'] = $info_next['wday'];
			$v['from_ts'] = $v['next_ts'] = $next_ts;
			$regions_festivities[$k] = $v;
			$fests[$k] = $v;
		}
		//

		//calculate the end date for each festivity by considering weekends and bridges ('to_ts', and 'from_ts', if necessary)
		foreach ($fests as $k => $v) {
			$info_date = getdate($v['from_ts']);
			if ((int)$info_date['wday'] < 2) {
				//Festivity is on a Sunday or a Monday, switch 'from_ts' to the Saturday before
				$fests[$k]['from_ts'] = mktime(0, 0, 0, $info_date['mon'], ($info_date['mday'] - ((int)$info_date['wday'] + 1)), $info_date['year']);
			}
			if (isset($v['bridge']) && count($v['bridge'])) {
				//Check next bridges of this festivity
				$bridges_ts = array();
				foreach ($v['bridge'] as $fest_key) {
					if (isset($fests[$fest_key]) && isset($fests[$fest_key]['from_ts'])) {
						$bridges_ts[] = $regions_festivities[$fest_key]['from_ts'];
					}
				}
				if (count($bridges_ts)) {
					//bridges found: set the 'to_ts' to the last festivity of the bridge and continue
					$info_max = getdate(max($bridges_ts));
					$fests[$k]['to_ts'] = mktime(23, 59, 59, $info_max['mon'], $info_max['mday'], $info_max['year']);
					continue;
				}
			}
			if ((int)$info_date['wday'] == 4) {
				//Festivity is on a Thursday, set 'to_ts' to the Saturday after
				$fests[$k]['to_ts'] = mktime(0, 0, 0, $info_date['mon'], ($info_date['mday'] + 2), $info_date['year']);
				continue;
			}
			if ((int)$info_date['wday'] == 5) {
				//Festivity is on a Friday, set 'to_ts' to the Saturday after
				$fests[$k]['to_ts'] = mktime(0, 0, 0, $info_date['mon'], ($info_date['mday'] + 1), $info_date['year']);
				continue;
			}
			//no bridges, set 'to_ts' to the end of the same day at 23:59:59
			$fests[$k]['to_ts'] = mktime(23, 59, 59, $info_date['mon'], $info_date['mday'], $info_date['year']);
		}
		//

		//sorting and translation
		$sort_map = array();
		foreach ($fests as $k => $v) {
			$sort_map[$k] = $v['next_ts'];
			$fests[$k]['trans_name'] = $this->translate ? JText::_('VCMFEST'.strtoupper($k)) : $k;
		}
		asort($sort_map);
		$sorted_fests = array();
		foreach ($sort_map as $k => $v) {
			$sorted_fests[$k] = $fests[$k];
		}
		$fests = $sorted_fests;

		return $fests;
	}

	/**
	 * Custom method that returns the next Easter ts.
	 * We use easter_days() to get the number of days where Easter
	 * falls after March 21st of the given year.
	 *
	 * @return 	int
	 */
	public function getEasterDate()
	{
		$next_ts = mktime(0, 0, 0, 3, (21 + easter_days(date('Y', $this->now))), date('Y', $this->now));
		if ($next_ts < $this->now) {
			$next_ts = mktime(0, 0, 0, 3, (21 + easter_days(((int)date('Y', $this->now) + 1))), ((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the Rosenmontag ts.
	 * (Monday before the Ash Wednesday, 7th week before Easter)
	 *
	 * @return 	int
	 */
	public function getRosenmontagDate()
	{
		$eightw_easter = getdate(strtotime('-7 weeks', mktime(0, 0, 0, 3, (21 + easter_days(date('Y', $this->now))), date('Y', $this->now))));
		$next_ts = mktime(0, 0, 0, $eightw_easter['mon'], ($eightw_easter['mday'] + 1), $eightw_easter['year']);
		if ($next_ts < $this->now) {
			$eightw_easter = getdate(strtotime('-7 weeks', mktime(0, 0, 0, 3, (21 + easter_days(((int)date('Y', $this->now) + 1))), ((int)date('Y', $this->now) + 1))));
			$next_ts = mktime(0, 0, 0, $eightw_easter['mon'], ($eightw_easter['mday'] + 1), $eightw_easter['year']);
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the Mardi Gras ts.
	 * (Tuesday before the Ash Wednesday, 7th week before Easter)
	 *
	 * @return 	int
	 */
	public function getMardigrasDate()
	{
		$eightw_easter = getdate(strtotime('-7 weeks', mktime(0, 0, 0, 3, (21 + easter_days(date('Y', $this->now))), date('Y', $this->now))));
		$next_ts = mktime(0, 0, 0, $eightw_easter['mon'], ($eightw_easter['mday'] + 2), $eightw_easter['year']);
		if ($next_ts < $this->now) {
			$eightw_easter = getdate(strtotime('-7 weeks', mktime(0, 0, 0, 3, (21 + easter_days(((int)date('Y', $this->now) + 1))), ((int)date('Y', $this->now) + 1))));
			$next_ts = mktime(0, 0, 0, $eightw_easter['mon'], ($eightw_easter['mday'] + 2), $eightw_easter['year']);
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the next ts for the
	 * Martin Luther King's Day (3rd Monday of January).
	 *
	 * @return 	int
	 */
	public function getMLKDate()
	{
		$next_ts = strtotime('third Monday of January '.date('Y', $this->now));
		if (!empty($next_ts) && $next_ts < $this->now) {
			$next_ts = strtotime('third Monday of January '.((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the next ts for the
	 * President's Day (3rd Monday of February).
	 *
	 * @return 	int
	 */
	public function getPresidentsDate()
	{
		$next_ts = strtotime('third Monday of February '.date('Y', $this->now));
		if (!empty($next_ts) && $next_ts < $this->now) {
			$next_ts = strtotime('third Monday of February '.((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the next ts for the
	 * Memorial's Day (last Monday of May).
	 *
	 * @return 	int
	 */
	public function getMemorialDate()
	{
		$next_ts = strtotime('last Monday of May '.date('Y', $this->now));
		if (!empty($next_ts) && $next_ts < $this->now) {
			$next_ts = strtotime('last Monday of May '.((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the next ts for the
	 * Labor's Day (first Monday of September).
	 *
	 * @return 	int
	 */
	public function getLaborDate()
	{
		$next_ts = strtotime('first Monday of September '.date('Y', $this->now));
		if (!empty($next_ts) && $next_ts < $this->now) {
			$next_ts = strtotime('first Monday of September '.((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the next ts for the
	 * Columbus's Day (second Monday of October).
	 *
	 * @return 	int
	 */
	public function getColumbusDate()
	{
		$next_ts = strtotime('second Monday of October '.date('Y', $this->now));
		if (!empty($next_ts) && $next_ts < $this->now) {
			$next_ts = strtotime('second Monday of October '.((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}

	/**
	 * Custom method that returns the next ts for the
	 * Thanksgiving Day (4th Thursday of November).
	 *
	 * @return 	int
	 */
	public function getThanksgivingDate()
	{
		$next_ts = strtotime('fourth Thursday of November '.date('Y', $this->now));
		if (!empty($next_ts) && $next_ts < $this->now) {
			$next_ts = strtotime('fourth Thursday of November '.((int)date('Y', $this->now) + 1));
		}

		return $next_ts;
	}
}
