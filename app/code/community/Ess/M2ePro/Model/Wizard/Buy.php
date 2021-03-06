<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Buy extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'marketplace',
        'synchronization',
        'account'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Buy')->isActive();
    }

    // ########################################

    public function disableChildWizards()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $wizardHelper->setStatus('buyNewSku', Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED);

        return true;
    }

    // ########################################
}