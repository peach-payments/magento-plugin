<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_Web_Hooks
 */
class PeachPayments_Hosted_Model_Web_Hooks extends Mage_Core_Model_Abstract
{
    /**
     * Entity code
     */
    const ENTITY = 'peachpayments_hosted_web_hooks';
    const CACHE_TAG = 'PeachPayments_Hosted_web_hooks';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'peachpayments_hosted_web_hooks';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'web_hooks';

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('peachpayments_hosted/web_hooks');
    }

    /**
     * @param integer | string $orderId
     *
     * @return PeachPayments_Hosted_Model_Web_Hooks
     */
    public function loadByOrderId($orderId)
    {
        return $this->load($orderId, 'order_id');
    }

    /**
     * @param string $incrementId
     *
     * @return PeachPayments_Hosted_Model_Web_Hooks
     */
    public function loadByIncrementId($incrementId)
    {
        return $this->load($incrementId, 'order_increment_id');
    }

    /**
     * Before save
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();

        if ($this->isObjectNew()) {
            $this->setData('created_at', $now);
        }

        $timestamp = $this->_getData('timestamp');

        if ($timestamp !== '') {
            $this->setData(
                'timestamp',
                Mage::getSingleton('core/date')
                    ->gmtDate(null, $timestamp)
            );
        }

        return $this;
    }
}
