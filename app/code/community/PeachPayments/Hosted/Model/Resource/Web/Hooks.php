<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_Resource_Web_Hooks
 */
class PeachPayments_Hosted_Model_Resource_Web_Hooks extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('peachpayments_hosted/web_hooks', 'entity_id');
    }
}
