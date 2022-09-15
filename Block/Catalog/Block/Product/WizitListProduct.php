<?php
 
namespace Wizit\Wizit\Block\Catalog\Block\Product;
 


class WizitListProduct
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



    public function aroundGetProductDetailsHtml(
        $subject,
        $proceed,
        \Magento\Catalog\Model\Product 	$product
    )
    {

        $price = $product->getPrice();
        $min_price =  $product->getMinPrice();
        $max_price = $product->getMaxPrice();

        
        return $this->wizit_helper->getWizitMessage('List', $price, $this->assetRepository, $min_price, $max_price);    

    }
}