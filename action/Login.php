<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_type.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/sconti_customer.php");
include_once(APP_ROOT."/beans/destinazioni.php");
include_once(APP_ROOT."/beans/spese_spedizione.php");

class Login extends DBSmartyAction
{
	function Login()
	{
		parent::DBSmartyAction();
		
		if(!empty($_REQUEST['from_admin']))
		{
			$_SESSION[session_id()] = null;
			$BeanWoraUsers = new users();
			$BeanWoraUsers->dbGetOne($this->conn, $_REQUEST['id']);
			$result = $BeanWoraUsers->vars();

			$_SESSION['LoggedUser'] = $result;
			$BeanWoraUsersType = new users_type();
			$userType = $BeanWoraUsersType->dbGetOne($this->conn, $_SESSION['LoggedUser']['id_type']);
				
			$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
			$BeanUserAnag = new users_anag($this->conn, $result['id_anag']);
			$_SESSION['LoggedUser']['user_anag'] = $BeanUserAnag->vars();
			$_SESSION['LoggedUser']['listino'] = $BeanCustomer->listino;
			$_SESSION['LoggedUser']['is_foreign'] = $BeanCustomer->is_foreign;
			$_SESSION['LoggedUser']['tipo_pagamento'] = $BeanCustomer->tipo_pagamento;
			$_SESSION['LoggedUser']['customer_data'] = $BeanCustomer->vars();
				
			$BeanDestinazioni = new destinazioni();
			$destinazioni = $BeanDestinazioni->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
			$_SESSION['LoggedUser']['destinazioni'] = $destinazioni;
				
			$BeanScontiCustomer = new sconti_customer();
			$sconti = $BeanScontiCustomer->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
			$_SESSION['LoggedUser']['sconto'] = $sconti;
				
			$BeanSpeseSpedizione = new spese_spedizione();
			$_SESSION['LoggedUser']['spese_spedizione_peso'] = $BeanSpeseSpedizione->dbGetAll($this->conn, $BeanCustomer->id);

			$_SESSION['LoggedUser']['userType'] = $userType['name'];
			
			if($_SESSION['LoggedUser']['customer_data']['stato'] != 'IT')
				$lang = '&lang='.strtolower($_SESSION['LoggedUser']['customer_data']['stato']);
				
			$this->_redirect('?act=ShoppingCart'.$lang);
		}
				
		if(!$this->tEngine->isValidForm($_REQUEST))
		{
			$_SESSION['SECURE_AUTH'] = null;
			$this->_redirect('?act=Home');
		}
		
		if(!empty($_REQUEST['new_pwd']))
		{
			$BeanWoraUsers = new users($this->conn, $_SESSION['LoggedUser']['id']);
			$BeanWoraUsers->setPassword(md5($_REQUEST['new_password']).PASSWORD_SALT);
			$BeanWoraUsers->setPwd_resetted(0);
			$BeanWoraUsers->dbStore($this->conn);
			unset($_SESSION);
			$this->_redirect('?act=Login');
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$BeanWoraUsers = new users();
			$result = $BeanWoraUsers->login($this->conn, mysql_real_escape_string($_REQUEST['username']), md5(mysql_real_escape_string($_REQUEST['password'])).PASSWORD_SALT);
			if(is_array($result) && $result != array())
			{
				$BeanWoraUsers->setLast_access();
				$BeanWoraUsers->dbStore($this->conn);
				
				$_SESSION['LoggedUser'] = $result;
				$BeanWoraUsersType = new users_type();
				$userType = $BeanWoraUsersType->dbGetOne($this->conn, $_SESSION['LoggedUser']['id_type']);

				$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
				$BeanUserAnag = new users_anag($this->conn, $result['id_anag']);
				$_SESSION['LoggedUser']['user_anag'] = $BeanUserAnag->vars();
				$_SESSION['LoggedUser']['listino'] = $BeanCustomer->listino;
				$_SESSION['LoggedUser']['is_foreign'] = $BeanCustomer->is_foreign;
				$_SESSION['LoggedUser']['tipo_pagamento'] = $BeanCustomer->tipo_pagamento;
				$_SESSION['LoggedUser']['customer_data'] = $BeanCustomer->vars();
				
				$BeanDestinazioni = new destinazioni();
				$destinazioni = $BeanDestinazioni->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
				$_SESSION['LoggedUser']['destinazioni'] = $destinazioni;
				
				$BeanScontiCustomer = new sconti_customer();
				$sconti = $BeanScontiCustomer->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
				$_SESSION['LoggedUser']['sconto'] = $sconti;
								
				$BeanSpeseSpedizione = new spese_spedizione();
				$_SESSION['LoggedUser']['spese_spedizione_peso'] = $BeanSpeseSpedizione->dbGetAll($this->conn, $BeanCustomer->id);
				
				$_SESSION['LoggedUser']['userType'] = $userType['name'];
				
				if($_SESSION['LoggedUser']['customer_data']['stato'] != 'IT')
					$lang = '&lang='.strtolower($_SESSION['LoggedUser']['customer_data']['stato']);

				if(!empty($_REQUEST['return_uri']))
					$_SESSION[session_id()]['return_uri'] = $_REQUEST['return_uri'];

				if(!empty($_SESSION[session_id()]['return']))
					$this->_redirect('?act='.$_SESSION[session_id()]['return'].$lang);
				elseif(!empty($_SESSION[session_id()]['return_uri']))
					$this->_redirect($_SESSION[session_id()]['return_uri'].$lang);
				else
					$this->_redirect('?act=Search'.$lang);
				exit();
			}
			else
			{
//				if(empty($_REQUEST['username']))
//					echo 'error_login_username';
//				elseif(empty($_REQUEST['password']))
//					echo 'error_login_password';
//				else
//					echo 'error_login';
//				exit();
				$this->tEngine->assign('error_message', 'lbl_error_login');
				$this->tEngine->assign('post_data', $_POST);
			}
		}
		
		
		$this->tEngine->assign('tpl_action', 'Login');
		$this->tEngine->display('Index');
	}
}
?>