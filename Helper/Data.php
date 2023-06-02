<?php

namespace Improntus\PowerPay\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Improntus\PowerPay\Logger\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Data
{
    private const CONFIG_ROOT = 'payment/powerpay/';
    public const CLIENTID = 'clientid';
    public const SECRET = 'secret';
    public const SANDBOX = 'sandbox';
    public const USER_AUTHENTICATED = 1;
    public const INCOMPLETE_CREDENTIALS = 0;
    public const ACTIVE = 'active';
    public const TITLE = 'title';
    public const DEBUG = 'debug';
    public const MERCHANT_ID = 'merchant_id';
    public const CONCEPT = 'concept';
    public const CANCEL_HOURS = 'cancel_hours';
    public const WIDGETS_ENABLED = 'widgets';
    public const PRODUCT_WIDGET = 'product_widget';
    public const HEADER_WIDGET = 'header_widget';
    public const BANNER_WIDGET = 'banner_widget';
    public const CHECKOUT_WIDGET = 'checkout_widget';
    public const CUSTOM_SUCCESS = 'custom_success';
    public const EP_MERCHANT_TRANSACTIONS = 'merchant-transactions';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     *
     * /**
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $value
     * @param $storeId
     * @return mixed|string
     */
    public function getConfigData($value, $storeId = null)
    {
        $path = $this::CONFIG_ROOT . $value;
        /* client_id and secret must be decrypted after retrieved */
        if ($value === self::CLIENTID || $value === self::SECRET) {
            return $this->encryptor->decrypt($this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) ?? '');
        }
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) ?? '';
    }

    /**
     * @param $path
     * @param $params
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getUrl($path, $params = null)
    {
        if ($params) {
            return $this->storeManager->getStore()->getUrl($path, $params);
        }
        return $this->storeManager->getStore()->getUrl($path);
    }

    /**
     * @return int
     */
    public function validateCredentials()
    {
        $result = $this::INCOMPLETE_CREDENTIALS;
        if ($this->getConfigData($this::CLIENTID) && $this->getConfigData($this::SECRET)) {
            $result = $this::USER_AUTHENTICATED;
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getConfigData($this::ACTIVE);
    }

    /**
     * @return mixed|string
     */
    public function getTitle($storeId = null)
    {
        return $this->getConfigData($this::TITLE, $storeId);
    }

    /**
     * @return mixed|string
     */
    public function getSecret($storeId = null)
    {
        return $this->getConfigData($this::SECRET, $storeId);
    }

    /**
     * @return mixed|string
     */
    public function getClientId($storeId = null)
    {
        return $this->getConfigData($this::CLIENTID, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isDebugEnabled($storeId = null)
    {
        return (bool)$this->getConfigData($this::DEBUG, $storeId);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRedirectUrl()
    {
        return $this->getUrl('powerpay/order/create');
    }
    /**
     * @param $token
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCallBackUrl($token = null)
    {
        if ($token)
        {
            return $this->getUrl('powerpay/order/response', ['token' => $token]);
        }
        return $this->getUrl('powerpay/order/response');
    }

    /**
     * @param $storeId
     * @return mixed|string
     */
    public function getMerchantId($storeId = null)
    {
        return $this->getConfigData($this::MERCHANT_ID, $storeId);
    }

    /**
     * @param $storeId
     * @return mixed|string
     */
    public function getPaymentConcept($storeId = null)
    {
        return $this->getConfigData($this::CONCEPT, $storeId);
    }
    /**
     * @param $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCompanyName($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getName();
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getSandbox($storeId = null)
    {
        return (bool)$this->getConfigData($this::SANDBOX, $storeId);
    }


    /**
     * @param $storeId
     * @return mixed|string
     */
    public function getCancelHours($storeId = null)
    {
        return $this->getConfigData($this::CANCEL_HOURS, $storeId) ?? '';
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getWidgetsEnabled($storeId = null)
    {
        return (bool)$this->getConfigData($this::WIDGETS_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getProductWidgetEnabled($storeId = null)
    {
        return (bool)$this->getConfigData($this::PRODUCT_WIDGET, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getHeaderWidgetEnabled($storeId = null)
    {
        return (bool)$this->getConfigData($this::HEADER_WIDGET, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getBannerWidgetEnabled($storeId = null)
    {
        return (bool)$this->getConfigData($this::BANNER_WIDGET, $storeId);
    }


    /**
     * @param $storeId
     * @return bool
     */
    public function getCheckoutWidgetEnabled($storeId = null)
    {
        return (bool)$this->getConfigData($this::CHECKOUT_WIDGET, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getCustomSuccess($storeId = null)
    {
        return (bool)$this->getConfigData($this::CUSTOM_SUCCESS, $storeId);
    }

    /**
     * @param $message
     * @return void
     */
    public function log($message, $type = 'debug')
    {
        if ($this->isDebugEnabled()) {
            $this->logger->setName('Powerpay');
            if ($type !== 'debug') {
                $this->logger->info($message);
            } else {
                $this->logger->debug($message);
            }
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    public function generateToken($order)
    {
        return hash('sha256', $this->getSecret($order->getStoreId()) .  $order->getIncrementId() . $order->getCreatedAt());
    }
}
