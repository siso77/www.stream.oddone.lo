<?php
//ini_set('display_errors', 'On');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/gruppi_merceologici.php");
class Search extends DBSmartyAction
{
	var $className;
	var $prefixCacheKey = 'ecm_content_search_';

	var $limit;
	var $limit_start;
	var $limit_end;

	function Search()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		if($_SESSION['LoggedUser']['pwd_resetted'])
			$this->tEngine->assign('reset_password', true);

		if(!empty($_REQUEST['reset']))
		{
			unset($_SESSION[$this->className]['key_alpha']);
			unset($_SESSION[$this->className]['key_search']);
			unset($_SESSION[$this->className]['order_by']);
			unset($_SESSION[$this->className]['order_type']);
			unset($_SESSION[$this->className]['result']);
			unset($_SESSION[$this->className]['colore']);
			unset($_SESSION[$this->className]['tipo_colore']);
			unset($_SESSION[$this->className]['gm']);
			unset($_SESSION[$this->className]['famiglia']);
			unset($_SESSION[$this->className]['name']);
			unset($_SESSION[$this->className]['price_from']);
			unset($_SESSION[$this->className]['price_to']);
			unset($_SESSION[$this->className]['C4']);
			unset($_SESSION[$this->className]['C5']);
			unset($_SESSION[$this->className]['openstage']);
			unset($_SESSION[$this->className]['origin']);
		}
		
		$this->assignSearchFields();
		$this->setKeySearchInSession();
		
		if(empty($_REQUEST['pageID']))
		{
			$this->limit_start = 0;
			$this->limit_end = $this->rowForPage;
// 			$this->limit = ' LIMIT '.$this->limit_start.','.$this->limit_end;
			$_REQUEST['pageID'] = 1;
		}
		else
		{
			$this->limit_start = ($this->rowForPage * $_REQUEST['pageID']) - $this->rowForPage;
			$this->limit_end = $this->rowForPage;
// 			$this->limit = ' LIMIT '.$this->limit_start.','.$this->limit_end;
		}

		if(!empty($_REQUEST['layout']))
			$_SESSION[$this->className]['layout'] = $_REQUEST['layout'];
		
		if(!empty($_REQUEST['reset']))
		{
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
			$_SESSION[$this->className]['result'] = null;
		}
// 		$_REQUEST['only_disp'] = true;
		$_REQUEST['only_disp'] = true;
		if(!empty($_REQUEST['only_disp']))
		{
			$_SESSION[$this->className]['all_disp'] = false;
			$_SESSION[$this->className]['only_disp'] = true;
			
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
		}
		elseif($_REQUEST['all_disp'])
		{
			$_SESSION[$this->className]['only_disp'] = false;
			$_SESSION[$this->className]['all_disp'] = true;
			
			$_SESSION[$this->className]['order_by'] = null;
			$_SESSION[$this->className]['order_type'] = null;
		}

