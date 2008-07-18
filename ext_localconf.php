<?php

if (!defined ("TYPO3_MODE"))     die ("Access denied.");


if(TYPO3_MODE == 'FE') {
	require_once(t3lib_extMgm::extPath('sg_div').'class.tx_sgdiv.php');
}
	require_once(t3lib_extMgm::extPath('sg_div').'class.tx_sgdate.php');

?>