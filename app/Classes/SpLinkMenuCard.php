<?php


namespace App\Classes;

use App\link;
use App\Classes\Constants;



class SpLinkMenuCard
{
//    const DYNAMIC_ADDRESS = 'http://localhost:8080/target/';
//    const STATIC_ADDRESS = 'http://localhost/spaces/';

    public $content = array();
    private $titleOut='';
    private $orientOut='';

    function __construct($thisCardId, $orgId, $publishableLayouts, $thisCardContent, $cardSubElementProperties, $layoutId )
    {
        $thisLink = new Link();
        $cardLinks = array();
        $linksForThisCard = $thisLink->getLinksForCardId($thisCardId);
        $thisConstants = new Constants;
        foreach($linksForThisCard as $thisCardLink){
            $linkIsPublishable = false;
            foreach($publishableLayouts as $thisPublishableLayout){
                if($thisPublishableLayout->layout_id == $thisCardLink->layout_link_to){
                    $linkIsPublishable = true;
                    break;
                }
            }
            if($linkIsPublishable){
//                $newLink = self::STATIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to;
                $newLink = $thisConstants->Options['staticAddress'].$orgId.'/'.$thisCardLink->layout_link_to;

            }else{
//                $newLink = self::DYNAMIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to;
                $newLink = $thisConstants->Options['dynamicAddress'].'/'.$thisCardLink->layout_link_to;
            }
            $fullLink = array($newLink, $thisCardLink->description);
            array_push($cardLinks, $fullLink);


        }
        if(isset($thisCardContent['linkMenuTitle'])){
            $this->titleOut=$thisCardContent['linkMenuTitle'];
        }else{
            $this->titleOut='';
        }
        if(isset($thisCardContent['orient'])){
            $this->orientOut = $thisCardContent['orient'];
        }else{
            $this->orientOut = 'vertical';
        }
/*
        $subProperties = $cardSubElementProperties[$thisCardId]['sub'];
        $subCss = '';
        foreach($subProperties as $thisSubProperty){
            $subCss = $subCss.$thisSubProperty[1];
        }
*/
        $searchLink = $thisConstants->Options['dynamicAddress'].$orgId.'/'.$layoutId;
        $this->content = array('links'=>$cardLinks, 'title'=>$this->titleOut, 'orient'=>$this->orientOut, 'searchLink'=>$searchLink);


    }
    public function getCardContent(){
        return $this->content;
    }

}
