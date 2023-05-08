<?php

namespace Improntus\PowerPay\Block\Onepage;

use Magento\Sales\Api\OrderRepositoryInterface;

class Failure extends \Magento\Checkout\Block\Onepage\Failure
{

    private $orderRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    )
    {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $checkoutSession, $data);
    }

    /**
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderData()
    {
        if ($this->getRealOrderId()) {
            return $this->orderRepository->get($this->getRealOrderId());
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
