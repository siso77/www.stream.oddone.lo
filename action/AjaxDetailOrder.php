<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");

include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

class AjaxDetailOrder extends DBSmartyAction
{
	function AjaxDetailOrder()
	{
		parent::DBSmartyAction();

		$BeanEcmOrdini = new ecm_ordini($this->conn, $_REQUEST['id']);
		$ordine = array($BeanEcmOrdini->vars());
		foreach ($ordine as $key => $value)
		{
			$data['ordini'][$key] = $value;
			$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
			$ordini_magazzino = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $value['id']);
			if($ordini_magazzino == array())
				continue;
			$tot = 0;
			foreach ($ordini_magazzino as $k => $val)
			{
				$data['ordini'][$key]['ordini_gardesana']['ordini_magazzino'][$k]['ordine_magazzino'] = $val;
				$tot+=str_replace(',','.', $val['importo']);
				$data['ordini'][$key]['prodotti'][$k] = $val;

				
				$BeanGiacenza = new giacenze($this->conn, $data['ordini'][$key]['ordini_gardesana']['ordini_magazzino'][$k]['ordine_magazzino']['id_magazzino']);
				$giacenza = $BeanGiacenza->vars();
				$data['ordini'][$key]['ordini_gardesana']['ordini_magazzino'][$k]['ordine_magazzino']['prezzo_singolo'] = $giacenza['prezzo_0'];
				if($val['box_type'] == 'pianale')
					$data['ordini'][$key]['ordini_gardesana']['ordini_magazzino'][$k]['ordine_magazzino']['quantita'] = $val['quantita']*$giacenza['qta_pianale'];
				elseif($val['box_type'] == 'carrello')
					$data['ordini'][$key]['ordini_gardesana']['ordini_magazzino'][$k]['ordine_magazzino']['quantita'] = $val['quantita']*$giacenza['qta_carrello'];
				elseif($val['box_type'] == 'confezione')
					$data['ordini'][$key]['ordini_gardesana']['ordini_magazzino'][$k]['ordine_magazzino']['quantita'] = $val['quantita']*$giacenza['qta_minima'];
			}
		}

		$this->tEngine->assign('data', $data);
		echo $this->tEngine->fetch('AjaxDetailOrder');
		exit();

	}
}
