<?php
/**
*
* @package phpBB Advanced Board Announcements
* @version $Id$
* @copyright (c) 2015 Lucifer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
namespace anavaro\abannouncements\controller;
class ajaxify
{
	public function __construct(\phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\user $user,
	$announcments_table)
	{
		$this->helper = $helper;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->announs_table = $announcments_table;
	}
	public function close($announcement_id)
	{
		$sql = 'SELECT * FROM ' . $this->announs_table . ' WHERE announce_id = ' . $announcement_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		// Check the link hash to protect against CSRF/XSRF attacks
		if (!check_link_hash($this->request->variable('hash', ''), 'close_boardannouncement') || !$row['announce_akn'])
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}
		// Close the announcement for registered users
		if ($this->user->data['user_id'] != ANONYMOUS)
		{
			$response = $this->update_board_announcement_status($announcement_id);
		}
		else
		{
			$response = true;
		}
		// Send a JSON response if an AJAX request was used
		if ($this->request->is_ajax())
		{
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'success' => $response,
				'id'	=> $announcement_id,
			));
		}
		// Redirect the user back to their last viewed page (non-AJAX requests)
		$redirect = $this->request->variable('redirect', $this->user->data['session_page']);
		$redirect = reapply_sid($redirect);
		redirect($redirect);
		// We shouldn't get here, but throw an http exception just in case
		throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
	}
	protected function update_board_announcement_status($id)
	{
		$excluded = explode(':', $this->user->data['announce_akn']);
		if (in_array($id, $excluded))
		{
			return 0;
		}
		$excluded[] = $id;
		$ex_string = implode(':', array_filter($excluded));
		$ex_string = ':' . $ex_string . ':';
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET announce_akn = \'' . $ex_string . '\'
			WHERE user_id = ' . (int) $this->user->data['user_id'] . '
			AND user_type <> ' . USER_IGNORE;
		$this->db->sql_query($sql);
		return (bool) $this->db->sql_affectedrows();
	}
}