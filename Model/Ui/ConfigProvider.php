<?php

namespace Platon\PlatonPay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Platon\PlatonPay\Gateway\Http\Client\ClientMock;
use Platon\PlatonPay\Helper\Data;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class ConfigProvider
 *
 * @package Platon\PlatonPay\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Platon Pay code
     */
    const CODE = 'platon_pay';

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            InstallData::ORDER_STATE_CUSTOM_CODE => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ],
                    'policy' => $this->dataHelper->getScopeConfigValue(Data::XML_PAYMENT_PLATON_POLICY)
                ]
            ]
        ];
    }
}
