<?php
 
namespace Wizit\Wizit\Block\Catalog\Block\Product;
 
use \Wizit\Wizit\Helper\Data;
use Magento\Framework\View\Asset\Repository;

class WizitView
{
    private $wizit_helper;
    private $assetRepository;


    public function __construct(
         Data $helper,
         Repository $assetRepository
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
            $product = $subject->getProduct();

            

            $wizit_info = '';
            if(isset($product)){

                $price = $product->getPrice();

                $min_price =  $product->getMinPrice();
                $max_price = $product->getMaxPrice();


                $wizit_info = $this->wizit_helper->getWizitMessage('Detail',  $price, $this->assetRepository, $min_price, $max_price);
            }
                  
            return  $html . $wizit_info; 
        }else{
            return  $html;
        }
        
    }


}