<?php

namespace Platon\PlatonPay\Gateway\Http\Client;

use Exception;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Platon\PlatonPay\Helper\Data;
use Platon\PlatonPay\Gateway\Response\FraudHandler;
use Platon\PlatonPay\Gateway\Validator\ResponseCodeValidator;

/**
 * Class ClientMock
 *
 * @package Platon\PlatonPay\Gateway\Http\Client
 */
class ClientMock implements ClientInterface
{
    /**
     * Success status
     */
    const SUCCESS = 1;

    /**
     * Failure status
     */
    const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws Exception
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $response = $this->generateResponseForCode(
            $this->getResultCode(
                $transferObject
            )
        );

        $this->logger->debug(
            [
                'request'  => $transferObject->getBody(),
                'response' => $response
            ]
        );

        return $response;
    }

    /**
     * Generate response for code
     *
     * @param int $resultCode
     * @return array
     * @throws Exception
     */
    protected function generateResponseForCode(int $resultCode): array
    {
        return array_merge(
            [
                ResponseCodeValidator::RESULT_CODE => $resultCode,
                Data::TXN_ID  => $this->generateTxnId()
            ],
            $this->getFieldsBasedOnResponseType($resultCode)
        );
    }

    /**
     * Generate txn id
     *
     * @return string
     * @throws Exception
     */
    protected function generateTxnId(): string
    {
        return hash('md5', random_int(0, 1000));
    }

    /**
     * Returns result code
     *
     * @param TransferInterface $transfer
     * @return int
     * @throws Exception
     */
    private function getResultCode(TransferInterface $transfer): int
    {
        $headers = $transfer->getHeaders();

        if (isset($headers['force_result'])) {
            return (int)$headers['force_result'];
        }

        return $this->results[random_int(0, 1)];
    }

    /**
     * Returns response fields for result code
     *
     * @param int $resultCode
     * @return array
     */
    private function getFieldsBasedOnResponseType(int $resultCode): array
    {
        if ($resultCode === self::FAILURE) {
            return [
                FraudHandler::FRAUD_MSG_LIST => [
                    'Stolen card',
                    'Customer location differs'
                ]
            ];
        }

        return [];
    }
}
