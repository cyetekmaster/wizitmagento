<?php
 
namespace Wizit\Wizit\Block\Catalog\Block\Product;
 

class WizitAbstractProduct  extends WizitProductBase
{
    public function __construct(
        \Wizit\Wizit\Helper\Data $helper,
        \Magento\Framework\View\Asset\Repository $assetRepository         
     ){
         parent::__construct($helper, $assetRepository);
     }


}