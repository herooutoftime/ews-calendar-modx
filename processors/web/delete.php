<?php

/**
 * Processor - delete
 *
 * Processor to delete items based on an given array of IDs
 * Used for AJAX in frontend context
 */

$ews = $modx->getService('ews','Ews',$modx->getOption('ews.core_path',null,$modx->getOption('core_path').'components/ews/').'model/ews/',$scriptProperties);
if (!($ews instanceof Ews)) return '';

echo $ews->deleteItems(array($_REQUEST['uid']));
return;
