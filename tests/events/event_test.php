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
 * @group event
 */

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
	public function setUp() : void
	{
		global $cache, $user, $phpbb_dispatcher, $phpbb_path_helper, $phpbb_container, $phpbb_root_path, $db, $config, $phpbb_filesystem, $phpEx;
		parent::setUp();

		$this->db = $this->new_dbal();
		$db = $this->db; //We need to set global variable for bbcode
		// Mock some global classes that may be called during code execution
		$cache = $this->cache = new \phpbb_mock_cache;

		$this->user = $this->getMockBuilder('\phpbb\user')
			->setConstructorArgs(array(
				new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
				'\phpbb\datetime'
			))
			->getMock();

		$this->user->optionset('viewcensors', false);
		$this->user->style['style_path'] = 'prosilver';
		$user = $this->user;
		// Load/Mock classes required by the event listener class
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new \phpbb_mock_request()
			),
			new \phpbb\filesystem\filesystem(),
			$this->request,
			$phpbb_root_path,
			'php'
		);
		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('path_helper', $phpbb_path_helper);

		$config = new \phpbb\config\config(array());
		$phpbb_container->set('config', $config);

		$phpbb_filesystem = $filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_container->set('filesystem', $filesystem);

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();

		$extension_manager = new \phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'anavaro/abannouncements' => array(
					'ext_name' => 'anavaro/abannouncements',
					'ext_active' => '1',
					'ext_path' => 'ext/anavaro/abannouncements/',
				),
			)
		);

		$phpbb_container->set('ext.manager', $extension_manager);

		$phpbb_container->setParameter('core.cache_dir', $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');

		$phpbb_container->set('user', $user);

		$context = new \phpbb\template\context();
		$this->environment = $this->getMockBuilder('\phpbb\template\twig\environment')
			->disableOriginalConstructor()
			->getMock();
		$twig_extension = new \phpbb\template\twig\extension($context, $this->environment, $this->language);

		$phpbb_container->set('template.twig.extensions.phpbb', $twig_extension);

		$twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$twig_extensions_collection->add('template.twig.extensions.phpbb');
		$phpbb_container->set('template.twig.extensions.collection', $twig_extensions_collection);

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();

		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$this->template['bbcode_bitfield'] = '';


		$_SERVER['REQUEST_URI'] = '/index.php';
	}

	public function test_install()
	{
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->db);
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
	 * Test data for the test_display_board_announcements test
	 *
	 * @return array Test data
	 */
	public function display_board_announcements_data()
	{
		return array(
			'base_case' => array(
				1, //User group_id
				':', // User Acknowledged string
				'', // User on page
				3 //expected count
			),
			'limit_by_group' => array(
				2, //User group_id
				':', // User Acknowledged string
				'', // User on page
				2 //expected count
			),
			'akn_1' => array(
				1, //User group_id
				':1:', // User Acknowledged string
				'', // User on page
				2 //expected count
			),
			'base_case_on_index' => array(
				1, //User group_id
				':', // User Acknowledged string
				'somepath/index.php', // User on page
				3 //3 //expected count
			),
			'base_case_on_ucp' => array(
				1, //User group_id
				':', // User Acknowledged string
				'somepath/ucp.php', // User on page
				3 //3 //expected count
			),
		);
	}
	/**
	* Test the display_board_announcements event
	* @dataProvider display_board_announcements_data
	*/
	public function test_display_board_announcements($group_id, $akn_string, $base_uri, $expected)
	{
		$this->user->data['group_id'] = $group_id;
		$this->user->data['announce_akn'] = $akn_string;
		$_SERVER['REQUEST_URI'] = $base_uri;
		$this->set_listener();
		$this->template->expects($this->exactly($expected))
			->method('assign_block_vars');
		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->listener, 'display_board_announcements'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
