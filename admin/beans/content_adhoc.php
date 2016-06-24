<?php

class content_adhoc extends BeanBase
{
	var $id;
	var $id_gm;
	var $id_famiglia;
	var $nome_it;
	var $descrizione_it;
	var $nome_en;
	var $descrizione_en;
	var $vbn;
	var $note;
	var $peso;
	var $C1;
	var $C2;
	var $C3;
	var $C4;
	var $C5;
	var $tipo_colore;
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
	var $cod_iva;
	var $have_image;
	var $vbn_image;
	var $data_inserimento_riga;
	var $data_modifica_riga;
	var $is_active;
	var $is_vbn_updated;
	var $operatore;
	var $codice_articolo;

	function content_adhoc($conn=null, $id=null)
	{
		parent::BeanBase();

		$this->table_name = "content_adhoc";

		if(isset($id))
		{
			if(is_array($id))
				$this->fill($id);
			elseif(is_numeric($id) && $id>0)
				$this->dbGetOne($conn, $id);
		}
	}

	function dbFree($db=null, $query = null)
	{
		if (!$this->_is_connection($db) || empty($query))
			return false;

		$result=$db->query($query);
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$ret[] = $row;

		$result->free();
		return $ret;
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
				$values[$row['vbn']]=$row;

		$result->free();
		return $values;
	}

