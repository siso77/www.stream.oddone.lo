<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");

include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

class Orders extends DBSmartyAction
{
	var $className;
	
	function Orders()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(empty($_SESSION['LoggedUser']))
			$this->_redirect('?act=Login');
			
		if(!empty($_REQUEST['export']))
			$this->exportExcel();

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
		}
		
		$BeanEcmOrdini = new ecm_ordini();
		$ordini = $BeanEcmOrdini->dbGetAllByIdUser($this->conn, $_REQUEST['user_id']);
		foreach ($ordini as $key => $value)
		{
			$data['ordini'][$key] = $value;
			$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
			$ordini_magazzino = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $value['id']);
			if($ordini_magazzino == array())
				continue;
			$tot = 0;
			foreach ($ordini_magazzino as $k => $val)
			{
				$tot+=str_replace(',','.', $val['importo']);
				$data['ordini'][$key]['prodotti'][$k] = $val;
			}
		}
		
		$p = new MyPager($data['ordini'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('action_class_name', $this->className);
		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
//		$this->tEngine->display($this->className);
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, 'lista_content_'.date('d_m_Y'));
	}	
}
?>