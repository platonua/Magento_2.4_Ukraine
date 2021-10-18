<?php
 
namespace Platon\PlatonPay\Controller\Success;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

/**
 * Class Index
 *
 * @package Platon\PlatonPay\Controller\Success
 */
class Index extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Session $session
    ) {
        $this->session = $session;

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

        $this->session->getQuote()->setIsActive(false)->save();

        $this->_redirect('checkout/onepage/success', ['_secure' => true]);
    }
}
