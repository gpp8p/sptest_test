<?php


namespace App\Classes;


use App\link;
use Storage;
use File;
use App\Classes\Constants;

class SpRichTextCard
{
    const DYNAMIC_ADDRESS = 'http://localhost:8080/target/';
    const STATIC_ADDRESS = 'http://localhost/spaces/';

    public $content;
    var $contentIn='';
    function __construct($thisCardId, $orgId, $publishableLayouts, $thisCardContent ){


        $orgDirectory = '/images/'.$orgId;
        $thisLink = new link();
        $thisConstants = new Constants;
        $cardLinks = $thisLink->getLinksForCardId($thisCardId);
        if(isset($thisCardContent['cardText'])){
            $this->contentIn = $thisCardContent['cardText'];
            foreach($cardLinks as $thisCardLink){
                if($thisCardLink->type=="U"){
                    $linkIsPublishable = false;
                    foreach($publishableLayouts as $thisPublishableLayout){
                        if($thisPublishableLayout->layout_id == $thisCardLink->layout_link_to){
                            $linkIsPublishable = true;
                            break;
                        }
                    }
                    if($linkIsPublishable){
//                        $newLink = self::STATIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to;
                        $newLink = $thisConstants->Options['staticAddress'].$orgId.'/'.$thisCardLink->layout_link_to;

                    }else{
                        $newLink = $thisConstants->Options['dynamicAddress'].$orgId.'/'.$thisCardLink->layout_link_to;
                    }
                    $this->contentIn = str_replace($thisCardLink->link_url, $newLink, $this->contentIn);
                }else if($thisCardLink->type=="I"){
                    $imageLink = $thisCardLink->link_url;
                    $imageFileNameAt = strpos($imageLink, 'images/'.$orgId.'/');
                    if($imageFileNameAt!=false){
                        $imageFileNameAt = strlen('http://localhost:8000/images/'.$orgId.'/');
                        $imageFileName = substr($imageLink, $imageFileNameAt);
                        $imageSource = $orgDirectory.'/'.$imageFileName;
                        $copyToLocation = '/published/'.$orgId.'/images'.'/'.$imageFileName;
                        Storage::copy($imageSource, $copyToLocation);
                        $newLink = $thisConstants->Options['staticAddress'].$orgId.'/images/'.$imageFileName;
                        $oldLink = $thisConstants->Options['newImageLink'].$orgId.'/'.$imageFileName;
                        $this->contentIn = str_replace($oldLink, $newLink, $this->contentIn);


                    }
                }
            }

        }else{
            $content='';
        }
        $this->content = $this->contentIn;
    }
    public function getCardContent(){
        return array('cardText'=>$this->content);
    }


}
