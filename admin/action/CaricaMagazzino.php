<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/beans/images.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/fornitore.php");
include_once(APP_ROOT."/beans/fornitore_srl.php");
// include_once(APP_ROOT."/beans/color.php");
// include_once(APP_ROOT."/beans/sizes.php");
// include_once(APP_ROOT."/beans/percent_discount.php");
include_once(APP_ROOT."/beans/giacenze.php");

class CaricaMagazzino extends DBSmartyAction
{
	var $className;
	
	function CaricaMagazzino()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['error']))
			$this->tEngine->assign('error_contenuto_precaricato', 1);
			
		$BeanApplicationSetup 	= new ApplicationSetup();
		$perc_ricarico 			= $BeanApplicationSetup->dbGetAllByField($this->conn, 'perc_ricarico');
		$this->tEngine->assign('perc_ricarico', $perc_ricarico[0]);
			
		
		$BeanCategory = new gruppi_merceologici();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		$this->tEngine->assign('categories', $Categories);

		$BeanFornitore = new fornitore();
		$fornitori = $BeanFornitore->dbGetAll($this->conn, 'nome', 'ASC');
		$this->tEngine->assign('fornitori', $fornitori);

		$BeanFornitoreSrl = new fornitore_srl();
		$fornitori_srl = $BeanFornitoreSrl->dbGetAll($this->conn, 'nome', 'ASC');
		$this->tEngine->assign('fornitori_srl', $fornitori_srl);
		
		$BeanImages = new images();
		if(!empty($_REQUEST['id_content']))
			$images 	 = $BeanImages->dbGetAllByIdContent($this->conn, $_REQUEST['id_content']);
		$this->getElemendByKey($images, 'images', 'img', $BeanImages);

		// Init Recupero del contenuto dallo step uno
		if(!empty($_REQUEST['id_content']))
			$this->getContenutoPrecaricato($_REQUEST['id_content']);
		// End Recupero del contenuto dallo step uno
			
		if(!empty($_REQUEST['rem_image']))
		{
			$BeanImages = new images($this->conn, $_REQUEST['id_image']);
			$image = $BeanImages->vars();
			unlink($image['local_path'].$image['name'].$image['ext']);
			unlink($image['local_path'].'Large_'.$image['name'].$image['ext']);
			unlink($image['local_path'].'Medium_'.$image['name'].$image['ext']);
			unlink($image['local_path'].'Small_'.$image['name'].$image['ext']);
			$BeanImages->dbDelete($this->conn, array($_REQUEST['id_image']),false);
			$this->_redirect('?act='.$this->className.'&id_content='.$_REQUEST['id_content']);
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id_content']))
				$BeanContent = new content($this->conn,$_REQUEST['id_content']);
			else
				$BeanContent = new content();

			$BeanCategory = new gruppi_merceologici($this->conn, $_REQUEST['id_categoria']);
			$BeanContent->setId_gm($_REQUEST['id_categoria']);
			$BeanContent->setVbn($_REQUEST['vbn']);
			$BeanContent->setNome_it($_REQUEST['nome']);
			$BeanContent->setDescrizione_it($_REQUEST['descrizione']);
			$BeanContent->setNome_en($_REQUEST['nome_en']);
			$BeanContent->setDescrizione_en($_REQUEST['descrizione_en']);			
			$BeanContent->setNome_fr($_REQUEST['nome_fr']);
			$BeanContent->setDescrizione_fr($_REQUEST['descrizione_fr']);
			$BeanContent->setNome_de($_REQUEST['nome_de']);
			$BeanContent->setDescrizione_de($_REQUEST['descrizione_de']);
			
			$BeanContent->setNote($_REQUEST['descrizione']);

			$BeanContent->setCod_iva($BeanCategory->iva);

			$BeanContent->setPrezzo_0($_REQUEST['prezzo']);
			$BeanContent->setData_inserimento_riga(date('Y-m-d H:i:s'));
			$BeanContent->setData_modifica_riga(date('Y-m-d H:i:s'));
			$BeanContent->setOperatore($_SESSION['LoggedUser']['username']);
			$idContent = $BeanContent->dbStore($this->conn);
				
			if(!empty($_REQUEST['id_giacenza']))
				$BeanGiacenze = new giacenze($this->conn,$_REQUEST['id_giacenza']);
			else
				$BeanGiacenze = new giacenze();
				
			$BeanGiacenze->setId_content($idContent);
			$BeanGiacenze->setBar_code($_REQUEST['codice']);
			$BeanGiacenze->setPrezzo_0($_REQUEST['prezzo']);
			$BeanGiacenze->setPrezzo_acquisto($_REQUEST['prezzo_acquisto']);
			$BeanGiacenze->setProduttore($_REQUEST['id_produttore']);
			$BeanGiacenze->setId_gm($_REQUEST['id_categoria']);
			$BeanGiacenze->setId_fornitore($_REQUEST['id_fornitore']);
			$BeanGiacenze->setId_fornitore_srl($_REQUEST['id_fornitore_srl']);
			$BeanGiacenze->setQuantita_mazzo($_REQUEST['qta_minima']);
			$BeanGiacenze->setQta_minima($_REQUEST['qta_minima']);
			$BeanGiacenze->setQta_min_ordine($_REQUEST['qta_min_ordine']);
			$BeanGiacenze->setQta_carrello($_REQUEST['qta_carrello']);
			$BeanGiacenze->setQta_pianale($_REQUEST['qta_pianale']);
			$BeanGiacenze->setDisponibilita($_REQUEST['quantita']);
			$BeanGiacenze->setQuantita($_REQUEST['qta_min_ordine']);
			if($_REQUEST['visibile'] == 'on')
				$BeanGiacenze->setVisibile(1);
// 			else
// 				$BeanGiacenze->setVisibile(0);
			if($_REQUEST['visibile_en'] == 'on')
				$BeanGiacenze->setVisibile_en(1);
// 			else
// 				$BeanGiacenze->setVisibile_en(0);
			if($_REQUEST['visibile_fr'] == 'on')
				$BeanGiacenze->setVisibile_fr(1);
// 			else
// 				$BeanGiacenze->setVisibile_fr(0);
			if($_REQUEST['visibile_de'] == 'on')
				$BeanGiacenze->setVisibile_de(1);
// 			else
// 				$BeanGiacenze->setVisibile_de(0);
			if($_REQUEST['in_home'] == 'on')
				$BeanGiacenze->setIn_home(1);
			else
				$BeanGiacenze->setIn_home(0);
				
			if($_REQUEST['stato'] == 'novita')
				$BeanGiacenze->setStato('N');
			
			if($_REQUEST['stato'] == 'offerta')
				$BeanGiacenze->setStato('O');
			
			if($_REQUEST['stato'] == 'E')
				$BeanGiacenze->setStato('E');
				
			$BeanGiacenze->setData_inserimento_riga(date('Y-m-d H:i:s'));
			$BeanGiacenze->setData_modifica_riga(date('Y-m-d H:i:s'));
			$BeanGiacenze->setOperatore($_SESSION['LoggedUser']['username']);
			$idGiacenza = $BeanGiacenze->dbStore($this->conn);
			
			$i = 1;
			foreach ($_FILES as $key => $file)
			{
				if(!empty($file['name']))
					$this->uploadFile($_REQUEST['codice'], $key, $file, $idContent, $i);
				else
				{
					$BeanImages = new images();
					if(!$BeanImages->dbGetOneByIdContent($this->conn, $idContent))
					{
						$BeanImages->setName('default.jpg');
						$BeanImages->setId_content($idContent);
						$BeanImages->setLocal_path(APP_ROOT.'/img/web/product');
						$BeanImages->setWww_path(WWW_ROOT.'/img/web/product');
						$BeanImages->dbStore($this->conn);
					}
				}
				$i++;
			}
			if(empty($idContent) && !empty($_REQUEST['id_content']))
				$idContent = $_REQUEST['id_content'];
			// Init Recupero del contenuto dallo step uno
			if(!empty($idContent))
				$this->getContenutoPrecaricato($idContent);
			// End Recupero del contenuto dallo step uno
					
			Base_CacheCore::getInstance()->clean();
			unset($_SESSION[$this->className]);
		}

		
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}

	function getContenutoPrecaricato($id)
	{
		
		$BeanMagazzino = new giacenze();
		$giacenze = $BeanMagazzino->dbSearch($this->conn, " AND giacenze.id_content = ".$id." ORDER BY giacenze.id DESC");
		$List['giacenza'] = $giacenze[0];

// 		$BeanContent = new content($this->conn, $id);
// 		$List['content'] = $BeanContent->vars();
		$result = mysql_query("SELECT * FROM content WHERE id = ".$id, $this->conn->connection);
		while ($row = mysql_fetch_assoc($result))
			$List['content'] = $row;

		$BeanImages = new images();
		$images = $BeanImages->dbGetAllByIdContent($this->conn, $List['content']['id']);
		$List['images'] = $images;

		if(count($List)>1)
			$this->tEngine->assign('content', $List);
		elseif(!empty($List))
			$this->tEngine->assign('content', $List);
		else
			$this->tEngine->assign('error_contenuto_precaricato', true);
	}
	
	function getElemendByKey($value, $element, $suffixRequestKey, $BeanApplicationSetup)
	{		
		if(empty($_SESSION[$this->className][$element]))
			$_SESSION[$this->className][$element][0]['name'] = '';
		if(!empty($_REQUEST['add_'.$suffixRequestKey]))
			$_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) ]['name'] = '';
		if(!empty($_REQUEST['rem_'.$suffixRequestKey]))
		{
			$elToRemove = $_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) - 1 ];
			if(key_exists('id', $elToRemove))
			{
				$BeanApplicationSetup->dbGetOne($this->conn, $elToRemove['id_img']);
				unlink($BeanApplicationSetup->local_path.'/'.$BeanApplicationSetup->name);
				unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
				unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
				
				$BeanApplicationSetup->dbDelete($this->conn, array($elToRemove['id']), false);
			}
			unset($_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) - 1 ]);
		}
		if(!empty($_REQUEST['delete_'.$suffixRequestKey]))
		{
			$BeanApplicationSetup->dbGetOne($this->conn, $_REQUEST['id_img']);
			unlink($BeanApplicationSetup->local_path.'/'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
			
			$BeanApplicationSetup->dbDelete($this->conn, array($_REQUEST['id_img']), false);
			unset($_SESSION[$this->className]);
			$params = '';
			if(!empty($_REQUEST['id_content']))
				$params = '&id_content='.$_REQUEST['id_content'];
			$this->_redirect('?act='.$this->className.'&id='.$_REQUEST['id'].$params);
		}

		$this->tEngine->assign($element, $_SESSION[$this->className][$element]);		
	}	
	
	function uploadFile($fName, $index, $server_file, $id, $i)
	{
		$localPath = str_replace('/admin', '', APP_ROOT.'/FlorSysIntegration/img/');
		$wwwPath = str_replace('/admin', '', WWW_ROOT.'FlorSysIntegration/img/');		

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);
		
		switch ($server_file['type'])
		{
			case "image/gif":
				$ext = '.gif';
			break;
			case "image/jpeg":
				$ext = '.jpg';
			break;
			case "image/tiff":
				$ext = '.tiff';
			break;
			case "image/x-png":
				$ext = '.png';
			break;
			case "image/png":
				$ext = '.png';
			break;
			case "image/rgb":
				$ext = '.rgb';
			break;
		}
		
		$pathFName = $localPath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);
		
		$suffixImg = date('YmdHis');
		
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Small_".$fName.'_'.$i.'_'.$suffixImg.$ext, 40);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');
		
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Medium_".$fName.'_'.$i.'_'.$suffixImg.$ext, 100);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Large_".$fName.'_'.$i.'_'.$suffixImg.$ext, 640);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');
			
		if(!move_uploaded_file($server_file['tmp_name'], $localPath.'/'.$fName.'_'.$i.'_'.$suffixImg.$ext))
			throw new Exception();
		
		$BeanImages = new images();
		$BeanImages->setName($fName.'_'.$i.'_'.$suffixImg);
		$BeanImages->setExt($ext);
		$BeanImages->setId_content($id);
		$BeanImages->setLocal_path($localPath);
		$BeanImages->setWww_path($wwwPath);		
		$BeanImages->dbStore($this->conn);
	}
}
?>