		if(empty($_SESSION[$this->className]['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = 'content.nome_it';
		}

		if(!empty($_SESSION[$this->className]['only_disp']))
			$dataList = $this->getDispoData();
		elseif(!empty($_SESSION[$this->className]['all_disp']))
			$dataList = $this->getDefaultData();
		else
		{
			$_SESSION[$this->className]['only_disp'] = true;
			$_SESSION[$this->className]['all_disp'] = false;
			$dataList = $this->getDispoData();
		}
		
		$BeanContent = new content();
		$price_to = $BeanContent->dbFree($this->conn, "SELECT MAX(giacenze.".$this->key_prezzo.") as MAX FROM content INNER JOIN famiglie ON content.id_famiglia = famiglie.id INNER JOIN gruppi_merceologici ON content.id_gm = gruppi_merceologici.id INNER JOIN giacenze ON giacenze.id_content = content.id");
		$price_from = $BeanContent->dbFree($this->conn, "SELECT MIN(giacenze.".$this->key_prezzo.") as MIN FROM content INNER JOIN famiglie ON content.id_famiglia = famiglie.id INNER JOIN gruppi_merceologici ON content.id_gm = gruppi_merceologici.id INNER JOIN giacenze ON giacenze.id_content = content.id");

		$this->tEngine->assign('default_price_from'  , (empty($price_from[0]['MIN'])) ? 0 : round($price_from[0]['MIN']));
		$this->tEngine->assign('default_price_to'  , round($price_to[0]['MAX']));

		$p = new MyPager($dataList['content'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign('content', $p->getData());
		
		$this->tEngine->assign('tot_items'  , $dataList['num_contents']['num']);
// 		$this->tEngine->assign('last_page'  , ( round(($dataList['num_contents']['num'] / $this->rowForPage)) == 0)? 1 : round(($dataList['num_contents']['num'] / $this->rowForPage)));
		$p->pager->_currentPage = $_REQUEST['pageID'];

		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);

		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		
		if(!empty($_SESSION[$this->className]['all_disp']))
			$this->tEngine->assign('all_disp', true);
		if(!empty($_SESSION[$this->className]['only_disp']))
			$this->tEngine->assign('only_disp', true);
		
		if($this->IsMobileDevice)
		{
			$this->tEngine->assign('tpl_action', 'Search');
			$this->tEngine->display('Index');
		}
		elseif(empty($_SESSION[$this->className]['layout']) || empty($_REQUEST['is_ajax']))
		{
			if(empty($_SESSION[$this->className]['layout']))
			{
				$this->tEngine->assign('tpl_action', DEFAULT_LAYOUT_DISPLAY);
				$_SESSION[$this->className]['layout'] = DEFAULT_LAYOUT_DISPLAY_SESSION;
			}
			else
			{
				switch ($_SESSION[$this->className]['layout'])
				{
					case 'grid':
						$this->tEngine->assign('tpl_action', 'SearchListDetailed');
					break;
					case 'boxed':
						$this->tEngine->assign('tpl_action', 'SearchBoxed');
					break;
					case 'thumb':
						$this->tEngine->assign('tpl_action', 'SearchThumb');
					break;
				}
			}
				
			$this->tEngine->display('Index');
		}
		else 
		{
			switch ($_SESSION[$this->className]['layout'])
			{
				case 'grid':
					echo $this->tEngine->fetch('SearchListDetailed');
				break;
				case 'boxed':
					echo $this->tEngine->fetch('SearchBoxed');
				break;
				case 'thumb':
					echo $this->tEngine->fetch('SearchThumb');
				break;
			}
		}
	}
	
	function setKeySearchInSession()
	{
		if(!empty($_REQUEST['key_alpha']))
		{
			$_SESSION[$this->className]['key_alpha'] = $_REQUEST['key_alpha'];
			unset($_SESSION[$this->className]['key_search']);
			unset($_SESSION[$this->className]['order_by']);
			unset($_SESSION[$this->className]['order_type']);
			unset($_SESSION[$this->className]['result']);
			unset($_SESSION[$this->className]['colore']);
			unset($_SESSION[$this->className]['tipo_colore']);
			unset($_SESSION[$this->className]['gm']);
			unset($_SESSION[$this->className]['famiglia']);
			unset($_SESSION[$this->className]['name']);
			unset($_SESSION[$this->className]['price_from']);
			unset($_SESSION[$this->className]['price_to']);
			unset($_SESSION[$this->className]['C4']);
			unset($_SESSION[$this->className]['C5']);
			unset($_SESSION[$this->className]['openstage']);
			unset($_SESSION[$this->className]['origin']);
		}		
		if($_REQUEST['colore'] == '')
			$_SESSION[$this->className]['colore'] = null;
		if($_REQUEST['tipo_colore'] == '')
			$_SESSION[$this->className]['tipo_colore'] = null;
		if($_REQUEST['gm'] == '')
			$_SESSION[$this->className]['gm'] = null;
		if($_REQUEST['famiglia'] == '')
			$_SESSION[$this->className]['famiglia'] = null;
		if($_REQUEST['name'] == '')
			$_SESSION[$this->className]['name'] = null;
		if($_REQUEST['price_from'] == '')
			$_SESSION[$this->className]['price_from'] = null;
		if($_REQUEST['price_to'] == '')
			$_SESSION[$this->className]['price_to'] = null;

		
		if($_REQUEST['C4'] == '')
			$_SESSION[$this->className]['C4'] = null;
		if($_REQUEST['C5'] == '')
			$_SESSION[$this->className]['C5'] = null;
		if($_REQUEST['openstage'] == '')
			$_SESSION[$this->className]['openstage'] = null;
		if($_REQUEST['promo'] == '')
			$_SESSION[$this->className]['promo'] = null;
		if($_REQUEST['origin'] == '')
			$_SESSION[$this->className]['origin'] = null;
		if($_REQUEST['key_alpha'] == '')
			$_SESSION[$this->className]['key_alpha'] = null;
			
				
		$is_empty = true;
		if(!empty($_SESSION[$this->className]['colore']))
		{
			$_REQUEST['colore'] = $_SESSION[$this->className]['colore'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['tipo_colore']))
		{
			$_REQUEST['tipo_colore'] = $_SESSION[$this->className]['tipo_colore'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['gm']))
		{
			$_REQUEST['gm'] = $_SESSION[$this->className]['gm'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['famiglia']))
		{
			$_REQUEST['famiglia'] = $_SESSION[$this->className]['famiglia'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['name']))
		{
			$_REQUEST['name'] = $_SESSION[$this->className]['name'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['price_from']))
		{
			$_REQUEST['price_from'] = $_SESSION[$this->className]['price_from'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['price_to']))
		{
			$_REQUEST['price_to'] = $_SESSION[$this->className]['price_to'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['C4']))
		{
			$_REQUEST['C4'] = $_SESSION[$this->className]['C4'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['C5']))
		{
			$_REQUEST['C4'] = $_SESSION[$this->className]['C5'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['openstage']))
		{
			$_REQUEST['C4'] = $_SESSION[$this->className]['openstage'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['promo']))
		{
			$_REQUEST['promo'] = $_SESSION[$this->className]['promo'];
			$is_empty = false;
		}
		if(!empty($_SESSION[$this->className]['origin']))
		{
			$_REQUEST['origin'] = $_SESSION[$this->className]['origin'];
			$is_empty = false;
		}
		
		if(!$is_empty)
			$_SERVER['REQUEST_METHOD'] = 'POST';
	}
	
	function getDispoData()	
	{
		$BeanContent = new content();
// 		if( ($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['order_by'])) && empty($_REQUEST['is_ajax']) )
		if( $_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['order_by']) || !empty($_REQUEST['pageID']))
		{
			if($_REQUEST['key_search'] == 'Cerca in tutto lo store...')
			{
				$_REQUEST['key_search'] = null;
				$_SESSION[$this->className]['key_search'] = null;
			}
			if(!empty($_REQUEST['order_by']))
			{
				$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
				$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
				$_SESSION[$this->className]['result'] = null;
			}
			if(!empty($_REQUEST['key_search']))
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
			if(!empty($_SESSION[$this->className]['key_search']))
			{
				$where .= " (content.nome_it LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " content.descrizione_it LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " content.nome_en LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " content.descrizione_en LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " giacenze.bar_code LIKE '%".$_SESSION[$this->className]['key_search']."%' OR ";
				$where .= " content.vbn LIKE '%".$_SESSION[$this->className]['key_search']."%' ) AND";
				$_SESSION[$this->className]['order_type'] = 'DESC';
			}
			if(!empty($_SESSION[$this->className]['key_alpha']))
				$where .= " content.nome_".$_SESSION['lang']." LIKE '".$_SESSION[$this->className]['key_alpha']."%' AND";
				
			if(!empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " giacenze.".$this->key_prezzo." BETWEEN ".$_REQUEST['price_from']." AND ".$_REQUEST['price_to']." OR ";
			elseif(!empty($_REQUEST['price_from']) && empty($_REQUEST['price_to']))
				$where .= " giacenze.".$this->key_prezzo." > ".$_REQUEST['price_from']." OR ";
			elseif(empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
				$where .= " giacenze.".$this->key_prezzo." < ".$_REQUEST['price_to']." OR ";

			$this->tEngine->assign('menu_gm_selected', $_REQUEST['gm']);
			if(!empty($_REQUEST['famiglia']) && $_REQUEST['famiglia'] != 'empty')
			{
				$where .= " famiglie.id = '".$_REQUEST['famiglia']."' OR";
				$_REQUEST['gm'] = null;
				$_SESSION[$this->className]['gm'] = null;
			}
			if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
			{
				$BeanGrupppi = new gruppi_merceologici($this->conn, $_REQUEST['gm']);
				if($BeanGrupppi->getParent_id() == 0)
				{
					$cates = $BeanGrupppi->dbGetCategoryByParentId($this->conn, $BeanGrupppi->getId());
					$where .= " giacenze.id_gm IN(".implode(', ', $cates).") AND";
				}
				else
					$where .= " giacenze.id_gm = ".$_REQUEST['gm']." AND";
				$_REQUEST['famiglia'] = null;
				$_SESSION[$this->className]['famiglia'] = null;
			}
			if(!empty($_REQUEST['colore']) && $_REQUEST['colore'] != 'empty')
				$where .= " giacenze.C3 LIKE '%".$_REQUEST['colore']."%' AND";
// 			if(!empty($_REQUEST['tipo_colore']) && $_REQUEST['tipo_colore'] != 'empty')
// 				$where .= " tipo_colore LIKE '%".$_REQUEST['tipo_colore']."%' OR";
// 			if(!empty($_REQUEST['C4']) && $_REQUEST['C4'] != 'empty')
// 				$where .= " giacenze.C4 LIKE '%".$_REQUEST['C4']."%' OR";

			if(!empty($_SESSION[$this->className]['C4']) && $_SESSION[$this->className]['C4'] != 'empty')
				$where .= " giacenze.C4 LIKE '%".$_SESSION[$this->className]['C4']."%' OR";
			if(!empty($_SESSION[$this->className]['C5']) && $_SESSION[$this->className]['C5'] != 'empty')
				$where .= " giacenze.C5 LIKE '%".$_SESSION[$this->className]['C5']."%' OR";
			if(!empty($_SESSION[$this->className]['openstage']) && $_SESSION[$this->className]['openstage'] != 'empty')
				$where .= " giacenze.openstage LIKE '%".$_SESSION[$this->className]['openstage']."%' OR";
			if(!empty($_SESSION[$this->className]['promo']) && $_SESSION[$this->className]['promo'] != 'empty')
				$where .= " giacenze.promo LIKE '%".$_SESSION[$this->className]['promo']."%' OR";
			if(!empty($_SESSION[$this->className]['origin']) && $_SESSION[$this->className]['origin'] != 'empty')
				$where .= " giacenze.C5 LIKE '%".$_SESSION[$this->className]['origin']."%' OR";
			$where .= " content.nome_".$_SESSION['lang']." IS NOT NULL AND content.nome_".$_SESSION['lang']." != '' OR";
				
			$where = substr($where, 0, -3);

			if(!empty($where))
			{
				$where = " AND (".$where;
				$where .= ")";
			}
//if(!empty($_SESSION[$this->className]['display_prod_img']))
//	$where.= ' AND giacenze.have_image = 1';

			//nuova logica alex 7-6-16
			
//			if ($_SESSION['lang'] == 'it')
//			$where.= ' AND giacenze.visibile = 1';
//			if ($_SESSION['lang'] == 'en')
//			$where.= ' AND giacenze.visibile_en = 1';
//			if ($_SESSION['lang'] == 'fr')
//			$where.= ' AND giacenze.visibile_fr = 1';
//			if ($_SESSION['lang'] == 'de')
//			$where.= ' AND giacenze.visibile_de = 1';
			
			//fine nuova logica
			
			//vecchia logica
			
			$where.= ' AND giacenze.visibile = 1';
			
			// fine vecchia logica
			
			if($_SESSION[$this->className]['order_by'] == 'stato_novita')
			{
				$this->tEngine->assign('search_stato', 'novita');
				$_SESSION[$this->className]['order_by'] = 'stato, content.nome_it';
				$_SESSION[$this->className]['order_type'] = 'ASC';
				$where.= " AND stato = 'N'";
			}
			elseif($_SESSION[$this->className]['order_by'] == 'stato_evidenza')
			{
				$this->tEngine->assign('search_stato', 'evidenza');
				$_SESSION[$this->className]['order_by'] = 'content.nome_it';
				$_SESSION[$this->className]['order_type'] = 'ASC';
				$where.= " AND giacenze.in_home = '1'";
			}
			elseif($_SESSION[$this->className]['order_by'] == 'stato_offerta')
			{
				$this->tEngine->assign('search_stato', 'offerta');
				$_SESSION[$this->className]['order_by'] = 'stato, content.nome_it';
				$_SESSION[$this->className]['order_type'] = 'ASC';
				$where.= " AND stato = 'O'";
			}

			if(!empty($_SESSION[$this->className]['order_by']))
				$order = ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];

			$configCacheKey = $this->prefixCacheKey.'_disp'.md5($where.$order.$this->limit);
			$content = Base_CacheCore::getInstance()->load($configCacheKey);

			if (empty($content)) 
			{
				$content = $BeanContent->dbSearchDisponibili($this->conn, $where.$order.$this->limit);

				if(!empty($content) && CACHE_PRODUCTS)
					Base_CacheCore::getInstance()->save($content, $configCacheKey);
			}
		}
		else
		{
			if(!empty($_SESSION[$this->className]['order_by']))
				$order = ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];

			$configCacheKey = $this->prefixCacheKey.'_disp'.md5($this->limit);
			if (!$content = Base_CacheCore::getInstance()->load($configCacheKey)) 
			{
				$content = $BeanContent->dbSearchDisponibili($this->conn, $where.$order.$this->limit);

				if(!empty($content) && CACHE_PRODUCTS)
					Base_CacheCore::getInstance()->save($content, $configCacheKey);
			}
		}

		$configCacheKey = 'num_content_disp'.md5($where.$this->limit);
		if (!$num_contents = Base_CacheCore::getInstance()->load($configCacheKey))
		{
			$num_contents = $BeanContent->dbSearchCountedDisponibili($this->conn, $where);
			if(!empty($content) && CACHE_PRODUCTS)
				Base_CacheCore::getInstance()->save($num_contents, $configCacheKey);
		}

		return array('content'=>$content, 'num_contents'=>$num_contents);		
	}
	
	function assignSearchFields()
	{
		if(!empty($_REQUEST['colore']) && $_REQUEST['colore'] != 'empty')
			$_SESSION[$this->className]['colore'] = $_REQUEST['colore'];
		elseif($_REQUEST['colore'] == 'empty')
			$_SESSION[$this->className]['colore'] = null;
		
		if(!empty($_REQUEST['tipo_colore']) && $_REQUEST['tipo_colore'] != 'empty')
			$_SESSION[$this->className]['tipo_colore'] = $_REQUEST['tipo_colore'];
		elseif($_REQUEST['tipo_colore'] == 'empty')
			$_SESSION[$this->className]['tipo_colore'] = null;
		
		if(!empty($_REQUEST['gm']) && $_REQUEST['gm'] != 'empty')
			$_SESSION[$this->className]['gm'] = $_REQUEST['gm'];
		elseif($_REQUEST['gm'] == 'empty')
			$_SESSION[$this->className]['gm'] = null;
		
		if(!empty($_REQUEST['famiglia']) && $_REQUEST['famiglia'] != 'empty')
			$_SESSION[$this->className]['famiglia'] = $_REQUEST['famiglia'];
		elseif($_REQUEST['famiglia'] == 'empty')
			$_SESSION[$this->className]['famiglia'] = null;
		
		if(!empty($_REQUEST['name']) && $_REQUEST['name'] != 'empty')
			$_SESSION[$this->className]['name'] = $_REQUEST['name'];
		elseif($_REQUEST['name'] == 'empty')
			$_SESSION[$this->className]['name'] = null;
		
		if(!empty($_REQUEST['price_from']))
			$_SESSION[$this->className]['price_from'] = $_REQUEST['price_from'];
		if(!empty($_REQUEST['price_to']))
			$_SESSION[$this->className]['price_to'] = $_REQUEST['price_to'];

		if(!empty($_REQUEST['C4']))
			$_SESSION[$this->className]['C4'] = $_REQUEST['C4'];
		elseif($_REQUEST['C4'] == 'empty')
			$_SESSION[$this->className]['C4'] = null;
		
		if(!empty($_REQUEST['C5']))
			$_SESSION[$this->className]['C5'] = $_REQUEST['C5'];
		elseif($_REQUEST['C5'] == 'empty')
			$_SESSION[$this->className]['C5'] = null;
		
		if(!empty($_REQUEST['openstage']))
			$_SESSION[$this->className]['openstage'] = $_REQUEST['openstage'];
		elseif($_REQUEST['openstage'] == 'empty')
			$_SESSION[$this->className]['openstage'] = null;
		
		if(!empty($_REQUEST['origin']))
			$_SESSION[$this->className]['origin'] = $_REQUEST['origin'];
		elseif($_REQUEST['origin'] == 'empty' || empty($_REQUEST['origin']))
			$_SESSION[$this->className]['origin'] = null;
		
		if(!empty($_REQUEST['display_prod_img']))
			$_SESSION[$this->className]['display_prod_img'] = $_REQUEST['display_prod_img'];
		elseif($_REQUEST['display_prod_img'] == 'empty' || empty($_REQUEST['display_prod_img']))
			$_SESSION[$this->className]['display_prod_img'] = null;
				
		$assignSearchFields['C4'] = $_SESSION[$this->className]['C4'];
		$assignSearchFields['C5'] = $_SESSION[$this->className]['C5'];
		$assignSearchFields['openstage'] = $_SESSION[$this->className]['openstage'];
		$assignSearchFields['promo'] = $_SESSION[$this->className]['promo'];
		
		$assignSearchFields['colore'] = $_SESSION[$this->className]['colore'];
		$assignSearchFields['tipo_colore'] = $_SESSION[$this->className]['tipo_colore'];
		$assignSearchFields['gm'] = $_SESSION[$this->className]['gm'];
		$assignSearchFields['famiglia'] = $_SESSION[$this->className]['famiglia'];
		$assignSearchFields['name'] = $_SESSION[$this->className]['name'];
		$assignSearchFields['price_from'] = $_SESSION[$this->className]['price_from'];
		$assignSearchFields['price_to'] = $_SESSION[$this->className]['price_to'];
		$assignSearchFields['origin'] = $_SESSION[$this->className]['origin'];
		$assignSearchFields['display_prod_img'] = $_SESSION[$this->className]['display_prod_img'];
		
		$this->tEngine->assign('search', $assignSearchFields);		
	}
}
?>