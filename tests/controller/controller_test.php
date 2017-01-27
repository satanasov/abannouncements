<?php
/**
*
* Advanced Board Announcements extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Lucifer <https://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\abannouncements\tests\controller;

/**
 * @group controller
 */

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';

class controller_test extends \phpbb_database_test_case
{
	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array('anavaro/abannouncements');
	}
	
	protected $db;

	/**
	* Get data set fixtures
	*/
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/users.xml');
	}

	/**
	* Setup test environment
	*/
	public function setUp()
	{
		global $config;
		parent::setUp();

		$this->db = $this->new_dbal();

		$config = $this->config = new \phpbb\config\config(array());
	}

	public function test_install()
	{
		$db_tools = new \phpbb\db\tools\tools($this->db);
		$this->assertTrue($db_tools->sql_table_exists('phpbb_board_announce'));
		$this->assertTrue($db_tools->sql_column_exists('phpbb_users', 'announce_akn'));
	}

	/**
	* Create our controller
	*/
	protected function get_controller($user_id, $is_registered, $mode, $ajax)
	{
		global $request, $phpbb_root_path, $phpEx;
		$this->user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$user = $this->user;
		$user->data['user_id'] = $user_id;
		$user->data['is_registered'] = $is_registered;

		$request = $this->getMock('\phpbb\request\request');
		$request->expects($this->any())
			->method('is_ajax')
			->will($this->returnValue($ajax)
		);
		$request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap(array(
				array('hash', '', false, \phpbb\request\request_interface::REQUEST, generate_link_hash($mode))
			))
		);

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		
		return new \anavaro\abannouncements\controller\ajaxify(
			$this->controller_helper,
			$this->db,
			$request,
			$user,
			'phpbb_board_announce'
		);
	}

	/**
	 * Test data for the test_controller test
	 *
	 * @return array Test data
	 */
	public function controller_data()
	{
		return array(
			array(
				1,
				1, // Guest
				false, // Guest is not a registered user
				'close_boardannouncement',
				true,
				200,
				'{"success":true,"id":1}', // True because a cookie was set
				false, // Guests can't acknowledge announcement 
			),
			array(
				1,
				2, // Member
				true, // Member is a registered user
				'close_boardannouncement',
				true,
				200,
				'{"success":true,"id":1}', // True because a cookie and status were set
				true, // Should be true for users
			),
			array(
				1,
				0, // Invalid member
				true, // Set is_registered to true to test close_announcement() with invalid user_id
				'close_boardannouncement',
				true,
				200,
				'{"success":false,"id":1}', // False because user did not exist
				false, // User not existisng so - no one can akn the message
			),
		);
	}

	/**
	 * Test the controller response under normal conditions
	 *
	 * @dataProvider controller_data
	 */
	public function test_controller($announce_id, $user_id, $is_registered, $mode, $ajax, $status_code, $content, $expected)
	{
		$controller = $this->get_controller($user_id, $is_registered, $mode, $ajax);

		$response = $controller->close($announce_id);
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		$this->assertEquals($status_code, $response->getStatusCode());
		$this->assertEquals($content, $response->getContent());
		$this->assertEquals($expected, $this->check_board_announcement_status($user_id, $announce_id));
	}
	
	/**
	 * Test data for the test_controller_fails test
	 *
	 * @return array Test data
	 */
	public function controller_fails_data()
	{
		return array(
			array(
				1,
				false, // Guest is not a registered user
				'foobar', // Invalid hash
				true,
				1,
				403,
				'NO_AUTH_OPERATION',
			),
			array(
				1,
				false, // Guest is not a registered user
				'', // Empty hash
				true,
				1,
				403,
				'NO_AUTH_OPERATION',
			),
			array(
				1,
				false, // Guest is not a registered user
				'close_boardannouncement',
				true,
				2, // Board Announcement can not be aknowledged
				403,
				'NO_AUTH_OPERATION',
			),
		);
	}
	/**
	 * Test the controller throws exceptions under failing conditions
	 *
	 * @dataProvider controller_fails_data
	 */
	public function test_controller_fails($user_id, $is_registered, $mode, $ajax, $announce_id, $status_code, $content)
	{
		$controller = $this->get_controller($user_id, $is_registered, $mode, $ajax);
		try
		{
			$controller->close($announce_id);
			$this->fail('The expected \phpbb\exception\http_exception was not thrown');
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			$this->assertEquals($status_code, $exception->getStatusCode());
			$this->assertEquals($content, $exception->getMessage());
		}
	}
	
	/**
	 * Helper to get the stored board announcement status for a user
	 *
	 * @param $user_id
	 * @return int
	 */
	protected function check_board_announcement_status($user_id, $announce_id)
	{
		$sql = 'SELECT announce_akn
			FROM phpbb_users
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$status = $this->db->sql_fetchfield('announce_akn');
		$this->db->sql_freeresult($result);
		$akn = explode(':', $status);
		$response = in_array($announce_id, $akn);
		return $response;
	}
}