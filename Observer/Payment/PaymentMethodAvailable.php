<?php declare(strict_types=1);

namespace Wizit\Wizit\Observer\Payment;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class PaymentMethodAvailable implements ObserverInterface {
    private $logger;
    private $wizit_data_helper;
    private $cartManagement;
    private $session;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Wizit\Wizit\Helper\Data $wizit_helper,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\Session $session
    ) {
        
        $this->logger = $logger;
        $this->wizit_data_helper = $wizit_helper;
        $this->cartManagement = $cartManagement;
        $this->session = $session;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer) {
        //Replace this code with your own checks.  This code is checking a customer attribute.  If they are not approved for billing terms, the purchaseorder method is turned off.
        
        $result = $observer->getEvent()->getResult();
        $method_instance = $observer->getEvent()->getMethodInstance()->getCode();
        $quote = $this->session->getQuote();
        if($method_instance == 'wizit') {
            $this->logger->info("-------------------->>>>>>>>>>>>>>>>>>WIZIT  Check PaymentMethodAvailable Start<<<<<<<<<<<<<<<<<<<<-------------------");
            // get total
            $total = floatval($quote->getGrandTotal());
            // get min and max
            // get limit
            $wizit_minimum_payment_amount = $this->wizit_data_helper->getConfig('payment/wizit/min_max_wizit/wz_min_amount');
            $wizit_maxmum_payment_amount = $this->wizit_data_helper->getConfig('payment/wizit/min_max_wizit/wz_max_amount');


            $wizit_merchant_min_amount =  $this->wizit_data_helper->getConfig('payment/wizit/min_max_wizit/merchant_min_amount');
            $wizit_merchant_max_amount =  $this->wizit_data_helper->getConfig('payment/wizit/min_max_wizit/merchant_max_amount');

            if (empty($wizit_merchant_min_amount) || empty($wizit_merchant_max_amount))
            {
                $wizit_merchant_min_amount = $wizit_minimum_payment_amount;
                $wizit_merchant_max_amount = $wizit_maxmum_payment_amount;
            }

            $m_min = floatval($wizit_merchant_min_amount);
            $m_max = floatval($wizit_merchant_max_amount);

            
            // check limit
            if($total < $m_min ||  $total > $m_max){
                $result->setData('is_available', false);
                $this->logger->info("wizit turned off");
                $this->logger->info("wizit order total=" . $total . ', Min=' . $m_min . ', Max=' . $m_max);
            }
            
            $this->logger->info("-------------------->>>>>>>>>>>>>>>>>>WIZIT  Check PaymentMethodAvailable End<<<<<<<<<<<<<<<<<<<<-------------------");
        }

        
    }


}





