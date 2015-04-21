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
	'ACP_AB_ANNOUNCEMENTS'				=> 'Settings',
	'BOARD_ANNOUNCEMENTS'				=> 'Board Announcements',
	'BOARD_ANNOUNCEMENTS_EXP'			=> 'Here you can configure different board announcments',
	'ID'								=> 'ID',
	'TITLE'								=> 'Title',
	'CONTENT'							=> 'Content',
	'VISIBILITY'						=> 'Visibility',
	'NO_ANNOUNCEMENTS'					=> 'There are no announcements',
	'ADD_NEW_BOARD_ANNOUNCEMENT'		=> 'Add new announcement',

	'BOARD_ANNOUNCEMENTS_BGCOLOR'		=> 'Announcement background color',
	'BOARD_ANNOUNCEMENTS_BGCOLOR_EXPLAIN'	=> 'Announcement background color',
	'BOARD_ANNOUNCEMENTS_CREATED'		=> 'Board announcement created',
	'BOARD_ANNOUNCEMENTS_UPDATE'		=> 'Board announcement updated',
	'BOARD_ANAUNCMENT_DELETE_CONFIRM'	=> 'Are you sure you want to delete this board announcement?',
	'BOARD_ANAUNCMENT_DELETED'			=> 'Board announcement deleted',
	'AKNOWLEGABLE'						=> 'Can be acknowleded by the user',
	'RESET_AKNOWLEDGED'					=> 'Reset aknowledgment',
));
