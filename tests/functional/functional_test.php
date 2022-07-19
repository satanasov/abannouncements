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
	public function setUp() : void
	{
		parent::setUp();
	}
	public function test_acp_menu()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('anavaro/abannouncements', 'info_acp_announcements');
		$crawler = self::request('GET', 'adm/index.php?i=-anavaro-abannouncements-acp-announcements_module&mode=main&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('BOARD_ANNOUNCEMENTS'), $crawler->text());
		$this->assertStringContainsString($this->lang('NO_ANNOUNCEMENTS'), $crawler->text());

		$this->logout();
		$this->logout();
	}
	public function test_acp_add_new()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('anavaro/abannouncements', 'info_acp_announcements');
		$crawler = self::request('GET', 'adm/index.php?i=\anavaro\abannouncements\acp\announcements_module&mode=main&act=add&sid=' . $this->sid);
		//$this->assertContains('zazazazaza', $crawler->text());
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'name'	=> 'Test Full',
			'board_announcements_text'	=> 'This is a simple board announcement full viewable by admin and guests on all pages',
		//	'groups'	=> array(1),
		//	'pages'	=> array('all'),
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('BOARD_ANNOUNCEMENTS_CREATED'), $crawler->text());
		$this->logout();
		$this->logout();
	}
}