	function dbGetAllIdKey($db=null, $key = 'id')
	{
		if (!$this->_is_connection($db))
			return false;
	
		$values=array();
		$query="SELECT * FROM ".$this->table_name." WHERE is_active = 1 AND operatore = 'StreamImportProcedure'";
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

		return $id;
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

	function dbDeleteAll($db=null, $where = null)
	{
		$query = "DELETE FROM ".$this->table_name."".$where;
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
		
		$query="SELECT
					content_adhoc.id as id_content_adhoc,
					content_adhoc.id_famiglia,
					content_adhoc.id_gm,
					content_adhoc.nome_it,
					content_adhoc.vbn,
					content_adhoc.C1,
					content_adhoc.C2,
					content_adhoc.C3,
					content_adhoc.C4,
					content_adhoc.C5,
					content_adhoc.tipo_colore,
					content_adhoc.prezzo_0,
					content_adhoc.prezzo_1,
					content_adhoc.prezzo_2,
					content_adhoc.prezzo_3,
					content_adhoc.prezzo_4,
					content_adhoc.prezzo_5,
					content_adhoc.prezzo_6,
					content_adhoc.prezzo_7,
					content_adhoc.prezzo_8,
					content_adhoc.prezzo_9,
					content_adhoc.cod_iva,
					content_adhoc.note,
					content_adhoc.have_image,
					content_adhoc.vbn_image,
					famiglie.famiglia,
					gruppi_merceologici.gruppo
				FROM
					content_adhoc
				INNER JOIN famiglie ON content_adhoc.id_famiglia = famiglie.id
				INNER JOIN gruppi_merceologici ON content_adhoc.id_gm = gruppi_merceologici.id
				WHERE content_adhoc.is_active = 1 ".$search;

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
	
	function dbSearchDisponibili($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
		$query="SELECT
					content_adhoc.id,
					content_adhoc.id_famiglia,
					content_adhoc.id_gm,
					content_adhoc.nome_it,
					content_adhoc.vbn,
					content_adhoc.C1,
					content_adhoc.C2,
					content_adhoc.C3,
					content_adhoc.C4,
					content_adhoc.C5,
					content_adhoc.tipo_colore,
					content_adhoc.prezzo_0,
					content_adhoc.prezzo_1,
					content_adhoc.prezzo_2,
					content_adhoc.prezzo_3,
					content_adhoc.prezzo_4,
					content_adhoc.prezzo_5,
					content_adhoc.prezzo_6,
					content_adhoc.prezzo_7,
					content_adhoc.prezzo_8,
					content_adhoc.prezzo_9,
					content_adhoc.cod_iva,
					content_adhoc.note,
					content_adhoc.have_image,
					gruppi_merceologici.gruppo,
					giacenze.id as id_gicenza,
					giacenze.bar_code,
					giacenze.bar_code,
					giacenze.id_fornitore,
					giacenze.prezzo_0 as prezzo_giac,
					giacenze.prezzo_acquisto,
					giacenze.visibile,
					giacenze.qta_minima,
					giacenze.qta_pianale,
					giacenze.qta_carrello,
					giacenze.qta_min_ordine,
					giacenze.quantita,
					giacenze.quantita_mazzo,
					giacenze.disponibilita,
					giacenze.stato,
					giacenze.promo,
					giacenze.in_home,
					giacenze.operatore
				FROM
					content_adhoc
				INNER JOIN gruppi_merceologici ON content_adhoc.id_gm = gruppi_merceologici.id
				INNER JOIN giacenze ON giacenze.id_content_adhoc = content_adhoc.id
				WHERE content_adhoc.is_active = 1 ".$search;

// 		$query="SELECT
// 					content_adhoc.id,
// 					content_adhoc.id_famiglia,
// 					content_adhoc.id_gm,
// 					content_adhoc.nome_it,
// 					content_adhoc.vbn,
// 					content_adhoc.C1,
// 					content_adhoc.C2,
// 					content_adhoc.C3,
// 					content_adhoc.C4,
// 					content_adhoc.C5,
// 					content_adhoc.tipo_colore,
// 					content_adhoc.prezzo_0,
// 					content_adhoc.prezzo_1,
// 					content_adhoc.prezzo_2,
// 					content_adhoc.prezzo_3,
// 					content_adhoc.prezzo_4,
// 					content_adhoc.prezzo_5,
// 					content_adhoc.prezzo_6,
// 					content_adhoc.prezzo_7,
// 					content_adhoc.prezzo_8,
// 					content_adhoc.prezzo_9,
// 					content_adhoc.cod_iva,
// 					content_adhoc.note,
// 					content_adhoc.have_image
// 				FROM
// 					content_adhoc
// 				WHERE content_adhoc.is_active = 1".$search;

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
	
	function dbSearchCounted($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
		$query="SELECT
					count(content_adhoc.id) as num
				FROM
					content_adhoc
				INNER JOIN famiglie ON content_adhoc.id_famiglia = famiglie.id
				INNER JOIN gruppi_merceologici ON content_adhoc.id_gm = gruppi_merceologici.id
				WHERE content_adhoc.is_active = 1 ".$search;

		$result=$db->query($query);
		
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values=$row;
			
		$result->free();
		return $values;
	}
	
	function dbSearchCountedDisponibili($db=null, $search=null)
	{
		if (!$this->_is_connection($db))
			return false;

		$values=array();
		
		$query="SELECT
					count(content_adhoc.id) as num
				FROM
					content_adhoc
				INNER JOIN famiglie ON content_adhoc.id_famiglia = famiglie.id
				INNER JOIN gruppi_merceologici ON content_adhoc.id_gm = gruppi_merceologici.id
				INNER JOIN giacenze ON content_adhoc.id = giacenze.id_content_adhoc
				WHERE content_adhoc.is_active = 1 AND giacenze.disponibilita >= 0 AND content_adhoc.prezzo_0 > 0 ".$search;

		$result=$db->query($query);
		
		if(get_class($result) == "DB_Error")
			return $this->_showErrorNoQuery("File: ".__FILE__."<BR> Class: ".get_class($this)."<BR>Line: ".__LINE__."<BR>Query: <BR>".$query);

		//		Loggo la query sql
		$this->BeanLog("query", $query);
		//		Loggo la query sql

		while($row=$result->fetchRow(DB_FETCHMODE_ASSOC))
			$values=$row;
			
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

		function getId_famiglia(){return $this->id_famiglia;}

	function setId_famiglia($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->id_famiglia = (int)  $value;
	}

		function getNome_it(){return $this->nome_it;}

	function setNome_it($value= null)
	{
				
								
		$this->nome_it = (string)  mysql_real_escape_string($value);
	}

		function getDescrizione_it(){return $this->descrizione_it;}

	function setDescrizione_it($value= null)
	{
		$this->descrizione_it = $value;
	}

		function getNome_en(){return $this->nome_en;}

	function setNome_en($value= null)
	{
		$this->nome_en = mysql_real_escape_string($value);
	}

		function getDescrizione_en(){return $this->descrizione_en;}

	function setDescrizione_en($value= null)
	{
		$this->descrizione_en = $value;
	}

		function getVbn(){return $this->vbn;}

	function setVbn($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->vbn = (string)  mysql_real_escape_string($value);
	}

	function getPeso(){return $this->peso;}
	
	function setPeso($value= null)
	{
		$this->peso = $value;
	}
	
	function getNote(){return $this->note;}
	
	function setNote($value= null)
	{
		$this->note = $value;
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

		function getTipo_colore(){return $this->tipo_colore;}

	function setTipo_colore($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->tipo_colore = (string)  mysql_real_escape_string($value);
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

		function getCod_iva(){return $this->cod_iva;}

	function setCod_iva($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->cod_iva = (int)  $value;
	}

		function getHave_image(){return $this->have_image;}

	function setHave_image($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->have_image = (int)  $value;
	}

		function getVbn_image(){return $this->vbn_image;}

	function setVbn_image($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->vbn_image = (string)  mysql_real_escape_string($value);
	}

		function getData_inserimento_riga(){return $this->data_inserimento_riga;}

	function setData_inserimento_riga($value= null)
	{
				
						$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies data_inserimento_riga il valore ".$value." risulta essere incorretto!";
			exit;
		}
				
		$this->data_inserimento_riga = (string)  mysql_real_escape_string($value);
	}

		function getData_modifica_riga(){return $this->data_modifica_riga;}

	function setData_modifica_riga($value= null)
	{
				
						$exp = explode(" ", $value);
		if(strrpos($exp[0], "-") != 7)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$exp[0]." risulta essere incorretto!";
			exit;
		}
		$expTime = explode(":", $exp[1]);
		if(count($expTime) <= 1)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$exp[1]." risulta essere incorretto!";
			exit;
		}
						$expTime = explode(":", $value);
		if(count($expTime) <= 1 || count($expTime) == 2)
		{
			echo "Errore nel settaggio della properies data_modifica_riga il valore ".$value." risulta essere incorretto!";
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

		function getIs_vbn_updated(){return $this->is_vbn_updated;}

	function setIs_vbn_updated($value= null)
	{
				if(strlen($value) > 11)
			$value = substr($value, 0, 11);
				
								
		$this->is_vbn_updated = (int)  $value;
	}

		function getOperatore(){return $this->operatore;}

	function setOperatore($value= null)
	{
				if(strlen($value) > 255)
			$value = substr($value, 0, 255);
				
								
		$this->operatore = (string)  mysql_real_escape_string($value);
	}

		function getCodice_articolo(){return $this->codice_articolo;}

	function setCodice_articolo($value= null)
	{
				if(strlen($value) > 20)
			$value = substr($value, 0, 20);
				
								
		$this->codice_articolo = (string)  mysql_real_escape_string($value);
	}
}
?>