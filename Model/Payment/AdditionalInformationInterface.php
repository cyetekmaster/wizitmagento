<?php declare(strict_types=1);

namespace Wizit\Wizit\Model\Payment;

interface AdditionalInformationInterface
{
    const WIZIT_ORDER_ID = 'wizit_order_id';
    const WIZIT_OPEN_TO_CAPTURE_AMOUNT = 'wizit_open_to_capture_amount';
    const WIZIT_PAYMENT_STATE = 'wizit_payment_state';
    const WIZIT_AUTH_EXPIRY_DATE = 'wizit_auth_expiry_date';
    const WIZIT_ROLLOVER_DISCOUNT = 'wizit_rollover_discount';
    const WIZIT_CAPTURED_DISCOUNT = 'wizit_captured_discount';
}