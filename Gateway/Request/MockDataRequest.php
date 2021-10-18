<?php
namespace Platon\PlatonPay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Platon\PlatonPay\Gateway\Http\Client\ClientMock;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class MockDataRequest
 *
 * @package Platon\PlatonPay\Gateway\Request
 */
class MockDataRequest implements BuilderInterface
{
    /**
     * Force Result
     */
    const FORCE_RESULT = 'FORCE_RESULT';

    /**
     * Transaction Result
     */
    const TRANSACTION_RESULT = 'transaction_result';

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

        $paymentDO = $buildSubject[InstallData::ORDER_STATE_CUSTOM_CODE];
        $payment = $paymentDO->getPayment();

        $transactionResult = $payment->getAdditionalInformation(self::TRANSACTION_RESULT);

        return [
            self::FORCE_RESULT => $transactionResult ?? ClientMock::SUCCESS
        ];
    }
}
