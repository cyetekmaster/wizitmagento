<?php declare(strict_types=1);

namespace Wizit\Wizit\Helper\Api\Data;

interface CheckoutInterface{
     /**#@+
     * Checkout result keys
     */
    const WIZIT_TOKEN = 'wizit_token';
    const WIZIT_AUTH_TOKEN_EXPIRES = 'wizit_expires';
    const WIZIT_REDIRECT_CHECKOUT_URL = 'wizit_redirectCheckoutUrl';


}