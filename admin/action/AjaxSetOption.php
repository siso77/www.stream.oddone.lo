<?php
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/content.php");

class AjaxSetOption extends DBSmartyAction
{
	function AjaxSetOption()
	{
		parent::DBSmartyAction();
				
		if(!empty($_REQUEST['id_content']))
		{
			switch ($_REQUEST['action'])
			{
				case 'visibile':
					
					$BeanGiacenza = new giacenze($this->conn, $_REQUEST['id_content']);
					switch ($_REQUEST['lang'])
					{
						case "it":
							if($BeanGiacenza->visibile == 1)
								$BeanGiacenza->setVisibile(0);
							else
								$BeanGiacenza->setVisibile(1);
						break;
						case "de":
							if($BeanGiacenza->visibile_de == 1)
								$BeanGiacenza->setVisibile_de(0);
							else
								$BeanGiacenza->setVisibile_de(1);
						break;
						case "fr":
							if($BeanGiacenza->visibile_fr == 1)
								$BeanGiacenza->setVisibile_fr(0);
							else
								$BeanGiacenza->setVisibile_fr(1);
						break;
						case "en":
							if($BeanGiacenza->visibile_en == 1)
								$BeanGiacenza->setVisibile_en(0);
							else
								$BeanGiacenza->setVisibile_en(1);
						break;
					}
					$BeanGiacenza->dbStore($this->conn);
					exit();
					
				break;
				case 'is_in_ecommerce':
					$BeanContent = new content();
					$BeanContent->dbGetOne($this->conn, $_REQUEST['id_content']);
					if($BeanContent->is_in_ecommerce == 1)
						$BeanContent->setIs_in_ecommerce(0);
					else
						$BeanContent->setIs_in_ecommerce(1);
					$BeanContent->dbStore($this->conn);
				break;
				case 'is_in_evidence':
					$BeanContent = new content();
					$BeanContent->dbGetOne($this->conn, $_REQUEST['id_content']);
					if($BeanContent->is_in_evidence == 1)
						$BeanContent->setIs_in_evidence(0);
					else
						$BeanContent->setIs_in_evidence(1);
					$BeanContent->dbStore($this->conn);
				break;
				case 'is_invisible':
					$BeanContent = new content();
					$BeanContent->dbGetOne($this->conn, $_REQUEST['id_content']);
					if($BeanContent->is_invisible == 1)
						$BeanContent->setIs_invisible(0);
					else
						$BeanContent->setIs_invisible(1);
					$BeanContent->dbStore($this->conn);
				break;
				case 'is_in_offer':
					$BeanContent = new content();
					$BeanContent->dbGetOne($this->conn, $_REQUEST['id_content']);
					if($BeanContent->is_in_offer == 1)
						$BeanContent->setIs_in_offer(0);
					else
						$BeanContent->setIs_in_offer(1);
					$BeanContent->dbStore($this->conn);
				break;
			}
			echo "";
			exit();
		}

		//echo $this->tEngine->fetch('shared/DivGetGiacenze');
	}
}
?>