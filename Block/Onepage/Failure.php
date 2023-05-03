<?php

namespace Improntus\PowerPay\Block\Onepage;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

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

    public function getTryAgainUrl($id)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $id]);
    }
}
