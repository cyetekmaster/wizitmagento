<?php
/**
 *
 * @package     magento2
 * @author
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link
 */

 namespace Wizit\Wizit\Controller\Index;

class WebhookComfirmUrl extends Success
{
    
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
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
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
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->callback_source = "WebhookComfirmUrl CALL BACK";
    }

}
