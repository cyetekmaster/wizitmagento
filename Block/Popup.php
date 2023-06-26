<?php
namespace Wizit\Wizit\Block;

//use Magento\Framework\View\Asset\Repository;

class Popup extends \Magento\Framework\View\Element\Template
{

    public $assetRepos;
    public $helperImageFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory
    ) {
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        parent::__construct($context, []);
    }


    public function getContent() 
    {
        return 'test popup';
    }


    public function getPopupImageUrl(){
        return $this->assetRepos->getUrlWithParams('Wizit_Wizit::images/wizit_popup.png', []);
    }

}