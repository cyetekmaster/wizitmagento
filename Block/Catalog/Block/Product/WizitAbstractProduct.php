<?php
 
namespace Wizit\Wizit\Block\Catalog\Block\Product;
 
use \Wizit\Wizit\Helper\Data;
use Magento\Framework\View\Asset\Repository;

class WizitAbstractProduct
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

    private $displayBlocks = ['product.info.addtocart'];


    public function aroundGetProductDetailsHtml(
        $subject,
        $proceed,
        \Magento\Catalog\Model\Product 	$product)
    {         

        $price = $product->getPrice();
        $min_price =  $product->getMinPrice();
        $max_price = $product->getMaxPrice();

       return $this->wizit_helper->getWizitMessage('List', $price, $this->assetRepository, $min_price, $max_price);        
    }


}