<?php

class giacenze extends BeanBase
{
	var $id;
	var $id_gm;
	var $bar_code;
	var $id_content;
	var $id_fornitore;
	var $id_fornitore_srl;
	var $C1;
	var $C2;
	var $C3;
	var $C4;
	var $C5;
	var $prezzo_0;
	var $prezzo_1;
	var $prezzo_2;
	var $prezzo_3;
	var $prezzo_4;
	var $prezzo_5;
	var $prezzo_6;
	var $prezzo_7;
	var $prezzo_8;
	var $prezzo_9;
	var $quantita;
	var $quantita_mazzo;
	var $disponibilita;
	var $stato;
	var $promo;
	var $dimensione;
	var $scelta;
	var $fusto;
	var $produttore;
	var $openstage;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $operatore;
	
	var $prezzo_acquisto;
	var $visibile;
	var $in_home;
	var $qta_minima;
	var $qta_pianale;
	var $qta_carrello;
	var $qta_min_ordine;
	
	function giacenze($conn=null, $id=null)
	{
		parent::BeanBase();

		$this->table_name = "giacenze";

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

	function dbGetAllCaratteristiche($db=null, $caratteristica)
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT DISTINCT(".$caratteristica.") as value FROM ".$this->table_name." WHERE is_active = 1 ORDER BY ".$caratteristica." ASC";
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
	
	function dbGetAllIdKey($db=null, $key = 'id')
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
			$values[$row[$key]]=$row;
	
		$result->free();
		return $values;
	}
	
