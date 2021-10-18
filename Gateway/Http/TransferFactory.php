<?php

namespace Platon\PlatonPay\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Platon\PlatonPay\Gateway\Request\MockDataRequest;

/**
 * Class TransferFactory
 *
 * @package Platon\PlatonPay\Gateway\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request): TransferInterface
    {
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod('POST')
            ->setHeaders(
                [
                    'force_result' => $request[MockDataRequest::FORCE_RESULT] ?? null
                ]
            )
            ->build();
    }
}
