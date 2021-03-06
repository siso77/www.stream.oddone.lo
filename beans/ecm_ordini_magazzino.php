<?php

class ecm_ordini_magazzino extends BeanBase
{
	var $id;
	var $id_ordine;
	var $id_magazzino;
	var $id_content;
	var $quantita;
	var $name_it;
	var $nota;
	var $indispensabile;
	var $importo;
	var $box_type;
	var $importo_ivato;
	
	function ecm_ordini_magazzino($conn=null, $id=null)
	{
		parent::BeanBase();
		
		$this->table_name = "ecm_ordini_magazzino";
		
		if(isset($id))
		{
			if(is_array($id))
				$this->fill($id);
			elseif(is_numeric($id) && $id>0)
				$this->dbGetOne($conn, $id);
		}
	}

	function dbGetOne($db=null, $id=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$query="SELECT * FROM ".$this->table_name." WHERE id=". $id ;
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

	function dbGetAllByIdOrdine($db=null, $idOrdine)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE id_ordine = ".$idOrdine;
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
		$query="SELECT * FROM ".$this->table_name;
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
		$this->setID($id);
		
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

	function dbDelete($db=null, $ID=null, $is_logical = true)
	{
		$query = "DELETE FROM ".$this->table_name." WHERE id = ".$ID;

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

	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query = "";
		
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

	function getId_ordine(){return $this->id_ordine;}

	function setId_ordine($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_ordine = (int)$value;
	}

	function getId_magazzino(){return $this->id_magazzino;}

	function setId_magazzino($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_magazzino = (int)$value;
	}

	function getId_content(){return $this->id_content;}

	function setId_content($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_content = (int)$value;
	}

	function getQuantita(){return $this->quantita;}

	function setQuantita($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->quantita = (string)$value;
	}

	function getName_it(){return $this->name_it;}

	function setName_it($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->name_it = (string)$value;
	}
	
	function getNota(){return $this->nota;}
	
	function setNota($value= null)
	{
		$this->nota = (string)$value;
	}
	
	function getIndispensabile(){return $this->indispensabile;}
	
	function setIndispensabile($value= null)
	{
		$this->indispensabile = (int)$value;
	}
	
	function getImporto(){return $this->importo;}

	function setImporto($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->importo = (string)$value;
	}

	function getBox_type(){return $this->box_type;}

	function setBox_type($value= null)
	{
		$this->box_type = $value;
	}
	
	function getImporto_ivato(){return $this->Importo_ivato;}

	function setImporto_ivato($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->Importo_ivato = (string)$value;
	}
}
?>