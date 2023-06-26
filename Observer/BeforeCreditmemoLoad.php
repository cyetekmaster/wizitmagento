<?php
namespace Wizit\Wizit\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use \Wizit\Wizit\Helper\Data;

class BeforeCreditmemoLoad implements ObserverInterface
{
    protected $_helper;
    protected $_layout;
    protected $_registry;

    public function __construct(
        \Wizit\Wizit\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Registry $registry
    ) {
        $this->_helper = $helper;
        $this->_layout = $layout;
        $this->_registry = $registry;
    }

    public function execute(Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $layout = $block->getLayout();

        $creditmemo = $this->_registry->registry('current_creditmemo');

        if ($creditmemo) {

            $order = $creditmemo->getOrder();
            $payment = $order->getPayment();

            if ($payment->getMethod() == 'wizit') {

                $block->unsetChild('submit_offline');
            }
        }
    }
}
