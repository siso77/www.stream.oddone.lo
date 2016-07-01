<?php
include_once(APP_ROOT.'/beans/gruppi_merceologici.php');

class ListaCategorie extends DBSmartyAction
{
	var $className;
	
	function ListaCategorie()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		if(!empty($_REQUEST['reset']))	
			unset($_SESSION['reset']);

		if(!empty($_REQUEST['ordinamento']))
		{
			foreach ($_REQUEST['ordinamento'] as $id => $val)
			{
				$BeanCategory = new gruppi_merceologici($this->conn, $id);
				$BeanCategory->setOrder_number($val);
				$BeanCategory->dbStore($this->conn);				
			}
		}
		if(!empty($_REQUEST['delete']))
		{
			$BeanCategory = new gruppi_merceologici();
			if(!empty($_REQUEST['id']))
				$BeanCategory->dbDelete($this->conn, array($_REQUEST['id']), false);
		}
		
		if(!empty($_REQUEST['id_category']))
		{
			$idCategoryToFilter = $_REQUEST['id_category'];
			$this->tEngine->assign('contenuto_precaricato', array('id_category' => $_REQUEST['id_category']));
		}
			
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			unset($_SESSION[$this->className]);
			$this->_redirect('?act='.$this->className);
		}
		else 
		{
			if(isset($_REQUEST['order_by']))
			{
				$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
				$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];				
			}
			else 
			{	
				$_SESSION[$this->className]['order_by'] = 'order_number';
				$_SESSION[$this->className]['order_type'] = 'ASC';
			}
			if(empty($_SESSION[$this->className]['order_by']))
			{
				$_SESSION[$this->className]['order_by'] = 'id';
				$_SESSION[$this->className]['order_type'] = 'ASC';
			}
			$BeanCategory = new gruppi_merceologici();
			$category = $BeanCategory->dbGetCategoryTree($this->conn, $_SESSION[$this->className]['order_by'], $_SESSION[$this->className]['order_type'], $idCategoryToFilter);
			//$category = $BeanCategory->dbGetRootCategory($this->conn);
			$_SESSION[$this->className]['result'] = $category;
			
			$p = new MyPager($category, $this->rowForPage);
			$links = $p->getLinks();
			$this->tEngine->assign("list"	    , $p->getData());
			$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage', $this->numViewPage);
		}

		$this->tEngine->assign('cmb_category', $BeanCategory->dbGetRootCategory($this->conn, 'name', 'ASC'));
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, $this->className.'_'.date('d_m_Y'));
	}	
}
?>