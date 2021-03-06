<?php

class users extends BeanBase
{
	var $id;
	var $id_type;
	var $id_anag;
	var $id_customer;
	var $sconto_fornitori_nl;
	var $sconto_fornitori_de;
	var $username;
	var $password;
	var $secret_question;
	var $secret_response;
	var $confirmation_code;
	var $last_access;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_newsletter_subscribed;
	var $is_t_c_accepted;
	var $is_active;
	var $operatore;

	function users($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "users";
		
		if(isset($id))
		{
			if(is_array($id))
				$this->fill($id);
			elseif(is_numeric($id) && $id>0)
				$this->dbGetOne($conn, $id);
		}
	}

	function login($db=null, $username, $password)
	{
		$ret = false;
		$query="SELECT * FROM ".$this->table_name." WHERE username = '". $username . "' AND password = '". $password ."' AND is_active = 1";
		$result=$db->query($query);
		
		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$ret = $row;
			$this->fill($row);
		}
		$result->free();
		return $ret;
	}
	
	function dbGetOneByUsername($db=null, $username=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE username='". $username."'";
		
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$this->fill($row);

		$result->free();
	}
	
	function dbGetOneNotActive($db=null, $id=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE id=". $id . "";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$this->fill($row);

		$result->free();
	}
	
	function dbGetOne($db=null, $id=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE id=". $id . " AND is_active = 1";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		if($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$this->fill($row);

		$result->free();
	}

	function dbGetAllCustom($db=null, $where)
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE ".$where;
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);
	
		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
	
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[]=$row;
	
		$result->free();
		return $values;
	}
	
	function dbGetAll($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
				$values[]=$row;

		$result->free();
		return $values;
	}

	function dbGetAllNewUser($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 0 AND confirmation_code IS NOT NULL";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
				$values[]=$row;

		$result->free();
		return $values;
	}
	
	function dbGetAllByPassword($db=null, $pwd)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=false;
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1 AND password = '".$pwd."'";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[]=$row;

		$result->free();
		return $values;
	}
	
	function dbGetAllAccess($db=null)
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT users.last_access,
					users.username,
					customer.customer_code,
					customer.nome,
					customer.cognome,
					customer.ragione_sociale,
					customer.p_iva,
					customer.indirizzo,
					customer.provincia,
					customer.stato,
					customer.citta,
					customer.cap,
					customer.cellulare,
					customer.fisso,
					customer.fax,
					customer.email
				FROM users INNER JOIN customer ON users.id_customer = customer.id WHERE last_access IS NOT NULL ORDER BY  last_access DESC ";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);
	
		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
	
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[]=$row;
	
		$result->free();
		return $values;
	}

	function dbGetAllOnLine($db=null)
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT users.last_access, users.username, customer.customer_code, customer.nome, customer.cognome, customer.ragione_sociale, customer.p_iva, customer.indirizzo, customer.provincia, customer.stato, customer.citta, customer.cap, customer.cellulare, customer.fisso, customer.fax, customer.email
				FROM users
				INNER JOIN customer ON users.id_customer = customer.id
				WHERE TIMESTAMPDIFF( MINUTE , last_access, NOW( ) ) <=5 ORDER BY last_access DESC ";
		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);
	
		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql
	
		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values[]=$row;
	
		$result->free();
		return $values;
	}
	
	function _dbAdd($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$id = $db->nextId($this->table_name);
		$this->setId($id);
		$this->setData_inserimento_riga(date('Y-m-d'));
		$this->setData_modifica_riga(date('Y-m-d'));
		$this->setIs_active(0);
		$values=$this->vars();
		
		
		$table_fields=array_keys($values);
		$table_values=array_values($values);

		$sth = $db->autoPrepare($this->table_name, $table_fields, DB_AUTOQUERY_INSERT);		
		$db->execute($sth, $table_values);

		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql

		return $id;
	}

	function _dbUpdate($db=null)
	{
		if(!$this->_is_connection($db))
			return false;

		$id = $this->id;
		$this->setData_modifica_riga(date('Y-m-d'));
		$this->setIs_active(1);

		$values=$this->vars();
		
		unset($values['id']);

		$table_fields = array_keys($values);
		$table_values = array_values($values);

		$sth = $db->autoPrepare($this->table_name, $table_fields, DB_AUTOQUERY_UPDATE, "id = ".$id);
		$db->execute($sth, $table_values);
		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql

		return $this->vars();
	}

	function dbStore($db=null)
	{
		if(!$this->_is_connection($db))
			return false;

		if(isset($this->id) && is_numeric($this->id) && $this->id>0)
			return $this->_dbUpdate($db);
		else
			return $this->_dbAdd($db);
	}

	function fast_edit($db, $id=null, $key="", $value="")
	{
		if(!$this->_is_connection($db))
			return false;
		
		$query="UPDATE ".$this->table_name." SET ".$key."='".$value."' WHERE id =".$id."";

		$db->query($query);

		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql
	}

	function _is_connection($db)
	{
		$ret = false;
		if(is_object($db) && is_subclass_of($db, 'db_common') && method_exists($db, 'simpleQuery') )
			$ret = true;
		return $ret;
	}

	function dbDelete($db=null, $IDS=null, $is_logical = true)
	{
																																																
		if(is_array($IDS) && count($IDS) > 1)
		{
			if($is_logical)
				$query = "UPDATE ".$this->table_name." SET is_active = 0 WHERE id IN (".implode(", ", $IDS).")";
			else
				$query = "DELETE FROM ".$this->table_name." WHERE id IN (".implode(", ", $IDS).")";
		}
		else
		{
			if($is_logical)
				$query = "UPDATE ".$this->table_name." SET is_active = 0 WHERE id = ".$IDS[0];
			else
				$query = "DELETE FROM ".$this->table_name." WHERE id = ".$IDS[0];
		}

		$db->query($query);
		//		Loggo la query sql
		$this->BeanLog("PEAR_DB", $db);
		//		Loggo la query sql		
	}

	function fill($value=null) 
	{ 
		if(!is_array($value)) 
			$value=array(); 	
		
		$props = $this->vars(); 
		foreach($props as $k=>$v) 
		{ 
			$func = "set".ucfirst($k); 
			if(isset($value[$k]))
			{
				$value[$k] = str_replace('"', "''", stripslashes($value[$k]));
								$this->$func($value[$k]);
			}
		}
	}

	function dbSearch($db=null, $where=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
//		$query = "SELECT * FROM ".$this->table_name." WHERE `".$this->table_name."`.is_active = 1 ".$where;
		$query = 'SELECT 
			users.id as id, 
			users.username, 
			users.last_access, 
			users_anag.name, 
			users_anag.surname, 
			users_anag.email, 
			users_type.name as type,
			users.data_inserimento_riga, 
			users.data_modifica_riga
		FROM 
			(
				users INNER JOIN users_type ON users.id_type = users_type.id
			) 
			INNER JOIN users_anag ON users.id_anag = users_anag.id WHERE is_active = 1 '.$where;

		$result=$db->query($query);

		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$values[]=$row;
		}
		$result->free();
		return $values;
	}
	
	function vars() 
	{  
		$vars = get_object_vars($this);
		unset($vars['table_name']);
		return $vars;  
	}
	
	/*			GET e SET		*/	
	function getId(){return $this->id;}

	function setId($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id = (int)$value;
	}

	function getId_type(){return $this->id_type;}

	function setId_type($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_type = (int)$value;
	}

	function getId_anag(){return $this->id_anag;}

	function setId_anag($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_anag = (int)$value;
	}

	function getId_customer(){return $this->id_customer;}

	function setId_customer($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_customer = (int)$value;
	}
	
	function getSconto_fornitori_nl(){return $this->sconto_fornitori_nl;}
	
	function setSconto_fornitori_nl($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->sconto_fornitori_nl = (int)$value;
	}

	function getSconto_fornitori_de(){return $this->sconto_fornitori_de;}
	
	function setSconto_fornitori_de($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
		$this->sconto_fornitori_de = (int)$value;
	}
	
	function getUsername(){return $this->username;}

	function setUsername($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->username = (string)$value;
	}

	function getPassword(){return $this->password;}

	function setPassword($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->password = (string)$value;
	}

	function getSecret_question(){return $this->secret_question;}

	function setSecret_question($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->secret_question = (string)$value;
	}
	
	function getSecret_response(){return $this->secret_response;}

	function setSecret_response($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->secret_response = (string)$value;
	}
	
	function getConfirmation_code(){return $this->confirmation_code;}

	function setConfirmation_code($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->confirmation_code = $value;
	}
	
	
	function getLast_access(){return $this->last_access;}

	function setLast_access($value= null)
	{
		$this->last_access = date('Y-m-d H:i:s');
	}		
	
	function getData_inserimento_riga(){return $this->data_inserimento_riga;}

	function setData_inserimento_riga($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga!";
			exit;
		}
								
		$this->data_inserimento_riga = (string)$value;
	}

	function getData_modifica_riga(){return $this->data_modifica_riga;}

	function setData_modifica_riga($value= null)
	{
				
				if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_modifica_riga!";
			exit;
		}
								
		$this->data_modifica_riga = (string)$value;
	}

	function getIs_newsletter_subscribed(){return $this->is_newsletter_subscribed;}

	function setIs_newsletter_subscribed($value= null)
	{
		if(empty($value))
			$value = 0;
		$this->is_newsletter_subscribed = $value;
	}
	
	function getIs_t_c_accepted(){return $this->is_t_c_accepted;}

	function setIs_t_c_accepted($value= null)
	{
		$this->is_t_c_accepted = $value;
	}
	
	function getIs_active(){return $this->is_active;}

	function setIs_active($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_active = (int)$value;
	}

	function getOperatore(){return $this->operatore;}

	function setOperatore($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->operatore = (string)$value;
	}

			
}
?>