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

    private $reg_pricing_code = 'regular_price';
    private $final_pricing_code = 'final_price';
    private $product_type_conf = 'configurable';
    private $product_type_bund = 'bundle';
    private $product_type_gro = 'grouped';
    

    public function aroundGetProductDetailsHtml(
        $subject,
        $proceed,
        \Magento\Catalog\Model\Product 	$selectedProd
    )
    {        
        $p_max_price = 9999; $p_min_price = 0;
        // Get price if Simple Product
        $price = $selectedProd->getPriceInfo()->getPrice($this->reg_pricing_code)->getValue();

        // Get price if Configurable product
        if ($selectedProd->getTypeId() == $this->product_type_conf) {
            $regular_price = $selectedProd->getPriceInfo()->getPrice($this->reg_pricing_code);              
            $price = $regular_price->getMinRegularAmount()->getValue();
        }

        // Get price if Bundle product
        if ($selectedProd->getTypeId() == $this->product_type_bund) {
            $price = $selectedProd->getPriceInfo()->getPrice($this->reg_pricing_code)->getMinimalPrice()->getValue();
            $p_min_price = $selectedProd->getPriceInfo()->getPrice($this->final_pricing_code)->getMinimalPrice()->getValue(); 
            $p_max_price = $selectedProd->getPriceInfo()->getPrice($this->final_pricing_code)->getMaximalPrice()->getValue();             
        }

        // Get price if Group product
        if ($selectedProd->getTypeId() == $this->product_type_gro) {
            $group_prods = $selectedProd->getTypeInstance(true)->getAssociatedProducts($selectedProd);            
            foreach ($group_prods as $p) {
                if ($p->getId() != $selectedProd->getId()) {
                        $price += $p->getPrice();
                }
            }
        }

        
        return $this->wizit_helper->getWizitMessage('List', $price, $this->assetRepository, $p_min_price, $p_max_price);    

    }
}