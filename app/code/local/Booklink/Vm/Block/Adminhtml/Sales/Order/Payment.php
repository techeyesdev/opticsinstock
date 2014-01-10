<?php

class Booklink_Vm_Block_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
{
//Mage_Adminhtml_Block_Sales_Order_Payment
//Mage_Adminhtml_Block_Template

	/*
	protected function _construct()
    {
		Mage::log("_construct");
        parent::_construct();
        $this->setTemplate('chaseorbitalgateway/sales/order/chaseorbitalgateway.phtml');
        
    }*/
	
	protected function _beforeToHtml()
    {
		//Mage::log("_beforeToHtml");
		//Mage::log($this->getParentBlock());
		
        if (!$this->getParentBlock()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid parent block for this block'));
        }		
        //$this->setPayment($this->getParentBlock()->getOrder()->getPayment());
        parent::_beforeToHtml();
    }
	
    public function setPayment($payment)
    {
		$paymentInfoBlock = Mage::helper('payment')->getInfoBlock($payment);
		$paymentInfoBlock->setTemplate('vm/sales/order/vm.phtml');
		$this->setChild('info', $paymentInfoBlock);
        //$this->setData('payment', $payment);
        return $this;
    }
	
	 protected function _toHtml()
    {
		//Mage::log("_toHtml");
        return $this->getChildHtml('info');
    }
	
}
