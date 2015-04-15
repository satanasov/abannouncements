<?php
/**
*
* Board Announcements extension for the phpBB Forum Software package.
* (Thanks/credit to nickvergessen for desigining these tests)
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\abannouncements\tests\event;

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_content.php';
require_once dirname(__FILE__) . '/../../../../../includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/../../../../../includes/bbcode.php';
require_once dirname(__FILE__) . '/../../../../../includes/message_parser.php';

class listener_test extends \phpbb_database_test_case
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

	/** @var \anavaro\abannouncements\event\listener */
	protected $listener;

	protected $cache;
	protected $config;
	protected $controller_helper;
	protected $db;
	protected $request;
	protected $template;
	protected $user;

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
		parent::setUp();

		global $cache, $user, $phpbb_dispatcher;

		// Load the database class
		$this->db = $this->new_dbal();

		// Mock some global classes that may be called during code execution
		$cache = $this->cache = new \phpbb_mock_cache;
		$user = new \phpbb_mock_user;
		$user->optionset('viewcensors', false);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		// Load/Mock classes required by the event listener class
		$this->request = $this->getMock('\phpbb\request\request');
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$this->user = $this->getMock('\phpbb\user', array(), array('\phpbb\datetime'));
		$this->user->data['announce_akn'] = 0;
		$this->user->data['group_id'] = 2;

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper->expects($this->any())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			})
		;
		$_SERVER['REQUEST_URI'] = 'index.php';
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
		), array_keys(\phpbb\boardannouncements\event\listener::getSubscribedEvents()));
	}

	/**
	* Test the display_board_announcements event
	*/
	public function test_display_board_announcements()
	{
		$this->set_listener();

		$this->template->expects($this->exactly(2))->method('assign_block_vars');

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->listener, 'display_board_announcements'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
