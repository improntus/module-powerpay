<?php

namespace Improntus\PowerPay\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Improntus\PowerPay\Logger\Logger;
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
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getUrl($path)
    {
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

    public function getCreateUrl()
    {
        return $this->getUrl('powerpay/order/create');
    }

    public function getCallBackUrl()
    {
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
}
