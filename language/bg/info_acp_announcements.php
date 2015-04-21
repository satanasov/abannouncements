<?php
/**
*
* info_acp_announcements [English]
*
/**
*
* @package phpBB Advanced Board Announcements
* @version $Id$
* @copyright (c) 2015 Lucifer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
$lang = array_merge($lang, array(
	'AB_ANNOUNCEMENTS'					=> 'Advanced Board Announcements',
	'ACP_AB_ANNOUNCEMENTS'				=> 'Настройки',
	'BOARD_ANNOUNCEMENTS'				=> 'Форумни съобщения',
	'BOARD_ANNOUNCEMENTS_EXP'			=> 'Тук можете да конфигурирате различни форумни съобщения',
	'ID'								=> 'Номер',
	'TITLE'								=> 'Заглавие',
	'CONTENT'							=> 'Съдържание',
	'VISIBILITY'						=> 'Видимост',
	'NO_ANNOUNCEMENTS'					=> 'Няма съобщения',
	'ADD_NEW_BOARD_ANNOUNCEMENT'		=> 'Добави ново форумно съобщение',

	'BOARD_ANNOUNCEMENTS_BGCOLOR'		=> 'Цвят на фона на съобщението',
	'BOARD_ANNOUNCEMENTS_BGCOLOR_EXPLAIN'	=> 'Какъв ще е фоновия цвят на съобщението',
	'BOARD_ANNOUNCEMENTS_CREATED'		=> 'Форумното съобщение е създадено',
	'BOARD_ANNOUNCEMENTS_UPDATE'		=> 'Форумното съобщение е опреснено',
	'BOARD_ANAUNCMENT_DELETE_CONFIRM'	=> 'Сигурни ли сте, че искате да изтриетете това форумно съпбщение?',
	'BOARD_ANAUNCMENT_DELETED'			=> 'Форумното съобщение е изтрито',
	'AKNOWLEGABLE'						=> 'Може да бъде махано от потребителя',
	'RESET_AKNOWLEDGED'					=> 'Занули премахванията',
));
