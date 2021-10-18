<?php

namespace Platon\PlatonPay\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Platon\PlatonPay\Helper\Data;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class TxnIdHandler
 *
 * @package Platon\PlatonPay\Gateway\Response
 */
class TxnIdHandler implements HandlerInterface
{
    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($handlingSubject[InstallData::ORDER_STATE_CUSTOM_CODE])
            || !$handlingSubject[InstallData::ORDER_STATE_CUSTOM_CODE] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject[InstallData::ORDER_STATE_CUSTOM_CODE];

        $payment = $paymentDO->getPayment();

        /** @var Payment $payment */
        $payment->setTransactionId($response[Data::TXN_ID]);
        $payment->setIsTransactionClosed(false);
    }
}
