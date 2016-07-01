<?php

include_once APP_ROOT.'/libs/ext/PHPExcel/Classes/PHPExcel.php';

include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/customer.php");

include_once(APP_ROOT."/beans/ecm_basket_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");

include_once(APP_ROOT."/beans/ecm_basket_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

include_once(APP_ROOT."/beans/index_ord_forn.php");

class CheckoutPayment extends DBSmartyMailAction
{
	var $className;
	var $importo_spese;
	var $id_negozio;
	var $attachedOrderGasa;
	var $attachedOrderDenDekker;
	var $attachedOrder;

	function CheckoutPayment()
	{
		parent::DBSmartyMailAction();

		$tmpSessNl = $_SESSION[session_id()]['basket_fornitori'];
		$tmpSessDe = $_SESSION[session_id()]['basket_fornitori_de'];
		unset($tmpSessNl['n_carrelli']);
		unset($tmpSessNl['perc_occupazione']);
		unset($tmpSessDe['n_carrelli']);
		unset($tmpSessDe['perc_occupazione']);

		if(!empty($_REQUEST['final_note']))
			$_SESSION[session_id()]['final_data_basket']['final_note']=$_REQUEST['final_note'];
		
		if(!empty($_REQUEST['trasporto']))
			$_SESSION[session_id()]['final_data_basket']['trasporto']=$_REQUEST['trasporto'];
		if(!empty($_REQUEST['destinazione']))
			$_SESSION[session_id()]['final_data_basket']['destinazione']=$_REQUEST['destinazione'];
		if(!empty($_REQUEST['codice_nuova_destinazione']))
		{
			$_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']=$_REQUEST['codice_nuova_destinazione'];
			$_SESSION[session_id()]['final_data_basket']['destinazione']=$_REQUEST['codice_nuova_destinazione'];
			$_SESSION[session_id()]['final_data_basket']['provincia_nuova_destinazione']=$_REQUEST['provincia_nuova_destinazione'];
			$_SESSION[session_id()]['final_data_basket']['localita_nuova_destinazione']=$_REQUEST['localita_nuova_destinazione'];
			$_SESSION[session_id()]['final_data_basket']['indirizzo_nuova_destinazione']=$_REQUEST['indirizzo_nuova_destinazione'];
		}
		
		
		
// 		if(empty($_SESSION['user_choice']['date']))
// 			$this->_redirect('?act=CheckoutShopping&error_partenza=1');
		
// 		if(!empty($tmpSessNl) && $tmpSessNl != array())
// 		{
// 			if(empty($_SESSION[session_id()]['partenza_fornitori']))
// 				$this->_redirect('?act=CheckoutShopping&error_partenza_fornitori_1=1');
// 		}	
// 		if(!empty($tmpSessDe) && $tmpSessDe != array())
// 		{
// 			if(empty($_SESSION[session_id()]['partenza_fornitori_de']))
// 				$this->_redirect('?act=CheckoutShopping&error_partenza_fornitori_2=1');
// 		}
			
		if(empty($_SESSION[session_id()]['basket']) && empty($_SESSION[session_id()]['basket_fornitori']) && empty($_SESSION[session_id()]['basket_fornitori_de']))
			$this->_redirect('');
		
		include_once(APP_ROOT.'/beans/ApplicationSetup.php');
		$BeanApplicationSetup = new ApplicationSetup();
		$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
		
		$this->importo_spese = $speseSpedizione[0]['name'];
		$this->id_negozio = "ID NEGOZIO";
		
		$this->className = get_class($this);

		if(!empty($_REQUEST['params']))
		{
			$params = base64_decode($_REQUEST['params']);
			$exp = explode('&', $params);
			foreach ($exp as $val)
			{
				$ex = explode('=', $val);
				if($ex[0] == 'back')
					$back = 1;
				if($ex[0] == 'confirm')
					$confirm = 1;
				if($ex[0] == 'id_ordine')
					$idOrdine = $ex[1];
				if($ex[0] == 'remote_address')
					$remote_address = $ex[1];
			}
			if($_SESSION[session_id()]['remote_address'] != $remote_address)
			{
				$this->_redirect('?CheckoutShopping=err_remote_addr');
				mail('siso77@gmail.com', 'STREAM - Truffa', 'Remote address: '.$remote_address.'<br>User ID: '.$_SESSION['LoggedUser']['id'].'<br>User Name: '.$_SESSION['LoggedUser']['username']);
			}
		}

		if(!empty($_REQUEST['payment_type']))
			$_SESSION[session_id()]['payment_type'] = $_REQUEST['payment_type'];

		if(!empty($confirm))
		{
			if(!empty($_SESSION[session_id()]['ecm_id_ordine']))
			{
				$beanBasketMagazzino = new ecm_basket_magazzino();
				$data = $beanBasketMagazzino->dbGetAllByIdBasket($this->conn, $_SESSION[session_id()]['ecm_basket']);
				foreach ($data as $bkm)
					$beanBasketMagazzino->dbDelete($this->conn, array($bkm['id']));

				$beanBasket = new ecm_basket($this->conn);
				$activeBasket = $beanBasket->dbGetOneByIdUser($this->conn, $_SESSION['LoggedUser']['id']);
				$beanBasket->dbDelete($this->conn, array($activeBasket['id']), false);

				$BeanEcmOrdini = new ecm_ordini($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdini->setOperatore(ECM_OPERATORE);
				$BeanEcmOrdini->setPagato(1);
				if(!empty($_SESSION[session_id()]['partenza_fornitori']))
					$BeanEcmOrdini->setData_partenza_fornitore_1($_SESSION[session_id()]['partenza_fornitori']);
				if(!empty($_SESSION[session_id()]['partenza_fornitori_de']))
					$BeanEcmOrdini->setData_partenza_fornitore_2($_SESSION[session_id()]['partenza_fornitori_de']);
				$idOrdine = $BeanEcmOrdini->dbStore($this->conn);

				$BeanUsers = new users($this->conn, $BeanEcmOrdini->id_user);
				$BeanUsersAnag = new users_anag($this->conn, $BeanUsers->id_anag);
				
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				$products = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $BeanEcmOrdini->id);

				$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);

				if(!empty($_SESSION[session_id()]['basket']))
				{
					$sconto = '-'.$_SESSION['LoggedUser']['sconto'][0]['percentuale'];

					include_once(APP_ROOT.'/beans/fornitore.php');
					include_once(APP_ROOT.'/beans/fornitore_srl.php');
					$tmpSess = $_SESSION[session_id()]['basket'];
					unset($tmpSess['ecm_id_ordine']);
					unset($tmpSess['ecm_basket']);
					unset($tmpSess['payment_type']);
					unset($tmpSess['final_data_basket']);
					unset($tmpSess['n_carrelli']);
					unset($tmpSess['perc_occupazione']);
					$csvSeparator = ';';
						
					if(strtoupper($_SESSION['customer_data']['stato']) != 'IT')
						$fp = fopen(APP_ROOT.'/FlorSysIntegration/Out2/'.$BeanCustomer->customer_code.'_'.date('dmY').'.CSV', 'w+');
					else
						$fp = fopen(APP_ROOT.'/FlorSysIntegration/Out/'.$BeanCustomer->customer_code.'_'.date('dmY').'.CSV', 'w+');
					
					$str = str_replace(';','-',$_SESSION['LoggedUser']['customer_data']['customer_code']).$csvSeparator; // CUSTOMER CODE
					
					if(!empty($_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']))
						$str .= str_replace(';','-',$_SESSION[session_id()]['final_data_basket']['trasporto']).' - '.str_replace(';','-',$_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']).$csvSeparator; // TRASPORTO
					else
						$str .= str_replace(';','-',$_SESSION[session_id()]['final_data_basket']['trasporto']).$csvSeparator; // TRASPORTO
					
					if(empty($_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']))
						$str .= str_replace('\n',' ',str_replace(';','-',$_SESSION[session_id()]['final_data_basket']['destinazione'])).$csvSeparator; // DESTINAZIONE
					else
						$str .= $csvSeparator; // DESTINAZIONE
					
					if(!empty($_SESSION['user_choice']['date']))
						$str .= $_SESSION['user_choice']['date'].$csvSeparator; // DATA DESIDERATA
					else
						$str .= "".$csvSeparator; // DATA DESIDERATA
					$str .= $this->FormatEuro($this->getSpeseSpedizione($_SESSION[session_id()]['basket']['n_carrelli'], $_SESSION['LoggedUser']['customer_data']['id'])).$csvSeparator; // SPESE SPEDIZIONE
					$str .= "\r\n";
						
					//if(!empty($_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']))
					if(false)
					{
						$nuova_destinazione = str_replace(';', '', $_SESSION[session_id()]['final_data_basket']['provincia_nuova_destinazione'].' '.$_SESSION[session_id()]['final_data_basket']['localita_nuova_destinazione'].' '.$_SESSION[session_id()]['final_data_basket']['indirizzo_nuova_destinazione']);
						$str .= 'D'.$csvSeparator;// COD ART
						$str .= str_replace(';', '', $_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']).$csvSeparator;// COD ART
						$str .= $nuova_destinazione.$csvSeparator;// NOME ART
						$str .= $csvSeparator;// QTA CONFEZIONE
						$str .= $csvSeparator;// QTA PIANALE
						$str .= $csvSeparator;// QTA CARRELLO
						$str .= '1'.$csvSeparator;// QTA MIN RDINE
						$str .= $csvSeparator;// QTA TOT RIGA
						$str .= $csvSeparator;// TIPO IMBALLO ACQUISTATO
						$str .= '0'.$csvSeparator;// PREZZO
						$str .= '0'.$csvSeparator;// PREZZO SCONTATO
						$str .= '0'.$csvSeparator;// PREZZO RIGA
						$str .= $csvSeparator;// NOTA RIGA
						$str .= $csvSeparator;
						$str .= "\r\n";
					}
					foreach ($tmpSess as $key => $value)
					{
						if(strtoupper($_SESSION['customer_data']['stato']) != 'IT')
							$BeanFornitori = new fornitore_srl($this->conn, $value['giacenza']['id_fornitore_srl']);
						else
							$BeanFornitori = new fornitore($this->conn, $value['giacenza']['id_fornitore']);
						$fornitore = $BeanFornitori->vars();
						
						$str .= str_replace(';','-',$value['giacenza']['bar_code']).$csvSeparator;// COD ART
						$str .= str_replace(';','-',$value['contenuto']['nome_it']).$csvSeparator;// NOME ART
						$str .= $value['giacenza']['qta_minima'].$csvSeparator;// QTA CONFEZIONE
						$str .= $value['giacenza']['qta_pianale'].$csvSeparator;// QTA PIANALE
						$str .= $value['giacenza']['qta_carrello'].$csvSeparator;// QTA CARRELLO
						$str .= $value['giacenza']['qta_min_ordine'].$csvSeparator;// QTA MIN RDINE
						
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
						$str .= $qta_tot.$csvSeparator;// QTA TOT RIGA
						$str .= $value['box_type'].$csvSeparator;// TIPO IMBALLO ACQUISTATO
						
						if(!empty($_SESSION['LoggedUser']['customer_data']['costo_reso']))
						{
							$costo_reso = round($_SESSION['LoggedUser']['customer_data']['costo_reso'] / $value['giacenza']['qta_carrello'],2);
						}						
						if(!empty($BeanCustomer->is_pz_commissione))
						{
							if(!empty($_SESSION['LoggedUser']['customer_data']['costo_reso']))
								$str .= str_replace("&nbsp;", "", str_replace("&euro;", "", str_replace(",", ".", $this->FormatEuro($this->tEngine->getPrezzo($value['giacenza']))))).$csvSeparator;// PREZZO
							else 
								$str .= $value['giacenza']['prezzo_acquisto'].$csvSeparator;// PREZZO
							$prezzoScontato = $value['giacenza']['prezzo_acquisto'];

							$str .= $prezzoScontato.$csvSeparator;// PREZZO SCONTATO
							$str .= $this->FormatEuro( (str_replace(",", ".", $prezzoScontato)*$qta_tot) ).$csvSeparator;// PREZZO RIGA
						}
						else
						{
							if(!empty($_SESSION['LoggedUser']['customer_data']['costo_reso']))
								$str .= str_replace("&nbsp;", "", str_replace("&euro;", "", str_replace(",", ".", $this->FormatEuro($this->tEngine->getPrezzo($value['giacenza']))))).$csvSeparator;// PREZZO
							else 
								$str .= $value['giacenza']['prezzo_0'].$csvSeparator;// PREZZO
							
							$prezzoScontato = str_replace("&nbsp;", "", str_replace("&euro;", "", str_replace(",", ".", $this->FormatEuro($this->tEngine->getPrezzo($value['giacenza'])))));
							$str .= $prezzoScontato.$csvSeparator;// PREZZO SCONTATO
							$str .= $this->FormatEuro( (str_replace(",", ".", $prezzoScontato)*$qta_tot) ).$csvSeparator;// PREZZO RIGA
						}
						if($value['note'] != 'Inserisci una nota sul prodotto')
						{
							$value['note'] = preg_replace('~[\r\n]+~', ' ', $value['note']);
								
							$str .= str_replace(';','-',$value['note']).$csvSeparator;// NOTA RIGA
						}
						else 
							$str .= "".$csvSeparator;// NOTA RIGA
						
						$str .= $fornitore['codice_fornitore'].$csvSeparator;
						
						if(!empty($_SESSION['LoggedUser']['customer_data']['costo_reso']))
							$sconto = '0';
						$str .= $sconto.$csvSeparator;
						$str .= "\r\n";
					}
					fwrite($fp, $str);
					fclose($fp);

					$fpBck = fopen(APP_ROOT.'/FlorSysIntegration/Out/bck/'.$BeanCustomer->customer_code.'_'.date('dmY').'.CSV', 'w+');
					fwrite($fpBck, $str);
					fclose($fpBck);
				}
				//$this->attachedOrder = $this->createPdfOrder($_SESSION[session_id()]['basket'], $this->tEngine, APP_ROOT.'/documenti_ordini/'.$_SESSION[session_id()]['ecm_id_ordine']);
				$this->attachedOrder = APP_ROOT.'/FlorSysIntegration/Out/bck/'.$BeanCustomer->customer_code.'_'.date('dmY').'.CSV';
				$this->SendEmail($BeanEcmOrdini, $BeanUsersAnag, $products, $products_fornitori, $products_fornitori_de);
				unlink($this->attachedOrder);
			}

			$this->tEngine->assign('confirm', true);
			$this->tEngine->assign('num_ordine', $_SESSION[session_id()]['ecm_id_ordine']);
			unset($_SESSION[session_id()]);
		}
		else 
		{
			if(empty($_SESSION['LoggedUser']))
			{
				$_SESSION[session_id()]['return'] = 'CheckoutShopping';
				$this->_redirect('?act=Login');
			}
			if(empty($_SESSION[session_id()]['ecm_id_ordine']))
			{
				$BeanEcmOrdini = new ecm_ordini();
				$BeanEcmOrdini->setTipo_pagamento($_SESSION[session_id()]['payment_type']);
				$BeanEcmOrdini->setId_user($_SESSION['LoggedUser']['id']);
				$BeanEcmOrdini->setOperatore(ECM_OPERATORE);
				$BeanEcmOrdini->setPagato(0);
				$BeanEcmOrdini->setFatturato(0);
				$BeanEcmOrdini->setSpedito(0);
				$idOrdine = $BeanEcmOrdini->dbStore($this->conn);
				$_SESSION[session_id()]['ecm_id_ordine'] = $idOrdine;
			}
			else
			{
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				$values = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				foreach ($values as $val)
					$BeanEcmOrdiniMagazzino->dbDelete($this->conn, $val['id']);
			}

			$IMPORTO = 0.00;
			$tmpSess = $_SESSION[session_id()]['basket'];
			unset($tmpSess['n_carrelli']);
			unset($tmpSess['perc_occupazione']);

			foreach ($tmpSess as $val)
			{
				$price_it_qty = 0;
				if($val['box_type'] == 'confezione')
				{
					$qta_selected = $val['basket_qty']['quantita'];
					$qta_linea = $val['basket_qty']['sel_quantita'];
				
					$qta_tot = $val['giacenza']['qta_minima']*$val['basket_qty']['sel_quantita'];
				}
				if($val['box_type'] == 'pianale')
				{
					$qta_conf = $val['giacenza']['qta_pianale'] / $val['giacenza']['qta_minima'];
				
					$qta_selected = $qta_conf * $val['basket_qty']['quantita'];
					$qta_linea = $val['basket_qty']['sel_quantita'];
				
					$qta_tot = $val['giacenza']['qta_pianale']*$val['basket_qty']['sel_quantita'];
				}
				if($val['box_type'] == 'carrello')
				{
					$qta_conf = $val['giacenza']['qta_carrello'] / $val['giacenza']['qta_minima'];
				
					$qta_selected = $qta_conf * $val['basket_qty']['quantita'];
					$qta_linea = $val['basket_qty']['sel_quantita'];
				
					$qta_tot = $val['giacenza']['qta_carrello']*$val['basket_qty']['sel_quantita'];
				}
//				$price_it_qty = $val['price_it_qty'];
				$price_it_qty = $this->tEngine->getPrezzo(str_replace(',', '.', $val['giacenza']))*$qta_tot;
				
				$price_discounted_it_qty = $val['price_discounted_it_qty'];
				if(!empty($price_discounted_it_qty) && $price_discounted_it_qty > 0)
					$IMPORTO = $IMPORTO + str_replace(',', '.', $price_discounted_it_qty);
				else
					$IMPORTO = $IMPORTO + str_replace(',', '.', $price_it_qty);
				
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				if(
					!empty($_REQUEST['nota_'.$val['giacenza']['id']]) && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Inserisci una nota per il prodotto') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Enter a note for the product') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Geben Sie eine Note') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Entrez une note pour le produit') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Vvedite zapisku dlya produkta')
				)
					$BeanEcmOrdiniMagazzino->setNota($_REQUEST['nota_'.$val['giacenza']['id']]);
				if(!empty($_REQUEST['indispensabile_'.$val['giacenza']['id']]) && $_REQUEST['indispensabile_'.$val['giacenza']['id']] == 'on')
					$BeanEcmOrdiniMagazzino->setIndispensabile(1);
				
				$BeanEcmOrdiniMagazzino->setName_it($val['contenuto']['nome_it']);
				$BeanEcmOrdiniMagazzino->setId_content($val['contenuto']['id']);
				$BeanEcmOrdiniMagazzino->setId_magazzino($val['giacenza']['id']);
				$BeanEcmOrdiniMagazzino->setId_ordine($_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdiniMagazzino->setQuantita($val['basket_qty']['sel_quantita']);
				$BeanEcmOrdiniMagazzino->setBox_type($val['box_type']);
				$BeanEcmOrdiniMagazzino->setImporto($price_it_qty);
				$BeanEcmOrdiniMagazzino->dbStore($this->conn);
			}
			
				$BeanEcmOrdini = new ecm_ordini($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdini->setImporto($IMPORTO);
				$BeanEcmOrdini->dbStore($this->conn);			
		}
		
		if($_REQUEST['payment_type'] == 'CC')
		{
			exit();
		}
		else if(empty($confirm))
			$this->_redirect("?act=".$this->className.'&params='.base64_encode('confirm=0&id_ordine='.$_SESSION[session_id()]['ecm_id_ordine']).'&stream='.session_id());

		$this->tEngine->assign('content', $content);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function createPdfOrder($data, $tEngine, $pathPdf)
	{
		ini_set('precision', 12);
		
		$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
		$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
		$ordine = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
		
		include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
		include_once(APP_ROOT.'/libs/TemplateClass/Template_Orders_PDF.php');
		include_once(APP_ROOT."/beans/gruppi_merceologici.php");
		
		$pdf=new PDF_MC_Table();
		$pdf->AddPage('L');
		$pdf->SetFont('Arial','',12);
	
		$pdf->PageBreakTrigger = 188;
		
		$imageHeaderX = 10;
		$imageHeaderY = 1;
		$imageHeaderWidth = 40;
		$imageHeaderHeight = 20;		
		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/logo.png',$imageHeaderX,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
// 		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/greenitaly.jpg',$imageHeaderX+100,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
		$pdf->setY(31);

		$pdf->SetX(2);
		$pdf->SetWidths(array(196, 97));
		$pdf->Row(
				array(
						$this->tEngine->getTranslation('Cliente').': '.$BeanCustomer->ragione_sociale
				));
		$pdf->SetX(2);
		$pdf->Row(
				array(
						$this->tEngine->getTranslation('Destinazione').': '.$BeanCustomer->indirizzo.' '.$BeanCustomer->citta.' '.$BeanCustomer->provincia.' '.$BeanCustomer->cap,
						$this->tEngine->getTranslation('Data Partenza Merce: ').$_SESSION['user_choice']['date']
				));
				
		$pdf->SetFont('Arial','',10);
		//Table with
		$pdf->SetWidths(array(15,16,14,52,15,15,12,12,15,12,18,35,15,10,15,22));
			
		srand(microtime()*1000000);
	
		$pdf->setX(2);
		$pdf->Row(array('Img','Vbn',$tEngine->getTranslation('Gruppo'),$tEngine->getTranslation('Descrizione'),$tEngine->getTranslation('Colore'), 
						'S1', 'S2', 'MS', $tEngine->getTranslation('Imballi'), 'Q x I', 'Q '.$tEngine->getTranslation('Totale'), $tEngine->getTranslation('Note'), 
						$tEngine->getTranslation('Prezzo'), $tEngine->getTranslation('IVA'), $tEngine->getTranslation('Prezzo Totale'), $tEngine->getTranslation('Urgente')));
		$currency = chr(128);
		
		foreach($data as $key => $value)
		{
			$BeanGM = new gruppi_merceologici($this->conn, $value['contenuto']['id_gm']);
				
			$pdf->SetFont('Arial','',8);
			$pdf->setX(2);
			$image = null;
			$image = $tEngine->getImageFromVbn($value['contenuto']['vbn']);
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
				$im = $pdf->Image($image,$pdf->GetX()+1,$y_image+1,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
			else
				$im = $pdf->Image(WWW_ROOT."/img/web/image_large.gif",$pdf->GetX()+1,$pdf->GetY()+1,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
	
			$indispensabile = !empty($ordine[$key]['indispensabile']) ? 'Si' : '';
			
			$tot_prod = $value['contenuto']['prezzo_'.$_SESSION['LoggedUser']['listino']]*$value['giacenza']['quantita']*$value['basket_qty']['sel_quantita'];
			$pdf->Row(array(
					$im,
					$value['contenuto']['vbn'],
					$BeanGM->gruppo,
					substr($value['contenuto']['nome_it'], 0, 40),
					$value['contenuto']['C3'],
					$value['giacenza']['C4'],
					substr($value['giacenza']['dimensione'], 0, 7),
					$value['giacenza']['openstage'],
					$value['basket_qty']['sel_quantita'],
					$value['giacenza']['quantita'],
					$value['basket_qty']['sel_quantita']*$value['giacenza']['quantita'],
					$ordine[$key]['nota'],
					$currency.' '.$tEngine->getFormatPrice($value['giacenza']['prezzo_'.$_SESSION['LoggedUser']['listino']]),
					$value['contenuto']['cod_iva'],
					$tEngine->getFormatPrice($tot_prod),
					$indispensabile
			),
					5
			);
			$tot += $tot_prod;
		}
	
		$pdf->SetWidths(array(10,22));
	
		// 		$pdf->SetX(263);
		// 		$pdf->MultiCell(32,120-count($data),'',1);

		$pdf->SetX(248);
		$pdf->SetWidths(array(10,15));
		$pdf->Row(array('Tot.', $currency.' '.$tEngine->getFormatPrice($tot)));
		$pdf->Output($pathPdf.'.pdf', 'f');
		return $pathPdf.'.pdf';
		
	}

	function generateEtiflorFileOrder($products, $fp, $BeanMagazzino)
	{
		$data_doc = date('d-m-Y');
		foreach ($products as $key => $value)
		{
			$BeanMagazzino->dbGetOne($this->conn, $value['id_magazzino']);
			
			$magazzino = $BeanMagazzino->vars();
			$magazzino['prezzo_sc'] = $magazzino['prezzo_sc'] + round($this->tEngine->getRicarico($magazzino['prezzo_sc'], $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
			$magazzino['prezzo_pi'] = $magazzino['prezzo_pi'] + round($this->tEngine->getRicarico($magazzino['prezzo_pi'], $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
				
			$str = 'FI';// TIPO DOC
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 7);// NUM DOC
			$str .= $data_doc;//DATA DOC
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($this->tEngine->getFormatCodiceCliente($_SESSION['LoggedUser']['id_customer'], $COD_CLI_PADD_IN_ORDER), 7);// COD CLIENTE
			$str .= '  ';//FILLER
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('10', 20);//COD ART/PART
			if(!empty($value['quantita']))
			{
				$exp = explode(' x ', $magazzino['qta_scatola']);
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(($value['quantita']*$exp[0]), 10);//QUANTITA
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($magazzino['prezzo_sc'], 10);//PREZZO
			}
			elseif(!empty($value['quantita_pianale']))
			{
				$exp = explode(' x ', $magazzino['qta_pianale']);
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(($value['quantita_pianale']*($exp[0]*$exp[1])), 10);//QUANTITA
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($magazzino['prezzo_pi'], 10);//PREZZO
			}
			
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 10);//PAGATO
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO RIGA
			$str .= '       ';//FILLER
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO DOC
			$str .= '*  ';//FISSO
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 7);//COD DEST
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO RIGA 2
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO RIGA 3
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO DOC 2
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 21);//FILLER
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(substr($magazzino['descrizione'], 0, 80), 80);//DESCRIZIONE
			$str .= "\r\n";
			fwrite($fp, $str);
		}
	}
	
	function generateCsvDenDekker($BeanEcmOrdini, $products_fornitori, $BeanUsers)
	{
		$separator = ';';
		include_once(APP_ROOT.'/beans/dendekker_codes.php');
		$BeanDenDekkerCode = new dendekker_codes();
		
		include_once(APP_ROOT.'/beans/customer.php');
		$BeanCustomer = new customer($this->conn, $BeanUsers->id_customer);
		
		$BeanDenDekkerCode->dbGetOneByCustomerCode($this->conn, 'GA'.substr($BeanCustomer->customer_code, 1, strlen($BeanCustomer->customer_code)));

		$exp = explode('-', $BeanEcmOrdini->data_partenza_fornitore_1);
		$data_partenza = $exp[2].'-'.$exp[1].'-'.$exp[0];
		
		$txt = '"'.$BeanDenDekkerCode->code.'"'.$separator.'"'.$data_partenza.'"'.$separator.'""';
		$txt .= "\r\n";
		
		foreach($products_fornitori as $chiave => $valore)
		{
			$BeanGicenzeFornitore = new giacenze_fornitori($this->conn, $valore['id_magazzino']);
			$exp_pi = explode(' x ', $BeanGicenzeFornitore->qta_pianale);
			$exp_sc = explode(' x ', $BeanGicenzeFornitore->qta_scatola);
			if(!empty($valore['quantita_pianale']))
				$quantita_acquistata = $valore['quantita_pianale']*($exp_pi[0]);
			elseif(!empty($valore['quantita']))
				$quantita_acquistata = $valore['quantita'];

			$txt .='"'.$BeanGicenzeFornitore->codice.'"'.$separator.'"'.$quantita_acquistata.'"'.$separator.'"'.$exp_sc[1].'"'.$separator.'"'.$BeanGicenzeFornitore->descrizione.'"'.$separator.'""'.$separator.'"'.$BeanGicenzeFornitore->prezzo_sc.'"'.$separator.'"0.00"'.$separator.'"K"';
			$txt .= "\r\n";
		}		

		$fileCsv = APP_ROOT.'/email_excel_fornitori/Order_DenDekker_N_'.$BeanEcmOrdini->id.'_CLI_GA'.$BeanCustomer->customer_code.'.csv';

		$fp = fopen($fileCsv, 'w');
		fwrite($fp, $txt);
		fclose($fp);
		
		return $fileCsv;
	}

	function SendEmail($BeanEcmOrdini, $userAnag, $products, $products_fornitori, $products_fornitori_de)
	{
		ini_set('precision', 12);
		
		$BeanEcmOrdini = $BeanEcmOrdini->vars();
		$userAnag	   = $userAnag->vars();
		$orderId 	   = $BeanEcmOrdini['id'];
		$assignmanet   = $this->tEngine->assignment;
		$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
		$customer = $BeanCustomer->vars();

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Riepilogo ordine N. '.$BeanEcmOrdini['id'].'</title>
				    <meta name="viewport" content="width=device-width">
				</HEAD>
				<body style="background-color:#fff;font-family: Arial, Tahoma, Verdana, FreeSans, sans-serif;font-size:12px">
				<table width="595" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'img/web/custom_logo/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#000000;">
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].',<br> il tuo ordine del '.date("d/m/Y H:i:s").' &egrave; andato a buon fine.<br><br>
						Di seguito ti riportiamo i dettagli del tuo ordine. 
						<br>
						<span style="color:red;">NOTA</span>: Il presente documento &egrave; stato generato automaticamente dal sistema ed &egrave; da considerasi provvisorio.<br>
			            La conferma d\'ordine verr&agrave; inviata in seguito non appena i nostri uffici avranno elaborato la richiesta.
					 	<br>	
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<table width="100%" height="100%" cellpadding="0" style="border:1px solid #000000;">
						<tr style="background-color:#000000;">
							<td colspan="2" style="color:#fff;"><b>Dati Fatturazione</b></td>
						</tr>
						<tr style="color:#000000;">
							<td>Nome</td>
							<td>'.$userAnag['name'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>Cogome</td>
							<td>'.$userAnag['surname'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>Ragione Sociale</td>
							<td>'.$customer['ragione_sociale'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>Indirizzo</td>
							<td>'.$customer['indirizzo'].' '.$customer['cap'].' - '.$customer['citta'].' ('.$customer['provincia'].') - '.$customer['stato'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>P.IVA</td>
							<td>'.$customer['p_iva'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>Telefono Fisso</td>
							<td>'.$customer['fisso'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>FAX</td>
							<td>'.$customer['fax'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>Telefono Mobile</td>
							<td>'.$customer['cellulare'].'</td>
						</tr>
						<tr style="color:#000000;">
							<td>Email</td>
							<td>'.$customer['email'].'</td>
						</tr>
						</table>
					</td>
					<td width="50%" valign="top">';
					
						$html .= '<table width="100%" cellpadding="0" style="border:1px solid #000000;">
									<tr style="background-color:#000000;">
										<td colspan="2" style="color:#fff;"><b>Modalit&aacute; di pagamento</b></td>
									</tr>
									<tr style="color:#000000;">
										<td>';
										$html .= $BeanEcmOrdini['tipo_pagamento'];
										$html .= '
										</td>
									</tr>
									</table>';
		
						
						$html .= '<br><table width="100%" cellpadding="6" style="border:1px solid #000000;">
									<tr style="background-color:#000000;">
									<td colspan="2" style="color:#fff;"><b>Destinazione Merce / Note / Data Consegna Desiderata</b></td>
									</tr>
									<tr style="color:#000000;">
									<td>';
						if(!empty($_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione']))
						{
							$html .= '<tr><td>Trasporto</td><td>'.$_SESSION[session_id()]['final_data_basket']['trasporto'].'</td></tr>';
							$html .= '<tr><td>Nuova Destinazione</td><td></td></tr>';
							$html .= '<tr><td>Destinazione</td><td>'.$_SESSION[session_id()]['final_data_basket']['codice_nuova_destinazione'].'</td></tr>';
							$html .= '<tr><td>Provincia</td><td>'.$_SESSION[session_id()]['final_data_basket']['provincia_nuova_destinazione'].'</td></tr>';
							$html .= '<tr><td>Localit&agrave;</td><td>'.$_SESSION[session_id()]['final_data_basket']['localita_nuova_destinazione'].'</td></tr>';
							$html .= '<tr><td>Indirizzo</td><td>'.$_SESSION[session_id()]['final_data_basket']['indirizzo_nuova_destinazione'].'</td></tr>';
						}
						else
						{
							foreach ($_SESSION['LoggedUser']['destinazioni'] as $dest)
							{
								if($_SESSION[session_id()]['final_data_basket']['destinazione'] == $dest['codice_etiflor'])
								{
									$destinazione = $dest['ragione_sociale'].' '.$dest['indirizzo'].' '.$dest['citta'].' '.$dest['cap'].' ('.$dest['provincia'].') ';
									break;
								}
							}
							$html .= '<tr><td>Trasporto</td><td>'.$_SESSION[session_id()]['final_data_basket']['trasporto'].'</td></tr>';
							$html .= '<tr><td>Destinazione</td><td>'.$destinazione.'</td></tr>';
						}
							
						$html .= '<tr><td>Data di consegna desiderata</td><td>'.$_SESSION['user_choice']['date'].'</td></tr>';
						$html .= '<tr><td>Nota Ordine</td><td>'.$_SESSION[session_id()]['final_data_basket']['final_note'].'</tr>';
						$html .= '</table>';

						$html .= '<br>
						<table width="100%" cellpadding="6" style="border:1px solid #000000;">
						<tr style="background-color:#000000;">
						<td colspan="2" style="color:#fff;"><b>Totale occupazione carrelli</b></td>
						</tr>
						<tr style="color:#000000;">';
						if($_SESSION[session_id()]['basket']['n_carrelli'] == 1)
						{
							if($_SESSION[session_id()]['basket']['perc_occupazione'] <= 20)
								$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']).'<img alt="" src="'.WWW_ROOT.'img/web/carrello_low.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %</td>';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 40)
							$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']).'<img alt="" src="'.WWW_ROOT.'img/web/carrello_low_2.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %</td>';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 55)
							$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']).'<img alt="" src="'.WWW_ROOT.'img/web/carrello_low_3.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %</td>';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 75)
							$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']).'<img alt="" src="'.WWW_ROOT.'img/web/carrello_medium.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %</td>';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 90)
							$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']).'<img alt="" src="'.WWW_ROOT.'img/web/carrello_medium_2.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %</td>';
							else
								$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']).'<img alt="" src="'.WWW_ROOT.'img/web/carrello_full.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %</td>';
						}
						else
						{
							$html .= '<td>'.($_SESSION[session_id()]['basket']['n_carrelli']-1);
						
							// 						for ($i=0;$i<$_SESSION[session_id()]['basket']['n_carrelli'];$i++)
							$html .= '<img alt="" src="'.WWW_ROOT.'img/web/carrello_full.png" width="25" style="margin-bottom: -3px;"> COMPLETI';
							$html .= '<br>';
							if($_SESSION[session_id()]['basket']['perc_occupazione'] <= 0)
								$html .= '';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 20)
							$html .= '1<img alt="" src="'.WWW_ROOT.'img/web/carrello_low.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 40)
							$html .= '1<img alt="" src="'.WWW_ROOT.'img/web/carrello_low_2.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 55)
							$html .= '1<img alt="" src="'.WWW_ROOT.'img/web/carrello_low_3.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 75)
							$html .= '1<img alt="" src="'.WWW_ROOT.'img/web/carrello_medium.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %';
							elseif($_SESSION[session_id()]['basket']['perc_occupazione'] <= 90)
							$html .= '1<img alt="" src="'.WWW_ROOT.'img/web/carrello_medium_2.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %';
							else
								$html .= '<img alt="" src="'.WWW_ROOT.'img/web/carrello_full.png" width="25" style="margin-bottom: -3px;"> '.$this->tEngine->assignment['perc_occupazione'].' %';
						
							$html .= '</td>';
						
						}
						$html .= '</table></td></tr>';

				$html .= '</td></tr>';				
				
				/* ORDINE ETIFLOR */
				if(!empty($_SESSION[session_id()]['basket']))
				{
					$html .= '<tr>
						<td width="50%" valign="top" colspan="2">
							<table width="100%" cellpadding="6" style="border:1px solid #000000;">
							<tr style="background-color:#000000;">
								<td colspan="11" style="color:#fff;"><b>Prodotti acquistati</b></td>
							</tr>';
								
					include_once(APP_ROOT.'/beans/ApplicationSetup.php');
					$BeanApplicationSetup = new ApplicationSetup();
					$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
					
					$peso_spedizione = 0;
					$tmpSess = $_SESSION[session_id()]['basket'];
					unset($tmpSess['n_carrelli']);
					unset($tmpSess['perc_occupazione']);
					foreach ($tmpSess as $key => $product)
					{
						$Content = $product['contenuto'];
						$BeanGiacenze = $product['giacenza'];
						if($key == 0)
						{
							$html .='
								<tr style="color:#000000;">
									<td>CC</td>
									<td>PIA</td>
									<td>Cod. Art.</td>
									<td>Articolo</td>
									<td align="center"nowrap="nowrap">Confezioni </td>
									<td align="center"nowrap="nowrap">Imballo</td>
									<td align="center"nowrap="nowrap">Quantit&agrave;</td>
									<td align="center">Prezzo</td>';
							$html .= '<td align="center"nowrap="nowrap">Importo</td>
									<td>Iva</td>
									<td align="center">Note</td>
								</tr>';
						}
						$qta_selected = null;
						if($product['box_type'] == 'confezione')
						{
							$qta_selected = $product['basket_qty']['quantita'];
							$qta_linea = $product['basket_qty']['sel_quantita'];
						
							$qta_tot = $product['giacenza']['qta_minima']*$product['basket_qty']['sel_quantita'];
						}
						if($product['box_type'] == 'pianale')
						{
							$qta_conf = $product['giacenza']['qta_pianale'] / $product['giacenza']['qta_minima'];
						
							$qta_selected = $qta_conf * $product['basket_qty']['quantita'];
							$qta_linea = $product['basket_qty']['sel_quantita'];
						
							$qta_tot = $product['giacenza']['qta_pianale']*$product['basket_qty']['sel_quantita'];
						}
						if($product['box_type'] == 'carrello')
						{
							$qta_conf = $product['giacenza']['qta_carrello'] / $product['giacenza']['qta_minima'];
						
							$qta_selected = $qta_conf * $product['basket_qty']['quantita'];
							$qta_linea = $product['basket_qty']['sel_quantita'];
						
							$qta_tot = $product['giacenza']['qta_carrello']*$product['basket_qty']['sel_quantita'];
						}
						
						$html .='
							<tr style="color:#000000;">
								<td align="center">'.number_format(round($qta_tot / $product['giacenza']['qta_carrello'],2), 2, ',', ' ').'</td>
								<td align="center">'.number_format(round($qta_tot / $product['giacenza']['qta_pianale'],2), 2, ',', ' ').'</td>
								<td nowrap="nowrap">'.$BeanGiacenze['bar_code'].'</td>
								<td nowrap="nowrap">'.substr($Content['nome_it'],0,20).'</td>
								<td align="center">'.$qta_selected.'</td>
								<td align="center">'.$product['giacenza']['qta_minima'].'</td>
								<td align="center">'.$qta_tot.'</td>
								<td align="center">'.Currency::FormatEuro($this->tEngine->getPrezzo($product['giacenza'])).'</td>';
						if ($_SESSION['LoggedUser']['customer_data']['stato'] != 'IT'){
						$html .= '<td align="center">'.Currency::FormatEuro( $this->tEngine->getPrezzo($product['giacenza']) * $qta_tot).'</td>
								<td>0%</td>';
						}
						else {
						$html .= '<td align="center">'.Currency::FormatEuro( $this->tEngine->getPrezzo($product['giacenza']) * $qta_tot).'</td>
								<td>'.$product['contenuto']['cod_iva'].'%</td>';
						}
						if(!empty($product['note']) && strtolower($product['note']) != 'inserisci una nota sul prodotto')
							$html .='<td>'.$product['note'].'</td>';
						$html .='</tr>
						';

						$imponibile += $this->tEngine->getPrezzo($product['giacenza']) * $qta_tot;

						if(!empty($product['contenuto']['cod_iva']))
							$mapsCodIva[$product['contenuto']['cod_iva']] += ($this->tEngine->getPrezzo($product['giacenza']) * $qta_tot);
					}
					
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
// if(!empty($tot_iva))
// 	$tot_iva += $iva_spese;
						$html .= '<tr style="color:#000000;">';
						$html .= '<td colspan="10" align="right">';
						$html .= '<table cellpadding="6" width="220">';
						$html .= '<tr style="color:#000000;">';
						
						
						$spese_spedizione = round(str_replace(',', '.', $this->tEngine->getSpeseSpedizione($tmpSess)), 2);
						if($spese_spedizione)
						{
							$spese_spedizione = $spese_spedizione*$_SESSION[session_id()]['basket']['n_carrelli'];
							$iva_trasporto = round( $spese_spedizione * ('0.'.$iva_spese) , 2);

							$html .= '<td align="right">Spese Spedizione</td>';
							$html .= '<td> '.Currency::FormatEuro($spese_spedizione).'</td>';
							$html .= '<td>&nbsp;</td>';
							$subTotale += $spese_spedizione;
// 							$subTotale += $spese_spedizione + round( $spese_spedizione * ('0.'.$iva_spese) , 2);
						}
						
						if(!empty($iva_trasporto))
						{
							$tot_iva += $iva_trasporto;
							$importo_iva += $iva_trasporto;
						}
						
						$html .= '<tr style="color:#000000;">';
						$html .= '<td align="right">Imponibile</td>';
						$html .= '<td>'.Currency::FormatEuro($imponibile).'</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '</tr>';
						if($_SESSION['LoggedUser']['is_foreign'] == 0)
						{
							$html .= '<tr style="color:#000000;">';
							$html .= '<td align="right">IVA</td>';
							if ($_SESSION['LoggedUser']['customer_data']['stato'] != 'IT')
							$html .= '<td>'.Currency::FormatEuro(round(0, 2)).'</td>';
							else
							$html .= '<td>'.Currency::FormatEuro(round($tot_iva, 2)).'</td>';
							$html .= '<td>&nbsp;</td>';
							$html .= '</tr>';
						}
						$subTotale += $imponibile;
						if($_SESSION['LoggedUser']['customer_data']['is_pz_commissione'] == 1)
						{
							$importo_commissione = round( ($subTotale) * ('0.'.$_SESSION['LoggedUser']['customer_data']['perc_commissione']) , 2);
							$subTotale += $importo_commissione;
						}

						$html .= '<tr style="color:#000000;">';
						$html .= '<td align="right">Totale</td>';
						if ($_SESSION['LoggedUser']['customer_data']['stato'] != 'IT')
						$html .= '<td>'.Currency::FormatEuro($subTotale).'</td>';
						else
						$html .= '<td>'.Currency::FormatEuro($subTotale+$tot_iva).'</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '</tr>';
						$html .= '</table>';
						$html .= '</td>';
						$html .= '</tr>';
					}
					//$totale = $tot_imponibile + round($tot_prezzo_iva, 2);
					$html .= '</table>
						</td>
					</tr>';
			$html_footer .= '
			<table>
			<tr>
				<td colspan="2" style="color:#000000;font-size:10px;">
					'.str_replace('|br|', '<br>', EMAIL_FOOTER).'
				</td>
			</tr>
			</table>';
				
			$html.= '</body>
			</html>';
			
			$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
			$ordineMagazzino = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
			foreach ($ordineMagazzino as $value) 
			{
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino($this->conn, $value['id']);
				$BeanEcmOrdiniMagazzino->setImporto_ivato($subTotale+$tot_iva);
				$BeanEcmOrdiniMagazzino->dbStore($this->conn);			
			}

if($_SESSION['LoggedUser']['username'] == 'siso')
{
// _dump($this->tEngine->assignment);
// _dump($_SESSION[session_id()]);
// _dump($userAnag['email']);
// _dump($BeanCustomer);
//_dump($val);
//_dump($price_it_qty);	
//_dump($BeanEcmOrdini);		
//echo($html);
//exit();
}
		$this->setAttachment($this->attachedOrder);
		/*********************** EMAIL PER L'ESERCENTE ***********************/
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
				"To" 			=> EMAIL_ADMIN_FROM,
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "Ordine web [".$BeanCustomer->ragione_sociale."]",
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);		
		$this->setHtmlText($html.$html_footer);
		$this->mail_factory();
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		$is_send = $this->sendMail('siso77@gmail.com');
		
		/*********************** EMAIL PER L'ESERCENTE ***********************/
		$this->setAttachment('-');

		/*********************** EMAIL PER L'UTENTE ***********************/
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
				"To" 			=> $userAnag['email'],
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "Oddone - Albenga - Riepilogo ordine N. ".$orderId."",
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
		$this->setHtmlText($html.$html_footer);
		$this->mail_factory();

 		$is_send = $this->sendMail($userAnag['email']);
		$is_send = $this->sendMail('siso77@gmail.com');
		/*********************** EMAIL PER L'UTENTE ***********************/

		if(PEAR::isError($is_send))
		{
			print_r($is_send);
			echo "Errore nell'invio della mail!";
			exit;
		}
		
		return $is_send;
	}
	
	function FormatEuro($str)
	{
		$str = round(str_replace(',', '.', $str), 2);
	
		if(strstr($str, "."))
		{
			$exp_price = explode(".", $str);
	
			if(strlen($exp_price[1]) == 1)
				$return = $str."0";
			elseif(strlen($exp_price[1]) == 0)
			$return = $exp_price[0].",00";
			else
				$return = $str;
		}
		else
			$return = $str.",00";
		
		$return = str_replace(',', '.', $return);
		return $return;
	}
	
	function getSpeseSpedizione($peso_spedizione, $idCustomer)
	{
		foreach ($_SESSION['LoggedUser']['spese_spedizione_peso'] as $val)
		{
			if($peso_spedizione <= $val['peso_spese_spedizione_a'])
					return $val['spese_spedizione_peso'];
		}
	 }	
}
?>