<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/users_type.php");
include_once(APP_ROOT."/beans/newsletter_emails.php");
include_once(APP_ROOT."/beans/customer.php");

class CreateAccount extends DBSmartyMailAction
{
	function CreateAccount()
	{
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;

		parent::DBSmartyMailAction();

// 		require_once(APP_ROOT.'/libs/ext/google_recaptcha/recaptchalib.php');
// 		$privatekey = GOOGLE_RECAPCHA_PRIVATE_KEY;
// 		$resp = recaptcha_check_answer ($privatekey,
// 				$_SERVER["REMOTE_ADDR"],
// 				$_POST["recaptcha_challenge_field"],
// 				$_POST["recaptcha_response_field"]);

// 		if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$resp->is_valid)
// 		{
// 			$this->_redirect('?act=CreateAccount&error_captcha=1');
// 			exit();
// 		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
			$this->SendEmail();
		
		
		$this->tEngine->assign('tpl_action', 'CreateAccount');
		$this->tEngine->display('Index');
	}
	
	function SendEmail($request_code)
	{
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_REQUEST['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Richiesta di registrazione nuovo account ".PREFIX_META_TITLE,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$consegnaCarrelli = ($_REQUEST['consegna_carrelli']) ? 'Si' : 'No';
		$acquistoCC = ($_REQUEST['acquisto_cc']) ? 'Si' : 'No';

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Registrazione utenti - '.PREFIX_META_TITLE.'</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'img/web/custom_logo/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						Nuova richiesta di accreditamento da:'.$_REQUEST['name'].' '.$_REQUEST['lastname'].'
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						Di seguito tutti i dati della richiesta
					</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Nome:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['name'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Cognome:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['lastname'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Tipologia di Vendita:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['tipologia_vendita'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Nome Societ&agrave;:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['company_name'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Email:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['email'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Sito Web:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['web_site'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Nazione:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['web_site'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Citt&agrave;:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['city'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Via:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['street'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Provincia:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['province'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Telefono:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['phone'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Fax:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['fax'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Oggetto:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['object'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Messaggio:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$_REQUEST['message'].'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Servizio Consegna a carrelli:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$consegnaCarrelli.'</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;">Acquisti presso i nostri magazzini Cash&Carry:</td>
					<td style="color:#8F8F8F;font-size:16px;">'.$acquistoCC.'</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						'.ADMIN_RAGIONE_SOCIALE.' - '.ADMIN_INDIRIZZO.' - '.ADMIN_TELEFONO.' '.ADMIN_P_IVA.'
					</td>
				</tr>
			</table>
			</body>
			</html>';
		
		$this->setHtmlText($html);
		$this->mail_factory();

		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		$is_send = $this->sendMail($_REQUEST['email']);

		/* Email per il richiedente*/
//		$hdrs = array("From" 		=> "info@pro-bike.it", 
//					  "To" 			=> $_REQUEST['email'],
//					  "Cc" 			=> "", 
//					  "Bcc" 		=> "", 
//					  "Subject" 	=> "",
//					  "Date"		=> date("r")
//					  );
//		$this->setHeaders($hdrs);
//		$this->setHtmlText($html);
//		$this->mail_factory();
//		$is_send = $this->sendMail($_REQUEST['email']);
//		/* Email per il richiedente*/
//
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>