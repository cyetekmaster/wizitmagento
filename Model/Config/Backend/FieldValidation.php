<?php
namespace Wizpay\Wizpay\Model\Config\Backend;

use \Wizpay\Wizpay\Helper\Data;
use Magento\Framework\App\RequestInterface;

class FieldValidation extends \Magento\Framework\App\Config\Value
{

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    protected $_configValueFactory;
    protected $_storeManager;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param RequestInterface $request
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RequestInterface $request,
        Data $helper,
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->resourceConfig = $resourceConfig;
        $this->dir = $dir;
        $this->_configInterface = $configInterface;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $error = false;
        //$label = $this->getData('field_config/label');
        //$get_api_key = $this->helper->getConfig('payment/wizpay/api_key');
        $postData = $this->request->getPost();
        $allpostdata = (array) $postData;

        $is_plugin_enable = true;
        if(array_key_exists('value', $allpostdata['groups']['wizpay']['fields']['active'])){
            // check if turn off wizpay then do nothing 
            $is_plugin_enable = intval($allpostdata['groups']['wizpay']['fields']['active']['value']) == 1 ? true : false;
        }
        
        if($is_plugin_enable == false){
            return;
        }


        // keep working if wizpay turn on.
        // print_r($allpostdata);
        $getallpostdata = $allpostdata['groups']['wizpay']['groups']['min_max_wizpay']['fields'];

        $mmin = 0;
        $mmax = 0;
        
        if (isset($getallpostdata['merchant_min_amount']['value'])) {
            $mmin = trim($getallpostdata['merchant_min_amount']['value']);
        }
        if (isset($getallpostdata['merchant_max_amount']['value'])) {
            $mmax = trim($getallpostdata['merchant_max_amount']['value']);
        }

        

        $environment = intval($allpostdata['groups']['wizpay']['fields']['environment']['value']);
        $get_api_key = '';
        
        if($environment == 1){
            // set api key to sandbox key
            $get_api_key = $allpostdata['groups']['wizpay']['fields']['api_key_sandbox']['value'];
        }else{
            $get_api_key = $allpostdata['groups']['wizpay']['fields']['api_key']['value'];
        }

        $wzresponse = $this->helper->callLimitapi($get_api_key, $environment);

        if (!is_array($wzresponse)) {

            $error = true;
            throw new \Magento\Framework\Exception\ValidatorException(__($wzresponse));
            // return false;
        } else {

            $wmin = $wzresponse['minimumAmount'];
            $wmax = $wzresponse['maximumAmount'];

            if (!empty($mmin) && !empty($mmax)) {
                
                if ($mmin < $wmin) {
                    $error = true;
                    throw new \Magento\Framework\Exception\ValidatorException(__('Error: Merchant Minimum Payment Amount can not be less than Wizpay Minimum Payment Amount.')); // phpcs:ignore
                }
                
                if ($mmax > $wmax) {
                    $error = true;
                    throw new \Magento\Framework\Exception\ValidatorException(__('Error: Merchant Maximum Payment Amount can not be more than Wizpay Maximum Payment Amount.')); // phpcs:ignore
                }
                
                if ($mmax < $mmin) {
                    $error = true;
                    throw new \Magento\Framework\Exception\ValidatorException(__('Error: Merchant Maximum Payment Amount can not be less than Merchant Minimum Payment Amount.')); // phpcs:ignore
                }
                
            } else {

                $mmin = $wmin;
                $mmax = $wmax;
            }

            if ($error) {
                return false;
            }

            //payment/wizpay/min_max_wizpay/merchant_min_amount
            // /payment/wizpay/min_max_wizpay/merchant_max_amount

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $objectManager->get('\Magento\Framework\App\ProductMetadataInterface');
            $getMagentoVer = $productMetadata->getVersion();
            
            if (version_compare($getMagentoVer, '2.1.0', '<')) {

                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/wz_min_amount',
                    $wmin,
                    'default',
                    0
                );

                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/wz_max_amount',
                    $wmax,
                    'default',
                    0
                );

                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/merchant_max_amount',
                    $mmin,
                    'default',
                    0
                );

                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/merchant_min_amount',
                    $mmax,
                    'default',
                    0
                );
                
                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/merchant_min_amounts',
                    $mmin,
                    'default',
                    0
                );
            
            } else {

                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/wz_min_amount',
                    $wmin,
                    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                    0
                );
                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/wz_max_amount',
                    $wmax,
                    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                    0
                );
                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/merchant_max_amount',
                    $mmax,
                    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                    0
                );
                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/merchant_min_amount',
                    $mmin,
                    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                    0
                );
                $this->resourceConfig->saveConfig(
                    'payment/wizpay/min_max_wizpay/merchant_min_amounts',
                    $mmin,
                    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                    0
                );
            }
        }

        $isEnableProduct = true;
        if(array_key_exists('value', $allpostdata['groups']['wizpay']['groups']['website_customisation']['fields']['payment_info_on_product_pages'])){
            $isEnableProduct = intval($allpostdata['groups']['wizpay']['groups']['website_customisation']['fields']['payment_info_on_product_pages']['value']) == 1 ? true : false;
        }
        $isEnableCategory = false;
        if(array_key_exists('value', $allpostdata['groups']['wizpay']['groups']['website_customisation']['fields']['payment_info_on_catetory_pages'])){
            $isEnableCategory = intval($allpostdata['groups']['wizpay']['groups']['website_customisation']['fields']['payment_info_on_catetory_pages']['value']) == 1 ? true : false;
        }
        $isEnableCart = false;
        if(array_key_exists('value', $allpostdata['groups']['wizpay']['groups']['website_customisation']['fields']['payment_info_on_cart_pages'])){
            $isEnableCart = intval($allpostdata['groups']['wizpay']['groups']['website_customisation']['fields']['payment_info_on_cart_pages']['value']) == 1 ? true : false;
        }

        // build data
        $plugin_config_api_data = [
            'merchantUrl' => $this->_storeManager->getStore()->getBaseUrl() ,
            'maxMerchantLimit' =>  $mmax,
            'minMerchantLimit' => $mmin,
            'isEnable' =>  $is_plugin_enable,
            'isEnableProduct' =>  $isEnableProduct,
            'isEnableCategory' => $isEnableCategory,
            'isEnableCart' => $isEnableCart,
            'isInstalled' => true,
            'pluginversion' => $this->helper->getPluginVersion(),
            'platformversion' => $getMagentoVer,
            'apikey' => $get_api_key,
            'platform' => 'Magento'
        ];

        $plugin_config_api_response  = $this->helper->callConfigurMerchantPlugin($get_api_key,$environment, $plugin_config_api_data);
    }
}
