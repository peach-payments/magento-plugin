<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/**
 * Class PeachPayments_Hosted_Model_System_Config_Source_Currency
 */
class PeachPayments_Hosted_Model_System_Config_Source_Currency
{
    /** @var array */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {

            $this->options = Mage::app()
                ->getLocale()
                ->getOptionCurrencies();

            foreach ($this->options as &$currencyOption) {
                $currencyOption['label'] = $currencyOption['label'] . ' (' . $currencyOption['value'] . ')';
            }
        };
        return $this->options;
    }
}
