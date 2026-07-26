<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\ViewModel;

use Improntus\PowerPay\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Widgets implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    CONST SANDBOX_JS_URL = 'https://components-bnpl-pe-bbva-beta.moprestamo.com/cdn/dist/powerpay-components/powerpay-components.esm.js';
    CONST PRODUCTION_JS_URL = 'https://components-bnpl-pe-bbva-production.moprestamo.com/cdn/dist/powerpay-components/powerpay-components.esm.js';
    CONST PRODUCTION_CSS_URL = 'https://components-bnpl-pe-bbva-production.moprestamo.com/css/config.css';

    /* Installments advertised by the neutral PDP widget */
    CONST NEUTRAL_INSTALLMENTS = 3;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        Data $helper,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return string
     */
    public function getCssUrl()
    {
        return self::PRODUCTION_CSS_URL;
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getJsUrl($storeId)
    {
        if ($this->helper->getSandbox($storeId)) {
            return self::SANDBOX_JS_URL;
        } else {
            return self::PRODUCTION_JS_URL;
        }
    }

    /**
     * @param $storeId
     * @return mixed|string
     */
    public function getClientId($storeId)
    {
        return $this->helper->getClientId($storeId);
    }


    /**
     * Powerpay's own <mo-product-page> widget.
     *
     * @param $storeId
     * @return bool
     */
    public function getProductWidgetEnabled($storeId)
    {
        return $this->helper->isActive()
            && $this->helper->getProductWidgetMode($storeId) === Data::PRODUCT_WIDGET_DEFAULT;
    }

    /**
     * Local neutral widget (own markup + logo, modal opened via <mo-offer-frame>).
     *
     * @param $storeId
     * @return bool
     */
    public function getNeutralProductWidgetEnabled($storeId)
    {
        return $this->helper->isActive()
            && $this->helper->getProductWidgetMode($storeId) === Data::PRODUCT_WIDGET_NEUTRAL;
    }

    /**
     * @return int
     */
    public function getNeutralInstallments()
    {
        return self::NEUTRAL_INSTALLMENTS;
    }

    /**
     * Price of a single installment, formatted in the store currency.
     *
     * @param float $price
     * @param $storeId
     * @return string
     */
    public function getInstallmentAmount($price, $storeId)
    {
        return $this->priceCurrency->format(
            (float)$price / self::NEUTRAL_INSTALLMENTS,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getBannerWidgetEnabled($storeId)
    {
        return $this->helper->isActive() && $this->helper->getBannerWidgetEnabled($storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getHeaderWidgetEnabled($storeId)
    {
        return $this->helper->isActive() && $this->helper->getHeaderWidgetEnabled($storeId);
    }
}