	function _dbAdd($db=null)
	{
		if (!$this->_is_connection($db))
			return false;

						$id = $db->nextId($this->table_name);
$this->setID($id);
																																																																																				$this->setIs_active(1);
																		
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

	function dbSearch($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();

		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1 ".$search;
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
	
	
		$this->id = (int)  $value;
	}
	
	function getId_gm(){return $this->id_gm;}
	
	function setId_gm($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->id_gm = (int)  $value;
	}
	
	function getBar_code(){return $this->bar_code;}
	
	function setBar_code($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->bar_code = (string)  mysql_real_escape_string($value);
	}
	
	function getId_content(){return $this->id_content;}
	
	function setId_content($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->id_content = (int)  $value;
	}
	
	function getId_fornitore(){return $this->id_fornitore;}
	
	function setId_fornitore($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->id_fornitore = (int)  $value;
	}
	
	function getId_fornitore_srl(){return $this->id_fornitore_srl;}
	
	function setId_fornitore_srl($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->id_fornitore_srl = (int)  $value;
	}
	
	function getC1(){return $this->C1;}
	
	function setC1($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->C1 = (string)  mysql_real_escape_string($value);
	}
	
	function getC2(){return $this->C2;}
	
	function setC2($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->C2 = (string)  mysql_real_escape_string($value);
	}
	
	function getC3(){return $this->C3;}
	
	function setC3($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->C3 = (string)  mysql_real_escape_string($value);
	}
	
	function getC4(){return $this->C4;}
	
	function setC4($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->C4 = (string)  mysql_real_escape_string($value);
	}
	
	function getC5(){return $this->C5;}
	
	function setC5($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->C5 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_0(){return $this->prezzo_0;}
	
	function setPrezzo_0($value= null)
	{
	
	
		$this->prezzo_0 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_1(){return $this->prezzo_1;}
	
	function setPrezzo_1($value= null)
	{
	
	
		$this->prezzo_1 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_2(){return $this->prezzo_2;}
	
	function setPrezzo_2($value= null)
	{
	
	
		$this->prezzo_2 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_3(){return $this->prezzo_3;}
	
	function setPrezzo_3($value= null)
	{
	
	
		$this->prezzo_3 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_4(){return $this->prezzo_4;}
	
	function setPrezzo_4($value= null)
	{
	
	
		$this->prezzo_4 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_5(){return $this->prezzo_5;}
	
	function setPrezzo_5($value= null)
	{
	
	
		$this->prezzo_5 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_6(){return $this->prezzo_6;}
	
	function setPrezzo_6($value= null)
	{
	
	
		$this->prezzo_6 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_7(){return $this->prezzo_7;}
	
	function setPrezzo_7($value= null)
	{
	
	
		$this->prezzo_7 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_8(){return $this->prezzo_8;}
	
	function setPrezzo_8($value= null)
	{
	
	
		$this->prezzo_8 = (string)  mysql_real_escape_string($value);
	}
	
	function getPrezzo_9(){return $this->prezzo_9;}
	
	function setPrezzo_9($value= null)
	{
	
	
		$this->prezzo_9 = (string)  mysql_real_escape_string($value);
	}
	
	function getQuantita(){return $this->quantita;}
	
	function setQuantita($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->quantita = (int)  $value;
	}
	
	function getQuantita_mazzo(){return $this->quantita_mazzo;}
	
	function setQuantita_mazzo($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->quantita_mazzo = (int)  $value;
	}
	
	function getDisponibilita(){return $this->disponibilita;}
	
	function setDisponibilita($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->disponibilita = (int)  $value;
	}
	
	function getStato(){return $this->stato;}
	
	function setStato($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->stato = (string)  mysql_real_escape_string($value);
	}
	
	function getPromo(){return $this->promo;}
	
	function setPromo($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->promo = (int)  $value;
	}
	
	function getDimensione(){return $this->dimensione;}
	
	function setDimensione($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->dimensione = (string)  mysql_real_escape_string($value);
	}
	
	function getScelta(){return $this->scelta;}
	
	function setScelta($value= null)
	{
		if(strlen($value) > 5)
			$value = substr($value, 0, 5);
	
	
		$this->scelta = (string)  mysql_real_escape_string($value);
	}
	
	function getFusto(){return $this->fusto;}
	
	function setFusto($value= null)
	{
		if(strlen($value) > 10)
			$value = substr($value, 0, 10);
	
	
		$this->fusto = (string)  mysql_real_escape_string($value);
	}
	
	function getProduttore(){return $this->produttore;}
	
	function setProduttore($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->produttore = (string)  mysql_real_escape_string($value);
	}
	
	function getOpenstage(){return $this->openstage;}
	
	function setOpenstage($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->openstage = (string)  mysql_real_escape_string($value);
	}
	
	
	function getPrezzo_acquisto(){return $this->prezzo_acquisto;}
	
	function setPrezzo_acquisto($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->prezzo_acquisto = (string)  mysql_real_escape_string($value);
	}
	
	function getVisibile(){return $this->visibile;}
	
	function setVisibile($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->visibile = (string)  mysql_real_escape_string($value);
	}
	
	function getIn_home(){return $this->in_home;}
	
	function setIn_home($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
		$this->in_home = (string)  mysql_real_escape_string($value);
	}
	
	function getQta_minima(){return $this->qta_minima;}
	
	function setQta_minima($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
		$this->qta_minima = (string)  mysql_real_escape_string($value);
	}
	
	function getQta_pianale(){return $this->qta_pianale;}
	
	function setQta_pianale($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
		$this->qta_pianale = (string)  mysql_real_escape_string($value);
	}

	function getQta_carrello(){return $this->qta_carrello;}
	
	function setQta_carrello($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
		$this->qta_carrello = (string)  mysql_real_escape_string($value);
	}

	function getQta_min_ordine(){return $this->qta_min_ordine;}
	
	function setQta_min_ordine($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
		$this->qta_min_ordine = (string)  mysql_real_escape_string($value);
	}
	
	function getData_inserimento_riga(){return $this->data_inserimento_riga;}
	
	function setData_inserimento_riga($value= null)
	{
	
		if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga!";
			exit;
		}
	
		$this->data_inserimento_riga = (string)  mysql_real_escape_string($value);
	}
	
	function getData_modifica_riga(){return $this->data_modifica_riga;}
	
	function setData_modifica_riga($value= null)
	{
	
		if(strrpos($value, "-") != 7)
		{
			echo "Errore nel settaggio della properies data_modifica_riga!";
			exit;
		}
	
		$this->data_modifica_riga = (string)  mysql_real_escape_string($value);
	}
	
	function getIs_active(){return $this->is_active;}
	
	function setIs_active($value= null)
	{
		if(strlen($value) > 11)
			$value = substr($value, 0, 11);
	
	
		$this->is_active = (int)  $value;
	}
	
	function getOperatore(){return $this->operatore;}
	
	function setOperatore($value= null)
	{
		if(strlen($value) > 255)
			$value = substr($value, 0, 255);
	
	
		$this->operatore = (string)  mysql_real_escape_string($value);
	}
}
?>