<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\Model\Api;

use Improntus\PowerPay\Api\CallbackInterface;
use Improntus\PowerPay\Helper\Data;
use Improntus\PowerPay\Model\PowerPay;

class Callback implements CallbackInterface
{
    private const CONCATENATOR = '~';

    /**
     * Remote statuses that authorize cancelling the order. Only an AUTHENTICATED
     * re-query reporting one of these terminal states may cancel the order; the
     * webhook body status is never trusted for cancellation.
     */
    private const TERMINAL_CANCEL_STATUSES = ['canceled', 'cancelled', 'expired', 'rejected'];

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
        PowerPay $powerPay
    ) {
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
                $secret = (string)$this->helper->getSecret($order->getStoreId());
                if ($secret === '') {
                    /**
                     * C1-h: never validate a signature against an empty secret - the HMAC would
                     * degrade to a value the caller can compute. Fail closed.
                     */
                    throw new \Magento\Framework\Webapi\Exception(__('Authentication failed'));
                }
                /**
                 * C1 replay: pin created_at to the STORED transaction record instead of the
                 * request body so the signed material cannot be steered by the caller.
                 */
                $transactionCreatedAt = $transaction->getCreatedAt();
                $unhashedSignature =
                    $secret .
                    self::CONCATENATOR .
                    $transactionId .
                    self::CONCATENATOR .
                    $transactionCreatedAt;

                $signature = hash('sha256', $unhashedSignature);
                if (hash_equals($signature, (string)$data['signature'])) {
                    /**
                     * Idempotency guard: key off whether the order was ACTUALLY invoiced,
                     * not the 'processed' status column. The customer redirect
                     * (Controller/Order/Response -> persistTransaction) writes status
                     * 'processed' WITHOUT invoicing, so when the browser redirect reaches the
                     * store before the async webhook a status-based guard would make the
                     * webhook return early and the order would NEVER be invoiced/captured.
                     * Only an order that truly has an invoice is treated as already-processed.
                     */
                    if ($order->hasInvoices()) {
                        return true;
                    }
                    /**
                     * A1 + C1 replay: the body status is NOT covered by the signature, so it can
                     * be flipped on a validly-signed notification. Re-query Powerpay for the
                     * authoritative status and captured amount; only a re-query that confirms
                     * "processed" AND reconciles the amount/currency may invoice the order.
                     */
                    $remote = $this->powerPay->getRemoteTransaction($order, $transactionId);
                    $verifiedFromRemote = is_array($remote);
                    $status = $verifiedFromRemote
                        ? strtolower((string)($remote['status'] ?? ''))
                        : strtolower((string)$data['status']);
                    if ($status === 'processed') {
                        if (!$verifiedFromRemote) {
                            $this->powerPay->flagForReview(
                                $order,
                                (string)__('Paid status could not be verified (re-query unavailable). '
                                    . 'Manual review required before invoicing.')
                            );
                            return true;
                        }
                        if (!$this->powerPay->isAmountReconciled($order, $remote)) {
                            $this->powerPay->flagForReview(
                                $order,
                                (string)__('Reported amount/currency does not match the order total. '
                                    . 'Manual review required before invoicing.')
                            );
                            return true;
                        }
                        if ($this->powerPay->invoice($order, $data['id'])) {
                            return true;
                        }
                        $response = new \Magento\Framework\Webapi\Exception(__('Order could not be invoiced.'));
                    } elseif ($verifiedFromRemote && in_array($status, self::TERMINAL_CANCEL_STATUSES, true)) {
                        /**
                         * Only cancel when the AUTHENTICATED re-query explicitly reports a
                         * terminal cancel/expired/rejected status. The webhook body status is
                         * never trusted here because the signature does not cover it.
                         */
                        return $this->processCancel($order, $status);
                    } else {
                        /**
                         * Unknown/absent remote status, or the re-query was unavailable: never
                         * cancel a cancelable (paid-but-pending) order on an unverified or
                         * unexpected status. Route to manual review instead.
                         */
                        $this->powerPay->flagForReview(
                            $order,
                            (string)__('Payment status could not be positively verified '
                                . '(unexpected or unavailable re-query). '
                                . 'Manual review required before any cancellation.')
                        );
                        return true;
                    }
                } else {
                    /**
                     * M1: never log the locally-computed (valid) signature or the raw request
                     * body - both leak secret-derived material / enable log injection.
                     */
                    $this->helper->log('Powerpay webhook signature validation failed for transaction ' . $transactionId);
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
