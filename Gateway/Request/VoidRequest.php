<?php

namespace Platon\PlatonPay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Platon\PlatonPay\Helper\Data;

/**
 * Class VoidRequest
 *
 * @package Platon\PlatonPay\Gateway\Request
 */
class VoidRequest implements BuilderInterface
{
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
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        return $this->dataHelper->getPaymentRequestData($buildSubject, 'V');
    }
}
