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
        
        parent::__construct(
            $request,
            $session,
            $redirectFactory,
            $messageManager,
            $placeOrderProcessor,
            $logger,
            $quoteFactory,
            $cartManagement,
            $wizit_helper,
            $order,
            $checkout,
            $invoiceSender,
            $quoteRepository,
            $productRepository,
            $customerRepository
        );
        
        $this->callback_source = "WebhookComfirmUrl CALL BACK";
    }

}
