<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2014 Leo Feyer
 *
 * PHP version 5
 * @copyright  Lingo4you 2014
 * @author     Mario Müller <http://www.lingolia.com/>
 * @version    1.0.0
 * @package    TimeTracker
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

class TimeTrackerHooks extends \Backend
{
	// public function __construct()
	// {
	// 	parent::__construct();
	// }

	public function postLoginHook(\BackendUser $objUser)
	{
		$objDatabase = \Database::getInstance();

		$objDatabase->prepare('UPDATE `tl_time_tracker` SET `logout_time` = `last_activity` WHERE `pid` = ? AND `logout_time` = 0')
					->execute($objUser->id);

		$objResult = $objDatabase->prepare('INSERT INTO `tl_time_tracker` (`pid`, `login_time`, `last_activity`, `do_activity`, `edit_count`) VALUES(?, ?, ?, "login", 0)')
		 						->execute($objUser->id, time(), time());

		$intTimeTrackId = $objResult->insertId;

		\Session::getInstance()->set('TIME_TRACKER_ID', $intTimeTrackId);
	}


	public function postLogoutHook(\BackendUser $objUser)
	{
		$objSession = \Session::getInstance();
		$objDatabase = \Database::getInstance();

		$intTimeTrackId = $objSession->get('TIME_TRACKER_ID');

		$objDatabase->prepare('UPDATE `tl_time_tracker` SET `logout_time` = ?, `last_activity` = ? WHERE `id` = ?')
					->execute(time(), time(), $intTimeTrackId);

		$objSession->remove('TIME_TRACKER_ID');
	}


	public function outputBackendTemplateHook($strContent, $strTemplate)
	{
		$objSession = \Session::getInstance();
		$objDatabase = \Database::getInstance();

		$intTimeTrackId = $objSession->get('TIME_TRACKER_ID');

		if (\Input::get('do'))
		{
			$objDatabase->prepare('UPDATE `tl_time_tracker` SET `last_activity` = ?, `do_activity` = ?, `edit_count` = `edit_count`+1 WHERE `id` = ?')
						->execute(time(), \Input::get('do') ?: '-', $intTimeTrackId);
		}
		else
		{
			$objDatabase->prepare('UPDATE `tl_time_tracker` SET `last_activity` = ?, `edit_count` = `edit_count`+1 WHERE `id` = ?')
						->execute(time(), $intTimeTrackId);
		}

		return $strContent;
	}

}