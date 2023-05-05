<?php

namespace Improntus\PowerPay\Block\Onepage;

use Magento\Catalog\Model\Product;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Catalog\Helper\Image;

class Success extends \Magento\Checkout\Block\Onepage\Success
{

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        OrderRepositoryInterface $orderRepository,
        Image $imageHelper,
        array $data = []
    )
    {
        $this->imageHelper = $imageHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderData()
    {
        return $this->orderRepository->get($this->getOrderId());
    }

    /**
     * @return Product[]
     */
    public function getOrderItems()
    {
        $products = [];
        $order = $this->getOrderData();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProduct()->getTypeId() !== 'configurable') {
                $products[] = [
                    'product' => $item->getProduct(),
                    'qty' => $item->getQtyOrdered()
            ];
            }
        }
        return $products;
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductImage($product)
    {
        return $this->imageHelper->init($product, 'cart_page_product_thumbnail')->getUrl();
    }

}
