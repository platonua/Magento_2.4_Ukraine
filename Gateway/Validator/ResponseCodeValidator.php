<?php

namespace Platon\PlatonPay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Platon\PlatonPay\Gateway\Http\Client\ClientMock;

/**
 * Class ResponseCodeValidator
 *
 * @package Platon\PlatonPay\Gateway\Validator
 */
class ResponseCodeValidator extends AbstractValidator
{
    /**
     * Result Code
     */
    const RESULT_CODE = 'RESULT_CODE';

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        if (!isset($validationSubject['response'])
            || !is_array($validationSubject['response'])
        ) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        }

        return $this->createResult(
            false,
            [__('Gateway rejected the transaction.')]
        );
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response): bool
    {
        return isset($response[self::RESULT_CODE])
            && $response[self::RESULT_CODE] !== ClientMock::FAILURE;
    }
}
