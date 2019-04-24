<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_Method_Hosted
 */
abstract class PeachPayments_Hosted_Model_Method_Hosted extends Mage_Payment_Model_Method_Abstract
{
    protected $_canManageRecurringProfiles = false;
    protected $_canOrder = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseInternal = false;
    protected $_canVoid = true;
    protected $_code = 'peachpayments_hosted';
    protected $_isGateway = true;
    protected $_isInitializeNeeded = true;

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     *
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getInfoInstance();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $stateCode = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;

        $message = Mage::helper('sales')->__(
            'Customer redirected to PeachPayments with total amount due of %s',
            $this->formatPrice($order->getBaseTotalDue())
        );

        $order->setState($stateCode, $stateCode, $message, false);

        $stateObject->setData('state', $stateCode);
        $stateObject->setData('status', $stateCode);
        $stateObject->setData('is_notified', false);
    }

    /**
     * Format price with currency sign
     *
     * @param float $amount
     * @param null|string $currency
     *
     * @return string
     */
    protected function formatPrice($amount, $currency = null)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getInfoInstance();

        return $payment->getOrder()->getBaseCurrency()->formatTxt(
            $amount,
            $currency ? ['currency' => $currency] : []
        );
    }

    /**
     * @param Mage_Sales_Model_Order_Payment|Varien_Object $payment
     * @param float $amount
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $helper = Mage::helper('peachpayments_hosted');
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $payment->getData('creditmemo');

        $helper->processRefund(
            $payment->getLastTransId(),
            $amount,
            $creditmemo->getOrderCurrencyCode()
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('peachpayments_hosted/secure/redirect', ['_secure' => true]);
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        $currencies = explode(
            ',',
            Mage::getStoreConfig('payment/peachpayments_hosted/currency')
        );

        return in_array($currencyCode, $currencies);
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {

        if (null === $storeId) {
            $storeId = $this->getData('store');
        }

        $code = $this->getCode();

        if ($field !== 'title') {
            $code = 'peachpayments_hosted';
        }

        $path = 'payment/' . $code . '/' . $field;

        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * @return bool
     */
    public function canUseCheckout()
    {
        $methods = explode(',', Mage::getStoreConfig('payment/peachpayments_hosted/methods'));
        $code = strtoupper(str_replace('peachpayments_hosted_', '', $this->getCode()));

        if (!in_array($code, $methods)) {
            return false;
        }

        return parent::canUseCheckout();
    }
}
