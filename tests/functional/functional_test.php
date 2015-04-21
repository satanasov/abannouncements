<?php
/**
*
* Advanced Board Announcements Functional test
*
* @copyright (c) 2014 Stanislav Atanasov
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/
namespace anavaro\abannouncements\tests\functional;
/**
* @group functional
*/
class functional_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('anavaro/abannouncements');
	}
	public function setUp()
	{
		parent::setUp();
	}
	public function test_acp_menu($ext, $lang, $path, $search)
	{
		$this->login();
		$this->admin_login();
		
		$this->add_lang_ext('anavaro/abannouncements', 'info_acp_announcements');
		$crawler = self::request('GET', $path . '&sid=' . $this->sid);
		$this->assertContainsLang($this->lang('BOARD_ANNOUNCEMENTS'), $crawler->text());
		$this->assertContainsLang($this->lang('NO_ANNOUNCEMENTS'), $crawler->text());
		
		$this->logout();
		$this->logout();
	}
}
