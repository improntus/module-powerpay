<?php

namespace Improntus\PowerPay\Model\ResourceModel\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Improntus\PowerPay\Model\Transaction::class,
            \Improntus\PowerPay\Model\ResourceModel\Transaction::class
        );
    }
}
