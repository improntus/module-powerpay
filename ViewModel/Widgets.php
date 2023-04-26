<?php
namespace Improntus\PowerPay\ViewModel;

use Improntus\PowerPay\Helper\Data;
class Widgets implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    CONST SANDBOX_JS_URL = 'https://components-bnpl-pe-bbva-beta.moprestamo.com/cdn/dist/powerpay-components/powerpay-components.esm.js';
    CONST PRODUCTION_JS_URL = 'https://components-bnpl-pe-bbva-production.moprestamo.com/cdn/dist/powerpay-components/powerpay-components.esm.js';
    CONST PRODUCTION_CSS_URL = 'https://components-bnpl-pe-bbva-production.moprestamo.com/css/config.css';

    /**
     * @var Data
     */
    private $helper;
    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getCssUrl()
    {
        return $this::PRODUCTION_CSS_URL;
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getJsUrl($storeId)
    {
        if ($this->helper->getSandbox($storeId)) {
            return $this::SANDBOX_JS_URL;
        } else {
            return $this::PRODUCTION_JS_URL;
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
     * @param $storeId
     * @return bool
     */
    public function getProductWidgetEnabled($storeId)
    {
        return $this->helper->getProductWidgetEnabled($storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getBannerWidgetEnabled($storeId)
    {
        return $this->helper->getBannerWidgetEnabled($storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getHeaderWidgetEnabled($storeId)
    {
        return $this->helper->getHeaderWidgetEnabled($storeId);
    }
}
