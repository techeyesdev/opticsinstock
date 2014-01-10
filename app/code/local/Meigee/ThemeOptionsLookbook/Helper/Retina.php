<?php 
/**
 * Magento
 *
 * @author    Meigeeteam http://www.meaigeeteam.com <nick@meaigeeteam.com>
 * @copyright Copyright (C) 2010 - 2012 Meigeeteam
 *
 */
class Meigee_ThemeOptionsLookbook_Helper_Retina extends Mage_Core_Helper_Abstract
{
 
	public function getRetinaData ($data, $_item=0, $_itemparameter=0) {
		
		$helpImg = MAGE::helper('ThemeOptionsLookbook/images');
		
 		if (Mage::getStoreConfig('meigee_lookbook_general/appearance/retina')) {
			switch ($data) {
				case 'list':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 800, null) . '"';
				break;
				case 'grid_large':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 840, null) . '"';
				break;
				case 'grid':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 1200, null) . '"';
				break;
				case 'grid_small':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 800, null) . '"';
				break;
				case 'related':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'thumbnail', 460, null) . '"';
				break;
				case 'upsell':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'thumbnail', 1120, null) . '"';
				break;
				case 'crosssell':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'thumbnail', 380, null) . '"';
				break;
				case 'crosssell_big':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'thumbnail', 580, null) . '"';
				break;
				case 'cart_item_default':
					$html = 'data-srcX2="' . $_item->getProductThumbnail()->resize(240, null) . '"';
				break;
				case 'currency_header':
					$html = 'data-srcX2="' . Mage::getDesign()->getSkinUrl('images/@x2/'.$_item.'@x2.png') . '"';
				break;
				case 'widget_grid':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 600, null) . '"';
				break;
				case 'widget_list':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 600, null) . '"';
				break;
				case 'widget_slider':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 600, null) . '"';
				break;
				case 'logo':
					$html = 'data-srcX2="' . Mage::getDesign()->getSkinUrl('images/@x2/logo@x2.png') . '"';
				break;
				case 'logo_custom':
    				$customlogo = MAGE::helper('ThemeOptionsLookbook')->getThemeOptionsLookbook('customlogo');
					$mediaurl = MAGE::helper('ThemeOptionsLookbook')->getThemeOptionsLookbook('mediaurl');
					$html = 'data-srcX2="' . $mediaurl.$customlogo['logo_retina'] . '"';
				break;
				case 'languages':
					$html = 'data-srcX2="' . Mage::getDesign()->getSkinUrl('images/@x2/'.$_item->getName().'@x2.png') . '"';
				break;
				case 'review_customer_view':
					$html = 'data-srcX2="' . $helpImg->getImg($_item->getProductData(), 'small_image', 250, null) . '"';
				break;
				case 'wishlist_item_image':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'small_image', 760, null) . '"';
				break;
				case 'wishlist_sidebar_item':
					$html = 'data-srcX2="' . $helpImg->getImg($_item, 'thumbnail', 200, null) . '"';
				break;
				case 'more_views':
					$html = 'data-srcX2="' . $helpImg->getImg($_item->getProduct(), 'thumbnail', 400, null) . '"';
				break;
				case 'checkout_cart_sidebar':
					$html = 'data-srcX2="' . $_item->getProductThumbnail()->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(80, null)->setWatermarkSize('60x20') . '"';
				break;
				default:
					# code...
					break;
			}
			return $html;
		}
		else return false;
	}

}