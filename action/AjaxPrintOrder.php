<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/gruppi_merceologici.php");
include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
include_once(APP_ROOT.'/libs/TemplateClass/Template_Orders_PDF.php');

include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");
include_once(APP_ROOT."/beans/customer.php");

class AjaxPrintOrder extends DBSmartyAction
{
	function AjaxPrintOrder()
	{
		parent::DBSmartyAction();

		$BeanEcmOrdini = new ecm_ordini($this->conn, $_REQUEST['id']);
		$ordine = $BeanEcmOrdini->vars();
		$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
		$ordini_magazzino = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $ordine['id']);
		$BeanCustomer = new customer($this->conn, $ordine['id_user']);
		
		$pdf=new PDF_MC_Table();
		$pdf->AddPage('L');
		$pdf->SetFont('Arial','',10);
		
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
		
		//Table with
		$pdf->SetWidths(array(15,16,14,52,15,15,12,12,15,12,18,35,15,10,15,22));
		srand(microtime()*1000000);

		$pdf->setX(2);
		$pdf->Row(array('Img','Vbn',$this->tEngine->getTranslation('Gruppo'),$this->tEngine->getTranslation('Descrizione'),$this->tEngine->getTranslation('Colore'),
				'S1', 'S2', 'MS', $this->tEngine->getTranslation('Imballi'), 'Q x I', 'Q '.$this->tEngine->getTranslation('Totale'), $this->tEngine->getTranslation('Note'),
				$this->tEngine->getTranslation('Prezzo'), $this->tEngine->getTranslation('IVA'), $this->tEngine->getTranslation('Prezzo Totale'), $this->tEngine->getTranslation('Urgente')));
		
		$currency = chr(128);
		foreach ($ordini_magazzino as $key => $value)
		{
			$BeanContent = new content($this->conn, $value['id_content']);
			$value['contenuto'] = $BeanContent->vars();
			$BeanGiacenze = new giacenze($this->conn, $value['id_magazzino']);
			$value['giacenza'] = $BeanGiacenze->vars();
				
			$BeanGM = new gruppi_merceologici($this->conn, $value['contenuto']['id_gm']);
				
			$pdf->SetFont('Arial','',8);
			$pdf->setX(2);
			$image = null;
			$image = $this->tEngine->getImageFromVbn($value['contenuto']['vbn']);
			$product_image = $this->tEngine->dbGetImageProductFromBarCode($value['giacenza']['bar_code']);
			if(empty($image)){
				$obj_image = $this->tEngine->dbGetImageFromBarCode($value['giacenza']['bar_code']);
				$product_image = $this->tEngine->dbGetImageProductFromBarCode($value['giacenza']['bar_code']);
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

			$indispensabile = !empty($value['indispensabile']) ? 'Si' : '';

			$tot_prod = str_replace(',','.',$value['importo']);
			$pdf->Row(array(
						$im,
						$value['contenuto']['vbn'],
						$BeanGM->gruppo,
						$value['contenuto']['nome_it'],
						$value['contenuto']['C3'],
						$value['giacenza']['C4'],
						substr($value['giacenza']['dimensione'], 0, 7),
						$value['giacenza']['openstage'],
						$value['quantita'],
						$value['giacenza']['quantita'],
						$value['quantita'],
						$value['nota'].'',
						$currency.' '.$this->tEngine->getFormatPrice((str_replace(',','.',$value['importo'])/$value['quantita'])),
						$value['contenuto']['cod_iva'],
						$this->tEngine->getFormatPrice($tot_prod),
						$indispensabile
				),5
			);
			$tot += $tot_prod;

		}
		$pdf->SetX(248);
		$pdf->SetWidths(array(10,15));
		$pdf->Row(array('Tot.', $currency.' '.$this->tEngine->getFormatPrice($tot)));
		$pdf->Output();		
		exit();

	}
}
