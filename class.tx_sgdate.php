<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2002-2007 Stefan Geith (typo3devYYYY@geithware.de)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Plugin 'sg_div'.
 * @author	Stefan Geith <typo3devYYYY@geithware.de>
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

class tx_sgdate {
	private static $instance = NULL;

	private function __clone() {}

	protected $defaultDateFormat = 'd.m.Y';
	protected $defaultTimeFormat = 'H:i';
	protected $defaultTimeSecFormat = 'H:i:s';
	protected $defaultDateTimeFormat = 'd.m.Y H:i';
	protected $defaultDateTimeSecFormat = 'd.m.Y H:i:s';
	protected $useGmDate = 0;


	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new tx_sgdate();
			self::$instance->init();
		}
		return (self::$instance);
	}

	protected function init() {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sg_div']);
		if (trim($confArr['defaultDateFormat'])) {
			$this->defaultDateFormat = $confArr['defaultDateFormat'];
		}
		if (trim($confArr['defaultTimeFormat'])) {
			$this->defaultTimeFormat = $confArr['defaultTimeFormat'];
		}
		if (trim($confArr['defaultTimeSecFormat'])) {
			$this->defaultTimeSecFormat = $confArr['defaultTimeSecFormat'];
		}
		if (trim($confArr['defaultDateTimeFormat'])) {
			$this->defaultDateTimeFormat = $confArr['defaultDateTimeFormat'];
		}
		if (trim($confArr['defaultDateTimeSecFormat'])) {
			$this->defaultDateTimeSecFormat = $confArr['defaultDateTimeSecFormat'];
		}
		$this->useGmDate = (intval(10 * TYPO3_branch)<42) ? 1 : 0;
		if (strlen(trim($confArr['useGmDate']))) {
			$this->useGmDate = intval($confArr['useGmDate']);
		}

		// t3lib_div::debug(Array('inited'=>'tx_sgdate', '$defaultDateFormat'=>$this->defaultDateFormat, '$defaultTimeFormat'=>$this->defaultTimeFormat, '$defaultTimeSecFormat'=>$this->defaultTimeSecFormat, '$defaultDateTimeFormat'=>$this->defaultDateTimeFormat, '$defaultDateTimeSecFormat'=>$this->defaultDateTimeSecFormat, '$useGmDate'=>$this->useGmDate, 'File:Line'=>__FILE__.':'.__LINE__));
	}

	/********************************************************************************
	 *
	 * Time Functions
	 *
	 ********************************************************************************/

	function formatDate ($myTime=NULL,$myFormat=NULL) {
		if (!isset($myTime)) {
			$myTime = time();
		}
		if (!isset($myFormat)) {
			$myFormat = $this->defaultDateFormat;
		}
		return ($this->format($myTime,$myFormat,($myTime<86401 && $myTime>=0)));
	}

	function formatTime ($myTime=NULL,$myFormat=NULL) {
		if (!isset($myTime)) {
			$myTime = time();
		}
		if (!isset($myFormat)) {
			$myFormat = $this->defaultTimeFormat;
		}
		return ($this->format($myTime,$myFormat,($myTime<86401 && $myTime>=0)));
	}

	function formatTimeSec ($myTime=NULL,$myFormat=NULL) {
		if (!isset($myTime)) {
			$myTime = time();
		}
		if (!isset($myFormat)) {
			$myFormat = $this->defaultTimeSecFormat;
		}
		return ($this->format($myTime,$myFormat,($myTime<86401 && $myTime>=0)));
	}

	function formatDateTime ($myDateTime=NULL,$myFormat=NULL) {
		if (!isset($myDateTime)) {
			$myDateTime = Time();
		}
		if (!isset($myFormat)) {
			$myFormat = $this->defaultDateTimeFormat;
		}
		return ($this->format($myDateTime,$myFormat,($myDateTime<86401 && $myDateTime>=0)));
	}

	function formatDateTimeSec ($myDateTime=NULL,$myFormat=NULL) {
		if (!isset($myDateTime)) {
			$myDateTime = Time();
		}
		if (!isset($myFormat)) {
			$myFormat = $this->defaultDateTimeSecFormat;
		}
		return ($this->format($myDateTime,$myFormat,($myDateTime<86401 && $myDateTime>=0)));
	}


	protected function format ($myTime,$myFormat,$useGmDate) {
		if (is_numeric($myTime)) {
			if ($useGmDate) {
				return (gmdate($myFormat,$myTime));
			} else {
				return (date($myFormat,$myTime));
			}
		} else {
			return ($myTime);
		}
	}

	function formatBeDate($myTime,$myFormat,$timeOnly=FALSE) {
		if (intval(10*TYPO3_branch)<42 && !$timeOnly) {
			return (date ($myFormat,$myTime));
		} else {
			return (gmdate ($myFormat,$myTime));
		}	}

	function mkTimeBE($hour,$min,$sec,$month,$day,$year) {
		if ($month==0 && $day==0 && $year==0) {
			return (mktime ($hour,$min,$sec));
		//} else if (intval(10*TYPO3_branch)<41) {
		//	return (gmmktime ($hour,$min,$sec,$month,$day,$year));
		} else {
			return (mktime ($hour,$min,$sec,$month,$day,$year));
		}	
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sg_div/class.tx_sgdate.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sg_div/class.tx_sgdate.php']);
}
?>