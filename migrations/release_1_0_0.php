<?php
/**
*
* phpBB Advanced Board Announcements for the phpBB Forum Software package.
*
* @copyright (c) 2014 Lucifer <http://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/
namespace anavaro\abannouncements\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'AB_ANNOUNCEMENTS')),
			array('module.add', array('acp', 'AB_ANNOUNCEMENTS', array(
				'module_basename'	=> '\anavaro\abannouncements\acp\announcements_module',
				'module_langname'	=> 'ACP_AB_ANNOUNCEMENTS',
				'module_mode'		=> 'main',
				'module_auth'		=> 'ext_anavaro/abannouncements',
			))),
		);
	}
	//lets create the needed table
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'board_announce'	=> array(
					'COLUMNS'	=> array(
						'announce_id'	=> array('UINT', null, 'auto_increment'),
						'announce_title'			=> array('VCHAR:255', ''),
						'announce_bgcolor'		=> array('VCHAR:6', '000000'),
						'announce_content'		=> array('MTEXT_UNI', ''),
						'announce_bitfield'		=> array('VCHAR:6', ''),
						'announce_uid'		=> array('VCHAR:8', ''),
						'announce_options'		=> array('VCHAR:6', ''),
						'announce_group'			=> array('VCHAR:255', ''),
						'announce_page'			=> array('VCHAR:255', ''),
						'announce_order'			=> array('UINT:3', 0),
						'announce_expire'		=> array('INT:11', 0),
						'announce_owner_id'		=> array('UINT', 0),
						'announce_akn'			=> array('BOOL', 1),
						'announce_akn_users'		=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'announce_id',
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'users' => array(
					'announce_akn' => array('VCHAR:255', ''),
				),
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'board_announce',
			),
		);
	}
}