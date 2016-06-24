<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/gruppi_merceologici.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");

class ShoppingCart extends DBSmartyAction
{
	function ShoppingCart()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['get_cart']))
		{
			if(!empty($_SESSION['Search']['gm']))
			{
				$this->tEngine->assign('menu_gm_selected', $_SESSION['Search']['gm']);
				$this->tEngine->assign('search', array('gm'=>$_SESSION['Search']['gm']));
			}
			echo str_replace(array("\n","\r"), "", $this->tEngine->fetch('shared/MenuLeft'));
			exit();
		}		
		if(empty($_SESSION['LoggedUser']))
		{
			if(!empty($_REQUEST['is_ajax']))
			{
				echo "<script>document.location.href = '".WWW_ROOT."?act=Login';</script>";
				exit();
			}
			else
				$this->_redirect('?act=Login');
		}

		if(!empty($_REQUEST['print_orders']))
		{
			$this->createPdfOrder($_SESSION[session_id()]['basket'], $this->tEngine);
			exit();
				
		}
		
		$tmpSess = $_SESSION[session_id()]['basket'];
		unset($tmpSess['n_carrelli']);
		unset($tmpSess['perc_occupazione']);
		$_SESSION[session_id()]['basket'] = $tmpSess;
		
		//if(empty($_SESSION['user_choice']['date']) && empty($_REQUEST['error_partenza']))
		//{
			//echo "<script>document.location.href = '".WWW_ROOT."?act=Search&error_partenza=1'</script>";
			//exit();
			//$this->_redirect('?act=ShoppingCart&error_partenza=1');
		//}
		
		if(!empty($_SERVER['HTTP_DELETE_SESSION']))
			unset($_SESSION[session_id()]);
			
		if(!empty($_REQUEST['params']))
			$this->tEngine->assign('params_banking', $_REQUEST['params']);
			
		if(!empty($_REQUEST['delete']))
		{
			$BeanContent = new content($this->conn, $_REQUEST['id_content']);
			$contenuto = $BeanContent->vars();
			$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_magazzino']);
			$giacenza = $BeanGiacenze->vars();
				
			$beanBasket = new ecm_basket();
			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id']);
				
			foreach ($_SESSION[session_id()]['basket'] as $key => $value)
			{
				if($value['giacenza']['id'] == $_REQUEST['id_magazzino'])
				{
					$BeanMagazzino = new giacenze($this->conn, $_REQUEST['id_magazzino']);
					//$BeanMagazzino->setDisponibilita(($BeanMagazzino->getDisponibilita()+($value['basket_qty']['sel_quantita'])*$BeanMagazzino->getQuantita()));
					//$BeanMagazzino->dbStore($this->conn);
						
					$beanBasketMagazzino = new ecm_basket_magazzino();
					$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
						
					if(!empty($basketMagazzino))
					{
						$beanBasketMagazzino->setIs_active(0);
						$beanBasketMagazzino->setQuantita(0);
						$beanBasketMagazzino->dbStore($this->conn);
					}
					unset($_SESSION[session_id()]['basket'][$key]);
				}
				else
					$tmp_session[] = $value;
			}
				
			if(count($_SESSION[session_id()]['basket']) == 0)
				unset($_SESSION[session_id()]['ecm_basket']);
				
			unset($_SESSION[session_id()]['basket']);
			$_SESSION[session_id()]['basket'] = $tmp_session;
				
			if(empty($_REQUEST['is_ajax']))
			{
				$this->_redirect(str_replace(WWW_ROOT, '', $_SERVER['HTTP_REFERER']));
				exit();
			}				
			if(count($_SESSION[session_id()]['basket']) == 0)
				unset($_SESSION[session_id()]['ecm_basket']);
				
// 			Base_CacheCore::getInstance()->clean();
			if(!empty($_REQUEST['delete_from_box']))
			{
				$this->_redirect(str_replace(WWW_ROOT, '', $_SERVER['HTTP_REFERER']));
				exit();
			}
		}

		$beanBasket = new ecm_basket();
		$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
		
		if(!empty($_REQUEST['quantita']))
		{
			if(!empty($_REQUEST['is_ajax']))
			{
				$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza']);
				$giacenza = $BeanGiacenze->vars();
				
				$BeanContent = new content($this->conn, $giacenza['id_content']);
				$contenuto = $BeanContent->vars();

				$beanBasket = new ecm_basket();
				$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
				if(empty($basket))
				{
					$beanBasket->setId_user($_SESSION['LoggedUser']['id']);
					$beanBasket->setOperatore(ECM_OPERATORE);
					$beanBasket->dbStore($this->conn);
					$basket = $beanBasket->vars();
				}
				
				$beanBasketMagazzino = new ecm_basket_magazzino();
				$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
				
				$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
// 				if(empty($basketMagazzino))
				{
					$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
					$beanBasketMagazzino->setId_basket($basket['id']);
					$beanBasketMagazzino->setId_magazzino($giacenza['id']);
					$beanBasketMagazzino->setQuantita($_REQUEST['quantita']);
					$beanBasketMagazzino->setBar_code($giacenza['bar_code']);
					$beanBasketMagazzino->setBox_type($_REQUEST['box_type']);
					$beanBasketMagazzino->setIs_active(1);
					if(!empty($_REQUEST['note']))
						$beanBasketMagazzino->setNote($_REQUEST['note']);
				}
				$beanBasketMagazzino->dbStore($this->conn);
				
				$_SESSION[session_id()]['ecm_basket'] = $basket['id'];
				
				$BeanMagazzino = new giacenze($this->conn, $_REQUEST['id_mag']);
// 				$BeanMagazzino->setDisponibilita(($BeanMagazzino->getDisponibilita())-($_REQUEST['quantita']*$BeanMagazzino->getQuantita()));
// 				$BeanMagazzino->dbStore($this->conn);
				
				$in_session = false;
				foreach ($_SESSION[session_id()]['basket'] as $k => $value)
				{
					if($value['giacenza']['id'] == $giacenza['id'])
					{
						$in_session = true;
						$_SESSION[session_id()]['basket'][$k]['giacenza'] = $giacenza;
						$_SESSION[session_id()]['basket'][$k]['contenuto'] = $contenuto;
						if(!empty($_REQUEST['note']))
							$_SESSION[session_id()]['basket'][$k]['note'] = $_REQUEST['note'];
						$_SESSION[session_id()]['basket'][$k]['box_type'] = $_REQUEST['box_type'];
						
						$_SESSION[session_id()]['basket'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita'];
						$_SESSION[session_id()]['basket'][$k]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
						$_SESSION[session_id()]['basket'][$k]['price_it_qty'] = $this->tEngine->getPrezzo($giacenza)*$_REQUEST['quantita'];
						//$_SESSION[session_id()]['basket'][$k]['price_it_qty'] = (str_replace(',', '.', $giacenza[$this->key_prezzo]) * $_REQUEST['quantita'] )*$giacenza['quantita'];
					}
				}
				if(!$in_session)
				{
					$index = count($_SESSION[session_id()]['basket']);
					$_SESSION[session_id()]['basket'][$index]['giacenza'] = $giacenza;
					$_SESSION[session_id()]['basket'][$index]['contenuto'] = $contenuto;
					if(!empty($_REQUEST['note']))
						$_SESSION[session_id()]['basket'][$index]['note'] = $_REQUEST['note'];
					$_SESSION[session_id()]['basket'][$index]['box_type'] = $_REQUEST['box_type'];
					
					$_SESSION[session_id()]['basket'][$index]['basket_qty']['quantita'] = $_REQUEST['quantita'];
					$_SESSION[session_id()]['basket'][$index]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
					
					$_SESSION[session_id()]['basket'][$index]['price_it_qty'] = $this->tEngine->getPrezzo($giacenza)*$_REQUEST['quantita'];
					//$_SESSION[session_id()]['basket'][$index]['price_it_qty'] = (str_replace(',', '.', $giacenza[$this->key_prezzo]) * $_REQUEST['quantita'] )*$giacenza['quantita'];
				}	
			}
			else
			{
				foreach ($_REQUEST['quantita'] as $key => $value)
				{
					$BeanContent = new content($this->conn, $_REQUEST['id_contenuto'][$key]);
					$contenuto = $BeanContent->vars();
					$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza'][$key]);
					$giacenza = $BeanGiacenze->vars();

					$beanBasketMagazzino = new ecm_basket_magazzino();
					$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
								
					$quantita_ordine = $_REQUEST['quantita'][$key]*$giacenza['qta_minima'];
					//if(($quantita_ordine * $giacenza['qta_minima']) < $giacenza['qta_min_ordine'])
					if($quantita_ordine < $giacenza['qta_min_ordine'])
						$this->_redirect('?act=ShoppingCart&error_qta=Atenzione la quantita minima d\' ordine e\' di '.$giacenza['qta_min_ordine'].' confezioni o un multiplo di '.$giacenza['qta_minima']);
						
					if(!empty($basketMagazzino))
					{
						$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
						$beanBasketMagazzino->setQuantita($qta_tot);
						$beanBasketMagazzino->setBar_code($giacenza['bar_code']);
						$beanBasketMagazzino->setBox_type($_REQUEST['box_type'][$key]);
						if(!empty($_REQUEST['note_'.$_REQUEST['id_giacenza'][$key]]))
							$beanBasketMagazzino->setNote($_REQUEST['note_'.$_REQUEST['id_giacenza'][$key]]);
						$beanBasketMagazzino->dbStore($this->conn);
					}
					$_SESSION[session_id()]['basket'][$key]['box_type'] = 'confezione';
					if(!empty($_REQUEST['note_'.$_REQUEST['id_giacenza'][$key]]))
						$_SESSION[session_id()]['basket'][$key]['note'] = $_REQUEST['note_'.$_REQUEST['id_giacenza'][$key]];
					$_SESSION[session_id()]['basket'][$key]['basket_qty']['quantita'] = $_REQUEST['quantita'][$key];
					$_SESSION[session_id()]['basket'][$key]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'][$key];
					$_SESSION[session_id()]['basket'][$key]['price_it_qty'] = $this->tEngine->getPrezzo($giacenza)*$_REQUEST['quantita'][$key];
				}
			}
		}
		else if(!empty($_REQUEST['id_mag']))
		{
			$BeanContent = new content($this->conn, $_REQUEST['id_content']);
			$contenuto = $BeanContent->vars();
			$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_mag']);
			$giacenza = $BeanGiacenze->vars();
				
			$beanBasket = new ecm_basket();
			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
			if(empty($basket))
			{
				$beanBasket->setId_user($_SESSION['LoggedUser']['id']);
				$beanBasket->setOperatore(ECM_OPERATORE);
				$beanBasket->dbStore($this->conn);
				$basket = $beanBasket->vars();
			}
			$_SESSION[session_id()]['ecm_basket'] = $basket['id'];
			
			$beanBasketMagazzino = new ecm_basket_magazzino();
			$beanBasketMagazzino->setId_basket($basket['id']);
			$beanBasketMagazzino->setId_magazzino($_REQUEST['id_mag']);
			if(empty($_REQUEST['quantita']))
					$beanBasketMagazzino->setQuantita(1*$giacenza['quantita']);
			$beanBasketMagazzino->dbStore($this->conn);
	
			$BeanMagazzino = new giacenze($this->conn, $giacenza['id']);
			if(empty($_REQUEST['quantita']))
					$BeanMagazzino->setDisponibilita($BeanMagazzino->getDisponibilita()-(1*$giacenza['quantita']));
			$BeanMagazzino->dbStore($this->conn);
			$giacenza['id'];
			$contenuto['id'];
	
			$keySession = count($_SESSION[session_id()]['basket']);
			$_SESSION[session_id()]['basket'][$keySession]['giacenza'] = $giacenza;
			$_SESSION[session_id()]['basket'][$keySession]['contenuto'] = $contenuto;
	
			$_SESSION[session_id()]['basket'][$keySession]['basket_qty']['quantita'] = 1*$giacenza['quantita'];
			$_SESSION[session_id()]['basket'][$keySession]['basket_qty']['sel_quantita'] = 1;
			$_SESSION[session_id()]['basket'][$keySession]['price_it_qty'] = (str_replace(',', '.', $giacenza[$this->key_prezzo])*1)*$giacenza['quantita'];
		}
		
		$this->calcolaVolumiCarrello();		
		$this->tEngine->assign('basket', $_SESSION[session_id()]['basket']);

		if(!empty($_REQUEST['is_ajax']))
		{
			$tmpSess = $_SESSION[session_id()]['basket'];
			unset($tmpSess['n_carrelli']);
			unset($tmpSess['perc_occupazione']);

			echo "<i class=icon-cart></i><span class=cart-items>(".count($tmpSess).")".$this->tEngine->getTranslation('prodotto/i')."</span>";
			echo "<script type='text/javascript'>jQuery('.dropdown-menu').html('".str_replace(array("\n","\r"), "", str_replace("'", "\\'", $this->tEngine->fetch('shared/BoxCart')))."')</script>";
			exit();
		}
		else
		{
			$this->tEngine->assign('tpl_action', 'ShoppingCart');
			$this->tEngine->display('Index');
		}
	}
	
	function createPdfOrder($data, $tEngine)
	{
		$spese_spedizione = $tEngine->getSpeseSpedizione($_SESSION[session_id()]['basket']);
		$tmpSess = $_SESSION[session_id()]['basket'];
		$n_carrelli = $tmpSess['n_carrelli'];
		unset($tmpSess['n_carrelli']);
		unset($tmpSess['perc_occupazione']);
		unset($data['n_carrelli']);
		unset($data['perc_occupazione']);
		
		$_SESSION[session_id()]['basket'] = $tmpSess;
		
		include_once(APP_ROOT.'/beans/customer.php');
		$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
		
		include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
		include_once(APP_ROOT.'/libs/TemplateClass/Template_Orders_PDF.php');
	
		$pdf=new PDF_MC_Table();
		$pdf->AddPage('L');
		$pdf->SetFont('Arial','',10);
	
		$pdf->PageBreakTrigger = 188;
		
		$imageHeaderX = 10;
		$imageHeaderY = 1;
		$imageHeaderWidth = 100;
		$imageHeaderHeight = 20;
		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/logo.png',$imageHeaderX,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
// 		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/greenitaly.jpg',$imageHeaderX+100,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
		$pdf->setY(31);

		$pdf->SetFont('Arial','',12);
		$pdf->SetX(2);
		$pdf->SetWidths(array(196, 97));
		$pdf->Row(
				array(
						$this->tEngine->getTranslation('Cliente').': '.$BeanCustomer->ragione_sociale
				));
		$pdf->SetX(2);
		$pdf->Row(
				array(
						$this->tEngine->getTranslation('Destinazione').': '.$BeanCustomer->indirizzo.' '.$BeanCustomer->citta.' '.$BeanCustomer->provincia.' '.$BeanCustomer->cap
						//$this->tEngine->getTranslation('Data Partenza Merce: ').$_SESSION['user_choice']['date']
				));
		$pdf->SetFont('Arial','',10);
		
		//Table with
		$pdf->SetWidths(array(15,12,12,20,20,62,20,12,18,55,15,10,22));
	
		srand(microtime()*1000000);
	
		$pdf->setX(2);
		$pdf->Row(array('Img','CC','PIA','Codice',$this->tEngine->getTranslation('Categoria'),$this->tEngine->getTranslation('Descrizione'), $this->tEngine->getTranslation('Confezioni'), 'Imballo', 'Quantita', $this->tEngine->getTranslation('Note'), $this->tEngine->getTranslation('Prezzo'), $this->tEngine->getTranslation('IVA'), $this->tEngine->getTranslation('Prezzo Tot.')));
		
		$currency = chr(128);
		foreach($data as $value)
		{
			$BeanGM = new gruppi_merceologici($this->conn, $value['contenuto']['id_gm']);
				
			$pdf->SetFont('Arial','',8);
			$pdf->setX(2);
			$image = null;
			$image = $tEngine->getImageFromIdContent($value['contenuto']['id'], '');
			$product_image = $tEngine->dbGetImageProductFromBarCode($value['giacenza']['bar_code']);
			if(empty($image)){
				$obj_image = $tEngine->dbGetImageFromBarCode($value['giacenza']['bar_code']);
				$product_image = $tEngine->dbGetImageProductFromBarCode($value['giacenza']['bar_code']);
			}
			if(!empty($obj_image)){
					
				$d = dir(APP_ROOT.'/email_images/');
				while (false !== ($entry = $d->read())) {
					if($entry != '.' && $entry != '..')
						$image = $obj_image[0]['www_path'].$obj_image[0]['name'];
				}
				$d->close();
			}
			elseif(!empty($product_image))
				$image = $product_image;

			$y_image = $pdf->GetY()+1;
			if($pdf->GetY()+$imageHeight > $pdf->PageBreakTrigger)
			{
				$pdf->AddPage('L');
				$y_image = 11;
				$pdf->SetX(2);
			}

			$imageWidth = 10;
			$imageHeight = 8;
			if(!empty($image))
				$im = $pdf->Image($image,$pdf->GetX()+1,$y_image,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
			else
				$im = $pdf->Image(WWW_ROOT."/img/web/image_large.gif",$pdf->GetX()+1,$pdf->GetY()+1,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
	
			$qta_selected = null;
			if($value['box_type'] == 'confezione')
			{											
				$qta_selected = $value['basket_qty']['quantita'];
				$qta_linea = $value['basket_qty']['sel_quantita'];
				
				$qta_tot = $value['giacenza']['qta_minima']*$value['basket_qty']['sel_quantita'];
			}
			if($value['box_type'] == 'pianale')
			{
				$qta_conf = $value['giacenza']['qta_pianale'] / $value['giacenza']['qta_minima'];
				
				$qta_selected = $qta_conf * $value['basket_qty']['quantita'];
				$qta_linea = $value['basket_qty']['sel_quantita'];
	
				$qta_tot = $value['giacenza']['qta_pianale']*$value['basket_qty']['sel_quantita'];
			}
			if($value['box_type'] == 'carrello')
			{
				$qta_conf = $value['giacenza']['qta_carrello'] / $value['giacenza']['qta_minima'];
				
				$qta_selected = $qta_conf * $value['basket_qty']['quantita'];
				$qta_linea = $value['basket_qty']['sel_quantita'];
				
				$qta_tot = $value['giacenza']['qta_carrello']*$value['basket_qty']['sel_quantita'];
			}
				
			if($value['box_type'] == 'confezione')
				$qta = $value['giacenza']['qta_minima'];
			if($value['box_type'] == 'pianale')
				$qta = $value['giacenza']['qta_minima'];
			if($value['box_type'] == 'carrello')
				$qta = $value['giacenza']['qta_minima'];
			
			$tot_prod = round($tEngine->getPrezzo($value['giacenza']) * $qta_tot, 2);
			$prezzo = $tEngine->getFormatPrice(round(str_replace(',', '.', $tEngine->getPrezzo($value['giacenza'])), 2));
			$nota = ($value['note'] != 'Inserisci una nota sul prodotto') ? $value['note'] : '';
			
			$pdf->Row(array(
					$im,
					number_format(round($qta_tot / $value['giacenza']['qta_carrello'],2), 2, ',', ' '),
					number_format(round($qta_tot / $value['giacenza']['qta_pianale'],2), 2, ',', ' '),
					$value['giacenza']['bar_code'],
					$BeanGM->gruppo,
					substr($value['contenuto']['nome_it'], 0, 40),
					$qta_selected,
					$qta,
					$qta_tot,
					$nota,
					$currency.' '.$prezzo,
					$value['contenuto']['cod_iva'].' %',
					$tEngine->getFormatPrice($tot_prod)
			),
					5
			);
			$tot += $tot_prod;
			
			$mapsCodIva[$value['contenuto']['cod_iva']] += ($tEngine->getPrezzo($value['giacenza']) * $qta_tot);
		}
	
		$pdf->SetWidths(array(10,22));
		
		$pdf->SetX(248);
		$pdf->SetWidths(array(25,22));
		$pdf->Row(array('Imponibile.', $currency.' '.$tEngine->getFormatPrice($tot)));
		
		$importo_iva = 0;
		foreach ($mapsCodIva as $iva => $impo)
		{
			$importo_iva = 0;
			if(round( $impo * ('0.'.$iva) , 2) > $importo_iva)
			{
				if(strlen($iva) == 1)
					$iva = '0'.$iva;
				$importo_iva = round( $impo * ('0.'.$iva) , 2);
				$tot_iva += $importo_iva;
				$iva_spese = $iva;
			}
		}
		if(!empty($spese_spedizione))
		{
			$spese_spedizione = $spese_spedizione*$n_carrelli;
			$iva_trasporto = round( $spese_spedizione * ('0.'.$iva_spese) , 2);
				
			$pdf->SetX(248);
			$pdf->SetWidths(array(25,22));
			$pdf->Row(array('Spese di spedizione', $currency.' '.$tEngine->getFormatPrice($spese_spedizione)));
			$tot += $spese_spedizione;
		}

		$pdf->SetX(248);
		$pdf->SetWidths(array(25,22));
		$pdf->Row(array('IVA ', $currency.' '.$tEngine->getFormatPrice($importo_iva+$iva_trasporto)));
		$tot += $importo_iva+$iva_trasporto;
		
		$pdf->SetX(248);
		$pdf->SetWidths(array(25,22));
		$pdf->Row(array('Totale ', $currency.' '.$tEngine->getFormatPrice($tot)));
		
		$pdf->Output();
	}	
}
?>