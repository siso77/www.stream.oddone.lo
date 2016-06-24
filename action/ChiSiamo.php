<?php
// include_once(APP_ROOT.'/beans/banner.php');

class ChiSiamo extends SmartyAction
{
	function ChiSiamo()
	{
		parent::SmartyAction();
		
		$this->tEngine->assign('tpl_action', 'ChiSiamo');
		$this->tEngine->display('Index');
	}
}
?>