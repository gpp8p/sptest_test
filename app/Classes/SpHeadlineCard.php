<?php

namespace App\Classes;

use App\link;
use App\Classes\Constants;

class SpHeadlineCard
{
//    const DYNAMIC_ADDRESS = 'http://localhost:8080/target/';
//    const STATIC_ADDRESS = 'http://localhost/spaces/';

    public $content = array();
    private $titleOut='';
    private $orientOut='';

    function __construct($thisCardId, $orgId, $publishableLayouts, $thisCardContent, $cardSubElementProperties ){
        $thisLink = new Link();
        $cardLinks = array();
        $thisConstants = new Constants;
        $linksForThisCard = $thisLink->getLinksForCardId($thisCardId);
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
//                $newLink = $thisConstants->Options['staticAddress'].'/'.$thisCardLink->layout_link_to;
                $newLink = $thisConstants->Options['staticAddress'].$orgId.'/'.$thisCardLink->layout_link_to;

            }else{
//                $newLink = self::DYNAMIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to;
                $newLink = $thisConstants->Options['dynamicAddress'].'/'.$thisCardLink->layout_link_to;
            }
            $fullLink = array($newLink, $thisCardLink->description);
            array_push($cardLinks, $fullLink);
        }
        if(isset($thisCardContent['linkMenuTitle'])) {
            $this->titleOut = $thisCardContent['linkMenuTitle'];
        }elseif(isset($thisCardContent['title'])){
            $this->thisCardContent = $thisCardContent['title'];
        } else {
            $this->titleOut='';
        }
        $this->orientOut = "horozontal";
        $this->content = array('links'=>$cardLinks, 'title'=>$this->titleOut, 'orient'=>$this->orientOut);


    }
    public function getCardContent(){
        return $this->content;
    }

}
