<?php
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class SetDefaultUserToCustomer extends DBSmartyAction
{
	var $className;
	var $operatore = 'default_association_procedure';
	
	function SetDefaultUserToCustomer()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
// exit('Funzionalita non abilitata!');
		$BeanCustomer = new customer();
		$customers = $BeanCustomer->dbGetAll($this->conn);
		
		foreach ($customers as $customer)
		{
// 			if($customer['customer_code'] == '0')
// 			{
// 				$query = 'SELECT MAX(customer_code) as max FROM customer';
// 				$result = mysql_query($query, $this->conn->connection);
// 				$array = mysql_fetch_assoc($result);
// 				$indice = $array['max']+1;
				
// 				$BeanCustomer = new customer($this->conn, $customer['id']);
// 				$BeanCustomer->setCustomer_code($indice);
// 				$BeanCustomer->dbStore($this->conn);
// 			}

			if(!empty($customer['customer_code']))
			{
				$BeanUsers = new users();
				$userExists = $BeanUsers->dbSearch($this->conn, " AND username ='".$customer['customer_code']."'");
				if(!empty($userExists) && $userExists != array())
				{
					$BeanUsers = new users($this->conn, $userExists[0]['id']);
					$BeanUsers->setId_customer($customer['id']);
					$BeanUsers->dbStore($this->conn);
				}
				else
				{
					_dump($userExists);
					_dump($customer);
// 					exit();
				}
			}
			if(!empty($customer['email']) || !empty($customer['customer_code']))
			{
// 				$BeanUsers = new users();
// 				if(!empty($customer['customer_code']))
// 					$userExists = $BeanUsers->dbSearch($this->conn, " AND username ='".$customer['customer_code']."'");
				
// 				if(empty($userExists) || $userExists == array())
// 				{
// 					$BeanUserAnag = new users_anag();
// 					$BeanUserAnag->setName($customer['ragione_sociale']);
// 					$BeanUserAnag->setSurname('-');
// 					$BeanUserAnag->setEmail(strtolower($customer['email']));
// 					$BeanUserAnag->setAddress(strtolower($customer['indirizzo']));
// 					$BeanUserAnag->setCity(strtolower($customer['citta']));
// 					$BeanUserAnag->setCap(strtolower($customer['cap']));
// 					$BeanUserAnag->setProvince(strtoupper($customer['provincia']));
// 					$BeanUserAnag->setNation(strtoupper($customer['stato']));
// 					$BeanUserAnag->setPhone($customer['fisso']);
// 					$BeanUserAnag->setMobile($customer['cellulare']);
// 					$id_user_anag = $BeanUserAnag->dbStore($this->conn);
// 					$BeanUsers = new users();

// 					if(!empty($customer['customer_code']))
// 						$BeanUsers->setUsername($customer['customer_code']);
	
// 					if(!empty($customer['p_iva']))
// 						$BeanUsers->setPassword(md5($customer['p_iva']).PASSWORD_SALT);
// 					else
// 						$BeanUsers->setPassword(md5($customer['customer_code']).PASSWORD_SALT);
	
// 					if(!empty($id_user_anag))
// 						$BeanUsers->setId_anag($id_user_anag);
	
// 					$BeanUsers->setId_customer($customer['id']);
	
// 					$BeanUsers->setIs_newsletter_subscribed(1);
// 					$BeanUsers->setIs_t_c_accepted(1);
// 					$BeanUsers->setOperatore($this->operatore);
// 					$BeanUsers->setData_inserimento_riga(date('Y-m-d'));
// 					$BeanUsers->setData_modifica_riga(date('Y-m-d'));
// 					$BeanUsers->setId_type(3);
// 					$id_user = $BeanUsers->dbStore($this->conn);
	
// 					$Bean = new users();
// 					$Bean->dbGetOneNotActive($this->conn, $id_user);
// 					$Bean->setIs_active(1);
// 					$Bean->dbStore($this->conn);
// 				}
			}
			else
			{
				$data_not_found++;
				_dump($customer);
			}
		}
		$this->_redirect('?act=ListaUtenti');
	}
}
?>