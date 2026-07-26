<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\Api;


use Magento\Framework\Webapi\Exception;

interface CallbackInterface
{
    /**
     * @param string[] $data
     * @throws Exception
     * @return mixed
     */
    public function updateStatus($data);

}
