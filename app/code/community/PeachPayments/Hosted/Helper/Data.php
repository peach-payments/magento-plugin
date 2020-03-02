<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Helper_Data
 */
class PeachPayments_Hosted_Helper_Data extends Mage_Core_Helper_Abstract
{
    const API_LIVE = 'https://api.peachpayments.com/v1/checkout/';
    const API_TEST = 'https://testapi.peachpayments.com/v1/checkout/';
    const CHECKOUT_LIVE = 'https://secure.peachpayments.com/checkout';
    const CHECKOUT_TEST = 'https://testsecure.peachpayments.com/checkout';
    const PLATFORM = 'MAGENTO';
    const XML_CONF = 'payment/peachpayments_hosted/';

    /** @var Varien_Http_Client */
    private $client;

    /** @var array */
    private $sandboxVariables = [
        'entity_id',
        'sign_key',
    ];

    /**
     * @param $path
     * @param bool $isBool
     * @return bool|string
     */
    private function getConfig($path, $isBool = false)
    {
        if (!$this->getMode() && in_array($path, $this->sandboxVariables)) {
            $path .= '_sandbox';
        }

        if ($isBool) {
            return Mage::getStoreConfigFlag(self::XML_CONF . $path);
        }

        return Mage::getStoreConfig(self::XML_CONF . $path);
    }

    /**
     * @return bool
     */
    private function getMode()
    {
        return Mage::getStoreConfigFlag(self::XML_CONF . 'mode');
    }

    /**
     * @param string $loc
     * @return string
     */
    public function getApiUrl($loc = '')
    {
        $url = $this->getMode() ? self::API_LIVE : self::API_TEST;

        return $url . $loc;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getMode() ? self::CHECKOUT_LIVE : self::CHECKOUT_TEST;
    }

    /**
     * @return string
     */
    public function getWaitingUrl()
    {
        $orderId = Mage::app()->getRequest()->getParam('id');
        return Mage::getUrl('peachpayments_hosted/secure/payment', ['merchantTransactionId' => $orderId]);
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->getConfig('entity_id');
    }

    /**
     * @return string
     */
    private function getSignKey()
    {
        return $this->getConfig('sign_key');
    }

    /**
     * @return string
     */
    public function getPlatformName()
    {
        return self::PLATFORM;
    }

    /**
     * @param string $id
     * @param float $amount
     * @param string $currency
     * @return array
     */
    public function processRefund($id, $amount, $currency)
    {

        $client = $this->getHttpClient($this->getApiUrl('refund'));

        $params = [
            'authentication.entityId' => $this->getEntityId(),
            'amount' => $amount,
            'paymentType' => 'RF',
            'currency' => $currency,
            'id' => $id
        ];

        $client->setParameterPost($this->signData($params, false));

        // NOTE: Curl defaults HTTP_VERSION to HTTP/2 extractHeaders broken, force back to HTTP/1.1
        if (extension_loaded('curl')) {
            $client->setAdapter(new Varien_Http_Adapter_Curl());
            /** @var Varien_Http_Adapter_Curl $adapter */
            $adapter = $client->getAdapter();
            $adapter->addOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }

        try {
            /** @var Zend_Http_Response $response */
            $response = $client->request(Zend_Http_Client::POST);
            /** @var array $json */
            $json = json_decode($response->getRawBody(), true);
            return $json;

        } catch (Exception $e) {
            Mage::logException($e);
        }

        return [];
    }

    /**
     * @param int $merchantTransactionId
     * @return array
     */
    public function processStatus($merchantTransactionId)
    {

        $client = $this->getHttpClient($this->getApiUrl('status'));

        $params = [
            'authentication.entityId' => $this->getEntityId(),
            'merchantTransactionId' => $merchantTransactionId,
        ];

        $client->setParameterGet($this->signData($params, false));

        // NOTE: Curl defaults HTTP_VERSION to HTTP/2 extractHeaders broken, force back to HTTP/1.1
        if (extension_loaded('curl')) {
            $client->setAdapter(new Varien_Http_Adapter_Curl());
            /** @var Varien_Http_Adapter_Curl $adapter */
            $adapter = $client->getAdapter();
            $adapter->addOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }

        try {
            /** @var Zend_Http_Response $response */
            $response = $client->request(Zend_Http_Client::GET);
            /** @var array $json */
            $json = json_decode($response->getRawBody(), true);
            return $json;

        } catch (Exception $e) {
            Mage::logException($e);
        }

        return [];
    }

