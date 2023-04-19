<?php

namespace Improntus\PowerPay\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Improntus\PowerPay\Model\PowerPay;
use Magento\Backend\Model\View\Result\RedirectFactory;
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

    private $powerPay;

    public function __construct(
        PageFactory $resultPageFactory,
        Session     $session,
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
        $this->resultPageFactory = $resultPageFactory;
    }


    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $path = 'checkout/onepage/failure';
        $result = $this->request->getParams();
        if (isset($result['error'])) {
            if ($result['error'] == 'true') {
                $message = $this->session->getPowerPayError();
                $this->session->setErrorMessage($message);
            } elseif ($result['error'] == 'noresponse') {
                $message = (__('There was a problem retrieving data from PowerPay.'));
                $this->session->setErrorMessage($message);
            }
        } elseif (isset($result['status'])) {
            $transactionId = $result['transaction_id'];
            $order = $this->session->getLastRealOrder();
            $this->powerPay->persistTransaction($order, $result);
            if ($result['status'] == 'Processed') {
                if ($this->powerPay->invoice($order,$transactionId)) {
                    $this->helper->log("Order: {$order->getIncrementId()} invoiced succesfully.");
                } else {
                    $this->helper->log("Order: {$order->getIncrementId()} was NOT invoiced.");
                }
                $path = 'checkout/onepage/success';
            } elseif ($result['status'] == 'Canceled') {
                $message = (__('The payment was cancelled by PowerPay.'));
                $this->session->setErrorMessage($message);
            } elseif ($result['status'] == 'Expired') {
                $message = (__('The payment date has expired.'));
                $this->session->setErrorMessage($message);
//                expired result?
            }
        }
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
