<?php

class Booklink_Vm_Model_Currency extends Varien_Object
{
    public function getCollection()
    {
        return $this->_getTranslatedCurrencies();
    }

    public function getOptionArray()
    {
        return Mage::getSingleton("core/locale")->getOptionCurrencies();
    }

    protected function _getTranslatedCurrencies()
    {
        $badCurrencies = Mage::getSingleton("core/locale")->getTranslationList('currencytoname');
        $allowedCurrencies = Mage::getSingleton("core/locale")->getAllowCurrencies();
        $goodCurrencies = array();
        foreach ($badCurrencies as $name => $code) {
            if (in_array($code, $allowedCurrencies)) {
                $goodCurrencies[$code] = $name;
            }
        }
        return $goodCurrencies;
    }
}

