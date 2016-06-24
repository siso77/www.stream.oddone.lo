<?php
include_once(APP_ROOT.'/beans/banner.php');
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/users_anag.php');

class Home extends DBSmartyMailAction
{
	function Home()
	{
		parent::DBSmartyMailAction();

		if(!empty($_REQUEST['activate_account']))
		{
			$BeanUsers = new users();
			$BeanUsers->dbGetOneNotActive($this->conn, $_REQUEST['id_user']);
			$BeanUsers->setConfirmation_code(null);
			$BeanUsers->setIs_active(1);
			$BeanUsers->dbStore($this->conn);
			$this->SendEmail($BeanUsers);
		}
		
		$BeanUsers = new users();
		$Users = $BeanUsers->dbGetAllNewUser($this->conn);
		foreach($Users as $key => $val)
		{
			$BeanUserAnag = new users_anag($this->conn, $val['id_anag']);
			$Users[$key]['user_anag'] = $BeanUserAnag->vars();
		}
		$this->tEngine->assign('new_users', $Users);
		
		$BeanUsers = new users();
		$UsersAccess = $BeanUsers->dbGetAllAccess($this->conn);
		$this->tEngine->assign('users_acces', $UsersAccess);
		if(!empty($_REQUEST['get_access_user']))
		{
			$html .='<table style="width:100%">
			<tr>
			<td><b>User</td>
			<td><b>Cliente</td>
			<td><b>P.IVA</td>
			<td><b>Data Ultimo Accesso</td>
			</tr>';
			foreach ($UsersAccess as $value){
				$html .='<tr>';
				$html .= '<td>'.$value['username'].'</td>';
				$html .= '<td>'.$value['ragione_sociale'].'</td>';
				$html .= '<td>'.$value['p_iva'].'</td>';
				$html .= '<td>'.substr($this->tEngine->getFormatDate($value['last_access']), 0, -3).'</td>';
				$html .= '</tr>';
			}
			$html .='</table>';
			$html .='<script type="text/javascript">setTimeout("refreshAccessUser();", 10000);</script>';
			echo $html;
			exit();
		}		
		
		
		$BeanUsers = new users();
		$UsersOnline = $BeanUsers->dbGetAllOnLine($this->conn);
		$this->tEngine->assign('users_online', $UsersOnline);
		if(!empty($_REQUEST['get_user_online']))
		{
			$html .='<table style="width:100%">
			<tr>
			<td><b>User</td>
			<td><b>Cliente</td>
			<td><b>P.IVA</td>
			<td><b>Data Ultimo Accesso</td>
			</tr>';
			foreach ($UsersOnline as $value){
				$html .='<tr>';
				$html .= '<td>'.$value['username'].'</td>';
				$html .= '<td>'.$value['ragione_sociale'].'</td>';
				$html .= '<td>'.$value['p_iva'].'</td>';
				$html .= '<td>'.substr($this->tEngine->getFormatDate($value['last_access']), 0, -3).'</td>';
				$html .= '</tr>';
			}
			$html .='</table>';
			$html .='<script type="text/javascript">setTimeout("refreshUserOnline();", 10000);</script>';
			echo $html;
			exit();
		}
		$BeanBanners = new banner();
		$img_slider = $BeanBanners->dbGetAllByIdCategory($this->conn, 0);
		$this->tEngine->assign('img_slider', $img_slider);

		$this->tEngine->assign('tpl_action', 'Home');
		$this->tEngine->display('Index');
	}
	
	function SendEmail($BeanUsers)
	{
		$BeanUsersAnag = new users_anag($this->conn, $BeanUsers->id_anag);
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_REQUEST['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma di attivazione del tuo account ".PREFIX_META_TITLE,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Registrazione utenti - Pro-Bike.it</title>
				</HEAD>
				<body style="background-color:#fff">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.ECM_WWW_ROOT.'/theme/styles/style2/images/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;font-family: Arial,Verdana,Sans-serif;color: #999;font-size: 1.3em;font-weight: bold;">
						<!--<h3>Krupy S.r.l.</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
					Gentile '.$BeanUsersAnag->name.', <br>
					il tuo account "'.$BeanUsers->username.'" è stato attivato.<br> 
					Da questo momento potrai utilizzare il tuo account per visionare il nostro catalogo prodotti.<br>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Grazie,<br>
						Lo stuff di '.ADMIN_RAGIONE_SOCIALE.'<br><br><br>
					</td>
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
		
		$to = $BeanUsers->username.", siso77@gmail.com, ".EMAIL_ADMIN_TO;
		$is_send = $this->sendMail($to);

		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>