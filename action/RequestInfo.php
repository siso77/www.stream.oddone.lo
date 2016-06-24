<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");

class RequestInfo extends DBSmartyMailAction
{
	var $className;

	function RequestInfo()
	{
		parent::DBSmartyMailAction();
		
		$this->className = get_class($this);
		$_SESSION['request_info']['customer_data']['nome'] = $_REQUEST['nome'];
		$_SESSION['request_info']['customer_data']['cognome'] = $_REQUEST['cognome'];
		$_SESSION['request_info']['customer_data']['telefono'] = $_REQUEST['telefono'];
		$_SESSION['request_info']['customer_data']['cellulare'] = $_REQUEST['cellulare'];
		$_SESSION['request_info']['customer_data']['email'] = $_REQUEST['email'];
		$_SESSION['request_info']['customer_data']['paese'] = $_REQUEST['paese'];
		$_SESSION['request_info']['customer_data']['provincia'] = $_REQUEST['provincia'];
		$_SESSION['request_info']['customer_data']['comune'] = $_REQUEST['comune'];
		$_SESSION['request_info']['customer_data']['indirizzo'] = $_REQUEST['indirizzo'];
		$_SESSION['request_info']['customer_data']['cap'] = $_REQUEST['cap'];
		$_SESSION['request_info']['customer_data']['richiesta'] = $_REQUEST['note'];
		
		require_once(APP_ROOT.'/libs/ext/google_recaptcha/recaptchalib.php');
		$privatekey = GOOGLE_RECAPCHA_PRIVATE_KEY;
		$resp = recaptcha_check_answer ($privatekey,
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]);
		
		if (!$resp->is_valid) 
			$this->_redirect('?act=Contatti&error_captcha=1');
		else 
		{
			include_once(APP_ROOT."/beans/newsletter_emails.php");
			if(empty($_SESSION['LoggedUser']))
			{
				$BeanNewsletterEmails = new newsletter_emails();
				$email_exists = $BeanNewsletterEmails->dbSearch($this->conn, $_REQUEST['email']);
				if(!$email_exists)
				{
					$BeanNewsletterEmails->setEmail($_REQUEST['email']);
					$BeanNewsletterEmails->dbStore($this->conn);
				}			
			}
			$this->SendEmail();
			unset($_SESSION['request_info']['customer_data']);
			$this->_redirect('?act=Contatti&confirm=1');
		}		
	}
	
	function SendEmail()
	{
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_REQUEST['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Richiesta informazioni da ".str_replace("\\", "", $_REQUEST['nome']).' '.str_replace("\\", "", $_REQUEST['cognome']),
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
				<table width="100%" height="100%" border="0" cellspacing="10">
				 <tr>
					<td colspan="2" style="color:#000;font-size:22px;">
						<table width="100%" height="100%" border="0" cellspacing="10">
						 <tr>
						    <td width="500" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'img/web/custom_logo/logo.png" width="200">&nbsp;</td>
						</tr>
				    	</table>
				    </td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Richiesta informazioni da '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['nome']).' '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['cognome']).'.<br>
					</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Richiesta: 
					</td>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						'.str_replace("\\", "", $_SESSION['request_info']['customer_data']['richiesta']).'
					</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;" valign="top">
						Dettaglio richiedente	
					</td>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						<table>
						<tr>
							<td>Nome</td>
							<td>'.str_replace("\\", "", $_SESSION['request_info']['customer_data']['nome']).'</td>
						</tr>
						<tr>
							<td>Cognome</td>
							<td>'.str_replace("\\", "", $_SESSION['request_info']['customer_data']['cognome']).'</td>
						</tr>
						<tr>
							<td>Telefono</td>
							<td>'.$_SESSION['request_info']['customer_data']['telefono'].'</td>
						</tr>
						<tr>
							<td>Cellulare</td>
							<td>'.$_SESSION['request_info']['customer_data']['cellulare'].'</td>
						</tr>
						<tr>
							<td>Email</td>
							<td>'.$_SESSION['request_info']['customer_data']['email'].'</td>
						</tr>
						<tr>
							<td>Indirizzo</td>
							<td>'.str_replace("\\", "", $_SESSION['request_info']['customer_data']['indirizzo']).' '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['comune']).' '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['provincia']).' '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['cap']).' '.str_replace("\\", "", $_SESSION['request_info']['customer_data']['paese']).'</td>
						</tr>
						<tr>
							<td>Data Richiesta</td>
							<td>'.date('d/m/Y H:i:s').'</td>
						</tr>
						</table>
					</td>
					</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						'.str_replace('|br|', '<br>', EMAIL_FOOTER).' 
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();

		if(!$this->checkEmail($_REQUEST['email']))
		{
			$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
						  "To" 			=> $_REQUEST['email'],
						  "Cc" 			=> "", 
						  "Bcc" 		=> "", 
						  "Subject" 	=> "Debug richiesta info ".$_REQUEST['email']." - Addr. ".$_SERVER["REMOTE_ADDR"],
						  "Date"		=> date("r")
						  );
			$this->setHeaders($hdrs);
			$is_send = $this->sendMail('siso77@gmail.com');
			return 1;
		}
		if(!empty($_REQUEST['email']))
			$is_send = $this->sendMail($_REQUEST['email']);

		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);

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