<?php
/**
 *
 * @package     magento2
 * @author
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link
 */

 namespace Wizit\Wizit\Controller\Index;


class Index implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const CHECKOUT_STATUS_CANCELLED = 'CANCELLED';
    const CHECKOUT_STATUS_SUCCESS = 'SUCCESS';

    private $request;
    private $session;
    private $redirectFactory;
    private $messageManager;
    private $placeOrderProcessor;
    private $validateCheckoutDataCommand;
    private $logger;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Wizit\Wizit\Model\Payment\Capture\PlaceOrderProcessor $placeOrderProcessor,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->placeOrderProcessor = $placeOrderProcessor;
        $this->logger = $logger;
    }

    public function execute()
    {
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_CANCELLED) {
            $this->messageManager->addErrorMessage(
                (string)__('You have cancelled your Wizit payment. Please select an alternative payment method.')
            );
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if ($this->request->getParam('status') != self::CHECKOUT_STATUS_SUCCESS) {
            $this->messageManager->addErrorMessage(
                (string)__('Wizit payment is failed. Please select an alternative payment method.')
            );
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        try {
            $quote = $this->session->getQuote();
            $wizitOrderToken = 'orderToken';//$this->request->getParam('orderToken');
            
            // go to wizit to process order
            $wizit_payment_url = $this->placeOrderProcessor->execute($quote, $wizitOrderToken);   

            return $this->redirectFactory->create()->setPath($wizit_payment_url);
        } catch (\Throwable $e) {
            $this->logger->info("-------------->>>>>>>>>>>>>>>>Wizit index error<<<<<<<<<<<<<<--------------");
            $this->logger->info($e->getMessage());
            $this->logger->info("-------------->>>>>>>>>>>>>>>>Wizit index error<<<<<<<<<<<<<<--------------");
            $errorMessage = (string)__('Payment is failed');
            $this->messageManager->addErrorMessage($errorMessage);
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        $this->messageManager->addSuccessMessage((string)__('Wizit Transaction Completed'));
        return $this->redirectFactory->create()->setPath('checkout/onepage/success');
    }
}
