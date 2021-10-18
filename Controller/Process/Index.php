<?php

namespace Platon\PlatonPay\Controller\Process;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Platon\PlatonPay\Helper\Data;
use Platon\PlatonPay\Setup\InstallData;

/**
 * Class Index
 *
 * @package Platon\PlatonPay\Controller\Process
 */
class Index extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Required Fields
     */
    const REQUIRED_FIELDS = [
        'id',
        'order',
        'status',
        'rrn',
        'approval_code',
        'description',
        'amount',
        'currency',
        'name',
        'email',
        'country',
        'state',
        'city',
        'address',
        'date',
        'ip',
        'sign',
    ];

    /**
     * Required Cart Fields
     */
    const REQUIRED_CARD_FIELDS = [
        'number',
        'card',
    ];

    /**
     * Canceled status
     */
    const CANCELED = 'canceled';

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Session $session
     * @param LoggerInterface $logger
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Session $session,
        LoggerInterface $logger,
        Data $dataHelper
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;

        parent::__construct($context);
    }

    /**
     * Index Action
     *
     * @return int|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $answer = $this->processCallback($data);

        $this->getResponse()->setBody($answer);

        return $this->_response->sendResponse();
    }

    /**
     * Process callback
     *
     * @param array $data
     * @return string
     */
    private function processCallback(array $data): string
    {
        try {
            $callbackStatus = $this->prepareCallbackStatus($data);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $callbackStatus;
    }

    /**
     * Prepare callback status
     *
     * @param array $data
     * @return string
     * @throws Exception
     */
    private function prepareCallbackStatus(array $data): string
    {
        $this->logger->info(var_export($data, 1));

        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($data[$field])) {
                $data[$field] = null;
            }
        }

        if (!$this->verifySignature($data)) {
            $this->logger->error('Invalid signature');

            return 'ERROR: Bad signature';
        }

        $this->logger->info('Callback signature OK');

        $lastRealOrder = $this->session->getLastRealOrder()
            ->loadByIncrementId($data['order']);

        if (!$lastRealOrder->getId()) {
            // log wrong order
            $this->logger->error('ERROR: Bad order ID');

            return 'ERROR: Bad order ID';
        }

        if (!$this->setOrderStatus($lastRealOrder, $data)) {
            $this->logger->error('Invalid callback data');

            return 'ERROR: Invalid callback data';
        }

        return 'OK';
    }

    /**
     * Verify signature
     *
     * @param array $data
     * @return bool
     */
    private function verifySignature(array $data): bool
    {
        if (isset($data['card'])) {
            $card = $data['card'];
        } elseif (isset($data['number'])) {
            $card = $data['number'];
        } else {
            return false;
        }

        return $this->getSignature($data['email'], $data['order'], $card) === $data['sign'];
    }

    /**
     * Get signature
     *
     * @param string $email
     * @param string $order
     * @param string $card
     * @return false|string
     */
    private function getSignature(string $email, string $order, string $card)
    {
        $pass = $this->dataHelper->getScopeConfigValue(Data::XML_PAYMENT_PLATON_PASS);

        $email = strrev($email);
        $card_1 = substr($card, 0, 6);
        $card_2 = substr($card, -4);
        $card = strrev($card_1 . $card_2);

        return hash('md5', strtoupper($email . $pass . $order . $card));
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Set Order Status
     *
     * @param Order $order
     * @param array $data
     * @return bool
     * @throws Exception
     */
    private function setOrderStatus(Order $order, array $data): bool
    {
        $payment = $order->getPayment();

        $payment->setAmount($data['amount'])
            ->setTransactionId($data['id'])
            ->setPreparedMessage('');

        switch ($data['status']) {
            case 'SALE':
                $payment->setIsTransactionClosed(1)
                    ->registerCaptureNotification($data['amount'])
                    ->setStatus(InstallData::ORDER_STATUS_PROCESSING_FULFILLMENT_CODE)
                    ->save();

                $order->setStatus(InstallData::ORDER_STATUS_PROCESSING_FULFILLMENT_CODE)->save();

                $this->logger->info(var_export($order, true));
                $this->logger->info("Order {$data['order']} processed as successfull sale");

                break;
            case 'REFUND':
                $order->setStatus(self::CANCELED)
                    ->save();

                $this->logger->info('Order ' . $data['order']
                    . ' processed as successfull REFUND');

                break;
            case 'CHARGEBACK':
                $order->setStatus(self::CANCELED)->save();
                $this->logger->info("Order {$data['order']} processed as successfull chargeback");

                break;
            default:
                return false;
        }

        return true;
    }
}
