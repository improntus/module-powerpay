<?php

namespace Improntus\PowerPay\Api\Data;

interface TransactionInterface
{
    const TRANSACTION_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const POWERPAY_TRANSACTION_ID = 'transaction_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const EXPIRED_AT = 'expired_at';

    /**
     * Get transaction_id
     * @return string|null
     */
    public function getTransactionId();

    /**
     * Set transaction_id
     * @param string $transactionId
     * @return \Improntus\PowerPay\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * @return int|string|null
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getPowerPayTransactionId();

    /**
     * @param $powerPayTransactionId
     * @return mixed
     */
    public function setPowerPayTransactionId($powerPayTransactionId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * @return mixed
     */
    public function getExpiredAt();

    /**
     * @param $expiredAt
     * @return mixed
     */
    public function setExpiredAt($expiredAt);
}

