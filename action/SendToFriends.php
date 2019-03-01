<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");

class SendToFriends extends DBSmartyMailAction
{
	var $className;

	function SendToFriends()
	{
		parent::DBSmartyMailAction();
		
		require_once(APP_ROOT.'/libs/ext/recaptcha-php/recaptchalib.php');
		$privatekey = GOOGLE_RECAPCHA_PRIVATE_KEY;
		$resp = recaptcha_check_answer ($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_REQUEST["recaptcha_challenge_field"],
				$_REQUEST["recaptcha_response_field"]);

		if (!$resp->is_valid)
		{
			$this->_redirect('?error_captcha=1');
			exit();
		}
		$this->className = get_class($this);
		if(!empty($_REQUEST['id_giacenza']))
		$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza']);
		$BeanContent = new content($this->conn, $BeanGiacenze->id_content);
		$giacenza = $BeanContent->vars();
				
		$_SESSION[$this->className]['product']['bar_code'] = $BeanGiacenze->bar_code;
		$_SESSION[$this->className]['product']['nome'] = $BeanContent->nome_it;
		$_SESSION[$this->className]['product']['descrizione_it'] = $BeanContent->descrizione_it;
		$vars = $BeanGiacenze->vars();
		$_SESSION[$this->className]['product']['prezzo'] = Currency::FormatEuro($this->tEngine->getPrezzo($giacenza));;
			
		$_SESSION[$this->className]['product']['link'] = WWW_ROOT.'?act=ProductInfo&id_giacenza='.$BeanGiacenze->id_content;
		
// 		if(!empty($_REQUEST['id']))
// 		{				
// 			$BeanContent = new content($this->conn, $_REQUEST['id']);
			
// 			$product_image = $this->tEngine->getImageFromVbn($BeanContent->vbn);
// 			$_SESSION[$this->className]['product']['nome'] = $BeanContent->nome_it;
// 			$_SESSION[$this->className]['product']['colore'] = $BeanContent->C3;
				
// 			if(!empty($product_image))
// 				$_SESSION[$this->className]['product']['image'] = $product_image;
// 			else
// 				$_SESSION[$this->className]['product']['image'] = $BeanContent->vbn_image;
// 		}
// 		else
// 		{
// 			$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza']);
// 			$BeanContent = new content($this->conn, $BeanGiacenze->id_content);
// 			$product_image = $this->tEngine->dbGetImageProductFromBarCode($BeanGiacenze->bar_code);
			
// 			$_SESSION[$this->className]['product']['bar_code'] = $BeanGiacenze->bar_code;
// 			$_SESSION[$this->className]['product']['nome'] = $BeanContent->nome_it;
// 			$_SESSION[$this->className]['product']['diposnibilita'] = round($BeanGiacenze->disponibilita / $BeanGiacenze->quantita, 0).' x '.$BeanGiacenze->quantita;
// 			$_SESSION[$this->className]['product']['dimensione'] = $BeanGiacenze->dimensione;
// 			$_SESSION[$this->className]['product']['scelta'] = $BeanGiacenze->scelta;
// 			$_SESSION[$this->className]['product']['fusto'] = $BeanGiacenze->fusto;
// 			$_SESSION[$this->className]['product']['produttore'] = $BeanGiacenze->produttore;
// 			$_SESSION[$this->className]['product']['openstage'] = $BeanGiacenze->openstage;
// 			$_SESSION[$this->className]['product']['colore'] = $BeanGiacenze->C1;
// 			$_SESSION[$this->className]['product']['altezza'] = $BeanGiacenze->C4;
// 			$_SESSION[$this->className]['product']['provenienza'] = $BeanGiacenze->C5;
// 			$vars = $BeanGiacenze->vars();
// 			$_SESSION[$this->className]['product']['prezzo'] = $vars[$this->key_prezzo];
			
// 			if(!empty($product_image))
// 				$_SESSION[$this->className]['product']['image'] = $product_image;
// 			else
// 				$_SESSION[$this->className]['product']['image'] = $BeanContent->vbn_image;			
// 		}

		$_SESSION[$this->className]['personal_email'] = $_REQUEST['personal_email'];
		$_SESSION[$this->className]['frined_email'] = $_REQUEST['frined_email'];
		$_SESSION[$this->className]['text_mail'] = $_REQUEST['text_mail'];
		
// 		if(!$this->checkEmail($_SESSION[$this->className]['personal_email']))
// 		{
// 			unset($_SESSION[$this->className]);
// 			$this->_redirect('?act=ProductInfo&id_giacenza='.$_REQUEST['id_giacenza'].'&error_friends=1');
// 		}
		$params = '&id_giacenza='.$BeanGiacenze->id_content;
			
		if(!$this->checkEmail($_SESSION[$this->className]['frined_email']))
		{
			unset($_SESSION[$this->className]);
			$this->_redirect('?act=ProductInfo'.$params.'&error_personal=1');
		}
		$this->SendEmail();
		
		unset($_SESSION[$this->className]);
		$this->_redirect('?act=ProductInfo'.$params.'&confirm=1');
	}
	
