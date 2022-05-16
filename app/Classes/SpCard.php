<?php


namespace App\Classes;

use App\Classes\Constants;
use App\Classes\SpLinkMenuCard;
use App\Classes\SpRichTextCard;
use App\Classes\SpHeadlineCard;
use Storage;
use App\Org;
class SpCard
{
    public $thisCardCss = "";
    public $thisCardProperties = "";
    public $thisCardContent = array();
    public $thisCardCol = 0;
    public $thisCardRow = 0;
    public $thisCardHeight = 0;
    public $thisCardWidth = 0;
    public $thisCardId = 0;
    public $thisCardPosition;
    public $thisCardComponent;
    private $orgId;

//    const DYNAMIC_ADDRESS = 'http://localhost:8080/target/';

    function __construct($thisCardArray, $orgId, $publishableLayouts, $cardSubElementProperties, $layoutId){
        $this->thisCardCss = '';
        $this->thisCardProperties = '';
        $thisCardContent = array();
        $thisConstants = new Constants;
        foreach ($thisCardArray as $thisCard){
            if ($thisCard[2] == 1) {
                $this->thisCardCss = $this->thisCardCss . $thisCard[1];
            } else {
                $this->thisCardProperties = $this->thisCardProperties . $thisCard[1];
                $thisCardContent[$thisCard[0]] = $thisCard[1];
            }
            $thisCardIsCss = $thisCard[2];
            $thisCardParameterKey = $thisCard[0];
            $this->thisCardComponent = $thisCard[3];

            $this->thisCardCol = $thisCard[4];
            $this->thisCardRow = $thisCard[5];
            $this->thisCardHeight = $thisCard[6];
            $this->thisCardWidth = $thisCard[7];
            $this->thisCardId = $thisCard[8];
        }
        $thisCardPosition = array($this->thisCardRow, $this->thisCardCol, $this->thisCardHeight, $this->thisCardWidth);
        $this->orgId = $orgId;
        switch($this->thisCardComponent){
            case "RichText":{
                $thisSpRichTextCard = new SpRichTextCard($this->thisCardId, $orgId, $publishableLayouts, $thisCardContent );
                $this->thisCardContent = $thisSpRichTextCard->getCardContent();
                break;
            }
            case "Headline":{
                $thisSpHeadlineCard = new SpHeadlineCard($this->thisCardId, $orgId, $publishableLayouts, $thisCardContent, $cardSubElementProperties);
                $this->thisCardContent = $thisSpHeadlineCard->getCardContent();
                break;
            }
            case "linkMenu":{
                $thisSpLinkMenuCard = new SpLinkMenuCard($this->thisCardId, $orgId, $publishableLayouts, $thisCardContent, $cardSubElementProperties, $layoutId->layout_id);
                $this->thisCardContent = $thisSpLinkMenuCard->getCardContent();
                break;
            }
            case "NavigationMenu":{
                $thisSpLinkMenuCard = new SpLinkMenuCard($this->thisCardId, $orgId, $publishableLayouts, $thisCardContent, $cardSubElementProperties, $layoutId->layout_id);
                $this->thisCardContent = $thisSpLinkMenuCard->getCardContent();
                break;
            }

            case "loginLink":{
                $thisOrg = new Org();
                $orgHome = $thisOrg->getOrgHomeFromOrgId($orgId);
//                $this->thisCardContent = "<a href='".self::DYNAMIC_ADDRESS.$orgId."/".$orgHome[0]->top_layout_id."'>Please Log In</a>";
                $this->thisCardContent = "<a href='".$thisConstants->Options['dynamicAddress'].$orgId."/".$orgHome[0]->top_layout_id."'>Please Log In</a>";
                break;
            }
            case "youTube":{
                $ytubeUrl = $thisCardContent['ytubeUrl'];
                $ytElements = explode(" ", $ytubeUrl);
//                $ytubeUrl = str_replace('watch?v=', 'embed/', $ytubeUrl);
                $spanHeight = intval($thisCardContent['spanHeight']*1.12);
                $spanWidth = intval($thisCardContent['spanWidth']*1.04);
                $elCount = 0;
                $editedYt = '';
                foreach($ytElements as $thisYtElement){
                    if(str_contains($thisYtElement, 'width=')){
                        $ytElements[$elCount]= 'width="'.$spanWidth.'"';
                    }
                    if(str_contains($thisYtElement, 'height=')){
                        $ytElements[$elCount]= 'height="'.$spanHeight.'"';
                    }
                    $editedYt = $editedYt.$ytElements[$elCount]." ";
                    $elCount++;
                }

                $this->thisCardContent=$editedYt;
//                $this->thisCardContent = "<iframe src='".$ytubeUrl."' width='".$spanWidth."' height='".$spanHeight."' ></iframe>";
                break;
            }
            case 'pdf':{
                $pdfFileLocation = $thisCardContent['fileLocation'];
                $pieces = explode("/", $pdfFileLocation);
                $elCount = count($pieces);
                $pdfFileName = $pieces[$elCount-1];
                try {
                    $contents = Storage::get($pdfFileLocation);
                } catch (\Exception $e) {
                    abort(500, 'pdf source file not found');
                }
                $copyToLocation = '/published/'.$orgId.'/'.$pdfFileName;
                Storage::copy($pdfFileLocation, $copyToLocation);
                $this->thisCardContent = "<iframe src='".$pdfFileName."' width='100%' height='100%' ></iframe>";
                break;
            }
        }

    }
    public function getCssGridParams(){
        return $this->computeGridCss($this->thisCardRow, $this->thisCardCol, $this->thisCardHeight, $this->thisCardWidth) . ";";
    }
    private function getCardParameters(){
        return array(
            'style' => $this->getCssGridParams() . $this->thisCardCss,
            'properties' => $this->thisCardProperties,
            'content' => $this->thisCardContent
        );
    }
    private function getCardPosition(){
        return array($this->thisCardRow, $this->thisCardCol, $this->thisCardHeight, $this->thisCardWidth);
    }
    public function getCardData(){
        return array(
            'id' => $this->thisCardId,
            'card_component' => $this->thisCardComponent,
            'card_parameters' => $this->getCardParameters(),
            'card_position' => $this->getCardPosition()
        );
    }
    private function computeGridCss($row, $col, $height, $width){
        $startRow = $row;
        $startColumn = $col;
        $endRow=0;
        $endCol = 0;

        if($height==1){
            $endRow = $row;
        }else{
            $endRow = $row+$height;
        }
        $endCol=$startColumn+$width;
        $thisCss = "grid-area:".$startRow."/".$startColumn."/".$endRow."/".$endCol;
        return $thisCss;

    }
}