    /**
     * @param string $url
     * @return Varien_Http_Client
     */
    public function getHttpClient($url = '')
    {
        if (!$this->client) {
            $this->client = new Zend_Http_Client($url);
        }
        return $this->client;
    }

    /**
     * @param array $data unsigned data
     * @param bool $includeNonce
     *
     * @return array signed data
     */
    public function signData($data = [], $includeNonce = true)
    {

        assert(count($data) !== 0, 'Error: Sign data can not be empty');
        assert(function_exists('hash_hmac'), 'Error: hash_hmac function does not exist');

        if ($includeNonce) {
            $nonce = $this->getUuid();
            assert(strlen($nonce) !== 0, 'Error: Nonce can not be empty, something went horribly wrong');
            $data = array_merge($data, ['nonce' => $this->getUuid()]);
        }

        $tmp = [];
        foreach ($data as $key => $datum) {
            // NOTE: Zend framework s/./_/g fix
            $tmp[str_replace('_', '.', $key)] = $datum;
        }

        ksort($tmp, SORT_STRING);

        $signDataRaw = '';
        foreach ($tmp as $key => $datum) {
            if ($key !== 'signature') {
                // NOTE: Zend framework s/./_/g fix
                $signDataRaw .= str_replace('_', '.', $key) . $datum;
            }
        }

        $signData = hash_hmac('sha256', $signDataRaw, $this->getSignKey());

        return array_merge($data, ['signature' => $signData]);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getUuid()
    {
        assert(function_exists('openssl_random_pseudo_bytes'), 'Error: Unable to generate random string');
        // NOTE: Allow PHP5 based pseudo random str if PHP7 not present
        $data = !function_exists('random_bytes')
            ? openssl_random_pseudo_bytes(16)
            : random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        $session = $this->getSession();
        $order = $session->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->getQuoteById($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $session->replaceQuote($quote)
                    ->unsetData('last_real_order_id');
                return true;
            }
        }
        return false;
    }

    /**
     * @param PeachPayments_Hosted_Model_Web_Hooks $result
     *
     * @return bool
     */
    public function processOrder($result)
    {
        /** @var string $resultCode */
        $resultCode = $result->getData('result_code');
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')
            ->load($result->getData('order_id'));
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        
        if (($resultCode === '000.000.000' || $resultCode === '000.100.110' || $resultCode === '000.100.111' || $resultCode === '000.100.112')
            && $order instanceof Mage_Sales_Model_Order
            && $payment instanceof Mage_Sales_Model_Order_Payment
        ) {
            try {

                $payment->setData('transaction_id', $result->getData('peach_id'));
                $payment->registerCaptureNotification($result->getData('amount'), true);
                $paymentBrand = strtolower($result->getData('payment_brand'));

                // ugly catch-all approach ~ for now
                switch ($paymentBrand) {
                    case 'eftsecure':
                        $methodCode = 'eftsecure';
                        break;
                    case 'masterpass':
                        $methodCode = 'masterpass';
                        break;
                    case 'mobicred':
                        $methodCode = 'mobicred';
                        break;
                    case 'mpesa':
                        $methodCode = 'mpesa';
                        break;
                    case 'ozow':
                        $methodCode = 'ozow';
                        break;
                    default:
                        $methodCode = 'card';
                        break;
                }

                $payment->setMethod('peachpayments_hosted_' . $methodCode);
                
                if ($order->getCanSendNewEmailFlag() && $this->getConfig('send_order_email', true)) {
                    $order->queueNewOrderEmail();
                }
                
                if ($this->getConfig('send_invoice_email', true)) {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        $invoice->sendEmail();
                    }
                }

                $order->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }

            return true;
        }

        if ($resultCode !== '000.200.000' && $resultCode !== '000.200.100') {
            try {
                $order->cancel();
                $order->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return false;
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return sales quote instance for specified ID
     *
     * @param int $quoteId Quote identifier
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuoteById($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }
}
