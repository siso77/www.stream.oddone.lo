<?php
class StaticPageController extends SmartyAction
{
	function StaticPageController()
	{
		parent::SmartyAction();
		
		switch ($_REQUEST['article'])
		{
			case 1:
				$intlKeyTitle 		 	= 'company_title';
				$intlKeyBody 		 	= 'company_body';
				$intlKeyBody2 		 	= 'company_body_2';
				$intlKeyBodyEvidence 	= 'company_body_evidence';
				$intlKeyParagraphTitle 	= 'company_paragraph_title';
			break;
			case 2:
				$intlKeyTitle 		 = 'contatti_title';
				$intlKeyBody 		 = 'contatti_body';
				$intlKeyBody2 		 = 'contatti_body_2';
				$intlKeyBodyEvidence = 'contatti_body_evidence';
				$intlKeyParagraphTitle 	= 'contatti_paragraph_title';
			break;
			case 3:
				$intlKeyTitle 		 = 'promozioni_title';
				$intlKeyBody 		 = 'promozioni_body';
				$intlKeyBody2 		 = 'promozioni_body_2';
				$intlKeyBodyEvidence = 'promozioni_body_evidence';
				$intlKeyParagraphTitle 	= 'promozioni_paragraph_title';
			break;
			case 4:
				$intlKeyTitle 		 = 'franchising_title';
				$intlKeyBody 		 = 'franchising_body';
				$intlKeyBody2 		 = 'franchising_body_2';
				$intlKeyBodyEvidence = 'franchising_body_evidence';
				$intlKeyParagraphTitle 	= 'franchising_paragraph_title';
			break;
			case 5:
				$intlKeyTitle 		 = 'prodotti_title';
				$intlKeyBody 		 = 'prodotti_body';
				$intlKeyBody2 		 = 'prodotti_body_2';
				$intlKeyBodyEvidence = 'prodotti_body_evidence';
				$intlKeyParagraphTitle 	= 'prodotti_paragraph_title';
			break;
		}
		$this->tEngine->assign('key_title', $intlKeyTitle);
		$this->tEngine->assign('key_body', $intlKeyBody);
		$this->tEngine->assign('key_body_2', $intlKeyBody2);
		$this->tEngine->assign('key_body_evidence', $intlKeyBodyEvidence);
		$this->tEngine->assign('key_body_evidence', $intlKeyBodyEvidence);
		$this->tEngine->assign('key_paragraph_title', $intlKeyParagraphTitle);
		$this->tEngine->assign('tpl_action', 'static/StaticPageController');
		$this->tEngine->display('Index');
	}
}
?>