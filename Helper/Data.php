<?php

namespace Improntus\PowerPay\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Improntus\PowerPay\Logger\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Data
{
    const CONFIG_ROOT = 'payment/powerpay/';
    const CLIENTID = 'clientid';
    const SECRET = 'secret';
    const SANDBOX = 'sandbox';
    const USER_AUTHENTICATED = 1;
    const INCOMPLETE_CREDENTIALS = 0;
    const ACTIVE = 'active';
    const TITLE = 'title';
    const DEBUG = 'debug';
    const MERCHANT_ID = 'merchant_id';
    const CONCEPT = 'concept';
    const CANCEL_HOURS = 'cancel_hours';

    const EP_MERCHANT_TRANSACTIONS = 'merchant-transactions';

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
     * @return mixed|string
     */
    public function isActive()
    {
        return $this->getConfigData($this::ACTIVE);
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
     * @return mixed|string
     */
    public function isDebugEnabled($storeId = null)
    {
        return $this->getConfigData($this::DEBUG, $storeId);
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

    public function getCancelHours($storeId = null)
    {
        return $this->getConfigData($this::CANCEL_HOURS, $storeId);
    }
    /**
     * @param $message
     * @return void
     */
    public function log($message)
    {
        if ($this->isDebugEnabled()) {
            $this->logger->setName('powerpay_payments.log');
            $this->logger->info($message);
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
