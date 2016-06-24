<?php
// include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/beans/banner.php');

class SliderHomePage extends DBSmartyAction
{
	var $className;
	
	function SliderHomePage()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		$localPath = str_replace('/admin', '', APP_ROOT)."/img/web/banners/";
		$localThumbPath = str_replace('/admin', '', APP_ROOT)."/img/web/banners/";
		
		$wwwPath = str_replace('/admin', '', WWW_ROOT)."img/web/banners/";
		$wwwThumbPath = str_replace('/admin', '', WWW_ROOT)."img/web/banners/";
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanBanners = new banner($this->conn, $_REQUEST['delete']);
			$BeanBanners->dbDelete($this->conn, array($_REQUEST['delete']), false);
			unlink($localPath.$BeanBanners->getImage_name());
			unlink($localPath.'Large_'.$BeanBanners->getImage_name());
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanBanners = new banner($this->conn, $_REQUEST['id']);
			else
			{ 
				$BeanBanners = new banner();
				$BeanBanners->setId_category(0);
			}
			
			if(!empty($_REQUEST['edit']))
			{
				if(!empty($_REQUEST['link_img']))
				{
					$BeanBanners->setLink($_REQUEST['link_img']);
					$BeanBanners->dbStore($this->conn);
				}
			}
			else
			{
				if(!empty($_FILES['image']['name']))
				{
					try {
						$fName = $this->uploadBanner('image', $_FILES['image'], 'banners');
					}
					catch (Exception $e)
					{
						_dump($e);
						exit();
					}

					$BeanBanners->setWww_path($wwwPath);
					$BeanBanners->setLocal_path($localPath);
					$BeanBanners->setImage_name($fName);
					if(!empty($_REQUEST['link_img']))
						$BeanBanners->setLink($_REQUEST['link_img']);
					$BeanBanners->dbStore($this->conn);
				}
			}
		}

		$BeanBanners = new banner();
		$banners = $BeanBanners->dbGetAll($this->conn);
		
		$this->tEngine->assign('images', $banners);
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function uploadBanner($index, $server_file, $customImgRelativePath, $id)
	{
		if($this->IsMobileDevice)
			$localPath = APP_ROOT.'/../'.IMG_DIR.'/wap/'.$customImgRelativePath;
		else
			$localPath = APP_ROOT.'/../'.IMG_DIR.'/web/'.$customImgRelativePath;

		if($this->IsMobileDevice)
			$wwwPath = str_replace('admin/', '', WWW_ROOT).''.IMG_DIR.'/wap/'.$customImgRelativePath;
		else	
			$wwwPath = str_replace('admin/', '', WWW_ROOT).''.IMG_DIR.'/web/'.$customImgRelativePath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);
			
		//$fName = str_replace(" ", "", date('d_m_Y_H_i_s_').$_FILES['attach']['name']);
		$fName = str_replace(" ", "", date('dmYHis_').$server_file['name']);
		$pathFName = $localPath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);
			
//		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Small_".$fName, 40);
//		if(!$obj->is_uploaded())
//			throw new Exception('Errore di caricamento dell\'immagine');
//		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Medium_".$fName, 100);
//		if(!$obj->is_uploaded())
//			throw new Exception('Errore di caricamento dell\'immagine');

		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Large_".$fName, 500);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');

		if(!move_uploaded_file($server_file['tmp_name'], $localPath.'/'.$fName))
			throw new Exception();

		return $fName;

//		$BeanBanners = new banner();
//		$BeanBanners->setImage_name($fName);
//		$BeanBanners->setLink($_REQUEST['link']);
//		$BeanBanners->setId_category($id);
//		$BeanBanners->setLocal_path($localPath);
//		$BeanBanners->setWww_path($wwwPath);	
//		$BeanBanners->dbStore($this->conn);
	}	
}
?>