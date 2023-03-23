<?php
/**
 *
 * @package     magento2
 * @author
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link
 */

namespace Wizit\Wizit\Controller\Index;

class Failed implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const CHECKOUT_STATUS_CANCELLED = "CANCELLED";
    const CHECKOUT_STATUS_SUCCESS = "SUCCESS";

    private $request;
    private $session;
    private $redirectFactory;
    private $messageManager;
    private $placeOrderProcessor;
    private $cartManagement;
    private $logger;
    private $quoteFactory;
    private $paymentDataObjectFactory;
    private $wizit_data_helper;
    private $order;
    private $checkoutHelper;
    private $invoiceSender;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Wizit\Wizit\Model\Payment\Capture\PlaceOrderProcessor $placeOrderProcessor,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Wizit\Wizit\Helper\Data $wizit_helper,
        \Magento\Sales\Model\Order $order,
        \Wizit\Wizit\Helper\Checkout $checkout,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->placeOrderProcessor = $placeOrderProcessor;
        $this->cartManagement = $cartManagement;
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->wizit_data_helper = $wizit_helper;
        $this->order = $order;
        $this->checkoutHelper = $checkout;
        $this->invoiceSender = $invoiceSender;
    }

    public function execute()
    {
        $this->logger->info("-------------------->>>>>>>>>>>>>>>>>>WIZIT Failed CALL BACK START<<<<<<<<<<<<<<<<<<<<-------------------");
        
        $callback_request_quote_id = $this->request->getParam("quoteId");
        $callback_request_mref = $this->request->getParam("mref");
        $this->logger->info("callback_request_quote_id->" . $callback_request_quote_id);
        $this->logger->info("callback_request_mref->" . $callback_request_mref);
        

        $this->logger->info("-------------------->>>>>>>>>>>>>>>>>>WIZIT Failed CALL BACK END<<<<<<<<<<<<<<<<<<<<-------------------");


        $failed_url = $this->wizit_data_helper->getConfig(
            "payment/wizit/failed_url"
        );


        $this->messageManager->addSuccessMessage(
            (string) __("was rejected by Wizit.")
        );

        if (!empty($failed_url)){
            return $this->redirectFactory
                ->create()
                ->setPath($failed_url);
        }else{
            return $this->redirectFactory
                ->create()
                ->setPath("checkout/cart");
        }

        
    }

}
