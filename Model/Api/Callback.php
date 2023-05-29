<?php

namespace Improntus\PowerPay\Model\Api;

use Improntus\PowerPay\Api\CallbackInterface;
use Magento\Framework\App\Request\Http;
use Improntus\PowerPay\Helper\Data;
use Improntus\PowerPay\Model\PowerPay;

class Callback implements CallbackInterface
{
    private const CONCATENATOR = '~';
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
    ) {
        $this->powerPay = $powerPay;
        $this->helper = $helper;
        $this->http = $http;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function updateStatus($data)
    {
        /** agregar el storeID de la orden para traer el secret */
        if (
            isset($data['id']) &&
            isset($data['status']) &&
            isset($data['expired_at']) &&
            isset($data['created_at']) &&
            isset($data['signature'])
        ) {
            if ($transaction = $this->powerPay->checkIfExists($data['id'])) {
                $transactionId = $transaction->getPowerPayTransactionId();
                $transactionCreatedAt = $transaction->getCreatedAt();
                $unhashedSignature =
                    $this->helper->getSecret() .
                    $this::CONCATENATOR .
                    $transactionId .
                    $this::CONCATENATOR .
                    $transactionCreatedAt;

                $signature = hash('sha256', $unhashedSignature);
                if ($signature === $data['signature']) {
                    $order = $this->powerPay->getOrderByTransactionId($data['id']);
                    if (strtolower($data['status']) == 'processed') {
                        if ($this->powerPay->invoice($order, $data['id'])) {
                            return true;
                        } else {
                            $response = new \Magento\Framework\Webapi\Exception(__('Order could not be invoiced.'));
                        }
                    } else {
                        return $this->processCancel($order, $data['status']);
                    }
                } else {
                    $response = new \Magento\Framework\Webapi\Exception(__('Authentication failed'));
                }
            } else {
                $response = new \Magento\Framework\Webapi\Exception(__('There was no transaction with requested Id.'));
            }
        } else {
            $response =  new \Magento\Framework\Webapi\Exception(__('Invalid request data.'));
        }
        throw $response;
    }

    /**
     * @param $order
     * @param $status
     * @return bool
     */
    private function processCancel($order, $status)
    {
        $status = strtolower($status);
        $message = (__('Order ' . $status . ' by Powerpay.'));
        if ($this->powerPay->cancelOrder($order, $message)) {
            return true;
        } else {
            return false;
        }
    }
}
