<?php 

class Booklink_Vm_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function setVmInformations($observer)
    {
		/*
        $accId = $this->_getRequest()->getPost("vm_bank_id"); 
        $observer->getEvent()->getOrder()->setVmBankId($accId);

        $installment = $this->_getRequest()->getPost("vm_installment");
        $observer->getEvent()->getOrder()->setVmInstallment($installment);
		*/
    }
}
