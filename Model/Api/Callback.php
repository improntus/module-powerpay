<?php

namespace Improntus\PowerPay\Model\Api;

use Improntus\PowerPay\Api\CallbackInterface;
use Improntus\PowerPay\Helper\Data;
use Improntus\PowerPay\Model\PowerPay;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Callback implements CallbackInterface
{
    private const CONCATENATOR = '~';

    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var PowerPay
     */
    private $powerPay;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper,
        PowerPay $powerPay,
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
        $this->powerPay = $powerPay;
        $this->helper = $helper;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Magento\Framework\Webapi\Exception
     * @throws \Exception
     */
    public function updateStatus($data)
    {
        if (
            isset($data['id']) &&
            isset($data['status']) &&
            isset($data['expired_at']) &&
            isset($data['created_at']) &&
            isset($data['signature'])
        ) {
            if ($transaction = $this->powerPay->checkIfExists($data['id'])) {
                $order = $this->powerPay->getOrderByTransactionId($data['id']);
                $transactionId = $transaction->getPowerPayTransactionId();
                $transactionCreatedAt = $transaction->getCreatedAt();
                $transactionCreatedAt = $this
                    ->timezone
                    ->date(new \DateTime($transactionCreatedAt))
                    ->format('Y-m-d H:i:s');
                $unhashedSignature =
                    $this->helper->getSecret($order->getStoreId()) .
                    $this::CONCATENATOR .
                    $transactionId .
                    $this::CONCATENATOR .
                    $transactionCreatedAt;

                $signature = hash('sha256', $unhashedSignature);
                if ($signature === $data['signature']) {
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
