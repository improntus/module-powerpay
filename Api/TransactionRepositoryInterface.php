<?php

namespace Improntus\PowerPay\Api;

interface TransactionRepositoryInterface
{

    /**
     * Save Transaction
     * @param \Improntus\PowerPay\Api\Data\TransactionInterface $transaction
     * @return \Improntus\PowerPay\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Improntus\PowerPay\Api\Data\TransactionInterface $transaction
    );

    /**
     * Retrieve Transaction
     * @param string $transactionId
     * @return \Improntus\PowerPay\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($transactionId);

    /**
     * Retrieve Transaction
     * @param int $orderId
     * @return \Improntus\PowerPay\Api\Data\TransactionInterface | false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByOrderId($orderId);

    /**
     * Retrieve Transaction matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Improntus\PowerPay\Api\Data\TransactionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Transaction
     * @param \Improntus\PowerPay\Api\Data\TransactionInterface $transaction
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Improntus\PowerPay\Api\Data\TransactionInterface $transaction
    );

    /**
     * Delete Transaction by ID
     * @param string $transactionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($transactionId);
}
