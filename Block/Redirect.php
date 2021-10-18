<?php

namespace Platon\PlatonPay\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Redirect
 *
 * @package Platon\PlatonPay\Block
 */
class Redirect extends Template
{
    /**
     * @var string $_template
     */
    protected $_template = 'Platon_PlatonPay::redirect.phtml';

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        return "<html><body><form method='POST' id='platon_checkout' name='platon_checkout'></form></body></html>";
    }
}
