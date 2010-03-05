<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


define ('CRLF', "\r\n");
define ('DQT', '"');
define ('QT', "'");

// ***********************************************************************************************************************************

class tx_sgmail {
	public $dbg;
	protected $activeFeUser;
	protected $myError;
	protected $myMessage;
	protected $fieldMessages = Array();

	function init ($conf, $type='default') {
		$this->dbg = ($_GET['dbg']==1);
		$error = '';
		$this->myError = 99;
		$this->myMessage = '';
		$this->conf = $conf;
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->actFeUser = $GLOBALS['TSFE']->fe_user->user['username'];

		$this->params = $this->getParams($type);

		if (strlen($this->params['type']) && is_array($conf[$this->params['type'].'.'])) {
			$this->conf = $conf[$this->params['type'].'.'];
		} elseif (is_array($conf['default.'])) {
			$this->conf = $conf['default.'];
		}

		if ($this->conf['formArray'] && is_array($_GET[$this->conf['formArray']])) {
			$this->params['maildata'] = $_GET[$this->conf['formArray']];
		} else {
			$this->params['maildata'] = $_GET['maildata'];
		}

		if ($this->dbg) {
			t3lib_div::debug(Array('$this->conf'=>$this->conf, '$this->params'=>$this->params, '$this->activeFeUser'=>$this->activeFeUser, 'File:Line'=>__FILE__.':'.__LINE__));
		}

		return ($error);
	}


	protected function getParams($type='default') {
		$params = Array();
		$params['lang'] = $GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$params['uid'] = $GLOBALS['TSFE']->id;
		$params['type'] = $type;

		return ($params);
	}


	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	public function main ($content, $conf) {
		$this->myError = $this->checkFields();
		if (!$this->myError) {
			$this->myError = $this->sendMail();
			$this->setResultMessage($this->myError);
		}
		$content = $this->getXmlResult();
		
		return ($content);
	}

	public function setResultMessage($error) {
		if ($error) {
			$this->myMessage = $this->cObj->TEXT($this->conf['errorMessage.']);
		} else {
			$this->myMessage = $this->cObj->TEXT($this->conf['successMessage.']);
		}
	}

	public function getResultMessage() {
		return ($this->myMessage);
	}

	public function checkFields () {
		$error = 0;
		$this->myMessage = 'ERROR - Internal Error';

		if (is_array($this->conf['fields.'])) {
			foreach ($this->conf['fields.'] as $fieldName=>$fieldConf) {
				$errorMessage = '';
				if (is_array($fieldConf)) foreach ($fieldConf as $testName=>$testConf) if (!$errorMessage) {
					$errorMessage = $this->checkFieldFor(substr($fieldName,0,-1),substr($testName,0,-1),$testConf);
					if ($errorMessage) {
						$this->fieldMessages[substr($fieldName,0,-1)] = $errorMessage;
						$error = 2;
						$this->myMessage = '';
					}
				}
			}
		}

		return ($error);
	}

	public function getErrorsAndValues() {
		$mailErrors = Array();

		$mailErrors['message'] = $this->myMessage;

		if (is_array($this->params['maildata']))  foreach ($this->params['maildata'] as $fieldName=>$fieldContent) {			
			$mailErrors['value_'.$fieldName] = addslashes(htmlspecialchars($fieldContent));
		}
		if (is_array($this->conf['fields.']))  foreach ($this->conf['fields.'] as $fieldName=>$fieldConf) {			
			$mailErrors['error_'.substr($fieldName,0,-1)] = '';
			$mailErrors['value_'.substr($fieldName,0,-1)] = $this->params['maildata'][substr($fieldName,0,-1)];
		}
		foreach ($this->fieldMessages as $fieldName=>$errorMessage) {
			$mailErrors['error_'.$fieldName] = $errorMessage;
		}
		return ($mailErrors);
	}

