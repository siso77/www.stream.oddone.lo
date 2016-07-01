<?php
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/gruppi_merceologici.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/fornitore.php");

class ListaContenuti extends DBSmartyAction
{
	var $className;

	function setSearchKeys($request)
	{
		unset($request['act']);
		unset($request['search']);
		if(!empty($request))
		{
			if($_SESSION[$this->className]['key_searched']['key_search'] == 'Cerca la parola chiave')
				unset($request['key_search']);

			if(!empty($request['id_category']))
				$_SESSION[$this->className]['key_searched']['id_category'] = $request['id_category'];
			if(!empty($request['id_brand']))
				$_SESSION[$this->className]['key_searched']['id_brand'] = $request['id_brand'];
			if(!empty($request['id_fornitore']))
				$_SESSION[$this->className]['key_searched']['id_fornitore'] = $request['id_fornitore'];
			if(!empty($request['is_in_ecommerce']))
				$_SESSION[$this->className]['key_searched']['is_in_ecommerce'] = $request['is_in_ecommerce'];
			if(!empty($request['is_in_evidence']))
				$_SESSION[$this->className]['key_searched']['is_in_evidence'] = $request['is_in_evidence'];
			if(!empty($request['is_in_offer']))
				$_SESSION[$this->className]['key_searched']['is_in_offer'] = $request['is_in_offer'];
			if(!empty($request['data_from']))
				$_SESSION[$this->className]['key_searched']['data_from'] = $request['data_from'];
			if(!empty($request['data_to']))
				$_SESSION[$this->className]['key_searched']['data_to'] = $request['data_to'];
			if(!empty($request['key_search']) && $request['key_search'] != 'Cerca la parola chiave')
				$_SESSION[$this->className]['key_searched']['key_search'] = $request['key_search'];
			
			if($request['visible'] != '')
				$_SESSION[$this->className]['key_searched']['visible'] = $request['visible'];				
		}
	}

	function ListaContenuti()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		if(!empty($_REQUEST['reset']))
			$_SESSION[$this->className] = null;

		if(!empty($_REQUEST['delete']))
		{
			$BeanContent = new content();
			$BeanContent->dbDelete($this->conn,array($_REQUEST['id']), true);
			Base_CacheCore::getInstance()->clean();
		}

		$this->setSearchKeys($_REQUEST);
		
		if($_REQUEST['visibile'] != '')
			$_SESSION[$this->className]['key_searched']['visibile'] = $_REQUEST['visibile'];
		if($_SESSION[$this->className]['key_searched']['visibile'] != '')
			$this->tEngine->assign("visibile", $_SESSION[$this->className]['key_searched']['visibile']);
			
			if($_REQUEST['stato'] != '')
			$_SESSION[$this->className]['key_searched']['stato'] = $_REQUEST['stato'];
		if($_SESSION[$this->className]['key_searched']['stato'] != '')
			$this->tEngine->assign("stato", $_SESSION[$this->className]['key_searched']['stato']);
			
		if($_REQUEST['in_home'] != '')
			$_SESSION[$this->className]['key_searched']['in_home'] = $_REQUEST['in_home'];
		if($_SESSION[$this->className]['key_searched']['in_home'] != '')
			$this->tEngine->assign("visibile", $_SESSION[$this->className]['key_searched']['in_home']);
		
