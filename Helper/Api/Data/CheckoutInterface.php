<?php declare(strict_types=1);

namespace Wizit\Wizit\Helper\Api\Data;

interface CheckoutInterface{
     /**#@+
     * Checkout result keys
     */
    const WIZPAY_TOKEN = 'wizit_token';
    const WIZPAY_AUTH_TOKEN_EXPIRES = 'wizit_expires';
    const WIZPAY_REDIRECT_CHECKOUT_URL = 'wizit_redirectCheckoutUrl';


}