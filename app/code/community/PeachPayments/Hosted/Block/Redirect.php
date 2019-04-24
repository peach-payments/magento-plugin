<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Block_Redirect
 *
 * @method getOrderId
 * @method getOrderIncrementId
 */
class PeachPayments_Hosted_Block_Redirect extends Mage_Core_Block_Template
{
    /**
     * Redirect template
     */
    protected function _construct()
    {
        $this->setTemplate('peachpayments_hosted/redirect.phtml');
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->helper('peachpayments_hosted')->getCheckoutUrl();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getFormData()
    {
        return $this->helper('peachpayments_hosted')
            ->signData($this->getUnsortedFormData());
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::getSingleton('checkout/session')
            ->getLastRealOrder();
    }

    /**
     * @return array
     */
    private function getUnsortedFormData()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->getOrder();
        /** @var PeachPayments_Hosted_Helper_Data $helper */
        $helper = $this->helper('peachpayments_hosted');
        /** @var int $amount */
        $amount = number_format(
            $order->getPayment()->getAmountOrdered(),
            2,
            '.',
            ''
        );

        $methodCode = strtoupper(
            str_replace(
                'peachpayments_hosted_',
                '',
                $order->getPayment()->getMethodInstance()->getCode()
            )
        );

        return [
            'authentication.entityId' => $helper->getEntityId(),
            'amount' => $amount,
            'paymentType' => 'DB',
            'currency' => $order->getOrderCurrencyCode(),
            'shopperResultUrl' => $this->getUrl('*/*/payment'),
            'merchantTransactionId' => $order->getIncrementId(),
            'defaultPaymentMethod' => $methodCode,
            'plugin' => $helper->getPlatformName(),
        ];
    }
}
