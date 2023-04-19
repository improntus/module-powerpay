<?php
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
