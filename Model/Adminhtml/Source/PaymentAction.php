<?php

namespace Platon\PlatonPay\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * Class PaymentAction
 *
 * @package Platon\PlatonPay\Model\Adminhtml\Source
 */
class PaymentAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => MethodInterface::ACTION_AUTHORIZE,
                'label' => __('Authorize')
            ]
        ];
    }
}
