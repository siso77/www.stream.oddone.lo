<?php
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");
include_once(APP_ROOT."/beans/giacenze.php");

class Charts extends DBSmartyAction
{
	var $className;
	
	function Charts()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanEcmBasket = new ecm_basket();
			$BeanEcmBasket->dbDelete($this->conn,array($_REQUEST['id']), true);
			$this->_redirect('?act=Charts&reset=true');
		}
					
		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
		}
		
		$BeanEcmBasket = new ecm_basket();
		$data = $BeanEcmBasket->dbGetAll($this->conn);
	
//		foreach($data as $key => $value)
//		{
//			$BeanEcmBasketMagazzino = new ecm_basket_magazzino();
//			$data[$key]['ordine_magazzino'] = $BeanEcmBasketMagazzino->dbGetAllByIdBasket($this->conn, $value['id']);
//			
//			foreach ($data[$key]['ordine_magazzino'] as $k => $val)
//			{
//				$beanGiacenze = new giacenze($this->conn, $val['id_magazzino']);
//				$data[$key]['ordine_magazzino'][$k]['giacenze'] = $beanGiacenze->vars();
//			}
//		}
		$p = new MyPager($data, $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		$this->tEngine->assign('keys_searched', $_SESSION[$this->className]['key_searched']);
		$this->tEngine->assign('action_class_name', $this->className);
		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, 'lista_content_'.date('d_m_Y'));
	}	
}
?>