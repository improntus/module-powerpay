<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\Model;

use Improntus\PowerPay\Model\Rest\WebService;
use Improntus\PowerPay\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface as PaymentTransactionRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Improntus\PowerPay\Api\TransactionRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class PowerPay
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;
    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var PaymentTransactionRepository
     */
    private $paymentTransactionRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $paymentRepository;
    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;

    /**
     * @var WebService
     */
    private $ws;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    public function __construct(
        WebService                      $ws,
        Data                            $helper,
        InvoiceManagementInterface      $invoiceManagement,
        OrderPaymentRepositoryInterface $paymentRepository,
        OrderRepositoryInterface        $orderRepository,
        PaymentTransactionRepository    $paymentTransactionRepository,
        InvoiceRepositoryInterface      $invoiceRepository,
        OrderSender                     $orderSender,
        TransactionRepositoryInterface  $transactionRepository,
        TransactionFactory              $transactionFactory,
        ResourceConnection              $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->orderSender = $orderSender;
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentTransactionRepository = $paymentTransactionRepository;
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->invoiceManagement = $invoiceManagement;
        $this->helper = $helper;
        $this->ws = $ws;
    }

    /**
     * @param $order
     * @return false|mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createTransaction($order)
    {
        $data = $this->getRequestData($order);
        try {
            $response = $this->ws->doRequest(
                $this->helper::EP_MERCHANT_TRANSACTIONS,
                $this->helper->getSecret(),
                $data
            );
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $response ?? false;
    }

    /**
     * @param Order $order
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getRequestData($order)
    {
        $token = $this->helper->generateToken($order);
        $customerData = $this->getCustomerData($order);
        return [
            'external_id' => $order->getIncrementId(),
            'callback_url' => $this->helper->getCallBackUrl($token),
            'values' => [
                'merchant_id' => $this->helper->getMerchantId($order->getStoreId()),
                'currency' => 'PEN',
                'document_number' => $customerData['document_number'],
                'document_type' => 'DNI',
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'email' => $customerData['email'],
                'country_code' => '+51',
                'phone_number' => $customerData['phone_number'],
                'payment_concept' => $this->helper->getPaymentConcept($order->getStoreId()),
                'shipping_postal_code' => $customerData['shipping_postal_code'],
                'shipping_address' => $customerData['shipping_address'],
            ],
            'amount' => round($order->getGrandTotal(), 2)
        ];
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerData($order)
    {
        $address = $order->getBillingAddress();
        return [
            'document_number' => $order->getCustomerTaxvat() ?? $address->getVatId() ?? '',
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'email' => $order->getCustomerEmail(),
            'phone_number' => $address->getTelephone() ?? '',
            'shipping_postal_code' => $address->getPostcode() ?? '',
            'shipping_address' =>
                "{$address->getStreetLine(1)} {$address->getStreetLine(2)} {$address->getStreetLine(3)} {$address->getStreetLine(4)}",
        ];
    }

    /**
     * @param $order
     * @param $transactionId
     * @return bool
     */
    public function invoice($order, $transactionId)
    {
        if (!$order->canInvoice() || $order->hasInvoices()) {
            return false;
        }
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $invoice = $this->invoiceManagement->prepareInvoice($order);
            $invoice->register();
            $this->orderRepository->save($order);
            $invoice->setTransactionId($transactionId);
            $payment = $order->getPayment();
            $this->paymentRepository->save($payment);
            $transaction = $this->generateTransaction($payment, $invoice, $transactionId);
            $transaction->setAdditionalInformation('amount', round($order->getGrandTotal(), 2));
            $transaction->setAdditionalInformation('currency', 'PEN');
            $this->paymentTransactionRepository->save($transaction);

            if (!$order->getEmailSent()) {
                $this->orderSender->send($order);
                $order->setIsCustomerNotified(true);
            }
            $invoice->pay();
            $invoice->getOrder()->setIsInProcess(true);
            $payment->addTransactionCommentsToOrder($transaction, __('Powerpay'));
            $this->invoiceRepository->save($invoice);
            $message = (__('Payment confirmed by PowerPay'));
            $order->addCommentToStatusHistory($message, Order::STATE_PROCESSING);
            $this->orderRepository->save($order);
            $ppTransaction = $this->transactionRepository->get($transactionId);
            $ppTransaction->setStatus('processed');
            $this->transactionRepository->save($ppTransaction);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $connection->rollBack();
            $message = "Invoice creating for order {$order->getIncrementId()} failed: \n";
            $message .= $e->getMessage() . "\n";
            $this->helper->log($message);
            return false;
        }
    }

    /**
     * @param $payment
     * @param $invoice
     * @param $paypalTransaction
     * @return mixed
     */
    private function generateTransaction($payment, $invoice, $transactionId)
    {
        $payment->setTransactionId($transactionId);
        return $payment->addTransaction(TransactionInterface::TYPE_CAPTURE, $invoice, true);
    }


    /**
     * @param $order
     * @param $result
     * @return void
     * @throws LocalizedException
     */
    public function persistTransaction($order, $result, $flow = 'response')
    {
        try {
            if ($flow !== 'response') {
                $transactionId = $result['id'];
            } else {
                $transactionId = $result['transaction_id'];
            }
            $status = strtolower($result['status'] ?? '');
            if (!$this->transactionRepository->getByOrderId($order->getId())) {
                $transaction = $this->transactionFactory->create();
                $transaction->setOrderId($order->getId());
                $transaction->setPowerPayTransactionId($transactionId ?? '');
                $transaction->setStatus($status);
                if (isset($result['created_at'])) {
                    $transaction->setCreatedAt($result['created_at']);
                }
                $transaction->setExpiredAt($result['expired_at'] ?? '');
                $this->transactionRepository->save($transaction);
            } else {
                $transaction = $this->transactionRepository->get($transactionId);
                $transaction->setStatus($status);
                $transaction->setExpiredAt($result['expired_at'] ?? '');
                $this->transactionRepository->save($transaction);
            }
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @param Phrase $message
     * @return bool
     */
    public function cancelOrder($order, $message)
    {
        try {
            if ($order->canCancel()) {
                $order->cancel();
                $order->setState(Order::STATE_CANCELED);
                $order->addCommentToStatusHistory($message, Order::STATE_CANCELED);
                $this->orderRepository->save($order);
                return true;
            }
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * @param $id
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     * @throws LocalizedException
     */
    public function getOrderByTransactionId($id)
    {
        $transaction = $this->transactionRepository->get($id);
        if ($transaction->getStatus()) {
            return $this->orderRepository->get($transaction->getOrderId());
        }
        return false;
    }

    /**
     * @param $id
     * @return false|\Improntus\PowerPay\Api\Data\TransactionInterface
     */
    public function checkIfExists($id)
    {
        try {
            return $this->transactionRepository->get($id);
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
            return false;
        }
    }

    /**
     * Re-query the transaction from Powerpay to obtain the authoritative status and the
     * amount that was actually captured.
     *
     * The signed webhook carries neither a trustworthy status (the signature does not cover
     * it) nor an amount, so the paid/amount decision MUST be taken from this authenticated
     * GET, never from the notification body.
     *
     * NOTE (deploy): the endpoint path (merchant-transactions/{id}) and the response field
     * names (status, amount, currency) must be confirmed against the Powerpay API. Any
     * unexpected or absent field is handled fail-closed by the caller (the order is flagged
     * for manual review instead of being invoiced).
     *
     * @param Order $order
     * @param string $transactionId
     * @return array|null Decoded transaction payload, or null when the re-query is unavailable
     */
    public function getRemoteTransaction($order, $transactionId)
    {
        try {
            $storeId = $order->getStoreId();
            $response = $this->ws->doRequest(
                $this->helper::EP_MERCHANT_TRANSACTIONS . '/' . rawurlencode((string)$transactionId),
                $this->helper->getSecret($storeId),
                null,
                'GET',
                $storeId
            );
            return is_array($response) ? $response : null;
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
            return null;
        }
    }

    /**
     * Reconcile the amount and currency reported by Powerpay against the order total.
     *
     * Powerpay settles in PEN (2 decimals) and the create request sends round(grand_total, 2),
     * so the re-queried amount must match the order total to the cent, the order must be a PEN
     * order, and any currency reported by Powerpay must also be PEN. Missing or invalid data is
     * treated as a mismatch (fail closed) so an order is never invoiced on an unverified amount.
     *
     * NOTE (deploy): comparison assumes Powerpay returns the amount in major units (e.g. 10.99),
     * matching what createTransaction sends. If the API returns minor units (cents), adjust here.
     *
     * @param Order $order
     * @param array $remoteTransaction
     * @return bool
     */
    public function isAmountReconciled($order, array $remoteTransaction)
    {
        if ($order->getOrderCurrencyCode() !== 'PEN') {
            return false;
        }
        if (isset($remoteTransaction['currency'])
            && strtoupper((string)$remoteTransaction['currency']) !== 'PEN') {
            return false;
        }
        if (!isset($remoteTransaction['amount']) || !is_numeric($remoteTransaction['amount'])) {
            return false;
        }
        $orderCents = (int)round((float)$order->getGrandTotal() * 100);
        $remoteCents = (int)round((float)$remoteTransaction['amount'] * 100);
        return $orderCents === $remoteCents;
    }

    /**
     * Flag an order for manual review instead of invoicing it.
     *
     * Used when the payment cannot be positively verified: the amount/currency does not match
     * the order total, or the paid status could not be confirmed via re-query. The order is
     * moved to payment_review with an explanatory comment; it is never invoiced or cancelled here.
     *
     * @param Order $order
     * @param string $reason
     * @return void
     */
    public function flagForReview($order, $reason)
    {
        try {
            $this->helper->log("Order {$order->getIncrementId()} flagged for review: {$reason}", 'info');
            if ($order->canInvoice() && $order->getState() !== Order::STATE_PAYMENT_REVIEW) {
                $order->setState(Order::STATE_PAYMENT_REVIEW);
                $order->setStatus(Order::STATE_PAYMENT_REVIEW);
                $order->addCommentToStatusHistory(__('Powerpay: %1', $reason), Order::STATE_PAYMENT_REVIEW);
                $this->orderRepository->save($order);
            }
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function addSuccessToStatusHistory($order)
    {
        if ($order->getState() === Order::STATE_NEW) {
            $message = (__('Payment confirmed by PowerPay, awaiting capture.'));
            $order->addCommentToStatusHistory($message, Order::STATE_PAYMENT_REVIEW);
            $this->orderRepository->save($order);
        }
    }
}
