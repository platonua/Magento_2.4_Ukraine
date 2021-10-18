<?php

namespace Platon\PlatonPay\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Platon\PlatonPay\Helper\Data;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class AuthorizationRequest
 *
 * @package Platon\PlatonPay\Gateway\Request
 */
class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE])
            || !$buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();

        return [
            Data::TXN_TYPE => 'A',
            'INVOICE'      => $order->getOrderIncrementId(),
            'AMOUNT'       => $order->getGrandTotalAmount(),
            'CURRENCY'     => $order->getCurrencyCode(),
            'EMAIL'        => $address->getEmail(),
            Data::MERCHANT_KEY => $this->config->getValue(
                Data::MERCHANT_GATEWAY_KEY,
                $order->getStoreId()
            )
        ];
    }
}
