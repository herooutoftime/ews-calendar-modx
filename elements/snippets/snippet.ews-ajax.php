<?php

/**
 * ews-ajax
 *
 * Snippet to translate parameter actions into processor calls
 * and outputs JSONified string
 *
 * @param string $action Action to take place
 */

$output='';
//this is the processor file name
$procname=$_REQUEST['action'];
//return $_REQUEST['uid'];

//get the processor file with path
$f= $modx->getOption('core_path').'components/ews/processors/web/'.$procname.'.php';
if (file_exists($f)) {
//include that file so the output of the processor is assigned here
            $output= include $f;
        } else {
            $output= 'Action not found: '.$f;
        }
//convert the processor output to json format
return $modx->toJSON($output);