	protected function checkFieldFor($fieldName,$testName,$testConf) {
		$message = '';
		$value = $this->params['maildata'][$fieldName];
		// t3lib_div::debug(Array('$fieldName'=>$fieldName, '$value'=>$value, '$testName'=>$testName, '$testConf'=>$testConf, 'File:Line'=>__FILE__.':'.__LINE__));

		switch($testName) {
			case 'minLenght':
				if (strlen($value)<$testConf['value']) {
					$message = $this->cObj->TEXT($testConf['error.']);
					// t3lib_div::debug(Array('$fieldName'=>$fieldName, '$message'=>$message, 'File:Line'=>__FILE__.':'.__LINE__));
				}
				break;
		
			case 'isEmail':
				if (!tx_sgdiv::validEmail($value) && $testConf['value']) {
					$message = $this->cObj->TEXT($testConf['error.']);
					// t3lib_div::debug(Array('$fieldName'=>$fieldName, '$message'=>$message, 'File:Line'=>__FILE__.':'.__LINE__));
				}
				break;
		
			case 'isPhone':
				if (!tx_sgdiv::validPhoneNumber($value) && $testConf['value']) {
					$message = $this->cObj->TEXT($testConf['error.']);
					// t3lib_div::debug(Array('$fieldName'=>$fieldName, '$value'=>$value, '$message'=>$message, 'File:Line'=>__FILE__.':'.__LINE__));
				}
				break;
		
			case 'pregMatch':
				if (!preg_match($testConf['value'], $value) || strlen(trim($value))<1) {
					$message = $this->cObj->TEXT($testConf['error.']);
					// t3lib_div::debug(Array('$fieldName'=>$fieldName, '$value'=>$value, '$testConf'=>$testConf, '$message'=>$message, 'File:Line'=>__FILE__.':'.__LINE__));
				}
				break;
		
			default:
				if (is_array($testConf)) {
					if ($this->dbg) t3lib_div::debug(Array('$fieldName'=>$fieldName, '$value'=>$value, '$testName'=>$testName, '$testConf'=>$testConf, 'File:Line'=>__FILE__.':'.__LINE__));
				}

		}
		 
		return ($message);
	}


	public function sendMail() {
		$markers = $this->getMailMarkers();
		$error = $this->sendContactData($markers);

		return ($error);
	}

	protected function getMailMarkers () {
		$markers = $this->getConst($conf);

		if (is_array($this->conf['fields.']))  foreach ($this->conf['fields.'] as $fieldName=>$fieldConf) {			
			$markers['###MAIL_'.strtoupper(substr($fieldName,0,-1)).'###'] = $fieldConf['preset'];
		}

		foreach ($this->params['maildata'] as $key=>$value) {
			$markers['###MAIL_'.strtoupper($key).'###'] = htmlspecialchars($value);
		}
		// t3lib_div::debug(Array('$markers'=>$markers, 'File:Line'=>__FILE__.':'.__LINE__));

		return ($markers);
	}

	protected function getConst($conf) {
		$markers = Array();
		$myTime = time();

		$indpEnv = t3lib_div::getIndpEnv('_ARRAY');
		for (reset($indpEnv);$key=key($indpEnv);next($indpEnv)) {
			$markers['###CONST_'.$key.'###'] = $indpEnv[$key];
		}
		
		$markers['###CONST_PAGE_URL###'] = $this->cObj->TypoLink_URL(array('parameter'=>$GLOBALS['TSFE']->id, 'target'=>'_self'));
		$markers['###CONST_PAGE_URL###'] .= (strpos($markers['###PAGE_URL###'], '?')>0) ? '' : '?';
		$markers['###CONST_TIME###'] = $myTime;
		$markers['###CONST_SYS_TSTAMP###'] = $myTime;
		$markers['###CONST_SYS_DATETIMESEC###'] = date('Ymd-His',$myTime);
		$markers['###CONST_SYS_DATETIME###'] = date('Ymd-Hi',$myTime);
		$markers['###CONST_SYS_DATE###'] = date('Ymd',$myTime);
		$markers['###CONST_SYS_TIMESEC###'] = date('His',$myTime);

		return ($markers);
	}

