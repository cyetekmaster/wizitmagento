<?php declare(strict_types=1);

namespace Wizit\Wizit\Model\Order\Payment;

use Magento\Sales\Model\Order\Payment;

class QuotePaidStorage
{
    private array $quotesOrderPayments = [];

    public function setWizitPaymentForQuote(int $quoteId, Payment $wizitPayment): self
    {
        $this->quotesOrderPayments[$quoteId] = $wizitPayment;
        return $this;
    }

    public function getWizitPaymentIfQuoteIsPaid(int $quoteId): ?Payment
    {
        return $this->quotesOrderPayments[$quoteId] ?? null;
    }
}