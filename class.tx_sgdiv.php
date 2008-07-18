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

define ('TAB', "\t");
define ('CRLF', "\r\n");
define ('DQT', '"');
define ('QT', "'");
define ('BR', '<br />');

class tx_sgdiv {

	/********************************************************************************
	 *
	 * Text / String Functions
	 *
	 ********************************************************************************/

	/**
	 * Shorten given Text
	 *
	 * @param	[type]		$text: ...
	 * @param	[type]		$maxlen: ...
	 * @return	[type]		...
	 */
	function cropHtmlText($text,$maxlen)	{
		if (isset($maxlen)) {
			$n = intval($maxlen);
			if ($n>1 && strlen($text)>$n+1) {
				$text = htmlspecialchars(substr($text,0,$n)).'<font color=blue>[...'.(strlen($text)).']</font>';
			} else {
				$text = htmlspecialchars($text);
			}
		}
		return ($text);
	}

	/**
	 * Strip Quotes from Text: Leading/trailing Quotes and double-quotes
	 *
	 * @param	[string]		$string: Text to process
	 * @param	[boolean]		$inner: Do replace double quotes in string
	 * @param	[boolean]		$outer: Do replace embracing quotes, if available
	 * @return	[string]		changed $text
	 */
	function stripQuotes($string,$inner=true,$outer=true) {
		if ($inner) {
			$string = str_replace("''","'",str_replace('""','"',$string));
		}
		if ($outer) {
			$first = substr($string,0,1);
			if (($first==DQT || $first==QT) && $first==substr($string,-1)) {
				$string = substr($string,1,-1);
			}
		}
		return($string);
	}

	/**
	 * Replace CRs and LFs with <BR> (or given Text)
	 *
	 * @param	[string]		$text: Text where CR/LF will get replaced
	 * @param	[string]		$with: Replace by this; if Empty, use <br />
	 * @return	[string]		changes $text
	 */
	function myNl2br ($text,$with='') {
		if (strlen($with)<1) {
			$with = BR;
		}
		return (str_replace(Array(CRLF,"\n\r","\r","\n"),$with,$text));
	}




	/********************************************************************************
	 *
	 * Timer Functions
	 *
	 ********************************************************************************/

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getMicroTime() {
		list($usec, $sec) = explode(' ',microtime());
		return ((float)$usec + (float)$sec);
	}


	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getMicroSec() {
		list($usec, $sec) = explode(' ',microtime());
		return (((float)$usec + (float)$sec) * 1000);
	}


	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$t: ...
	 * @return	[type]		...
	 */
	function getMicroDur($t) {
		list($usec, $sec) = explode(' ',microtime());
		return (((float)$usec + (float)$sec)) * 1000  - (float)$t;
	}


	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$t: ...
	 * @return	[type]		...
	 */
	function make_seed() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}




	/********************************************************************************
	 *
	 * Path Functions
	 *
	 ********************************************************************************/

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: filename
	 * @param	[type]		$relativePath: if empty, $name is treated a absolute; else relativePath to PATH_site is assumed. (e.g. Uploadfolder)
	 * @return	[type]		true if exists
	 */

	function filesExists($name,$relativePath) {
		if ($name) {
			if ($relativePath) {
				$myPath = str_replace('//','/',PATH_site.'/'.$relativePath.'/'.$name);
				return(file_exists($myPath));
			} else {
				return(file_exists($name));
			}
		} else {
			return (false);
		}
	}



	/********************************************************************************
	 *
	 * TCA Functions
	 *
	 ********************************************************************************/

	/**
	 * Loads TCA additions of other extensions
	 *
	 * Your extension may depend on fields that are added by other
	 * extensions. For reasons of performance parts of the TCA are only
	 * loaded on demand. To ensure that the extended TCA is loaded for
	 * the extensions yours depends you can apply this function.
	 *
	 * @param       array  extension keys which have TCA additions to load
	 * @return      void
	 * @author      Franz Holzinger
	 */
	function loadTcaAdditions($ext_keys){
		global $_EXTKEY, $TCA;

		//Merge all ext_keys
		if (is_array($ext_keys)) {
			for($i = 0; $i < sizeof($ext_keys); $i++)	{
				if (t3lib_extMgm::isLoaded($ext_keys[$i]))	{
					//Include the ext_table
					$_EXTKEY = $ext_keys[$i];
					include(t3lib_extMgm::extPath($ext_keys[$i]).'ext_tables.php');
				}
			}
		}
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sg_div/class.tx_sgdiv.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sg_div/class.tx_sgdiv.php']);
}
?>