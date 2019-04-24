<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_System_Config_Source_Method
 */
class PeachPayments_Hosted_Model_System_Config_Source_Method
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('peachpayments_hosted');

        return [
            ['value' => 'CARD', 'label' => $helper->__('CARD')],
            ['value' => 'EFTSECURE', 'label' => $helper->__('EFTSECURE')],
            ['value' => 'MASTERPASS', 'label' => $helper->__('MASTERPASS')],
            ['value' => 'MOBICRED', 'label' => $helper->__('MOBICRED')],
            ['value' => 'MPESA', 'label' => $helper->__('MPESA')],
            ['value' => 'OZOW', 'label' => $helper->__('OZOW')],
        ];
    }
}
