<?php

namespace Improntus\PowerPay\Model\Api;

use Improntus\PowerPay\Api\CallbackInterface;
use Magento\Framework\App\Request\Http;
use Improntus\PowerPay\Helper\Data;
use Improntus\PowerPay\Model\PowerPay;

class Callback implements CallbackInterface
{

    /**
     * @var PowerPay
     */
    private $powerPay;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Http
     */
    private $http;

    public function __construct(
        Http $http,
        Data $helper,
        PowerPay $powerPay
    )
    {
        $this->powerPay = $powerPay;
        $this->helper = $helper;
        $this->http = $http;
    }

    /**
     * @inheritDoc
     */
    public function updateStatus($data)
    {
        $response = new \Magento\Framework\Webapi\Exception(__('Authentication failed'));
        $basicAuth = $this->http->getHeader('Authorization');
        /** agregar el storeID de la orden para traer el secret */
        if (base64_decode($basicAuth) === $this->helper->getSecret()) {
            if (isset($data['id']) &&
                isset($data['status']) &&
                isset($data['expired_at']) &&
                isset($data['created_at']) &&
                isset($data['signature'])
            ) {
                $order = $this->powerPay->getOrderByTransactionId();
                if ($data['status'] == 'Processed') {
//                    if ($this->powerPay->invoice())
                }
            } else {
                $response =  new \Magento\Framework\Webapi\Exception(__('Invalid request data.'));
            }
        }
        throw $response;
    }
}
