<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   TimeTracker
 * @author    Mario Müller
 * @license   LGPL
 * @copyright Lingo4you
 */


/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['system']['time_tracker'] = array
(
		'tables'		=> array('tl_time_tracker'),
		'icon'			=> 'system/modules/time_tracker/assets/images/clock.png',
#		'export'		=> array('SiteExport', 'export'),
#		'stylesheet'	=> 'system/modules/site_export/assets/styles/site_export.css'
);


if (TL_MODE == 'BE')
{
	$GLOBALS['TL_HOOKS']['postLogin'][] = array('TimeTrackerHooks', 'postLoginHook');
	$GLOBALS['TL_HOOKS']['postLogout'][] = array('TimeTrackerHooks', 'postLogoutHook');
	$GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('TimeTrackerHooks', 'outputBackendTemplateHook');
}
