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

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_content.php';
require_once dirname(__FILE__) . '/../../../../../includes/utf/utf_tools.php';

class event_test extends \phpbb_database_test_case
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
	*
	* @return \PHPUnit_Extensions_Database_DataSet_XmlDataSet
	*/
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config_text.xml');
	}
	
	/**
	* Setup test environment
	*/
	public function setUp()
	{
		global $cache, $user, $phpbb_dispatcher, $phpbb_path_helper, $phpbb_container;
		parent::setUp();

		$this->db = $this->new_dbal();
		
		// Mock some global classes that may be called during code execution
		$cache = $this->cache = new \phpbb_mock_cache;
		
		$user = new \phpbb_mock_user;
		$user->optionset('viewcensors', false);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new \phpbb_mock_request()
			),
			new \phpbb\filesystem(),
			$this->getMock('\phpbb\request\request'),
			$phpbb_root_path,
			'php'
		);
		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('path_helper', $phpbb_path_helper);
		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		
		$this->request = $this->getMock('\phpbb\request\request');
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$this->user = $this->getMock('\phpbb\user', array(), array('\phpbb\datetime'));
		
		$_SERVER['REQUEST_URI'] = '';
		
	}

	public function test_install()
	{
		$db_tools = new \phpbb\db\tools($this->db);
		$this->assertTrue($db_tools->sql_table_exists('phpbb_board_announce'));
		$this->assertTrue($db_tools->sql_column_exists('phpbb_users', 'announce_akn'));
	}
	
	/**
	* Create our event listener
	*/
	protected function set_listener()
	{
		$this->listener = new \anavaro\abannouncements\event\listener(
			$this->cache,
			$this->controller_helper,
			$this->db,
			$this->request,
			$this->template,
			$this->user,
			'phpbb_board_announce'
		);
	}
	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$this->set_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}
	
	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.page_header_after',
		), array_keys(\anavaro\abannouncements\event\listener::getSubscribedEvents()));
	}

	/**
	* Test the display_board_announcements event
	*/
	public function test_display_board_announcements()
	{
		$this->user->data['group_id'] = 1;
		$this->set_listener();
		$this->template->expects($this->exactly(2))
			->method('assign_block_vars');
		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->listener, 'display_board_announcements'));
		$dispatcher->dispatch('core.page_header_after');
	}
}