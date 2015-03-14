<?php
/**
*
* @package phpBB Advanced Board Announcements
* @version $Id$
* @copyright (c) 2015 Lucifer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace anavaro\abannouncements\acp;
/**
* @package acp
*/
class announcements_module
{
	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $table_prefix, $request;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		
		$this->user = $user;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$this->db = $db;
		
		$action = request_var('act', 'main');
		$tid = request_var('tid', 0);
		$inputForm = request_var('input', '0');
		//$this->var_display($tid);
		$this->tpl_name		= 'acp_announcements';
		$this->page_title	= $user->lang('BOARD_ANNOUNCEMENTS');
		$form_name = 'acp_board_announcements';
		add_form_key($form_name);
		//Lets get some groups!
		$sql = 'SELECT group_id, group_name FROM ' . GROUPS_TABLE;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result)) {
			$groups_array[$row['group_id']] = array(
				'id'	=>	$row['group_id'],
				'name'	=>	$row['group_name'],
			); 
		}
		// Add the posting lang file needed by BBCodes
		$this->user->add_lang(array('posting'));
		// Include files needed for displaying BBCodes
		if (!function_exists('display_custom_bbcodes'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}
		//$this->var_display(append_sid(test));
		switch ($action) {
			case 'main':
				$sql = 'SELECT * FROM ' . $table_prefix . 'board_announce';
				$result = $db->sql_query($sql);
				//this->var_display($_POST);
				//$this->var_display($_GET);
				$messages = array();
				while ($row = $db->sql_fetchrow($result)) {
					$messages[$row['announce_id']] = array (
						'id'	=> $row['announce_id'],
						'name'	=> $row['announce_title'],
						'content'	=> $row['announce_content'],
						'group'	=> explode(':', substr($row['announce_group'], 3, -2)),
						'order'	=> $row['announce_order'],
						'expire'	=> $row['announce_expire'],
						'owner_id'	=> $row['announce_owner_id'],
						'akn'	=> $row['announce_akn'],
						'akn_users' => explode(':',$row['announce_akn_users']),
					);	
				}
				foreach ($messages as $VAR) {
					$group_out = '';
					if (count($VAR['group']) > 1) {
						foreach ($VAR['group'] as $RAW) {
							$group_out .= $groups_array[$RAW]['name']."<br>";
						}
					}
					else {
						$group_out = $groups_array[$VAR['group'][0]]['name'];
					}
					$edit_url = append_sid("index.php?i=".$id."&mode=".$mode."&act=edit&tid=".$VAR['id']);
					$del_url = append_sid("index.php?i=".$id."&mode=".$mode."&act=del&tid=".$VAR['id']);
					//$this->var_display($edit_url);
					$template->assign_block_vars('messages_table', array(
						'ID'	=> $VAR['id'],
						'NAME'	=> $VAR['name'],
						'CONTENT'	=> $VAR['content'],
						'GROUP'	=> $group_out,	
						'EDIT_URL' => $edit_url,
						'DEL_URL'	=> $del_url,
					));
				}
				$template->assign_var('NEW_MESSAGE',append_sid("index.php?i=".$id."&mode=".$mode."&act=add"));
				//$this->var_display($messages);
				$template->assign_vars(array(
					'WHERE_AM_I' => '1',
				));
			break;
			case 'add':
				if ($this->request->is_set_post('submit') || $this->request->is_set_post('preview'))
				{
					// Test if form key is valid
					if (!check_form_key($form_name))
					{
						$error = $this->user->lang('FORM_INVALID');
					}
					// Get new announcement text and bgcolor values from the form
					$data['announce_title'] = $this->request->variable('name', '', true);
					$data['announce_bgcolor'] = $this->request->variable('board_announcements_bgcolor', '', true);
					$comment = $this->request->variable('board_announcements_text', '', true);
					include_once($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);
					$message_parser = new \parse_message();
					$message_parser->message = utf8_normalize_nfc($comment);
					if ($message_parser->message)
					{
						$message_parser->parse(true, true, true, true, false, true, true, true);
					}
					// Get config options from the form
					$dismiss_announcements = $this->request->variable('board_announcements_dismiss', false);
					$groups = implode(':', $this->request->variable('groups', array(0)));
					$data['announce_group'] = '{g:' . $groups . ':}';
					$data['announce_bitfield'] = $message_parser->bbcode_bitfield; 
					$data['announce_uid'] = $message_parser->bbcode_uid;
					$data['announce_content'] = $message_parser->message;
					$disable_bbcode = $this->request->variable('disable_bbcode', 0);
					$disable_smilies = $this->request->variable('disable_smilies', 0);
					$disable_magic_url = $this->request->variable('disable_magic_url', 0);
					$data['announce_options'] = OPTION_FLAG_BBCODE + OPTION_FLAG_SMILIES + OPTION_FLAG_LINKS - $disable_bbcode - $disable_smilies - $disable_magic_url;
					$data['announce_akn'] = $this->request->variable('akn', 0);
					$data['announce_owner_id'] = $this->user->data['user_id'];
					//@ TODO - implement expire and order
					$data['announce_expire'] = 0;
					$data['announce_order'] = 0;
					$data['announce_akn_users'] = 0;
					if($this->request->is_set_post('submit'))
					{
						$sql = 'INSERT INTO ' . $table_prefix . 'board_announce ' . $db->sql_build_array('INSERT', $data);
						$db->sql_query($sql);
						trigger_error($this->user->lang('BOARD_ANNOUNCEMENTS_CREATED') . adm_back_link($this->u_action));
					}
				}
				else
				{
					$data = array(
						'announce_content' => '',
						'announce_uid'	=> '',
						'announce_group'	=> '',
						'announce_name'	=> '',
						'announce_bitfield'	=> '',
						'announce_options'	=> OPTION_FLAG_BBCODE + OPTION_FLAG_SMILIES + OPTION_FLAG_LINKS
					);
				}
				// Prepare a fresh announcement preview
				$announcement_text_preview = '';
				if ($this->request->is_set_post('preview'))
				{
					$announcement_text_preview = generate_text_for_display($data['announce_content'], $data['announce_uid'], $data['announce_bitfield'], $data['announce_options']);
				}
				$announcement_text_edit = generate_text_for_edit($data['announce_content'], $data['announce_uid'], $data['announce_bitfield'], 7);
				$post_url = append_sid("index.php?i=".$id."&mode=".$mode."&act=add");
				$groups = explode(':', $data['announce_group']);
				foreach ($groups_array as $VAR) {
					$id = $VAR['id'];
					$name = $VAR['name'];
					//$this->var_display($checked);
					$template->assign_block_vars('groups', array(
						'ID'	=> $id,
						'NAME'	=> $name,
						'CHECKED'	=> (in_array($id, $groups) ? 1 : 0),
					));
				}
				$template->assign_vars(array(
					'OWNER'	=>	$user->data['username'],
					'OWNER_ID'	=>	$user->data['user_id'],
					'BOARD_ANNOUNCEMENTS_PREVIEW'	=> $announcement_text_preview,
					'BOARD_ANNOUNCEMENTS_BGCOLOR'	=> '000000',
					'BOARD_ANAUNCMENT_NAME'			=> $data['announce_name'],
					'BOARD_ANNOUNCEMENTS_TEXT'		=> $announcement_text_edit['text'],
					'WHERE_AM_I' => '2',
					'U_ACTION'	=>	$post_url,
					'BBCODE_STATUS'			=> $user->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$phpbb_root_path}faq.{$phpEx}", 'mode=bbcode') . '">', '</a>'),
					'SMILIES_STATUS'		=> $user->lang('SMILIES_ARE_ON'),
					'IMG_STATUS'			=> $user->lang('IMAGES_ARE_ON'),
					'FLASH_STATUS'			=> $user->lang('FLASH_IS_ON'),
					'URL_STATUS'			=> $user->lang('URL_IS_ON'),
					'S_BBCODE_ALLOWED'		=> true,
					'S_SMILIES_ALLOWED'		=> true,
					'S_BBCODE_IMG'			=> true,
					'S_BBCODE_FLASH'		=> true,
					'S_LINKS_ALLOWED'		=> true,
				));
			break;
			case 'edit':
				//$this->var_display($tid);
				if ($this->request->is_set_post('submit') || $this->request->is_set_post('preview'))
				{
					// Test if form key is valid
					if (!check_form_key($form_name))
					{
						$error = $this->user->lang('FORM_INVALID');
					}
					// Get new announcement text and bgcolor values from the form
					$data['announce_title'] = $this->request->variable('name', '', true);
					$data['announce_bgcolor'] = $this->request->variable('board_announcements_bgcolor', '', true);
					$comment = $this->request->variable('board_announcements_text', '', true);
					include_once($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);
					$message_parser = new \parse_message();
					$message_parser->message = utf8_normalize_nfc($comment);
					if ($message_parser->message)
					{
						$message_parser->parse(true, true, true, true, false, true, true, true);
					}
					// Get config options from the form
					$dismiss_announcements = $this->request->variable('board_announcements_dismiss', false);
					$groups = implode(':', $this->request->variable('groups', array(0)));
					$data['announce_group'] = '{g:' . $groups . ':}';
					$data['announce_bitfield'] = $message_parser->bbcode_bitfield; 
					$data['announce_uid'] = $message_parser->bbcode_uid;
					$data['announce_content'] = $message_parser->message;
					$disable_bbcode = $this->request->variable('disable_bbcode', 0);
					$disable_smilies = $this->request->variable('disable_smilies', 0);
					$disable_magic_url = $this->request->variable('disable_magic_url', 0);
					$data['announce_options'] = OPTION_FLAG_BBCODE + OPTION_FLAG_SMILIES + OPTION_FLAG_LINKS - $disable_bbcode - $disable_smilies - $disable_magic_url;
					$data['announce_akn'] = $this->request->variable('akn', 0);
					$data['announce_owner_id'] = $this->user->data['user_id'];
					//@ TODO - implement expire and order
					$data['announce_expire'] = 0;
					$data['announce_order'] = 0;
					$data['announce_akn_users'] = 0;
					
					if($this->request->is_set_post('submit'))
					{
						$sql = 'UPDATE ' . $table_prefix . 'board_announce SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE announce_id = ' . $tid;
						$db->sql_query($sql);
						trigger_error($this->user->lang('BOARD_ANNOUNCEMENTS_UPDATE') . adm_back_link($this->u_action));
					}
					$row = $data;
					$row['announce_id'] = $tid;
					$post_url = append_sid("index.php?i=".$id."&mode=".$mode."&act=edit&tid=".$tid);
				}
				else
				{
					$post_url = append_sid("index.php?i=".$id."&mode=".$mode."&act=edit&tid=".$tid);
					$sql = 'SELECT * FROM ' . $table_prefix . 'board_announce WHERE announce_id=' . $tid;
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
				}
				$groups = explode(":", substr($row['announce_group'], 3, -2));
				foreach ($groups_array as $VAR) {
					$id = $VAR['id'];
					if (in_array($id, $groups)) {
						$checked = '1';
					}
					else {
						$checked = '0';
					}
					$name = $VAR['name'];
					//$this->var_display($checked);
					$template->assign_block_vars('groups', array(
						'ID'	=> $id,
						'NAME'	=> $name,
						'CHECKED'	=> $checked,
					));
				}
				$sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . $row['announce_owner_id'];
				$result = $db->sql_query($sql);
				$owner = $db->sql_fetchrow($result);

				$announcement_text_preview = '';
				if ($this->request->is_set_post('preview'))
				{
					$announcement_text_preview = generate_text_for_display($row['announce_content'], $row['announce_uid'], $row['announce_bitfield'], $row['announce_options']);
				}
				var_dump($announcement_text_preview);
				$announcement_text_edit = generate_text_for_edit($row['announce_content'], $row['announce_uid'], $row['announce_bitfield'], 7);
				$template->assign_vars(array(
					'ID'	=> $row['announce_id'],
					'NAME'	=> $row['announce_title'],
					'ORDER'	=> $row['announce_order'],
					'BOARD_ANNOUNCEMENTS_PREVIEW'	=> $announcement_text_preview,
					'BOARD_ANNOUNCEMENTS_BGCOLOR'	=> $row['announce_bgcolor'],
					'BOARD_ANNOUNCEMENTS_TEXT'		=> $announcement_text_edit['text'],
					'EXPIRE' => $row['announce_expire'],
					'OWNER'	=>	$owner['username'],
					'OWNER_ID'	=> $row['announce_owner_id'],
					'AKN'	=>	$row['announce_akn'],
					'EDITABLE' => '1',
					'WHERE_AM_I' => '2',
					'U_ACTION'	=>	$post_url,
					'BBCODE_STATUS'			=> $user->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$phpbb_root_path}faq.{$phpEx}", 'mode=bbcode') . '">', '</a>'),
					'SMILIES_STATUS'		=> $user->lang('SMILIES_ARE_ON'),
					'IMG_STATUS'			=> $user->lang('IMAGES_ARE_ON'),
					'FLASH_STATUS'			=> $user->lang('FLASH_IS_ON'),
					'URL_STATUS'			=> $user->lang('URL_IS_ON'),
					'S_BBCODE_ALLOWED'		=> true,
					'S_SMILIES_ALLOWED'		=> true,
					'S_BBCODE_IMG'			=> true,
					'S_BBCODE_FLASH'		=> true,
					'S_LINKS_ALLOWED'		=> true,
				));
				//$this->var_display($row);
				
				//$this->var_display($post_url);
			break;
			case 'del':
				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $table_prefix . 'board_announce WHERE announce_id = ' . $tid;
					$this->db->sql_query($sql);
					trigger_error($this->user->lang('BOARD_ANNOUNCEMENTS_DELETED') . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, 'BOARD_ANAUNCMENT_DELETE', '');
				}
			break;
		}
	}
}