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

class announcements_info
{
	function module()
	{
		return array(
			'title'		=> 'AB_ANNOUNCEMENTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'	=> array(
					'title' => 'ACP_AB_ANNOUNCEMENTS',
					'auth' => 'ext_anavaro/abannouncements',
					'cat' => array('AB_ANNOUNCEMENTS')
				),
			),
		);
	}
}
