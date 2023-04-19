<?php

namespace Improntus\PowerPay\Api\Data;

interface TransactionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PowerPay Transaction list.
     * @return \Improntus\PowerPay\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set PowerPay Transaction list.
     * @param \Improntus\PowerPay\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
