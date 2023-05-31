<?php

namespace Improntus\PowerPay\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Improntus\PowerPay\Model\PowerPay;
use Magento\Framework\Controller\Result\RedirectFactory;
use Improntus\PowerPay\Helper\Data;

class Response implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PowerPay
     */
    private $powerPay;

    public function __construct(
        Session $session,
        PowerPay $powerPay,
        RedirectFactory $redirectFactory,
        Data $helper,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->redirectFactory = $redirectFactory;
        $this->powerPay = $powerPay;
        $this->session = $session;
    }


    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();
        $path = 'checkout/onepage/failure';
        $result = $this->request->getParams();
        if (isset($result['error'])) {
            if ($result['error'] == 'true') {
                $message = $this->session->getPowerPayError();
                $this->session->setErrorMessage($message);
            } elseif ($result['error'] == 'noresponse') {
                $message = (__('There was a problem retrieving data from PowerPay. Wait for status confirmation from PowerPay.'));
                $this->session->setErrorMessage($message);
            }
        } elseif (isset($result['status'])) {
            $transactionId = $result['transaction_id'];
            $order = $this->session->getLastRealOrder();
            if (
                !isset($result['token']) ||
                $result['token'] !== $this->helper->generateToken($order)
            ) {
                $message = (__('Invalid Token.'));
                $this->powerPay->cancelOrder($order, $message);
                $this->session->setErrorMessage($message);
                $resultRedirect->setPath($path);
                return $resultRedirect;
            }
            $this->powerPay->persistTransaction($order, $result);
            if ($result['status'] == 'processed') {
                /** order is only invoiced via webhook */
//                if ($this->powerPay->invoice($order,$transactionId)) {
//                    $this->helper->log("Order: {$order->getIncrementId()} invoiced succesfully.");
//                } else {
//                    $this->helper->log("Order: {$order->getIncrementId()} was NOT invoiced.");
//                }
                $this->powerPay->addSuccessToStatusHistory($order);
                $path = 'checkout/onepage/success';
            } else {
                $message = (__('Unexpected response from PowerPay'));
                if ($result['status'] == 'canceled') {
                    $message = (__('The payment was cancelled by PowerPay.'));
                } elseif ($result['status'] == 'expired') {
                    $message = (__('The payment date has expired.'));
                }
                $this->session->setErrorMessage($message);
                $this->powerPay->cancelOrder($order, $message);
            }
        }
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
