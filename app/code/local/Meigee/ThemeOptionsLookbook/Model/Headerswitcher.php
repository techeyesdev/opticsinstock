<?php 
/**
 * Magento
 *
 * @author    Meigeeteam http://www.meaigeeteam.com <nick@meaigeeteam.com>
 * @copyright Copyright (C) 2010 - 2012 Meigeeteam
 *
 */
class Meigee_ThemeOptionsLookbook_Model_Headerswitcher
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'header_1', 'label'=>Mage::helper('ThemeOptionsLookbook')->__('Header 1')),
            array('value'=>'header_2', 'label'=>Mage::helper('ThemeOptionsLookbook')->__('Header 2'))
        );
    }

}