<?php

namespace Platon\PlatonPay\Controller\Cancel;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;

/**
 * Class Index
 *
 * @package Platon\PlatonPay\Controller\Cancel
 */
class Index extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Session $session
     * @param Order $order
     */
    public function __construct(
        Context $context,
        Session $session,
        Order $order
    ) {
        $this->session = $session;
        $this->order = $order;

        parent::__construct($context);
    }

    /**
     * Index Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $this->session->setQuoteId($this->session->getPlatonQuoteId());

        $lastRealOrderId = $this->session->getLastRealOrder()->getId();

        if ($lastRealOrderId) {
            $loadOrder = $this->order->loadByIncrementId($lastRealOrderId);

            if ($loadOrder->getId()) {
                $loadOrder->cancel()->save();
            }
        }

        $this->_redirect('checkout/cart')->sendResponse();
    }
}
