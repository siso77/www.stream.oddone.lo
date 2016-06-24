<?php
include_once(APP_ROOT."/beans/customer.php");
include_once(APP_ROOT."/beans/destinazioni.php");
include_once(APP_ROOT."/beans/ApplicationSetup.php");
include_once(APP_ROOT."/beans/spese_spedizione.php");
include_once(APP_ROOT."/beans/sconti_customer.php");
include_once(APP_ROOT."/beans/c.php");

class CaricaCliente extends DBSmartyAction
{
	function CaricaCliente()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['from']))
			$this->tEngine->assign('from', $_REQUEST['from']);

		if(!empty($_REQUEST['spese_spedizione_peso']))
		{
			for ($i=0;$i<count($_REQUEST['spese_spedizione_peso']);$i++)
			{
				$BeanSpeseSpedizione = new spese_spedizione();
				$BeanSpeseSpedizione->setId_customer($_REQUEST['id']);
				if(!empty($_REQUEST['id_spese_spedizione_peso'][$i]))
					$BeanSpeseSpedizione->setId($_REQUEST['id_spese_spedizione_peso'][$i]);
				$BeanSpeseSpedizione->setSpese_spedizione_peso($_REQUEST['spese_spedizione_peso'][$i]);
				$BeanSpeseSpedizione->setPeso_spese_spedizione_da($_REQUEST['peso_spese_spedizione_da'][$i]);
				$BeanSpeseSpedizione->setPeso_spese_spedizione_a($_REQUEST['peso_spese_spedizione_a'][$i]);
				if(!empty($_REQUEST['spese_spedizione_peso'][$i]))
					$BeanSpeseSpedizione->dbStore($this->conn);
			}
		}

		$BeanCustomer = new customer($this->conn, $_REQUEST['id']);
		$BeanScontiCustomer = new sconti_customer();
		$ScontiCustomer = $BeanScontiCustomer->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
		$this->tEngine->assign('sconti_customer', $ScontiCustomer);
		
		$BeanSconti = new sconti();
		$Sconti = $BeanSconti->dbGetAll($this->conn);
		$this->tEngine->assign('sconti', $Sconti);
		
		$BeanSpeseSpedizione = new spese_spedizione();
		$_SESSION['CaricaCliente']['spese_spedizione_peso'] = $BeanSpeseSpedizione->dbGetAll($this->conn, $_REQUEST['id']);
		$this->tEngine->assign('spese_spedizione_peso', $_SESSION['CaricaCliente']['spese_spedizione_peso']);

		if(!empty($_REQUEST['add_spese']))
		{
			$_SESSION['CaricaCliente']['spese_spedizione_peso'][count($_SESSION['CaricaCliente']['spese_spedizione_peso'])+1] = array('id_spese_spedizione_peso'=>'','spese_spedizione_peso'=>'','peso_spese_spedizione_da'=>'','peso_spese_spedizione_a'=>'');
			$this->tEngine->assign('spese_spedizione_peso', $_SESSION['CaricaCliente']['spese_spedizione_peso']);
		}

		if(!empty($_REQUEST['rem_spese']))
		{
			$elToRemove = $_SESSION['CaricaCliente']['spese_spedizione_peso'][ count($_SESSION['CaricaCliente']['spese_spedizione_peso']) - 1 ];
			if(key_exists('id', $elToRemove))
			{
				$BeanSpeseSpedizione = new spese_spedizione();
				$BeanSpeseSpedizione->dbDelete($this->conn, $elToRemove['id'], false);
			}
			unset($_SESSION['CaricaCliente']['spese_spedizione_peso'][ count($_SESSION['CaricaCliente']['spese_spedizione_peso']) - 1 ]);
			$this->tEngine->assign('spese_spedizione_peso', $_SESSION['CaricaCliente']['spese_spedizione_peso']);
		}

		if(!empty($_REQUEST['id']))
		{
			$BeanCustomer = new customer($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('cliente', $BeanCustomer->vars());

			$BeanDestinazioni = new destinazioni();
			$destinazioni = $BeanDestinazioni->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
			$this->tEngine->assign('destinazioni', $destinazioni);
				
			$BeanApplicationSetup = new ApplicationSetup();
			$tipoConsegna = $BeanApplicationSetup->dbGetAllByField($this->conn, 'tipo_consegna');
			$this->tEngine->assign('tipo_consegna', $tipoConsegna);
		}
			
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
			{
				$BeanCustomer = new customer($this->conn, $_REQUEST['id']);
				$BeanCustomer->fill($_REQUEST);
				if($_REQUEST['is_pz_commissione'] == 'on')
					$BeanCustomer->setIs_pz_commissione(1);
				else
					$BeanCustomer->setIs_pz_commissione(0);
				$id_cliente = $BeanCustomer->dbStore($this->conn);
				$id_cliente = $id_cliente['id'];
			}
			else
			{
				$BeanCustomer = new customer($this->conn, $_REQUEST);
				if($_REQUEST['is_pz_commissione'] == 'on')
					$BeanCustomer->setIs_pz_commissione(1);
				else
					$BeanCustomer->setIs_pz_commissione(0);
				$BeanCustomer->setOperatore($_SESSION['LoggedUser']['username']);
				$id_cliente = $BeanCustomer->dbStore($this->conn);
			}	

			$BeanScontiCustomer = new sconti_customer();
			$ScontiCustomer = $BeanScontiCustomer->dbGetAllByCustomerCode($this->conn, $BeanCustomer->customer_code);
				
			if(!empty($ScontiCustomer))
			{
				$BeanScontiCustomer->dbDelete($this->conn, $BeanCustomer->customer_code);
				$ScontiCustomer = null;
			}
			if(empty($ScontiCustomer) || $ScontiCustomer == array())
			{
				$BeanScontiCustomer->setCustomer_code($BeanCustomer->customer_code);
				$BeanScontiCustomer->setId_sconto($_REQUEST['id_sconto']);
				$BeanScontiCustomer->dbStore($this->conn);
			}
			$this->_redirect('?act=CaricaCliente&edit=1&id='.$id_cliente);
			exit();
		}
		$this->tEngine->assign('tpl_action', 'CaricaCliente');
		$this->tEngine->display('Index');
	}
}
?>