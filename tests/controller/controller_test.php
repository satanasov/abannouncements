<?php
/**
*
* Board Announcements extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\abannouncements\tests\controller;

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
		parent::setUp();

		$this->db = $this->new_dbal();
	}

	public function test_install()
	{
		$sql = 'SELECT * FROM phpbb_ext WHERE ext_name = \'anavaro/abannouncements\'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$this->assertEquals(1, $row['ext_active']);
		$this->assertContains('b:0;', $row['ext_state']);
	}
}