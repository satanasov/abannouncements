<?php
/**
*
* @package phpBB Advanced Board Announcements
* @version $Id$
* @copyright (c) 2015 Lucifer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace anavaro\abannouncements\event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\config\db_text */
	protected $config_text;
	/** @var \phpbb\controller\helper */
	protected $controller_helper;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/**
	* Constructor
	*
	* @param \phpbb\config\config        $config             Config object
	* @param \phpbb\config\db_text       $config_text        DB text object
	* @param \phpbb\controller\helper    $controller_helper  Controller helper object
	* @param \phpbb\request\request      $request            Request object
	* @param \phpbb\template\template    $template           Template object
	* @param \phpbb\user                 $user               User object
	* @access public
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request,
	\phpbb\template\template $template, \phpbb\user $user,
	$announcments_table)
	{
		$this->cache = $cache;
		$this->helper = $helper;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->announcements_table = $announcments_table;
	}
	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> 'display_board_announcements',
		);
	}
	/**
	* Display board announcements
	*
	* @return null
	* @access public
	*/
	public function display_board_announcements()
	{
		$cur_page = $this->request->server('REQUEST_URI', '');
		$fragments = explode('?', $cur_page);
		$url = explode ('/', $fragments[0]);
		if (isset($url))
		{
			$page_name = explode('.', end($url));
		}
		$exclude_announces = explode(':', $this->user->data['announce_akn']);
		$sql = 'SELECT * 
				FROM ' . $this->announcements_table . ' 
				WHERE ' . $this->db->sql_in_set('announce_id', $exclude_announces, true, true) . '
					and announce_group ' . $this->db->sql_like_expression($this->db->get_any_char() . ':' . $this->user->data['group_id'] . ':' . $this->db->get_any_char()) . '
					and ';
		if (isset($page_name[0]))
		{
			$sql .= '(announce_page ' . $this->db->sql_like_expression($this->db->get_any_char() . ':' . $page_name[0] . ':' . $this->db->get_any_char()) . ' 
						or ';
		}
		$sql .= 'announce_page ' . $this->db->sql_like_expression($this->db->get_any_char() . ':all:' . $this->db->get_any_char());
		if (isset($page_name[0]))
		{
			$sql .= ')';
		}
		$sql .=	' ORDER BY announce_order';
		$result = $this->db->sql_query($sql);
		$anouncemnts = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$anouncemnts[] = array(
				'announce_id' => $row['announce_id'],
				'announce_title' => $row['announce_title'],
				'announce_content' => $row['announce_content'],
				'announce_bitfield' => $row['announce_bitfield'],
				'announce_options' => $row['announce_options'],
				'announce_uid' => $row['announce_uid'],
				'announce_akn' => $row['announce_akn'],
				'announce_bgcolor' => $row['announce_bgcolor'],
			);
		}
		$this->db->sql_freeresult($result);
		foreach ($anouncemnts as $var)
		{
			$announcement_message = generate_text_for_display(
				$var['announce_content'],
				$var['announce_uid'],
				$var['announce_bitfield'],
				$var['announce_options']
			);
			$this->template->assign_block_vars('anouns', array(
				'ID'	=> $var['announce_id'],
				'BOARD_ANNOUNCEMENT' => $announcement_message,
				'S_BOARD_ANNOUNCEMENT_DISMISS'	=> $var['announce_akn'],
				'BOARD_ANNOUNCEMENT_BGCOLOR'	=> $var['announce_bgcolor'],
				'U_BOARD_ANNOUNCEMENT_CLOSE'	=> $this->helper->route('abannouncements_close', array(
					'announcement_id'	=> $var['announce_id'],
					'hash' => generate_link_hash('close_boardannouncement')
				)),
			));
		}
	}
}
