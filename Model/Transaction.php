<?php

namespace Improntus\PowerPay\Model;

use Improntus\PowerPay\Api\Data\TransactionInterface;
use Magento\Framework\Model\AbstractModel;

class Transaction extends AbstractModel implements TransactionInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Improntus\PowerPay\Model\ResourceModel\Transaction::class);
    }

    /**
     * @return int|string|null
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @param $transactionId
     * @return TransactionInterface|Transaction
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

     /**
     * inherit phpdoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * inherit phpdoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * inherit phpdoc
     */
    public function getPowerPayTransactionId()
    {
        return $this->getData(self::POWERPAY_TRANSACTION_ID);
    }

    /**
     * inherit phpdoc
     */
    public function setPowerPayTransactionId($powerPayTransactionId)
    {
        return $this->setData(self::POWERPAY_TRANSACTION_ID, $powerPayTransactionId);
    }

    /**
     * inherit phpdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * inherit phpdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * inherit phpdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * inherit phpdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * inherit phpdoc
     */
    public function getExpiredAt()
    {
        return $this->getData(self::EXPIRED_AT);
    }

    /**
     * inherit phpdoc
     */
    public function setExpiredAt($expiredAt)
    {
        return $this->setData(self::EXPIRED_AT, $expiredAt);
    }
}

