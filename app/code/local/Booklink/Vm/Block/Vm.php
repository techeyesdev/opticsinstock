<?php
class Booklink_Vm_Block_Vm extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getVm()
    {
        if (!$this->hasData('vm')) {
            $this->setData('vm', Mage::registry('vm'));
        }
        return $this->getData('vm');

    }
}