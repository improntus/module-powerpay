<?php

namespace Improntus\PowerPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    public function _construct()
    {
        $this->_init('powerpay_transaction', 'entity_id');
    }
}
