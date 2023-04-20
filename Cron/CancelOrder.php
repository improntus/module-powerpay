<?php

namespace Improntus\PowerPay\Cron;

use Improntus\PowerPay\Api\TransactionRepositoryInterface;
use Improntus\PowerPay\Helper\Data;
use Improntus\PowerPay\Model\PowerPay;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class CancelOrder
{

    CONST PENDING = 'pending';
    CONST EXPIRED = 'expired';
    CONST CANCELED = 'canceled';
    /**
     * @var PowerPay
     */
    private $powerPay;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        Data $helper,
        PowerPay $powerPay
    )
    {
        $this->powerPay = $powerPay;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionRepository = $transactionRepository;
    }


    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelPending()
    {
        $collection = $this->getTransactionCollection(self::PENDING);

        foreach ($collection as $transaction) {
            $order = $this->orderRepository->get($transaction->getOrderId());
            if ($order->getState() !== Order::STATE_NEW) {
                continue;
            }
            $datetime1 = date_create($order->getCreatedAt());
            $datetime2 = date_create(date('Y-m-d H:i:s', strtotime("now")));
            $interval = abs(($datetime1->getTimestamp() - $datetime2->getTimestamp())/3600);
            $cancelHours = $this->helper->getCancelHours($order->getStore()->getId());
            if ($cancelHours === '') {
                $cancelHours = 24;
            }
            if ($interval > $cancelHours) {
                $message = (__('Order canceled after' . $cancelHours . ' hours pending.'));
                $this->powerPay->cancelOrder($order, $message);
                $transaction->setStatus($this::CANCELED);
                $this->transactionRepository->save($transaction);
            }
        }
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelExpired()
    {
        $collection = $this->getTransactionCollection(self::PENDING);

        foreach ($collection as $transaction) {
            $order = $this->orderRepository->get($transaction->getOrderId());
            if ($order->getState() !== Order::STATE_NEW) {
                continue;
            }
            $timeZone = new \DateTimeZone('America/Lima');
            $currentTime = date_create(date('Y-m-d H:i:s', strtotime("now")), $timeZone);
            $expiredAt = date_create(date('Y-m-d H:i:s', strtotime($transaction->getExpiredAt())), $timeZone);

            if ($expiredAt->format('Y-m-d H:i:s') < $currentTime->format('Y-m-d H:i:s')) {
                $message = (__('Order canceled due to expiration time.'));
                $this->powerPay->cancelOrder($order, $message);
                $transaction->setStatus($this::EXPIRED);
                $this->transactionRepository->save($transaction);
            }
        }
    }
    /**
     * @return \Improntus\PowerPay\Api\Data\TransactionInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTransactionCollection($status)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', $status)
            ->create();
        return $this->transactionRepository->getList($searchCriteria)->getItems();
    }

}
