<?php

namespace Wizit\Wizit\Helper;


use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterfaceFactory;




class Data extends AbstractHelper
{

    /**
     * Base API URL setting
     * 
     */
    private  $base_url = 'https://api.wizpay.com.au/';
    private  $test_url = 'https://stagingapi.wizpay.com.au/';
    private  $version = 'v1/';
    private  $intermediate = 'api/';
    private  $apicall = '';


    private function GetApiUrl($environment){
        
        $env = intval($environment, 0);
        if($env == 1){
            return $this->test_url . $this->version . $this->intermediate;
        }else{
            return $this->base_url . $this->version . $this->intermediate;   
        }
    } 




    protected $logger;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $curlClient;


    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        PaymentData $paymentData,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        TransportBuilder $transportBuilder,
        TransportInterfaceFactory $mailTransportFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        //$this->_gatewayConfig = $gatewayConfig;
        $this->_objectManager = $objectManager;
        $this->_paymentData   = $paymentData;
        $this->_storeManager  = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->curlClient = $curl;
        $this->transportBuilder = $transportBuilder;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->_scopeConfig   = $context->getScopeConfig();

        $this->logger = $logger;

        parent::__construct($context);
    }


    public function getPluginVersion(){
        return '1.0';
    }

    public function initiateWizitLogger($log)
    {
        $enable_debug = $this->getConfig('payment/wizit/debug');

        if(intval($enable_debug, 0) == 1 ){
            $this->logger->info($log);
        }

        
    }

    public function createWcog($apiresult)
    {
        $capture = '1';// $this->getConfig('payment/wizit/capture');
        $getAmount = $this->getDataFromJsonObj('originalAmount', $apiresult); // phpcs:ignore
        $amount = $this->getDataFromJsonObj('amount', $getAmount); // phpcs:ignore
        $logdata = ['CaptureSettings' =>$capture,
            'merchantReference'     => $this->getDataFromJsonObj('merchantReference', $getAmount), // phpcs:ignore
            'WZTransactionID'       => $this->getDataFromJsonObj('transactionId', $getAmount), // phpcs:ignore
            'paymentDescription'    => $this->getDataFromJsonObj('paymentDescription', $getAmount), // phpcs:ignore
            'responseCode'          => $this->getDataFromJsonObj('responseCode', $getAmount), // phpcs:ignore
            'errorCode'             => $this->getDataFromJsonObj('errorCode', $getAmount), // phpcs:ignore
            'Amount'                => '$'. $amount,
            'errorMessage'          => $this->getDataFromJsonObj('errorMessage', $getAmount), // phpcs:ignore
            'transactionStatus'     => $this->getDataFromJsonObj('transactionStatus', $getAmount), // phpcs:ignore
            'paymentStatus'         => $this->getDataFromJsonObj('paymentStatus', $getAmount)
        ]; // phpcs:ignore
        
        $this->initiateWizitLogger(json_encode($logdata));
    }

    public function getStoreCurrency()
    {

        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
    
    public function getConfig($config_path)
    {

        $setting = $this->scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        // if try to get api key then check environment
        if($config_path == 'payment/wizit/api_key'){
            $environment = $this->scopeConfig->getValue('payment/wizit/environment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if( $environment == 1){
                $setting = $this->scopeConfig->getValue('payment/wizit/api_key_sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            }
        }

        return $setting;
    }

    public function transaction()
    {
        return $this->_transaction;
    }

    public function transportBuilder()
    {
        return $this->transportBuilder;
    }

    public function mailTransportFactory()
    {
        return $this->mailTransportFactory;
    }

    /**
     * Get an Instance of the Magento Store Manager
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @throws NoSuchEntityException If given store doesn't exist.
     * @return string
     */
    public function getCompleteUrl()
    {
        return $this->getStoreManager()->getStore()->getBaseUrl() . 'wizit/index/success';
    }

    /**
     * @param string
     * @throws NoSuchEntityException If given store doesn't exist.
     * @return string
     */
    public function getCancelledUrl()
    {
        return $this->getStoreManager()->getStore()->getBaseUrl() . "wizit/index/failed";
    }
    // private function apiUrl() {
    
    //  return 'https://uatapi.wizardpay.com.au/v1/api/';
    // }
    private function apiUrl($environment = null)
    {        
        if(!isset($environment)){
            // get from setting
            $environment = $this->getConfig('payment/wizit/environment');
        }
        

        return $this->GetApiUrl(intval($environment));
    }

    public function getCurlClient()
    {
        return $this->curlClient;
    }

    private function getWizitapi($url, $apikey)
    {

        $this->initiateWizitLogger('--------------------------getWizitapi start------------------------------------------');
        $this->initiateWizitLogger('>>>>>>>>>>apikey: ' . $apikey . PHP_EOL);
        $this->initiateWizitLogger('>>>>>>>>>>URI: ' . $url . PHP_EOL);
        
        try {

            //$api_key = $this->getConfig('payment/opmc_wizit/api_key');
            
            // $this->getCurlClient()->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOption(CURLOPT_SSL_VERIFYHOST, false);
            $this->getCurlClient()->setOption(CURLOPT_SSL_VERIFYPEER, false);

            // $curl_options[CURLOPT_SSL_VERIFYHOST] = false;
            // $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
    
            $headers = ["Content-Type" => "application/json", "Api-Key" => $apikey];
            $this->getCurlClient()->setHeaders($headers);

            $this->getCurlClient()->get($url);

            $response = $this->getCurlClient()->getBody();

            $finalresult = json_decode($response, true);
                
            // // echo "<pre>";
            // var_dump($finalresult);
            // var_dump($response);
            // var_dump($headers);
            // die('asfs');
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = true;
                $errormessage = 'Error: Invalid Json Format received from Wizit API. Please contact customer support in this regard!!'; // phpcs:ignore
                $this->initiateWizitLogger('>>>>>>>>response: ' . json_encode($response) . PHP_EOL);
                $this->initiateWizitLogger('>>>>>>>>error: ' . $errormessage . PHP_EOL);
                $this->initiateWizitLogger('--------------------------getWizitapi end------------------------------------------');
                return $errormessage;
            }


            $this->initiateWizitLogger('>>>>>>>>response: ' . json_encode($finalresult) . PHP_EOL);
            $this->initiateWizitLogger('--------------------------getWizitapi end------------------------------------------');

            return $finalresult;

        } catch (\Exception $e) {
            $this->initiateWizitLogger('>>>>>>>>error: ' . $e->getMessage() . PHP_EOL);
            $this->initiateWizitLogger('--------------------------getWizitapi end------------------------------------------');
            return $e->getMessage();
        }
    }

    private function postWizitapi($url, $requestbody, $apikey)
    {
        $this->getCurlClient()->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->getCurlClient()->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->getCurlClient()->setOption(CURLOPT_SSL_VERIFYPEER, false);

        $this->initiateWizitLogger('--------------------------postWizitapi start------------------------------------------');
        $this->initiateWizitLogger('>>>>>>>>>>apikey: ' . $apikey . PHP_EOL);
        $this->initiateWizitLogger('>>>>>>>>>>URI: ' . $url . PHP_EOL);
        $this->initiateWizitLogger('>>>>>>>>>>Request: ' . json_encode($requestbody) . PHP_EOL);


        try {
            $postdata = json_encode($requestbody);
            $headers = ["Content-Type" => "application/json", "Api-Key" => $apikey];
            $this->getCurlClient()->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setHeaders($headers);
            $this->getCurlClient()->post($url.'?timeout=80&sslverify=false', $postdata);
            $response = $this->getCurlClient()->getBody();
            $finalresult = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = true;
                $errormessage = 'Error: Invalid Json Format received from Wizit API. Please contact customer support in this regard!!'; // phpcs:ignore
                $this->initiateWizitLogger('>>>>>>>>response: ' . json_encode($response) . PHP_EOL);
                $this->initiateWizitLogger('>>>>>>>>error: ' . $errormessage . PHP_EOL);
                $this->initiateWizitLogger('--------------------------postWizitapi end------------------------------------------');
                return $errormessage;
            }


            $this->initiateWizitLogger('>>>>>>>>response: ' . json_encode($finalresult) . PHP_EOL);
            $this->initiateWizitLogger('--------------------------postWizitapi end------------------------------------------');

            return $finalresult;

        } catch (\Exception $e) {

            $this->initiateWizitLogger('>>>>>>>>error: ' . $e->getMessage() . PHP_EOL);
            $this->initiateWizitLogger('--------------------------postWizitapi end------------------------------------------');
            return $e->getMessage();
        }
    }

    public function callLimitapi($apikey, $environment)
    {
        $error = false;
        $actualapicall = 'GetPurchaseLimit';
        $finalapiurl = $this->apiUrl($environment) . $actualapicall;
        //$finalapiurl = 'http://mywp.preyansh.in/wzapi.php';
        $apiresult = $this->getWizitapi($finalapiurl, $apikey);
        // echo $finalapiurl;
        // echo "<Pre>";
        // print_r($apiresult);
        // die('asd');
        if ('' == $apiresult) {
            $error = true;
            $errormessage = 'Error: Looks like your Website IP Address is not white-listed in Wizit. Please connect with Wizit support team!'; // phpcs:ignore
            $apiresult = $errormessage;

        } elseif (false !== $apiresult && '200' == $apiresult['responseCode']) {


        } elseif ('402' == $apiresult['responseCode'] || '412' == $apiresult['responseCode']) {
            $error = true;
            $errormessage = 'Call Transaction Limit Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage']; // phpcs:ignore
            
            $apiresult = $errormessage;
            
        } else {
            $error = true;
            $errormessage = 'Error: Please enter a valid Wizit API Key!';
            $apiresult = $errormessage;
        }
        return $apiresult;
    }

    public function callCcheckoutsRredirectAapi($apikey, $requestbody)
    {
        $error = false;
        $actualapicall = 'transactioncheckouts';
        $finalapiurl = $this->apiUrl() . $actualapicall;
        
        $apiresult = $this->postWizitapi($finalapiurl, $requestbody, $apikey);


        if (isset($apiresult['errors']) && $apiresult['status'] == '400') {

            $error = true;
            $errormessage = 'Checkout Redirect Error: ' . 'Invalid address or One or more validation errors occurred.';
            
            $apiresult = $errormessage;
            
        } elseif (isset($apiresult) && '200' == $apiresult['responseCode'] && isset($apiresult['responseCode'])) {

        } elseif ('402' == $apiresult['responseCode'] || '412' == $apiresult['responseCode'] && isset($apiresult['responseCode'])) { // phpcs:ignore

            $error = true;
            $errormessage = 'Checkout Redirect Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'];
            
            $apiresult = $errormessage;
            
        } else {
            $error = true;
            $errormessage = 'Checkout Redirect Error: ' . $apiresult['responseCode'];
            $apiresult = $errormessage;
        }
        return $apiresult;
    }

    public function callConfigurMerchantPlugin($apikey, $environment, $requestbody)
    {
        $error = false;
        $actualapicall = 'ConfigurMerchantPlugin';
        $finalapiurl = $this->apiUrl($environment) . $actualapicall;
        //$finalapiurl = 'http://mywp.preyansh.in/wzapi.php';
        
        $apiresult = $this->postWizitapi($finalapiurl, $requestbody, $apikey);

        return $apiresult;
    }

    public function getOrderPaymentStatusApi($apikey, $requestbody)
    {
        $actualapicall = 'Payment/transactionstatus';
        $finalapiurl = $this->apiUrl() . $actualapicall;
        //print_r($apikey);
        $apiresult = $this->postWizitapi($finalapiurl, $requestbody, $apikey);

        if (false !== $apiresult && '200' == $apiresult['responseCode']) {
            //print_r($apiresult);
            $errormessage = '';
            $responseerror = $this->handleOrderPaymentStatusApiError($apiresult, $errormessage);

            if (!empty($responseerror)) {
                
                $apiresult = $responseerror;
            } else {
               
            }

        } elseif ('402' == $apiresult['responseCode'] || '412' == $apiresult['responseCode']) {
            $error = true;
            $errormessage = 'Order Status Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'] . ' - ' . $apiresult['paymentDescription']; // phpcs:ignore
            $apiresult = $errormessage;
        } else {
            $error = true;
            $errormessage = 'Order Status Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'];
            $apiresult = $errormessage;
        }
        return $apiresult;
    }


    public function updateOrderIdApi($apikey, $transactionId, $orderId)
    {
        $actualapicall = 'Payment/merchantOrderId/' . $transactionId;
        $finalapiurl = $this->apiUrl() . $actualapicall;
        //print_r($apikey);
        $api_data = [
            "merchantOrderId" => $orderId
        ];
        $this->postWizitapi($finalapiurl, $api_data, $apikey);
    }




    public function handleOrderPaymentStatusApiError($apiresult, $errormessage)
    {
        $errormessage = '';
        $apiOrderId = $apiresult['transactionId'];
        if ('APPROVED' != $apiresult['transactionStatus'] && 'COMPLETED' != $apiresult['transactionStatus']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
        }

        if ('AUTH_APPROVED' != $apiresult['paymentStatus'] && 'CAPTURED' != $apiresult['paymentStatus'] && 'PARTIALLY_CAPTURED' != $apiresult['paymentStatus']) { // phpcs:ignore
            $orderMessage = '';
            if ('AUTH_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
            } elseif ('CAPTURE_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction Wizit Transaction ' . $apiOrderId . ' Capture Attempt has been declined'; // phpcs:ignore
            } elseif ('VOIDED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' VOID';
            } else {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Payment Failed.';
            }
        }
        return $errormessage;
    }

    public function immediatePaymentCapture($apikey, $requestbody)
    {
        $actualapicall = 'Payment/transactioncapture';
        $finalapiurl = $this->apiUrl() . $actualapicall;
        
        $apiresult = $this->postWizitapi($finalapiurl, $requestbody, $apikey);


        if (false !== $apiresult && '200' == $apiresult['responseCode']) {
            
            $errormessage = '';
            $responseerror = $this->handleImmediatePaymentCaptureError($apiresult, $errormessage);

            if (!empty($responseerror)) {
                
                $apiresult = $responseerror;
            } else {
               
            }

        } elseif ('402' == $apiresult['responseCode'] || '412' == $apiresult['responseCode']) {
            $error = true;
            $errormessage = 'Immediate Payment Capture Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'] . ' - ' . $apiresult['paymentDescription']; // phpcs:ignore
            
            $apiresult = $errormessage;
        } else {
            $error = true;
            $errormessage = 'Immediate Payment Capture Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'] . ' - ' . $apiresult['paymentDescription']; // phpcs:ignore
            
            $apiresult = $errormessage;
        }
        return $apiresult;
    }

    public function handleImmediatePaymentCaptureError($apiresult, $errormessage)
    {
        $error = true;
        $apiOrderId = $apiresult['transactionId'];
        if ('APPROVED' != $apiresult['transactionStatus'] && 'COMPLETED' != $apiresult['transactionStatus']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
        }

        if ('3005' == $apiresult['errorCode']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' Reason: ' . $apiresult['errorMessage']; // phpcs:ignore
            

        }

        if ('3008' == $apiresult['errorCode']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' Reason: ' . $apiresult['errorMessage']; // phpcs:ignore
            

        }

        if ('3006' == $apiresult['errorCode']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' Reason: ' . $apiresult['errorMessage']; // phpcs:ignore
            
            
        }

        if ('AUTH_APPROVED' != $apiresult['paymentStatus'] &&
        'CAPTURED' != $apiresult['paymentStatus'] &&
        'CAPTURE_DECLINED' != $apiresult['paymentStatus']) {
            $orderMessage = '';
            if ('AUTH_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
            } elseif ('CAPTURE_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Capture Attempt has been declined';
            } elseif ('VOIDED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' VOID';
            } else {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Payment Failed.';
            }
           
            
        }
        return $errormessage;
    }

    public function orderPartialCaptureApi($apikey, $requestbody, $apiOrderId)
    {
        $actualapicall = 'Payment/transactioncapture/' . $apiOrderId;
        $finalapiurl = $this->apiUrl() . $actualapicall;
        
        $apiresult = $this->postWizitapi($finalapiurl, $requestbody, $apikey);
       
        if (false !== $apiresult && '200' == $apiresult['responseCode']) {
            
            $errormessage = '';
            $responseerror = $this->handlePartialPaymentCaptureError($apiresult, $errormessage);

            if (!empty($responseerror)) {
                
                $apiresult = $responseerror;
            } else {
               
            }

        } elseif ('402' == $apiresult['responseCode'] || '412' == $apiresult['responseCode']) {
            $error = true;
            $errormessage = 'Partial Payment Capture Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'] . ' - ' . $apiresult['paymentDescription']; // phpcs:ignore
            
            $apiresult = $errormessage;
        } else {
            $error = true;
            $errormessage = 'Partial Payment Capture Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'] . ' - ' . $apiresult['paymentDescription']; // phpcs:ignore
            
            $apiresult = $errormessage;
        }
        return $apiresult;
    }

    public function handlePartialPaymentCaptureError($apiresult, $errormessage)
    {
        $error = true;
        $apiOrderId = $apiresult['transactionId'];
        if ('APPROVED' != $apiresult['transactionStatus'] && 'COMPLETED' != $apiresult['transactionStatus']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
            
        }

        if ('3005' == $apiresult['errorCode']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' Reason: ' . $apiresult['errorMessage']; // phpcs:ignore
            

        }

        if ('3008' == $apiresult['errorCode']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' Reason: ' . $apiresult['errorMessage']; // phpcs:ignore
            

        }

        if ('3006' == $apiresult['errorCode']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' Reason: ' . $apiresult['errorMessage']; // phpcs:ignore
            
            
        }

        if ('AUTH_APPROVED' != $apiresult['paymentStatus'] && 'PARTIALLY_CAPTURED' != $apiresult['paymentStatus'] && 'CAPTURED' != $apiresult['paymentStatus'] && 'CAPTURE_DECLINED' != $apiresult['paymentStatus']) { // phpcs:ignore
            $orderMessage = '';
            if ('AUTH_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
            } elseif ('CAPTURE_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Capture Attempt has been declined';
            } elseif ('VOIDED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' VOID';
            } else {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Payment Failed.';
            }
            
            
        }
        return $errormessage;
    }

    public function orderRefundApi($apikey, $requestbody, $apiOrderId)
    {

        $actualapicall = 'Payment/refund/' . $apiOrderId;
        $finalapiurl = $this->apiUrl() . $actualapicall;

        $apiresult = $this->postWizitapi($finalapiurl, $requestbody, $apikey);
        if (false !== $apiresult && '200' == $apiresult['responseCode']) {
            
            $errormessage = '';

            $responseerror = $this->handleOrderRefundApiError($apiresult, $errormessage);

            if (!empty($responseerror)) {
                
                $apiresult = $responseerror;
            } else {
               
               
            }

        } elseif ('402' == $apiresult['responseCode'] || '412' == $apiresult['responseCode']) {
            $error = true;
            $errormessage = 'Error: ' . $apiresult['errorCode']
            . ' - ' . $apiresult['errorMessage']
            . ' - ' . $apiresult['paymentDescription'];
            
            $apiresult = $errormessage;
        } else {
            $error = true;
            $errormessage = 'Error: ' . $apiresult['errorCode'] . ' - ' . $apiresult['errorMessage'];
            
            $apiresult = $errormessage;
        }
        return $apiresult;
    }

    public function handleOrderRefundApiError($apiresult, $errormessage)
    {
        $error = true;
        $apiOrderId = $apiresult['transactionId'];
        if ('APPROVED' != $apiresult['transactionStatus'] && 'COMPLETED' != $apiresult['transactionStatus']) {

            $errormessage = 'Wizit Payment Failed. Wizit Transaction ' . $apiOrderId . ' has been Declined';
            
        }

        if ('AUTH_APPROVED' != $apiresult['paymentStatus'] &&
            'PARTIALLY_CAPTURED' != $apiresult['paymentStatus'] &&
            'CAPTURED' != $apiresult['paymentStatus']) {
            $orderMessage = '';
            if ('AUTH_DECLINED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Payment Failed. Wizit Transaction '
                . $apiOrderId . ' has been Declined';
           
            } elseif ('VOIDED' == $apiresult['paymentStatus']) {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' VOID';
            } else {
                $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Payment Failed.';
            }
            
        }
        return $errormessage;
    }

    public function orderVoidApi($apikey, $wz_txn_id)
    {
        
        $actualapicall = 'Payment/voidtransaction/' . $wz_txn_id;
        $finalapiurl = $this->apiUrl() . $actualapicall;
        
        $apiresult = $this->postWizitapi($finalapiurl, $wz_txn_id, $apikey);
        

        if (false !== $apiresult && '200' == $this->getDataFromJsonObj('responseCode', $apiresult)) { // phpcs:ignore

            $errormessage = '';
            $responseerror = $this->handleOrderVoidedApiError($apiresult, $errormessage);
            if (!empty($responseerror)) {
                
                $apiresult = $responseerror;
            } else {
               
                
            }

        } elseif ('412' == $this->getDataFromJsonObj('responseCode', $apiresult)) { // phpcs:ignore
            $error = true;
            $errormessage = 'Cancel attempt failed because payment has already been captured for this order';
            
            $apiresult = $errormessage;

        } elseif ('402' == $this->getDataFromJsonObj('responseCode', $apiresult)) { // phpcs:ignore
            $error = true;
            $errormessage = 'Error: ' . $this->getDataFromJsonObj('errorCode', $apiresult) . ' - ' . $this->getDataFromJsonObj('errorMessage', $apiresult) . ' - ' . $this->getDataFromJsonObj('paymentDescription', $apiresult); // phpcs:ignore
            
            $apiresult = $errormessage;
        } else {
            $error = true;
            $errormessage = 'Error: ' . $this->getDataFromJsonObj('errorCode', $apiresult) . ' - ' . $this->getDataFromJsonObj('errorMessage', $apiresult); // phpcs:ignore
            
            $apiresult = $errormessage;
        }
        return $apiresult;
    }

    public function handleOrderVoidedApiError($apiresult, $errormessage)
    {
        $error = true;
        $apiOrderId = $apiresult['transactionId'];
        if ('COMPLETED' != $apiresult['transactionStatus'] &&
            'COMPLETED' != $apiresult['transactionStatus']) {

            $errormessage = "Wizit Payment cancel doesn't authorised. Wizit Transaction " . $apiOrderId . '  has been Declined!'; // phpcs:ignore
            
        }

        if ('VOIDED' != $apiresult['paymentStatus'] && 'CAPTURED' != $apiresult['paymentStatus']) {
            $orderMessage = '';
               
            $errormessage = 'Wizit Transaction ' . $apiOrderId . ' Payment Cancel Failed';
            
        }
        return $errormessage;
    }

    private $wizit_info_style_oneline = 'display: block; padding-top: 5px; padding-bottom: 5px;';
    private $wizit_info_style_product_list = '';
    private $wizit_info_style_product_detail = '';
    private $wizit_info_logo_style = 'max-width: 100px; max-height: 30px; padding-top: 5px; border: none !important; vertical-align: bottom; display: inline-block;';
    private $wizit_info_content_style = 'line-height: 35px;';
    



    public function getWizitMessage($type, $price, $assetRepository, $min_price = 0, $max_price = 99999, $product_id = 0){
        $banktransferLogoUrl = $assetRepository->getUrlWithParams('Wizit_Wizit::images/Group.png', []);

               
        // get plugin setting
        $wizit_is_enable = $this->getConfig('payment/wizit/active');
        // get page setting
        $show_on_product_cat_page = $this->getConfig('payment/wizit/website_customisation/payment_info_on_catetory_pages');
        $show_on_product_page = $this->getConfig('payment/wizit/website_customisation/payment_info_on_product_pages');
        $show_on_cat_page = $this->getConfig('payment/wizit/website_customisation/payment_info_on_cart_pages');
        // get limit
        $wizit_minimum_payment_amount = $this->getConfig('payment/wizit/min_max_wizit/wz_min_amount');
        $wizit_maxmum_payment_amount = $this->getConfig('payment/wizit/min_max_wizit/wz_max_amount');

        $wizit_merchant_min_amount =  $this->getConfig('payment/wizit/min_max_wizit/merchant_min_amount');
        $wizit_merchant_max_amount =  $this->getConfig('payment/wizit/min_max_wizit/merchant_max_amount');


        if (empty($wizit_merchant_min_amount) || empty($wizit_merchant_max_amount))
        {

            $wizit_merchant_min_amount = $wizit_minimum_payment_amount;
            $wizit_merchant_max_amount = $wizit_maxmum_payment_amount;
        }


        if(intval($wizit_is_enable, 0) == 1 
           && (
                (floatval($wizit_merchant_min_amount) <= $price && $price <=  floatval($wizit_merchant_max_amount))
                ||
                ( $min_price > 0 && $max_price < 99999
                    && floatval($wizit_merchant_min_amount) <= $min_price && $max_price <= floatval($wizit_merchant_max_amount))
           )){


            $total_amount = '$' . number_format($price, 2, '.', ','); 
            $sub_amount = '$' . number_format($price / 4, 2, '.', ',');


            if($type == 'List' && intval( $show_on_product_cat_page, 0) == 1){
                return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_list .'">
                                    <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /></div>';
            }
            else if($type == 'Detail' && intval( $show_on_product_page, 0) == 1){
                if($min_price > 0 && $max_price < 99999){
                    // display icon only
                    $sub_amount1 = '$' . number_format($min_price / 4, 2, '.', ',');
                    $sub_amount2 = '$' . number_format($max_price / 4, 2, '.', ',');
                    return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_detail .'">
                        <input type="hidden" id="wizit-sub-amount-price-productid" name="wizit-sub-amount-price-productid" value="' . $product_id . '">
                        <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /> 
                        <span style="'. $this->wizit_info_content_style .'">&nbsp;or 4 payments of from <span id="wizit-sub-amount-price">'. $sub_amount1 . ' to ' . $sub_amount2 .
                        '</span> with Wizit <a href="#" class="wizit-learn-more-popup-link">learn more</a><span></div>';
                }else{
                    // display full info
                    return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_detail .'">
                        <input type="hidden" id="wizit-sub-amount-price-productid" name="wizit-sub-amount-price-productid" value="' . $product_id . '">
                        <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /> 
                        <span style="'. $this->wizit_info_content_style .'">&nbsp;or 4 payments of <span id="wizit-sub-amount-price">'. $sub_amount .
                        '</span> with Wizit <a href="#" class="wizit-learn-more-popup-link">learn more</a><span></div>';
                }
                
            }
            else if($type == 'Cart' && intval( $show_on_cat_page, 0) == 1){
                return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_detail .'">
                        <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /> 
                        <span style="'. $this->wizit_info_content_style .'">&nbsp;or 4 payments of <span id="wizit-sub-amount-price">'. $sub_amount .'</span> with Wizit. 
                        <a href="#" class="wizit-learn-more-popup-link">learn more</a><span></div>';
            }            
        }
        else if(intval($wizit_is_enable, 0) == 1 ){
            // out of range
            if($type == 'Detail' && intval( $show_on_product_page, 0) == 1){
                if($min_price > 0 && $max_price < 99999){
                    // display icon only
                    return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_list .'">
                                    <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /></div>';
                }else{
                    // display full info
                    return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_detail .'">
                        <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /> 
                        <span style="'. $this->wizit_info_content_style .'">&nbsp;is available on purchases between '
                        . '$' . number_format(floatval($wizit_merchant_min_amount), 2, '.', ',') .' and ' 
                        . '$' . number_format(floatval($wizit_merchant_max_amount), 2, '.', ',') . 
                        ' <a href="#" class="wizit-learn-more-popup-link">learn more</a><span></div>';
                }
                
            }
            else if($type == 'Cart' && intval( $show_on_cat_page, 0) == 1){
                return '<div style="'. $this->wizit_info_style_oneline . $this->wizit_info_style_product_detail .'">
                        <img style="'. $this->wizit_info_logo_style .'" src="' . $banktransferLogoUrl . '" /> 
                        <span style="'. $this->wizit_info_content_style .'">&nbsp;is available on purchases between '
                        . '$' . number_format(floatval($wizit_merchant_min_amount), 2, '.', ',') .' and ' 
                        . '$' . number_format(floatval($wizit_merchant_max_amount), 2, '.', ',') . 
                        '<a href="#" class="wizit-learn-more-popup-link">learn more</a><span></div>';
            } 
        }

        return '';
    }


    private function getDataFromJsonObj($key, $dataObj){
        $value = null;
        
        if(isset($dataObj) && is_object($dataObj) && property_exists($dataObj, $key)){
            $value = $dataObj->{$key};
        }
        
        return $value;
    }
}
