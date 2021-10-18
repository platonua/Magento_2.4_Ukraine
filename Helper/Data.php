<?php

namespace Platon\PlatonPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class Data
 *
 * @package Platon\PlatonPay\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Payment Platon Key xml path
     */
    const XML_PAYMENT_PLATON_KEY = 'payment/platon/key';

    /**
     * Platon Pay Success xml path
     */
    const XML_PLATON_PAY_SUCCESS = 'platon_platon_pay/success/index';

    /**
     * Payment Platon Url xml path
     */
    const XML_PAYMENT_PLATON_URL = 'payment/platon/url';

    /**
     * Payment Platon Pass xml path
     */
    const XML_PAYMENT_PLATON_PASS = 'payment/platon/pass';

    /**
     * Payment Platon Policy xml path
     */
    const XML_PAYMENT_PLATON_POLICY = 'payment/platon/policy';

    /**
     * TXN Id
     */
    const TXN_ID = 'TXN_ID';

    /**
     * TXN type
     */
    const TXN_TYPE = 'TXN_TYPE';

    /**
     * Merchant key
     */
    const MERCHANT_KEY = 'MERCHANT_KEY';

    /**
     * Merchant Gateway KEy
     */
    const MERCHANT_GATEWAY_KEY = 'merchant_gateway_key';

    /**
     * Get payment request data
     *
     * @param array $buildSubject
     * @param string $txnType
     * @return array
     */
    public function getPaymentRequestData(array $buildSubject, string $txnType): array
    {
        if (!isset($buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE])
            || !$buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE];

        $order = $paymentDO->getOrder();

        $payment = $paymentDO->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }

        return [
            self::TXN_TYPE     => $txnType,
            self::TXN_ID       => $payment->getLastTransId(),
            self::MERCHANT_KEY => $this->scopeConfig->getValue(
                self::MERCHANT_GATEWAY_KEY,
                $order->getStoreId()
            )
        ];
    }

    /**
     * Get scope config value
     *
     * @param string $path
     * @return mixed
     */
    public function getScopeConfigValue(string $path)
    {
        return $this->scopeConfig->getValue($path);
    }
}
