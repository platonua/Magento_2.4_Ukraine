<?php

namespace Platon\PlatonPay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Platon\PlatonPay\Gateway\Request\MockDataRequest;

/**
 * Class DataAssignObserver
 *
 * @package Platon\PlatonPay\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey(MockDataRequest::TRANSACTION_RESULT) !== null) {
            $paymentInfo->setAdditionalInformation(
                MockDataRequest::TRANSACTION_RESULT,
                $data->getDataByKey(MockDataRequest::TRANSACTION_RESULT)
            );
        }
    }
}
