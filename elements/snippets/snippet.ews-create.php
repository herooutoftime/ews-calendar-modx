<?php
/**
 * Snippet 'ews'
 * Instantiates a new EWS class for further handling
 * @param array $scriptProperties The script properties
 * @param int $year The year to search for, default current year
 * @param int $month The month to search for, default current month
 * @param int $range The range of months to go forward
 * @param string $fn The function to call
 * @return mixed The output
 * @author Andreas Bilz <andreas@subsolutions.at>
 */
$ews = $modx->getService('ews','Ews',$modx->getOption('ews.core_path',null,$modx->getOption('core_path').'components/ews/').'model/ews/',$scriptProperties);
if (!($ews instanceof Ews)) return '';

$tpl = $modx->getOption('addTpl', $scriptProperties, 'calendarAdd');

$get = $modx->sanitize($_GET);
$post = $modx->sanitize($_POST);
$files = $modx->sanitize($_FILES);
$props = $modx->sanitize($scriptProperties);

return $ews->makeItem($tpl, array('post' => $post, 'files' => $files, 'props' => $props, 'get' => $get));