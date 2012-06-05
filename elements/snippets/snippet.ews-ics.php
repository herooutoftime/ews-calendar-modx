<?php
/**
 * Snippet 'ews-ics'
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

$props['year']		= $modx->getOption('year', $scriptProperties, $_GET['year']);
$props['month']		= $modx->getOption('month', $scriptProperties, $_GET['month']);
$props['range']		= $modx->getOption('range', $scriptProperties);

$props['calname']   = $modx->getOption('calname', $scriptProperties, $modx->getOption('site_name'));
$props['caldesc']   = $modx->getOption('caldesc', $scriptProperties);
$props['timezone']   = $modx->getOption('timezone', $scriptProperties, date_default_timezone_get());

if(!$props['year']) $props['year'] = date('Y');
if(!$props['month']) $props['month'] = date('m');

return $ews->generateICS($props);