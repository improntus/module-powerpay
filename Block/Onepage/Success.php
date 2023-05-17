<?php

namespace Improntus\PowerPay\Block\Onepage;

use Magento\Catalog\Model\Product;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Catalog\Helper\Image;
use Improntus\PowerPay\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Success extends \Magento\Checkout\Block\Onepage\Success
{

    private $pci;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
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
        Data $helper,
        PriceCurrencyInterface $pci,
        array $data = []
    )
    {
        $this->pci = $pci;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper = $imageHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderData()
    {
        if ($this->checkoutSession->getLastRealOrder()->getId()) {
            return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
        } else {
            return false;
        }
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

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomSuccess()
    {
        return $this->helper->getCustomSuccess($this->_storeManager->getStore()->getId());
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductPrice($product)
    {
        return $this->pci->format($product->getPrice(), false);
    }

}
