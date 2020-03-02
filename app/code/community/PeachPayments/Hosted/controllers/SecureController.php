<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_SecureController
 */
class PeachPayments_Hosted_SecureController extends Mage_Core_Controller_Front_Action
{
    /**
     * Webhook action
     */
    public function webhookAction()
    {
        /** @var array $data */
        $data = $this->getRequest()->getParams();

        /** @var PeachPayments_Hosted_Helper_Data $helper */
        $helper = Mage::helper('peachpayments_hosted');

        /** @var array $signed */
        $signed = $helper->signData($data, false);

        /** @var string $incrementId */
        $incrementId = $this->getRequest()
            ->getParam('merchantTransactionId');

        /** @var PeachPayments_Hosted_Model_Web_Hooks $webHookM */
        $webHookM = $this->getWebHook()
            ->loadByIncrementId($incrementId);

        if ($webHookM->getId() && $signed['signature'] === $data['signature']) {
            $insert = $this->getWebHookData($data);
            foreach ($insert as $key => $item) {
                $webHookM->setData($key, $item);
            }
            $webHookM->save();
            $helper->processOrder($webHookM);
            $this->getResponse()->setHttpResponseCode(200);
            return;
        }
        
        if(count($this->getRequest()->getPost())){
            $this->getResponse()->setHttpResponseCode(500);
        }else{
            $this->getResponse()->setHttpResponseCode(200);
        }
    }

    /**
     * @param array $whData
     *
     * @return array
     */
    private function getWebHookData($whData = [])
    {
        $data = [];
        foreach ($whData as $key => $datum) {
            if ($key === 'id') {
                $data['peach_id'] = $datum;
            } else {
                $data[$this->snakeCase($key)] = $datum;
            }
        }

        return $data;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function snakeCase($str)
    {
        return strtolower(
            preg_replace(
                ['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'],
                '$1_$2',
                str_replace('.', '_', $str)
            )
        );
    }

    /**
     * Payment action
     */
    public function paymentAction()
    {
        /** @var string $incrementId */
        $incrementId = $this->getRequest()
            ->getParam('merchantTransactionId');
        /** @var PeachPayments_Hosted_Model_Web_Hooks $webHookM */
        $webHookM = $this->getWebHook()
            ->loadByIncrementId($incrementId);
        /** @var PeachPayments_Hosted_Helper_Data $helper */
        $helper = Mage::helper('peachpayments_hosted');

        if ($webHookM->getId()) {
            if (!strlen($webHookM->getData('checkout_id'))) {
                $response = $helper->processStatus($incrementId);
                if (!empty($response)) {
                    $insert = $this->getWebHookData($response);
                    foreach ($insert as $key => $item) {
                        $webHookM->setData($key, $item);
                    }
                    $webHookM->save();
                    $helper->processOrder($webHookM);
                }
            }

            if ($this->isSuccessful($webHookM->getData('result_code'))) {
                $this->_redirect('checkout/onepage/success');
                return;
            }

            if($this->isWaiting($webHookM->getData('result_code'))) {
                $this->_redirect('*/*/wait', ['real_order_id' => $incrementId]);
                return;
            }
        }

        $helper->restoreQuote();
        $this->_redirect('checkout/cart');
        return;
    }

    /**
     * Wait action
     */
    public function waitAction()
    {
        $block = $this->getLayout()
            ->createBlock(
                'peachpayments_hosted/wait',
                'wait',
                [
                    'id' => $this->getRequest()->getParam('merchantTransactionId')
                ]
            )->toHtml();

        $this->getResponse()
            ->setBody($block);
    }

    /**
     * @param string $resultCode
     * @return bool
     */
    private function isSuccessful($resultCode)
    {
        return $resultCode === '000.000.000' || $resultCode === '000.100.110' || $resultCode === '000.100.111' || $resultCode === '000.100.112' ? true : false;
    }

    /**
     * @param string $resultCode
     * @return bool
     */
    private function isWaiting($resultCode)
    {
        return $resultCode === '000.200.000' || $resultCode === '000.200.100' ? true : false;
    }

    /**
     * Redirect to PeachPayments hosted forms.
     */
    public function redirectAction()
    {
        /** @var string $orderId */
        $orderId = $this->getSession()
            ->getData('last_order_id');
        /** @var string $orderIncrementId */
        $orderIncrementId = $this->getSession()
            ->getData('last_real_order_id');
        /** @var PeachPayments_Hosted_Model_Web_Hooks $webHookM */
        $webHookM = $this->getWebHook()->loadByOrderId($orderId);

        if (!$webHookM->getId()) {
            $webHookM->addData([
                'order_id' => $orderId,
                'order_increment_id' => $orderIncrementId
            ]);
        } else {
            $webHookM->setData('order_id', $orderId);
            $webHookM->setData('order_increment_id', $orderIncrementId);
        }
        $webHookM->save();

        $this->getInternalRedirect($orderId, $orderIncrementId);
    }

    /**
     * @return PeachPayments_Hosted_Model_Web_Hooks
     */
    public function getWebHook()
    {
        return Mage::getModel('peachpayments_hosted/web_hooks');
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @param string $orderId
     * @param string $orderIncrementId
     */
    protected function getInternalRedirect($orderId, $orderIncrementId)
    {

        $block = $this->getLayout()
            ->createBlock(
                'peachpayments_hosted/redirect',
                'redirect',
                ['order_id' => $orderId, 'order_increment_id' => $orderIncrementId]
            )->toHtml();

        $this->getResponse()
            ->setBody($block);
    }
}
