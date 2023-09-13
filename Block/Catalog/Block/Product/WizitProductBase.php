<?php
 
namespace Wizit\Wizit\Block\Catalog\Block\Product;
 


class WizitProductBase
{


    public $wizit_helper;
    public $assetRepository;
    

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
        $min_price = 0;
        $max_price = 9999;
        
        
        // Simple Product
        $price = $product->getPriceInfo()->getPrice('regular_price')->getValue();


        // Configurable product
        if ($product->getTypeId() == 'configurable') {
            $basePrice = $product->getPriceInfo()->getPrice('regular_price');              
            $price = $basePrice->getMinRegularAmount()->getValue();
        }

        // Bundle product
        if ($product->getTypeId() == 'bundle') {
            $price = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
            $min_price = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue(); 
            $max_price = $product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();             
        }


        // Group product
        if ($product->getTypeId() == 'grouped') {
            $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);            
            foreach ($usedProds as $child) {
                if ($child->getId() != $product->getId()) {
                        $price += $child->getPrice();
                }
            }
        }

        
        return $this->wizit_helper->getWizitMessage('List', $price, $this->assetRepository, $min_price, $max_price);    

    }
}