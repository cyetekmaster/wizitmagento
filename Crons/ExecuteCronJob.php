<?php
namespace Wizit\Wizit\Crons;

use \Wizit\Wizit\Helper\Data;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

class ExecuteCronJob
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */

    protected $resourceConfig;
    protected $scopeConfig;
    private $helper;

    /**
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */

    public function __construct(
        \Wizit\Wizit\Helper\Data $helper,  
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Config\Model\ResourceModel\Config $resourceConfig) // phpcs:ignore
    {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
    }

    public function execute()
    {

        $get_api_key = $this->helper->getConfig('payment/wizit/api_key');
        $oldwmin = $this->helper->getConfig('payment/wizit/min_max_wizit/wz_min_amount');
        $oldwmax = $this->helper->getConfig('payment/wizit/min_max_wizit/wz_max_amount');

        if (!empty($oldwmin) && !empty($oldwmax)) {

            $wzresponse = $this->helper->call_limit_api($get_api_key);

            if (!is_array($wzresponse)) {

                throw new \Magento\Framework\Exception\ValidatorException(__($wzresponse));

            } else {

                $merchant_minimum_amount =
                $this->helper->getConfig('payment/wizit/min_max_wizit/merchant_min_amount');
                $merchant_maximum_amount =
                $this->helper->getConfig('payment/wizit/min_max_wizit/merchant_max_amount');
                $merchant_min_old =
                $this->helper->getConfig('payment/wizit/min_max_wizit/merchant_min_amount');
                $merchant_max_old =
                $this->helper->getConfig('payment/wizit/min_max_wizit/merchant_max_amount');

                $wmin = $wzresponse['minimumAmount'];
                $wmax = $wzresponse['maximumAmount'];

                //$wmin = 300;
                //$wmax = 800;

                if ($oldwmin < $wmin && $wmin < $oldwmax || $merchant_min_old < $wmin && $wmin < $merchant_max_old) {

                    $merchant_minimum_amount = $wmin;

                    $this->helper->initiateWizitLogger('Cron Scheduler Called and Updated Wizit minimumAmount value: '
                        .json_encode($merchant_minimum_amount));
                }

                if ($oldwmax > $wmax && $wmax > $oldwmin || $merchant_max_old > $wmax && $wmax > $merchant_min_old) {

                    $merchant_maximum_amount = $wmax;
                    $this->helper->initiateWizitLogger(
                        'Cron Scheduler Called and Updated Wizit maximumAmount value: '
                        .json_encode(
                            $merchant_maximum_amount
                        )
                    );
                }

                if (($oldwmin != $wmin || $oldwmax != $wmax ) ||
                     ($merchant_min_old != $merchant_minimum_amount ||
                     $merchant_max_old != $merchant_maximum_amount )) {

                    $this->resourceConfig->saveConfig(
                        'payment/wizit/min_max_wizit/wz_min_amount',
                        $wmin,
                        \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                        0
                    );

                    $this->resourceConfig->saveConfig(
                        'payment/wizit/min_max_wizit/wz_max_amount',
                        $wmax,
                        \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                        0
                    );

                    $recipientEmail = $this->scopeConfig->getValue(
                        'trans_email/ident_support/email',
                        ScopeInterface::SCOPE_STORE
                    );

                    $from = $recipientEmail;
                    $nameFrom = "Wizit";
                    $nameTo = "Wizit";
                    $message = "Hello Admin, Wizit minimum and maximum order amount limits have been changed. 
		            Please login to your Magento store and reset Merchant Minimum Amount and Merchant Maximum Amount. 
		            Thank you!";

                    $to = [$recipientEmail, $recipientEmail];
                    $email = new \Zend_Mail();
                    $email->setSubject("Wizit Transaction Limits Change Notification On Your Magento Store");
                    $email->setBodyText($message);
                    $email->setFrom($from, $nameFrom);
                    $email->addTo($recipientEmail, $nameTo);
                    $email->send();

                    $this->helper->initiateWizitLogger(
                        'Notification Email sent successfully to Magento Store Admin: '
                        .json_encode(
                            $email
                        )
                    );

                    /*throw new \Magento\Framework\Exception\CouldNotDeleteException(__(
                        'Warning: Wizit minimum and maximum order amount limits have been changed.'
                    ));*/
                }
            }
        }
    }
}
