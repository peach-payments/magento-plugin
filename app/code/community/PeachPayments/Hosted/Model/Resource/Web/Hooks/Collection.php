<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_Resource_Web_Hooks_Collection
 */
class PeachPayments_Hosted_Model_Resource_Web_Hooks_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('peachpayments_hosted/web_hooks');
    }
}
