<?php

class Booklink_Vm_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Booklink_Vm_Model_Payment::ACTION_AUTHORIZE,
                'label' => Mage::helper('vm')->__('Authorize Only')
            ),
            array(
                'value' => Booklink_Vm_Model_Payment::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('vm')->__('Authorize and Capture')
            ),
        );
    }
}
