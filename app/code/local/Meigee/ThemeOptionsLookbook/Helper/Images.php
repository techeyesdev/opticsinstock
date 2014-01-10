<?php 
/**
 * Magento
 *
 * @author    Meigeeteam http://www.meaigeeteam.com <nick@meaigeeteam.com>
 * @copyright Copyright (C) 2010 - 2012 Meigeeteam
 *
 */
class Meigee_ThemeOptionsLookbook_Helper_Images extends Mage_Core_Helper_Abstract
{
	public function getImg($_product, $imgType = "image", $w, $h, $file=NULL)
	{
		$url = "";
		$config = Mage::getStoreConfig('meigee_lookbook_general/productimages');
		$keepAspectRatio = $config['keepAspectRatio'];

		if ($config['reallyCustomAspectRatio']) {
			$customAspectRatio = $config['reallyCustomAspectRatio'];
		} else $customAspectRatio = $config['customAspectRatio'];
		
		$url = Mage::helper('catalog/image')
				->init($_product, $imgType, $file);

		if ($keepAspectRatio) {
			$url->keepAspectRatio(TRUE);
			$h = NULL;
		} else $url->keepAspectRatio(FALSE);

		$url->constrainOnly(TRUE)
			->keepFrame(FALSE)
			->resize($w, $w*$customAspectRatio);

		return $url;
	}
}