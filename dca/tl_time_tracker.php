<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');


$GLOBALS['TL_DCA']['tl_time_tracker'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'			=> 'Table',
		'ptable'				=> 'tl_user',
		'closed'				=> true,
		'notSortable'			=> true,
		'notEditable'			=> true,
		'notCopyable'			=> true,
		'notCopyable'			=> true,
		'notCreatable'			=> true,
		'doNotCopyRecords'		=> true,
		'sql' => array
		(
			'keys' => array
			(
				'id'	=> 'primary',
				'pid'	=> 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('last_activity DESC'),
			'panelLayout'             => 'filter,search,limit'
		),
		'label' => array
		(
			'fields'				=> array('pid', 'login_time', 'logout_time', 'last_activity', 'do_activity', 'edit_count'),
#			'format'                  => '<span style="color:#b3b3b3;padding-right:3px">[%s]</span> %s bis %s (zuletzt aktiv %s)',
			'maxCharacters'			=> 96,
			'group_callback'		=> array('tl_time_tracker', 'renderHeader'),
			'label_callback'		=> array('tl_time_tracker', 'renderRow')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
#				'button_callback'     => array('tl_site_export_rules', 'headerButtons')
			)
		),
		'operations' => array
		(
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_log']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_log']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),


	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'			=> "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_time_tracker']['pid'],
			'filter'		=> true,
			'sorting'		=> false,
			'flag'			=> 1,
			'options_callback'	=> array('tl_time_tracker', 'getUserNames'),
			'sql'			=> "int(10) unsigned NOT NULL"
		),
		'login_time' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_time_tracker']['login_time'],
			'filter'		=> true,
			'sorting'		=> true,
			'flag'			=> 6,
			'sql'			=> "int(10) unsigned NOT NULL default '0'"
		),
		'logout_time' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_time_tracker']['logout_time'],
			'filter'		=> false,
			'sorting'		=> true,
			'flag'			=> 6,
			'sql'			=> "int(10) unsigned NOT NULL default '0'"
		),
		'last_activity' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_time_tracker']['last_activity'],
			'filter'		=> false,
			'sorting'		=> true,
			'flag'			=> 6,
			'sql'			=> "int(10) unsigned NOT NULL default '0'"
		),
		'do_activity' => array
		(
			'label'			&$GLOBALS['TL_LANG']['tl_time_tracker']['do_activity'],
			'sql'			=> "varchar(255) NOT NULL default ''"
		),
		'edit_count' => array
		(
		 	'sql'			=> "int(10) unsigned NOT NULL default '0'"
		 )
	)
);


class tl_time_tracker extends \Backend
{
	protected $headerTemplate =
		'<span style="display:inline-block;width:12em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:10em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:12em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:8em;white-space:nowrap;text-align:center">%s</span>
		<span style="display:inline-block;width:6em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:6em;white-space:nowrap;text-align:right">%s</span>';

	protected $rowTemplate =
		'<span style="display:inline-block;width:12em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:10em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:12em;white-space:nowrap">%s</span>
		<span style="display:inline-block;width:8em;white-space:nowrap;text-align:center">%s</span>
		<span style="display:inline-block;width:3em;white-space:nowrap;text-align:right;padding-right:3em">%s</span>
		<span style="display:inline-block;width:6em;white-space:nowrap;text-align:right">%s</span>';

	protected $blnFirstHeader = TRUE;


	public function getUserNames(\DataContainer $dc)
	{
		$arrReturn = array();

		$objResult = \Database::getInstance()->prepare("SELECT `id`, `name` FROM `tl_user` WHERE `disable`<>'1' ORDER BY `name`")->execute();

		while ($objResult->next())
		{
			$arrReturn[$objResult->id] = $objResult->name;
		}

		return $arrReturn;
	}


	public function renderHeader($header, $flag)
	{
		if ($this->blnFirstHeader)
		{
			$this->blnFirstHeader = FALSE;
			return sprintf($this->headerTemplate, $header, 'Login', 'letzte Aktivität', 'um', 'Zähler', 'Zeit');
		}
		else
		{
			return $header;
		}
	}

	public function renderRow($row, $label)
	{
		$intEditTime = $row['last_activity'] - $row['login_time'];

		$intDays = floor($intEditTime/86400);
		$intHours = floor(($intEditTime-$intDays*86400)/3600);
		$intMinutes = floor(($intEditTime-$intDays*86400-$intHours*3600)/60);
		$intSeconds = $intEditTime-$intDays*86400-$intHours*3600-$intMinutes*60;

		$strActivity = $row['do_activity'];

		if (isset($GLOBALS['TL_LANG']['MOD'][$row['do_activity']]) && is_array($GLOBALS['TL_LANG']['MOD'][$row['do_activity']]))
		{
			$strActivity = $GLOBALS['TL_LANG']['MOD'][$row['do_activity']][0];
		}

		$label = sprintf($this->rowTemplate,
					\UserModel::findByPk($row['pid'])->name,
					date('d.m \u\m H:i', $row['login_time']),
					$strActivity,
					date('H:i:s', $row['last_activity']),
					$row['edit_count'],
					sprintf('%s%d:%02d:%02d', ($intDays>0?$intDays.'T ':''), $intHours, $intMinutes, $intSeconds)
		);

		if ($row['logout_time'] > 0)
		{
			$label = '<span style="color:#888;">'.$label.'</span>';
		}

		return $label;
	}
}
