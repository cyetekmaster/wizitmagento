<?php
/**
 *
 * @package     magento2
 * @author
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link
 */

namespace Wizit\Wizit\Controller\Index;



use Magento\Sales\Model\Order;
use \Wizit\Wizit\Helper\Data;
use \Wizit\Wizit\Helper\Checkout;
use \Magento\Checkout\Model\Session;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\ResponseInterface;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\CatalogInventory\Api\StockRegistryInterface;
use \Magento\CatalogInventory\Api\Data\StockItemInterface;


class WebhookComfirmUrl extends Success
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        StockRegistryInterface $stockRegistry,
        //\Magento\Paypal\Model\Adminhtml\ExpressFactory $authorisationFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Wizit\Wizit\Helper\Data $helper,
        \Wizit\Wizit\Helper\Checkout $checkoutHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession
    ) {
       
        
        parent::__construct($context, $resultPageFactory, $resultRedirectFactory, $orderRepository, $invoiceService, $transaction, $stockRegistry, $invoiceSender, $helper, $checkoutHelper,
    $checkoutSession, $orderFactory,  $logger, $customerSession);

        $this->callback_source = "WebhookComfirmUrl CALL BACK";
    }

}