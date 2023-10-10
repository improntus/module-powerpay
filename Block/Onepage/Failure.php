<?php

namespace Improntus\PowerPay\Block\Onepage;

use Magento\Sales\Api\OrderRepositoryInterface;

class Failure extends \Magento\Checkout\Block\Onepage\Failure
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $checkoutSession, $data);
    }

    /**
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderData()
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        if ($orderId) {
            return $this->orderRepository->get($orderId);
        } else {
            return false;
        }
    }

    public function getIsPowerpayPayment()
    {
        if ($order = $this->getOrderData()) {
            return $order->getPayment()->getMethod() == 'powerpay';
        } else {
            return false;
        }
    }
}
