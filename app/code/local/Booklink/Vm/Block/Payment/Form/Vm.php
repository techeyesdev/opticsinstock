<?php

class Booklink_Vm_Block_Payment_Form_Vm extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        //$this->setTemplate('vm/payment/form/vm.phtml');
    }

    public function getBankAvailableTypes()
    {
        return Mage::getSingleton("vm/bank")->getCollection();
    }
}
