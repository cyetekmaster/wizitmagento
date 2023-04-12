<?php
namespace Wizit\Wizit\Block;

//use Magento\Framework\View\Asset\Repository;

class Popup extends \Magento\Framework\View\Element\Template
{

    // private $assetRepository;

    // public function __construct(
    //     Repository $assetRepository
    // ) {
    //     $this->assetRepository = $assetRepository;
    // }


    public function getContent() : string
    {

        //$banktransferLogoUrl = $this->assetRepository->getUrlWithParams('Wizit_Wizit::images/wizit_popup.png', []);


        return 'test popup';
    }
}