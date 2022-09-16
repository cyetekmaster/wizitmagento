<?php declare(strict_types=1);

namespace Wizit\Wizit\Observer;

class SetQuoteIsPaidByWizit implements \Magento\Framework\Event\ObserverInterface
{
    private $quotePaidStorage;

    public function __construct(
        \Wizit\Wizit\Model\Order\Payment\QuotePaidStorage $quotePaidStorage
    ) {
        $this->quotePaidStorage = $quotePaidStorage;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getData('payment');

        if ($payment->getMethod() == \Wizit\Wizit\Gateway\Config\Config::CODE) {
            $this->quotePaidStorage->setWizitPaymentForQuote((int)$payment->getOrder()->getQuoteId(), $payment);
        }
    }
}