	function SendEmail()
	{
		if(!stristr($_SESSION[$this->className]['frined_email'], '@'))
			$_SESSION[$this->className]['frined_email'] = "";
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_SESSION[$this->className]['frined_email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> $_SESSION[$this->className]['personal_email'].' ha condiviso il prodotto '.$_SESSION[$this->className]['product']['nome'],
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);
		
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Richiesta informazioni da '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['nome']).' '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['cognome']).'</title>
				</HEAD>
				<body style="background-color:#fff">
				<table width="80%" height="100%" border="0" cellspacing="10">
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						'.$this->tEngine->getTranslation('Gentile').' '.$_SESSION[$this->className]['frined_email'].',<br> '.mysql_real_escape_string($_SESSION[$this->className]['personal_email']).' '.$this->tEngine->getTranslation('ti segnala il seguente prodotto').':<br>'.$this->tEngine->getTranslation('Testo: ').''.$_SESSION[$this->className]['text_mail'].'<br>
					</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						<img src="'.$_SESSION[$this->className]['product']['image'].'" style="max-width: 300px;"> 
					</td>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						<table style="color:#8F8F8F;font-size:16px;font-size:16px;">';
				if($_SESSION[$this->className]['product']['nome'])
					$html.='<tr>
								<td>'.$this->tEngine->getTranslation('Nome').': </td>
								<td>'.$_SESSION[$this->className]['product']['nome'].'</td>
							</tr>';
				if($_SESSION[$this->className]['product']['bar_code'])
					$html.='<tr>
								<td>'.$this->tEngine->getTranslation('Descrizione').': </td>
								<td>'.$_SESSION[$this->className]['product']['descrizione_it'].'</td>
							</tr>';
				$html.='<tr>
							<td>'.$this->tEngine->getTranslation('Data Invio').': </td>
							<td>'.date('d/m/Y H:i:s').'</td>
						</tr>
						<tr>
							<td colspan="2"><a href="'.$_SESSION[$this->className]['product']['link'].'" target="_blank">'.$this->tEngine->getTranslation('Vedi Prodotto').'</a> </td>
						</tr>
						</table>
					</td>								
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;" valign="top">

					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:12px;">
						<b>'.str_replace('|br|', '<br>', EMAIL_FOOTER).'</b> 
					</td>
				</tr>
			</table>
			</body>
			</html>';
		$this->setHtmlText($html);
		$this->mail_factory();
		
		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		if(stristr($_SESSION[$this->className]['frined_email'], '@'))
			$is_send = $this->sendMail($_SESSION[$this->className]['frined_email']);
		
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
	
	function checkEmail($email) 
	{
	   // Create the syntactical validation regular expression
	   $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
	   // Presume that the email is invalid
	   $valid = 0;
	   // Validate the syntax
	   if (eregi($regexp, $email))
	   {
	      list($username,$domaintld) = split("@",$email);
	      // Validate the domain
	      if (getmxrr($domaintld,$mxrecords))
	         $valid = 1;
	   } else {
	      $valid = 0;
	   }
	   return $valid;
	}		
}
?>