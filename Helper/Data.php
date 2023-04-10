<?php

namespace Improntus\PowerPay\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Improntus\PowerPay\Logger\Logger;

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
     *
     * /**
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    )
    {
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
    public function getTitle()
    {
        return $this->getConfigData($this::TITLE);
    }

    public function isDebugEnabled()
    {
        return $this->getConfigData($this::DEBUG);
    }

    /**
     * @param $message
     * @return true
     */
    public function log($message)
    {
        if($this->isDebugEnabled()) {
            $this->logger->setName('modo_payments.log');
            $this->logger->info($message);
        }
        return true;
    }
}
