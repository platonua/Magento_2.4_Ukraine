<?php

namespace Platon\PlatonPay\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class FraudHandler
 *
 * @package Platon\PlatonPay\Gateway\Response
 */
class FraudHandler implements HandlerInterface
{
    /**
     * Fraud MSG List
     */
    const FRAUD_MSG_LIST = 'FRAUD_MSG_LIST';

    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($response[self::FRAUD_MSG_LIST])
            || !is_array($response[self::FRAUD_MSG_LIST])
        ) {
            return;
        }

        if (!isset($handlingSubject[InstallData::ORDER_STATE_CUSTOM_CODE])
            || !$handlingSubject[InstallData::ORDER_STATE_CUSTOM_CODE] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject[InstallData::ORDER_STATE_CUSTOM_CODE];
        $payment = $paymentDO->getPayment();

        $payment->setAdditionalInformation(
            self::FRAUD_MSG_LIST,
            $response[self::FRAUD_MSG_LIST]
        );

        /** @var Payment $payment */
        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
    }
}
