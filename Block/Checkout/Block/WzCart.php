<?php
 
namespace Wizit\Wizit\Block\Checkout\Block;
 
use \Wizit\Wizit\Helper\Data;
use \Magento\Framework\View\Asset\Repository;

class WzCart
{
    private $wizit_helper;
    private $assetRepository;


    public function __construct(
        \Wizit\Wizit\Helper\Data $helper,
        \Magento\Framework\View\Asset\Repository $assetRepository
    ){
        $this->wizit_helper = $helper;
        $this->assetRepository = $assetRepository;
    }



    private $displayBlocks = ['checkout.cart.methods.bottom'];



    public function afterToHtml(
        \Magento\Checkout\Block\Cart $subject,
        $html
    ){

        if (in_array($subject->getNameInLayout(), $this->displayBlocks)){
           
            $grand_total = $subject->getQuote()->getGrandTotal();     
                             
            return  $html . $this->wizit_helper->getWizitMessage('Cart', $grand_total, $this->assetRepository); 
        }else{
            return  $html;
        }
        
    }


}