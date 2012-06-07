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


$props['year']		= $modx->getOption('year', $scriptProperties, $_GET['year']);
$props['month']		= $modx->getOption('month', $scriptProperties, $_GET['month']);
$props['range']		= $modx->getOption('range', $scriptProperties);
$props['limit']		= $modx->getOption('limit', $scriptProperties);

$props['outerTpl']	= $modx->getOption('outerTpl', $scriptProperties, 'ol');
$props['dayTpl']	= $modx->getOption('dayTpl', $scriptProperties, 'calendarDay');
$props['eventTpl']	= $modx->getOption('eventTpl', $scriptProperties, 'calendarItem');
$props['headerTpl']	= $modx->getOption('headerTpl', $scriptProperties, 'calendarHeader');
$props['navTpl']	= $modx->getOption('navTpl', $scriptProperties);
$props['addTpl']    = $modx->getOption('addTpl', $scriptProperties, 'calendarAdd');

$props['outerAttr']	= $modx->getOption('outerAttr', $scriptProperties, 'class="days-list"');
$props['dayAttr']	= $modx->getOption('dayAttr', $scriptProperties);
$props['dayClass']	= $modx->getOption('dayClass', $scriptProperties, 'day-item');
$props['eventAttr']	= $modx->getOption('eventAttr', $scriptProperties);

$props['cssResource'] = $modx->getOption('cssResource', $scriptProperties);

$props['calname']   = $modx->getOption('calname', $scriptProperties, $modx->getOption('site_name'));
$props['caldesc']   = $modx->getOption('caldesc', $scriptProperties);
$props['timezone']   = $modx->getOption('timezone', $scriptProperties, date_default_timezone_get());

if(!$props['year']) $props['year'] = date('Y');
if(!$props['month']) $props['month'] = date('m');

return $ews->getCalendarView($props);