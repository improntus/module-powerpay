<?php

namespace Improntus\PowerPay\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Improntus\PowerPay\Model\PowerPay;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Improntus\PowerPay\Helper\Data;

class Create implements ActionInterface
{
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
        Session     $session,
        PowerPay $powerPay,
        RedirectFactory $redirectFactory,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->redirectFactory = $redirectFactory;
        $this->powerPay = $powerPay;
        $this->session = $session;
    }


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $order = $this->session->getLastRealOrder();
        if ($response = $this->powerPay->createTransaction($order)) {
            if (isset($response['errors'])) {
                $message = "Order {$order->getIncrementId()} errors: \n";
                foreach ($response['errors'] as $error) {
                    $message .= "{$error['message']} \n";
                }
                $this->helper->log($message);
                $this->session->setPowerPayError($message);
                $url = "{$this->helper->getCallBackUrl()}?error=true";
            } else {
                $this->powerPay->persistTransaction($order, $response, 'create');
                $url = $response['redirection_url'];
            }
        } else {
            $url = "{$this->helper->getCallBackUrl()}?error=noresponse";
        }
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}