		$BeanCategory = new gruppi_merceologici();
		$this->tEngine->assign('cmb_category', $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC'));
		

		if(!empty($_REQUEST['export_google_merchant']))
			$this->exportExcelGoogleMerchant();
			
					
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (giacenze.bar_code LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.nome_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.descrizione_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.prezzo_0 LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION[$this->className]['key_search'] = null;
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['order_by'] = null;
				$_SESSION[$this->className]['order_type'] = null;
			}			
		}
		elseif(!empty($_SESSION[$this->className]['key_searched']))
		{
			if($_SESSION[$this->className]['key_searched']['key_search'] != '')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_SESSION[$this->className]['key_searched']['key_search'];
				$where = " AND (giacenze.bar_code LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.nome_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.descrizione_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.prezzo_0 LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%')";
			}
		}
		else
			$where = '';

		if($_SESSION[$this->className]['key_searched']['visibile'] != '')
			$where .= " AND giacenze.visibile = ".$_SESSION[$this->className]['key_searched']['visibile'];
			
		if($_SESSION[$this->className]['key_searched']['stato'] != '')
			$where .= " AND giacenze.stato = '".$_SESSION[$this->className]['key_searched']['stato']."'";
			
		if($_SESSION[$this->className]['key_searched']['stato'] == 'E')
			$where .= " AND giacenze.in_home = 1";

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
			$_SESSION[$this->className]['result'] = null;
		}

		
		if(!empty($_REQUEST['id_brand']))
			$id_brand = $_REQUEST['id_brand'];
		elseif(!empty($_SESSION[$this->className]['key_searched']['id_brand']))
			$id_brand = $_SESSION[$this->className]['key_searched']['id_brand'];
		
		if(!empty($id_brand))
		{
			$where .= " AND content.id_brand = ".$id_brand."";
			$keysSearchedBrand = array('id_brand'=>$id_brand);
			$this->tEngine->assign('id_brand', $id_brand);
		}
		
		if(!empty($_REQUEST['id_category']))
			$id_category = $_REQUEST['id_category'];
		if(!empty($_SESSION[$this->className]['key_searched']['id_category']))
			$id_category = $_SESSION[$this->className]['key_searched']['id_category'];

		if(!empty($id_category))
		{
			$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $id_category);
			if(is_array($ListCategory) && $ListCategory != array())
				$where .= " AND gruppi_merceologici.id IN(".implode(", ", $ListCategory).", ".$id_category.")";
			else
				$where .= " AND gruppi_merceologici.id = ".$id_category."";

			$keysSearchedCategory = array('id_category'=>$id_category);
			$this->tEngine->assign('id_category', $id_category);
		}
		if(is_array($keysSearchedBrand) && is_array($keysSearchedCategory))
			$keysSearched = array_merge($keysSearchedCategory, $keysSearchedBrand);
		elseif(!empty($keysSearchedBrand))
			$keysSearched = $keysSearchedBrand;
		elseif(!empty($keysSearchedCategory))
			$keysSearched = $keysSearchedCategory;
			
		$this->tEngine->assign("contenuto_precaricato", $keysSearched);

		if(!empty($_REQUEST['id_fornitore']))
			$_SESSION[$this->className]['key_searched']['id_fornitore'] = $_REQUEST['id_fornitore'];
		if(!empty($_SESSION[$this->className]['key_searched']['id_fornitore']))
			$this->tEngine->assign("id_fornitore", $_SESSION[$this->className]['key_searched']['id_fornitore']);
		
		if(!empty($_SESSION[$this->className]['order_by']))
			$where .= ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];
		else
			$where .= ' ORDER BY content.data_inserimento_riga DESC';

		if(!empty($_REQUEST['export']))
			$this->exportExcel($where);
		
		$BeanContent = new content();
		$List = $BeanContent->dbSearchDisponibili($this->conn, $where, new giacenze(), $_SESSION[$this->className]['key_searched']['id_fornitore']);
		$_SESSION[$this->className]['result'] = $List;
		
		$p = new MyPager($_SESSION[$this->className]['result'], $this->rowForPage);
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
	
	function exportExcel($where)
	{	
		$BeanContent = new content();
		$List = $BeanContent->dbSearchDisponibili($this->conn, $where, new giacenze(), $_SESSION[$this->className]['key_searched']['id_fornitore']);
		
		
		$fieldToDisplay['CODICE'] = 'bar_code';
		$fieldToDisplay['BAR CODE'] = 'vbn';
		$fieldToDisplay['ARTICOLO'] = 'nome';
		$fieldToDisplay['VASO'] = 'gruppo';
		$fieldToDisplay['NOTE'] = 'note';
		$fieldToDisplay['Q.TA CONFEZIONE'] = 'qta_minima';
		$fieldToDisplay['Q.TA PER PIANALE'] = 'qta_pianale';
		$fieldToDisplay['Q.TA PER C.C.'] = 'qta_carrello';
		$fieldToDisplay['PREZZO LIST.'] = 'prezzo_0';
		$fieldToDisplay['PREZZO A.V.R.'] = 'prz';
		$fieldToDisplay['ORDINE PIANALI'] = 'ord';
		$fieldToDisplay['FOTO'] = 'img';
		$fieldToDisplay['COSTO'] = 'prezzo_acquisto';
		$fieldToDisplay['FORNITORE'] = 'fornitore';
			
		foreach ($List as $key=>$val)
		{
			if($_REQUEST['lang'] == 'it')
			{
				if($val['visibile'] == 0)
					continue;
			}
			elseif($val['visibile_'.$_REQUEST['lang']] == 0)
				continue;

			$val['nome_'.$_REQUEST['lang']] = str_replace("'", '`', $val['nome_'.$_REQUEST['lang']]);
			$val['descrizione_'.$_REQUEST['lang']] = str_replace("'", '`', $val['descrizione_'.$_REQUEST['lang']]);

			$BeanGruppi = new gruppi_merceologici($this->conn, $val['id_gm']);
			if($BeanGruppi->parent_id != 0)
				$BeanGruppi = new gruppi_merceologici($this->conn, $BeanGruppi->parent_id);
			if($BeanGruppi->parent_id != 0)
				$BeanGruppi = new gruppi_merceologici($this->conn, $BeanGruppi->parent_id);

			$BeanGruppi = $BeanGruppi->vars();
			
			$data[$key]['bar_code'] = $val['bar_code'];
			$data[$key]['vbn'] = $val['vbn'];

			if(empty($val['nome_'.$_REQUEST['lang']]))
				$data[$key]['nome'] = utf8_encode($val['nome_it']);
			else
				$data[$key]['nome'] = utf8_encode($val['nome_'.$_REQUEST['lang']]);
			if($_REQUEST['lang'] == 'it')
				$data[$key]['gruppo'] = $BeanGruppi['gruppo'];
			else
				$data[$key]['gruppo'] = $BeanGruppi['name_'.$_REQUEST['lang']];

			if($_REQUEST['lang'] == 'it')
				$data[$key]['note'] = html_entity_decode(str_replace('</span>', '', str_replace('<br />', '', str_replace('<span style="color:#FF0000">', '', str_replace('<em>', '', str_replace('</em>', '', str_replace('<div>', '', str_replace('</div>', '', str_replace('<u>', '', str_replace('</u>', '', str_replace('</strong>', '', str_replace('<strong>', '', str_replace('</p>', '', str_replace('<p>', '', $val['note']))))))))))))));
			else
			{
				if(empty($val['descrizione_'.$_REQUEST['lang']])) 
					$data[$key]['note'] = html_entity_decode(str_replace('</span>', '', str_replace('<br />', '', str_replace('<span style="color:#FF0000">', '', str_replace('<em>', '', str_replace('</em>', '', str_replace('<div>', '', str_replace('</div>', '', str_replace('<u>', '', str_replace('</u>', '', str_replace('</strong>', '', str_replace('<strong>', '', str_replace('</p>', '', str_replace('<p>', '', $val['descrizione_it']))))))))))))));
				else 
					$data[$key]['note'] = html_entity_decode(str_replace('</span>', '', str_replace('<br />', '', str_replace('<span style="color:#FF0000">', '', str_replace('<em>', '', str_replace('</em>', '', str_replace('<div>', '', str_replace('</div>', '', str_replace('<u>', '', str_replace('</u>', '', str_replace('</strong>', '', str_replace('<strong>', '', str_replace('</p>', '', str_replace('<p>', '', $val['descrizione_'.$_REQUEST['lang']]))))))))))))));
			}
			$data[$key]['qta_minima'] = $val['qta_minima'];
			$data[$key]['qta_pianale'] = $val['qta_pianale'];
			$data[$key]['qta_carrello'] = $val['qta_carrello'];
			$data[$key]['prezzo_0'] = $val['prezzo_0'];
			$data[$key]['prz'] = $val['prz'];
			$data[$key]['ord'] = $val['ord'];
			$img = $this->tEngine->getImagePathFromIdContent($val['id'], '');
				
			$data[$key]['img'] = $img;
			$data[$key]['prezzo_acquisto'] = $val['prezzo_acquisto'];
			
			$BeanFornitori = new fornitore($this->conn, $val['id_fornitore']);				
			$data[$key]['fornitore'] = $BeanFornitori->nome;
		}
		
		$this->exportExcelData($data, $fieldToDisplay, 'lista_content_'.date('d_m_Y'));
	}

	function exportExcelGoogleMerchant()
	{
	}
}
?>