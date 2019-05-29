<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Block_Redirect
 */
class PeachPayments_Hosted_Block_Wait extends Mage_Core_Block_Template
{
    /**
     * Redirect template
     */
    protected function _construct()
    {
        $this->setTemplate('peachpayments_hosted/wait.phtml');
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->helper('peachpayments_hosted')->getWaitingUrl();
    }    
}
