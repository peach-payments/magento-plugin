<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_System_Config_Source_Mode
 */
class PeachPayments_Hosted_Model_System_Config_Source_Mode
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => Mage::helper('peachpayments_hosted')->__('Production')
            ],
            [
                'value' => 0,
                'label' => Mage::helper('peachpayments_hosted')->__('Sandbox')
            ],
        ];
    }
}
