<?php
 
namespace Wizit\Wizit\Block\Catalog\Block\Product;
 
use \Wizit\Wizit\Helper\Data;
use \Magento\Framework\View\Asset\Repository;

class WizitView
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



    private $displayBlocks = ['product.info.addtocart', 'customize.button', 'product.info.addtocart.additional'];



    public function afterToHtml(
        \Magento\Catalog\Block\Product\View $subject,
        $html
    ){

        if (in_array($subject->getNameInLayout(), $this->displayBlocks)){
            
            // get product from current session
            $product = $subject->getProduct();            

            // clear wizit info content
            $wizit_info = '';
            if(isset($product)){

                $product_price = $product->getPrice();
                $product_min_price = 0;
                $product_max_price = 9999;
                
                
                // Simple Product
                $product_price = $product->getPriceInfo()->getPrice('regular_price')->getValue();


                // Configurable product
                if ($product->getTypeId() == 'configurable') {
                    $basePrice = $product->getPriceInfo()->getPrice('regular_price');              
                    $product_price = $basePrice->getMinRegularAmount()->getValue();
                }

                // Bundle product
                if ($product->getTypeId() == 'bundle') {
                    $product_price = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
                    $product_min_price = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue(); 
                    $product_max_price = $product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();             
                }


                // Group product
                if ($product->getTypeId() == 'grouped') {
                    $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);            
                    foreach ($usedProds as $child) {
                        if ($child->getId() != $product->getId()) {
                                $product_price += $child->getPrice();
                        }
                    }
                }


                $wizit_info = $this->wizit_helper->getWizitMessage('Detail',  $product_price, $this->assetRepository, $product_min_price, $product_max_price, $product->getId());
            }
                  
            return  $html . $wizit_info; 
        }else{
            return  $html;
        }
        
    }


}