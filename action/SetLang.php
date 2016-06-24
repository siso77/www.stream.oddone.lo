<?php

class SetLang extends SmartyAction
{
	function SetLang()
	{
		parent::SmartyAction();
		
		if(!empty($_REQUEST['lang']))
			$_SESSION['lang'] = $_REQUEST['lang'];

		if(!empty($_REQUEST['from']))
			header('Location: '.WWW_ROOT.'?'.$_REQUEST['from']);
		else
			header('Location: '.WWW_ROOT);
		exit();
	}
}
?>