<?php declare(strict_types=1);

namespace Wizit\Wizit\Model\Payment;

interface AdditionalInformationInterface
{
    const WIZPAY_ORDER_ID = 'wizit_order_id';
    const WIZPAY_OPEN_TO_CAPTURE_AMOUNT = 'wizit_open_to_capture_amount';
    const WIZPAY_PAYMENT_STATE = 'wizit_payment_state';
    const WIZPAY_AUTH_EXPIRY_DATE = 'wizit_auth_expiry_date';
    const WIZPAY_ROLLOVER_DISCOUNT = 'wizit_rollover_discount';
    const WIZPAY_CAPTURED_DISCOUNT = 'wizit_captured_discount';
}