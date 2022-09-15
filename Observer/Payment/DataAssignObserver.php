<?php declare(strict_types=1);

namespace Wizit\Wizit\Observer\Payment;

class DataAssignObserver extends \Magento\Payment\Observer\AbstractDataAssignObserver
{
    private $additionalInformationList = array(
        \Wizit\Wizit\Helper\Api\Data\CheckoutInterface::WIZPAY_TOKEN,
        \Wizit\Wizit\Helper\Api\Data\CheckoutInterface::WIZPAY_AUTH_TOKEN_EXPIRES,
        \Wizit\Wizit\Helper\Api\Data\CheckoutInterface::WIZPAY_REDIRECT_CHECKOUT_URL
    );

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}