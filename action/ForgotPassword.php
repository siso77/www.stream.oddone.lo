<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/users_type.php");

class ForgotPassword extends DBSmartyMailAction
{
	function ForgotPassword()
	{
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;

		parent::DBSmartyMailAction();

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if($_REQUEST['send'])
			{
				$BeanUsersAnag = new users_anag();
				$BeanUsersAnag->dbGetOneByEmail($this->conn, $_REQUEST['email']);
				
				$BeanUsers = new users();
				$BeanUsers->dbGetOneByIdAnag($this->conn, $BeanUsersAnag->id);
				
// 				$BeanUsers->dbGetOneByUsername($this->conn, $_REQUEST['email']);
				
				if($_REQUEST['secret_response'] == $BeanUsers->secret_response)
				{
					$this->SendEmail($BeanUsers);
					$this->tEngine->assign('confirm_reset_password', true);
				}
				else
				{
					$this->tEngine->assign('step', 2);
					$this->tEngine->assign('error_secret_response', true);
					$this->tEngine->assign('secret_question', $BeanUsers->secret_question);
					$this->tEngine->assign('email', $_REQUEST['email']);
				}
			}
			else 
			{
				$BeanUsers = new users();
				$BeanUsers->dbGetOneByUsername($this->conn, $_REQUEST['email']);
				if($BeanUsers->username)
				{
					$this->tEngine->assign('step', 2);
					$this->tEngine->assign('secret_question', $BeanUsers->secret_question);
					$this->tEngine->assign('email', $_REQUEST['email']);
				}
			}
		}
		else
			$this->tEngine->assign('step', 1);
		
		$this->tEngine->assign('tpl_action', 'ForgotPassword');
		$this->tEngine->display('Index');
	}
	
	function getNewPassword($user)
	{
		$arr = str_split('ABCDEFGHIJKLMNOP');
		shuffle($arr);
		$arr = array_slice($arr, 0, 6);
		$password = implode('', $arr);
		$user_exists = $user->dbGetAllByPassword($this->conn, md5($password).PASSWORD_SALT);
		if($user_exists)
			return $this->getNewPassword($user);

		return $password;
	}
	
	function SendEmail($user)
	{
		$password = $this->getNewPassword($user);
		$user->setPassword(md5($password).PASSWORD_SALT);
		$user->setPwd_resetted(1);
		$user->dbStore($this->conn);
		
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_REQUEST['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Nuova password per ".PREFIX_META_TITLE." Flor. Webshop",
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Reset Password - Pro-Bike.it</title>
				</HEAD>
				<body>
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'/img/web/custom_logo/logo.png" style="width:200px;"></td>
					<td align="left" style="color:#fff;font-size:22px;font-family: Arial,Verdana,Sans-serif;color: #999;font-size: 1.3em;font-weight: bold;"><h3>'.PREFIX_META_TITLE.'</h3></td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Gentile cliente,
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						abbiamo ricevuto una tua richiesta di recupero password dimenticata per il tuo account utente ['.$user->username.'] nel nostro webshop.
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						La nuova password &egrave;: '.$password.'
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Dopo aver effettuato il <a href="'.WWW_ROOT.'?act=Login">login</a> con la nuova password potrai cambiarla accedendo ai dati personali del tuo <a href="'.WWW_ROOT.'?act=MyAccount">account</a>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Distinti saluti,
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						'.str_replace('|br|', '',EMAIL_FOOTER).'
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
		
		$is_send = $this->sendMail("siso77@gmail.com");
		$is_send = $this->sendMail($_REQUEST['email']);
		
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}

		return $is_send;
	}
}
?>