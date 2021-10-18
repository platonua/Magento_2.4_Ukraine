<?php

namespace Platon\PlatonPay\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Platon\PlatonPay\Helper\Data;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class Index
 *
 * @package Platon\PlatonPay\Controller\Redirect
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Session $session
     * @param SessionManager $sessionManager
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        SessionManager $sessionManager,
        StoreManagerInterface $storeManager,
        Data $dataHelper
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->sessionManager = $sessionManager;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;

        parent::__construct($context);
    }

    /**
     * Index Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws NoSuchEntityException
     * @throws \JsonException
     */
    public function execute()
    {
        $this->session->setQuoteId($this->session->getPlatonQuoteId());

        $this->session->getLastRealOrder()
            ->setStatus(Order::STATE_PENDING_PAYMENT)
            ->save();

        $this->sessionManager->setPlatonQuoteId($this->session->getQuoteId());

        $data = $this->getData(
            $this->session->getLastRealOrder()->getIncrementId()
        );

        $html = $this->getHtml($data);

        $this->getResponse()->setBody($html);
    }

    /**
     * Get data
     *
     * @throws NoSuchEntityException
     * @throws \JsonException
     */
    private function getData($order): array
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        $json = json_encode([
            'amount'   => sprintf("%01.2f",
                $this->session->getLastRealOrder()->getGrandTotal()),
            'name'     => 'Order from ' . $this->storeManager->getStore()
                    ->getGroup()->getName(),
            'currency' => $this->session->getLastRealOrder()
                ->getGlobalCurrencyCode()
        ], JSON_THROW_ON_ERROR);

        $data = base64_encode($json);

        $result = [
            'key'     => $this->dataHelper->getScopeConfigValue(Data::XML_PAYMENT_PLATON_KEY),
            InstallData::ORDER_STATE_CUSTOM_CODE => 'CC',
            'data'    => $data,
            'url'     => $baseUrl . Data::XML_PLATON_PAY_SUCCESS,
            'action'  => $this->dataHelper->getScopeConfigValue(Data::XML_PAYMENT_PLATON_URL),
            'email'   => $this->session->getLastRealOrder()->getCustomerEmail(),
            'phone'   => $this->session->getLastRealOrder()
                ->getShippingAddress()->getTelephone(),
            'order'   => $order,
        ];

        $result['sign'] = hash(
            'md5',
            strtoupper(
                strrev($result['key']) .
                strrev($result[InstallData::ORDER_STATE_CUSTOM_CODE]) .
                strrev($result['data']) .
                strrev($result['url']) .
                strrev($this->dataHelper->getScopeConfigValue(Data::XML_PAYMENT_PLATON_PASS))
            )
        );

        return $result;
    }

    /**
     * Get Html
     *
     * @param array $data
     * @return string
     */
    private function getHtml(array $data): string
    {
        $html = "<html><body><form action='" . $data['action']
            . "' method='post' name='platon_checkout' id='platon_checkout'>";

        unset($data['action']);

        foreach ($data as $field => $value) {
            $html .= "<input hidden name='" . $field . "' value='" . $value
                . "'>";
        }

        $html .= '</form>'
            . __('You will be redirected to Platon when you place an order.')
            . "<script type=\"text/javascript\">document.getElementById(\"platon_checkout\").submit();</script></body></html>";

        return $html;
    }
}
