<?php
class SetCalendar extends SmartyAction
{
	function SetCalendar()
	{
		parent::SmartyAction();
		
		if(!empty($_REQUEST['date']))
			$_SESSION['user_choice']['date'] = $_REQUEST['date'];

		$this->_redirect('');
	}
}
?>