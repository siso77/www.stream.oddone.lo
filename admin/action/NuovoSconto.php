<?php
include_once(APP_ROOT.'/beans/sconti.php');

class NuovoSconto extends DBSmartyAction
{
	var $className;
	
	function NuovoSconto()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{			
			$BeanBrand = new sconti($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanBrand->vars());
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanBrand = new sconti($this->conn, $_REQUEST['id']);
			else
				$BeanBrand = new sconti();

			$BeanBrand->fill($_REQUEST);
			$id = $BeanBrand->dbStore($this->conn);

			$params = '&id='.$id.'&edit=1';
			if(!empty($_REQUEST['id']))
				$params = '&id='.$_REQUEST['id'].'&edit=1';

			$this->_redirect('?act='.$this->className.$params);
		}
		
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}	
}