	protected function sendContactData ($markers) {
		$error = 0;
		$template = $this->cObj->cObjGetSingle('FILE',Array('file'=>$this->conf['template']));
		// t3lib_div::debug(Array('$template'=>$template, 'File:Line'=>__FILE__.':'.__LINE__));
		
		$subject = $this->cObj->substituteMarkerArray($this->conf['subject'],$markers);
		$bodytext = $this->cObj->substituteMarkerArray($template,$markers);
		$from = htmlspecialchars($this->params['maildata']['email']);
		$fromName = trim(htmlspecialchars($this->params['maildata']['vorname'] . ' ' . $this->params['maildata']['name']));

		$htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$htmlMail->start();
		$htmlMail->from_email = $from;
		$htmlMail->from_name = $fromName;
		$htmlMail->subject = $subject;
		$htmlMail->replyto_email = '';
		$htmlMail->replyto_name = '';
		$htmlMail->organisation = '';
		$htmlMail->addPlain($bodytext);
		$htmlMail->setRecipient($this->conf['mailTo']);
		$htmlMail->setHeaders();
		$htmlMail->setContent();
		$success = $htmlMail->sendTheMail();

		if ($from && $this->params['maildata']['kopie']) {
			$htmlMail->start();
			$htmlMail->from_email = $from;
			$htmlMail->from_name = $fromName;
			$htmlMail->subject = '[COPY]: ' . $subject;
			$htmlMail->replyto_email = '';
			$htmlMail->replyto_name = '';
			$htmlMail->organisation = '';
			$htmlMail->addPlain($bodytext);
			$htmlMail->setRecipient($from);
			$htmlMail->setHeaders();
			$htmlMail->setContent();
			$htmlMail->sendTheMail();
		}

		if (!$success) {
			$error = 1;
		}

		return ($error);
	}



	public function getXmlResult () {
		// return ($this->demoResult(strlen($this->params['maildata']['firma'])));
		$content = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$content .= '<result language="'.$this->params['lang'].'" feuser="'.$this->actFeUser.'">' . "\n";

		$content .= '   <errorcode>'.$this->myError.'</errorcode>'  . "\n";
		$content .= '   <message>'.$this->myMessage.'</message>'  . "\n";
		$content .= '   <fieldlist>'  . "\n";
		foreach ($this->fieldMessages as $fieldName=>$errorMessage) {
			$content .= '      <'.$fieldName.'>'.$errorMessage.'</'.$fieldName.'>'  . "\n";
		}
		$content .= '   </fieldlist>'  . "\n";

		$content .= '</result>' . "\n";

		return ($content);
	}


	protected function demoResult ($mode) {
		$content = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$content .= '<result language="'.$this->params['lang'].'" feuser="'.$this->actFeUser.'">' . "\n";

		if ($mode<1) {
			$content .= '   <errorcode>0</errorcode>'  . "\n";
			$content .= '   <message>Ihre E-Mail-Anfrage wurde erfolgreich verschickt</message>'  . "\n";
		} elseif ($mode==1) {
			$content .= '   <errorcode>1</errorcode>'  . "\n";
			$content .= '   <message>Ihre E-Mail-Anfrage konnte aufgrund technischer Schwierigkeiten nicht versendet werden. Bitte versuchen Sie es später erneut.</message>'  . "\n";
		} else {
			$content .= '   <errorcode>2</errorcode>'  . "\n";
			$content .= '   <message></message>'  . "\n";
			$content .= '   <fieldlist>'  . "\n";
			$content .= '      <firma>Feld "Firma" muß mindestens 3 Zeichen lang sein.</firma>'  . "\n";
			$content .= '      <email>Feld "E-Mail" enthält keine gültige E-Mail Adresse.</email>'  . "\n";
			$content .= '   </fieldlist>'  . "\n";
		}
		$content .= '</result>' . "\n";


		return($content);
	}





}


?>