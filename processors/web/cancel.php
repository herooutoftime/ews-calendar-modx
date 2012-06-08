<?php

$ews = $modx->getService('ews','Ews',$modx->getOption('ews.core_path',null,$modx->getOption('core_path').'components/ews/').'model/ews/',$scriptProperties);
if (!($ews instanceof Ews)) return '';

echo $ews->cancelItems(array($_REQUEST['uid']));
return;
