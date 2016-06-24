<?php
 include_once(APP_ROOT.'/beans/banner.php');
include_once(APP_ROOT."/beans/content.php");

class Home extends DBSmartyAction
{
	var $limit;
	var $limit_start;
	var $limit_end;
	
	function Home()
	{
		parent::DBSmartyAction();

		$BeanContent = new content();
		$content = $BeanContent->dbSearchDisponibili($this->conn, " AND giacenze.in_home = 1 AND giacenze.visibile = 1 ORDER BY giacenze.data_modifica_riga DESC ");
		$this->tEngine->assign('orizzontal_slider_1', $content);
		$content = $BeanContent->dbSearchDisponibili($this->conn, " AND (giacenze.stato = 'O' OR giacenze.stato = 'N') AND giacenze.visibile = 1  ORDER BY giacenze.data_modifica_riga DESC ");
		$this->tEngine->assign('orizzontal_slider_2', $content);

		$localPath = str_replace('/admin', '', APP_ROOT)."/img/web/banners/";
		$localThumbPath = str_replace('/admin', '', APP_ROOT)."/img/web/banners/";		
		$wwwPath = str_replace('/admin', '', WWW_ROOT)."img/web/banners/";
		$wwwThumbPath = str_replace('/admin', '', WWW_ROOT)."img/web/banners/";
		
		$BeanBanners = new banner();
		$banners = $BeanBanners->dbGetAll($this->conn);
		$this->tEngine->assign('images', $banners);
				
// 		$BeanBanners = new banner();
// 		$img_slider = $BeanBanners->dbGetAllByIdCategory($this->conn, 0);
// 		$this->tEngine->assign('img_slider', $img_slider);
		
		$this->tEngine->assign('tpl_action', 'Home');
		$this->tEngine->display('Index');
	}
}
?>