<?php

namespace App\Http\Controllers;

use App\Classes\Constants;
use Illuminate\Http\Request;
use App\CardInstances;
use App\InstanceParams;
use App\layout;
use App\link;
use App\Org;
use App\Solr;
use Illuminate\Support\Facades\DB;
use Exception;
use Storage;
use File;


class cardInstanceController extends Controller
{
    public function getLayoutCardInstances(Request $request){
        $inData =  $request->all();
        $thisLayoutName = $inData['layout_name'];
        $thisCardInstance = new CardInstances;
        $thisLayoutCardInstances = $thisCardInstance->getLayoutCardInstances($thisLayoutName);
        $thisCardInstanceId = $thisLayoutCardInstances[0]->id;
        $allCardInstances = array();
        $thisCardInstanceParameter = array();
        $thisCardInstanceText = "";
        $thisCardInstanceComponent = $thisLayoutCardInstances[0]->card_component;
        $lastCard = count($thisLayoutCardInstances)-1;
        $instancesAdded = 0;
        for($i =0; $i< count($thisLayoutCardInstances); $i++){
            if($thisLayoutCardInstances[$i]->id != $thisCardInstanceId){
                $newCardInstance = array('id'=>$thisCardInstanceId, 'card_component'=>$thisCardInstanceComponent, 'card_parameters'=>$thisCardInstanceParameter);
                $thisCardInstanceParameter = array();
                $thisCardInstanceText = "";
                $thisCardInstanceComponent = $thisLayoutCardInstances[$i]->card_component;
                array_push($allCardInstances, $newCardInstance);
                $instancesAdded++;
                $thisCardInstanceId = $thisLayoutCardInstances[$i]->id;
                $thisCardInstanceParameter[$thisLayoutCardInstances[$i]->parameter_key]=$thisLayoutCardInstances[$i]->parameter_value;
            }else{
                $thisCardInstanceParameter[$thisLayoutCardInstances[$i]->parameter_key]=$thisLayoutCardInstances[$i]->parameter_value;
            }
        }
        $thisCardInstanceParameter[$thisLayoutCardInstances[$i-1]->parameter_key]=$thisLayoutCardInstances[$i-1]->parameter_value;
        $newCardInstance = array('id'=>$thisCardInstanceId, 'card_component'=>$thisCardInstanceComponent, 'card_parameters'=>$thisCardInstanceParameter);
        array_push($allCardInstances, $newCardInstance);



        return json_encode($allCardInstances);
    }
    /*
    public function getThisLayout($layoutId, $orgId, $userId){
//    public function getLayoutById(Request $request){
//        $inData =  $request->all();
//        $layoutId = $inData['layoutId'];
//        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $layoutInstance = new Layout;
        $layoutInfo = $layoutInstance->where('id', $layoutId)->get();
        $thisLayoutDescription = $layoutInfo[0]->description;
        $thisLayoutWidth = $layoutInfo[0]->width;
        $thisLayoutHeight = $layoutInfo[0]->height;
        $thisLayoutBackgroundColor = $layoutInfo[0]->backgroundColor;
        $thisLayoutImageUrl=$layoutInfo[0]->backgroundUrl;
        $thisLayoutBackgroundType=$layoutInfo[0]->backgroundType;
        $thisLayoutLabel = $layoutInfo[0]->menu_label;
        $thisCardInstance = new CardInstances;
        $thisLayoutCardInstances = $thisCardInstance->getLayoutCardInstancesById($layoutId, $orgId);
        if($thisLayoutCardInstances==null){
            $layoutProperties =array('description'=>$thisLayoutDescription, 'menu_label'=>$thisLayoutLabel, 'height'=>$thisLayoutHeight, 'width'=>$thisLayoutHeight, 'backgroundColor'=>$thisLayoutBackgroundColor, 'backGroundImageUrl'=>$thisLayoutImageUrl, 'backgroundType'=>$thisLayoutBackgroundType);
            $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
            $returnData = array('cards'=>[], 'layout'=>$layoutProperties, 'perms'=>$thisLayoutPerms);
            return json_encode($returnData);
        }
        $cardsReadIn = array();
        $cardSubElementProperties = array();
        $allCardInstances = array();
        foreach($thisLayoutCardInstances as $card) {
            $thisId = strval($card->id);
            $thisCardData = array($card->parameter_key, $card->parameter_value, $card->isCss, $card->card_component, $card->col, $card->row, $card->height, $card->width, $card->id);
            if($card->dom_element=='main'){
                if(!array_key_exists($thisId, $cardsReadIn)){
                    $cardsReadIn[$thisId] = [$thisCardData];
                } else {
                    array_push($cardsReadIn[$thisId], $thisCardData);
                }
            }else{
                if(!array_key_exists($thisId, $cardSubElementProperties)){
                    $cardSubElementProperties[$thisId][$card->dom_element]=array();
                    array_push($cardSubElementProperties[$thisId][$card->dom_element],$thisCardData);
                }else{
                    array_push($cardSubElementProperties[$thisId][$card->dom_element],$thisCardData);
                }
            }
        }
        foreach($cardsReadIn as $thisCardArray){
            $thisCardCss="";
            $thisCardProperties="";
            $thisCardContent = array();
            foreach($thisCardArray as $thisCard){
                if($thisCard[2]==1){
                    $thisCardCss=$thisCardCss.$thisCard[1];
                }else{
                    $thisCardProperties=$thisCardProperties.$thisCard[1];
                    $thisCardContent[$thisCard[0]]=$thisCard[1];
                }
                $thisCardIsCss = $thisCard[2];
                $thisCardParameterKey = $thisCard[0];
                $thisCardComponent = $thisCard[3];
                if($thisCardComponent=="linkMenu"){
                    $thisLink = new link();
                    $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                    $thisCardContent['availableLinks']=$cardLinks;
                }
                $thisCardCol = $thisCard[4];
                $thisCardRow = $thisCard[5];
                $thisCardHeight = $thisCard[6];
                $thisCardWidth = $thisCard[7];
                $thisCardId = $thisCard[8];
            }
            $cssGridParams = $this->computeGridCss($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth).";";
            $thisCardParameters = array(
                'style'=>$cssGridParams.$thisCardCss,
                'properties'=>$thisCardProperties,
                'content'=>$thisCardContent
            );
            $thisCardPosition = array($thisCardRow,$thisCardCol,$thisCardHeight,$thisCardWidth);
            $thisCardData = array(
                'id'=>$thisCardId,
                'card_component'=>$thisCardComponent,
                'card_parameters'=>$thisCardParameters,
                'card_position'=>$thisCardPosition
            );
            array_push($allCardInstances, $thisCardData);
        }
        $subElementStyles = array();
        foreach($cardSubElementProperties as $key=>$value){
            $cardSubElement = $value;
            $cardId = $key;
            $thisSubElementStyle = '';
            foreach($cardSubElement as $key=>$value){
                foreach($value as $styleElement){
                    $thisSubElementStyle = $thisSubElementStyle.$styleElement[1];
                }
                if(!array_key_exists($cardId, $subElementStyles)){
                    $subElementStyles[$cardId][$key] = array();
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                }else{
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                }
            }
        }
        foreach($allCardInstances as $key=>$value){
            $thisCardId = $key;
            foreach($subElementStyles as $key=>$value){
                if($allCardInstances[$thisCardId]['id']==$key){
                    $allCardInstances[$thisCardId]['elementStyles'] = $value;
                }
            }
        }
        $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        $layoutProperties =array('description'=>$thisLayoutDescription, 'menu_label'=>$thisLayoutLabel, 'height'=>$thisLayoutHeight, 'width'=>$thisLayoutHeight, 'backgroundColor'=>$thisLayoutBackgroundColor, 'backGroundImageUrl'=>$thisLayoutImageUrl, 'backgroundType'=>$thisLayoutBackgroundType);
        $returnData = array('cards'=>$allCardInstances, 'layout'=>$layoutProperties, 'perms'=>$thisLayoutPerms);
        return $returnData;
//        return json_encode($returnData);
    }
*/
    public function getLayoutById(Request $request){
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $userId = $inData['userId'];
        $layoutInstance = new Layout;
        $thisLayoutData = $layoutInstance->getThisLayout($layoutId, $orgId, $userId);
        $encodedData = json_encode($thisLayoutData);
        return $encodedData;
    }
    public function getOrgCards(Request $request){
        $inData = $request->all();
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $orgId = $inData['orgId'];
        $orgInstance = new Org();
        $orgHomeId = $orgInstance->getOrgHomeFromOrgId($orgId);
        $layoutInstance = new Layout();
        $returnPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $orgHomeId[0]->top_layout_id);
        $thisCardInstance = new CardInstances();
        if($returnPerms['admin']==1){
            $allCardsForThisOrg = $thisCardInstance->getCardsForOrg($orgId, true);

        }else{
            $allCardsForThisOrg = $thisCardInstance->getCardsForOrg($orgId, false);
        }
        return $allCardsForThisOrg;
    }
    public function cardInsert(Request $request){
        $inData = $request->all();
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $toLayout = $inData['params']['toLayout'];
        $cardId = $inData['params']['cardId'];
        $cardInstance = new CardInstances();
        try {
            $cardInstance->insertCard($cardId, $toLayout);
        } catch (Exception $e) {
            $msg = 'Could not insert card:'.$e->getMessage();
            abort(500, $msg);
        }

    }

    public function saveCardOnly(Request $request){
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $cardTitle = $inData['cardTitle'];
        $restricted = $inData['restricted'];
        $cardType = $inData['cardType'];
        $topLeftRow = $inData['topLeftRow'];
        $topLeftCol = $inData['topLeftCol'];
        $bottomRightRow = $inData['bottomRightRow'];
        $bottomRightCol = $inData['bottomRightCol'];
        $background = 'background-color:#dbddd0;';
        $cardParams = [['backgroundColor', $background, true],['color','color:blue;', true],['backgroundTypeColor','backgroundTypeColor:checked;',true]];
//        $cardParams = [];
        $thisCardInstance = new CardInstances();
        $cardWidth = ($bottomRightCol-$topLeftCol)+1;
        $cardHeight = ($bottomRightRow-$topLeftRow)+1;
        $thisCardInstance->createCardInstance($layoutId, $cardParams, $topLeftRow,$topLeftCol, $cardHeight, $cardWidth,$cardType, $cardTitle, $restricted);
        //       return $this->getLayoutById($request);
        return "ok";

    }
    public function resizeCard(Request $request){
        $inData =  $request->all();
        $cardId = $inData['cardId'];
        $row = $inData['row'];
        $col = $inData['col'];
        $height = $inData['height'];
        $width = $inData['width'];
        $layoutId = $inData['layoutId'];
        $thisCardInstance = new CardInstances();

        try {
            $updated = $thisCardInstance->updateCardSize($cardId, $row, $col, $height, $width, $layoutId);
        } catch (Exception $e) {
            abort(500, 'Server error while resizing component:'.$e->getMessage());
        }

        return "ok";

    }

    public function getCardDataById(Request $request){
        $inData =  $request->all();
        $cardId = $inData['cardId'];
        $thisCardInstanceParams = new InstanceParams();
        $cardParams = $thisCardInstanceParams->getCardInstanceParams($cardId);
        $configParameters=array();
        $contentParameters=array();
        $subElementArray=array();
        foreach($cardParams as $thisCardParam){
            $thisCardParameterElement = $thisCardParam->parameter_key;
            if($thisCardParam->parameter_value=='checked'){
                $thisCardParameterValue="checked";
            }else{
                $colonLocationInCssParam = strpos($thisCardParam->parameter_value, ':')+1;
                $thisCardParameterValue = substr($thisCardParam->parameter_value,$colonLocationInCssParam );
                $thisCardParameterValue = str_replace(';','', $thisCardParameterValue);


//                $parameterElementLength = strlen($thisCardParam-> parameter_key)+2;
//                $thisCardParameterValue = substr($thisCardParam->parameter_value, $parameterElementLength, -1);
            }
//            $thisCardParameterElementCombo = [$thisCardParameterElement,$thisCardParameterElement.':'.$thisCardParameterValue];
//            $thisCardParameterElementCombo = [$thisCardParameterElement,$thisCardParameterValue];
            $thisCardParameterElementCombo = [$thisCardParam->parameter_key, $thisCardParam->parameter_value ];
            if($thisCardParam->isCss){
                if($thisCardParam->dom_element=='main'){
                    array_push($configParameters, $thisCardParameterElementCombo);
                }else{
                    if(!isset($subElementArray[$thisCardParam->dom_element])){
                        $subElementArray[$thisCardParam->dom_element] = array();
                    }
                    array_push($subElementArray[$thisCardParam->dom_element],$thisCardParameterElementCombo);
                }
            }else{
//                array_push($contentParameters, $thisCardParameterElementCombo);
                $contentParameters[$thisCardParameterElementCombo[0]]=$thisCardParameterElementCombo[1];
            }
        }
        $thisLink = new link();
        $thisCardContent = array();
        $cardLinks = $thisLink->getLinksForCardId($cardId);
        if(count($cardLinks)>0){
            $thisCardContent['availableLinks']=$cardLinks;
        }


        $returnData = [$configParameters, $contentParameters, $subElementArray,$thisCardContent];
        return json_encode($returnData);

    }

    public function saveCard(Request $request){
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $cardTitle = $inData['cardTitle'];
        $cardType = $inData['cardType'];
        $topLeftRow = $inData['topLeftRow'];
        $topLeftCol = $inData['topLeftCol'];
        $bottomRightRow = $inData['bottomRightRow'];
        $bottomRightCol = $inData['bottomRightCol'];

        $query1 = "select id from card_instances  where col >= ? and row >= ? and col <= ? and row <= ? and layout_id = ?";
        $retrievedCardInstances  =  DB::select($query1, [$topLeftCol, $topLeftRow, $bottomRightCol, $bottomRightRow, $layoutId]);
        $retrievedIds = array();
        $blanksToDelete = '';
        foreach($retrievedCardInstances as $thisRetrievedId){
            array_push($retrievedIds, $thisRetrievedId->id);
            $blanksToDelete = $blanksToDelete."'".$thisRetrievedId->id."',";
        }
        $blanksToDelete = substr($blanksToDelete, 0, strlen($blanksToDelete)-1);
        $query2 = "delete from instance_params where card_instance_id in (".$blanksToDelete.")";
        $deletedParams = DB::select($query2);
        $query3 = "delete from card_instances where id in(".$blanksToDelete.")";
        $deletedCards = DB::select($query3);

        $background = '#7FDBFF';
        $cardParams = [['background-color', $background, true],['color','blue', true]];
        $thisCardInstance = new CardInstances();
        $cardWidth = ($bottomRightCol-$topLeftCol)+1;
        $cardHeight = ($bottomRightRow-$topLeftRow)+1;
        $thisCardInstance->createCardInstance($layoutId, $cardParams, $topLeftRow,$topLeftCol, $cardHeight, $cardWidth,$cardType);



        return $layoutId;
    }

    public function saveCardParameters(Request $request){
        $inData =  $request->all();
        $decodedPost = json_decode($inData['cardParams']);
        $domElements = $decodedPost[3];
        $thisInstanceParams = new InstanceParams;

        DB::table('instance_params')->where('card_instance_id', '=', $decodedPost[0])->sharedLock()->get();
        $query = "delete from instance_params where card_instance_id = ? and isCss = 1 and dom_element = 'main'";
        DB::beginTransaction();
        try {
            DB::select($query, [$decodedPost[0]]);
        } catch (Exception $e) {
            throw new Exception('error - could not clean out existing params');
        }
        try {
            foreach ($decodedPost[1] as $key => $value) {
                $thisInstanceParams->createInstanceParam($key, $value, $decodedPost[0], true, 'main');
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();
        if(count($domElements)>0){
            foreach($domElements as $key => $value){
                $thisDomElementName  = $value->elementName;
                $thisDomElementParameters = $value->elementStyles;
                $query = "delete from instance_params where card_instance_id = ? and isCss = 1 and dom_element = ?";

                DB::beginTransaction();
                try {
                    DB::select($query, [$decodedPost[0], $thisDomElementName]);
                } catch (Exception $e) {
                    DB::rollBack();
                    throw new Exception('error - could not clean out existing params');
                }

                foreach($thisDomElementParameters as $key => $value){
                    $thisKey = $key;
                    $thisValue = $value;
                    try {
                        $thisInstanceParams->createInstanceParam($key, $value, $decodedPost[0], true, $thisDomElementName);
                    } catch (Exception $e) {
                        DB::rollBack();
                        throw new Exception('error - could not create new param - '.$thisKey.'-'.$thisValue);
                    }
                }
                DB::commit();
            }
        }

        return "Ok";
    }
    public function updateCardName(Request $request){
        $inData =  $request->all();
        $newCardName = $inData['newCardName'];
        $cardId = $inData['cardId'];
        $thisCardInstance = new CardInstances;
        $thisCardInstance->updateCardName($cardId, $newCardName);
        return "ok";
    }

    public function updateCardTitle(Request $request){

        $inData =  $request->all();
        $thisCardTitle = $inData['cardTitle'];
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $thisInstanceParams = new InstanceParams;
        $cardTitleId = $thisInstanceParams->hasInstanceParam($thisCardId, 'linkMenuTitle');
        if($cardTitleId>0 && (strlen($thisCardTitle)>0)){
            try {
                $thisInstanceParams->updateInstanceParam($cardTitleId, 'linkMenuTitle', $thisCardTitle, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error updating instance_param: '.$e->getMessage());
            }
        }else if($cardTitleId<0 && (strlen($thisCardTitle)>0)){
            try {
                $thisInstanceParams->createInstanceParam('linkMenuTitle', $thisCardTitle, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error creating instance_param: '.$e->getMessage());
            }
        }else if($cardTitleId>0 && (strlen($thisCardTitle)==0)){
            try {
                $thisInstanceParams->deleteInstanceParam($cardTitleId);
            } catch (\Exception $e) {
                abort(500, 'Server error deleting instance_param: '.$e->getMessage());
            }
        }
        return "ok";

    }

    public function saveCardContent(Request $request){
        $inData =  $request->all();
        $decodedPost = json_decode($inData['cardParams']);
        $domElement = $inData['domElement'];
        $org = $inData['org'];
        $layoutId = $inData['layoutId'];
        $cardId = $decodedPost[0];
        $thisInstanceParams = new InstanceParams;
        $thisConstants = new Constants;

        DB::beginTransaction();
        DB::table('instance_params')->where([
            ['card_instance_id', '=', $decodedPost[0]],
            ['isCss','=',0]
        ])->sharedLock()->get();
        /*
                DB::table('instance_params')->where([
                    ['card_instance_id', '=', $decodedPost[0]],
                    ['isCss','=',0]
                ])->delete();
        */
        $query = "delete from instance_params where card_instance_id = ? and isCss = 0";
        try {
            DB::select($query, [$decodedPost[0]]);
        } catch (Exception $e) {
            throw new Exception('error - could not clean out existing params');
        }
        $cardType = '';
        $indexFile = false;
        $cardTextContent = '';
        $contentFileName = '';
        $fileLocation = '';
        $keyWords = '';
        $cardTitle='';
        $accessType = '';
        $documentType = '';
        $createDate = '';
        $cardText ='';
        $createDate='';
        $ytubeUrl = '';
        foreach ($decodedPost[1] as $key => $value) {
            if ($key == 'keyWords') {
                $keyWords = $value;
            }
            if ($key == 'indexFile') {
                $indexFile = $value;
            }
            if ($key == 'accessType') {
                $accessType = $value;
            }
            if ($key == 'documentType') {
                $documentType = $value;
            }
            if ($key == 'createDate') {
                $createDate = $value;
            }
            if ($key == 'cardType') {
                $cardType = $value;
            }
            if ($key == 'fileLocation') {
                $fileLocation = $value;
            }
            if ($key == 'cardText') {
                $cardText = $value;
            }
            if ($key == 'title') {
                $cardTitle = $value;
            }
            if ($key == 'ytubeUrl'){
                $ytubeUrl = $value;
            }
        }

// a little fix-up here
        if($cardText != '' and $cardType!='textShow'){
            $cardType = 'textShow';
        }
        if($cardType == 'textShow'){
            $cardTextContent = $cardText;
            $pattern = "displayLayout/";
            $patternFoundAt = 0;
            $documentLinks = array();
            $linkAt = strpos($cardText, $pattern, $patternFoundAt);
            if ($linkAt != false) {
                $nextLink = $this->findNextLink($cardText, 0, $pattern);
                array_push($documentLinks, $nextLink[0]);
                while ($nextLink != false) {
                    $nextLink = $this->findNextLink($cardText, $nextLink[1], $pattern);
                    if ($nextLink == false) break;
                    array_push($documentLinks, $nextLink[0]);
                }
            }
            $thisLink = new Link;
            $thisLink->removeLinksForCardId($cardId, 'U');
            $showOrder = 0;
            foreach ($documentLinks as $thisDocumentLink) {
                $thisDescription = 'link from card:' . $cardId . ' to card:' . $thisDocumentLink;
                $linkUrl = $thisConstants->Options['linkUrlBase'] . $thisDocumentLink;
                $isExternal = 0;
                $layoutLinkTo = $thisDocumentLink;
                $thisLink->saveLink($org, $layoutId, $cardId, $thisDescription, $linkUrl, $isExternal, $layoutLinkTo, 'U', $showOrder);
                $showOrder++;
            }
            $pattern = $thisConstants->Options['storageLinkPattern'];
//            $pattern = "<img src=\"http://localhost:8000/storage/";
            $patternFoundAt = 0;
            $imageLinks = array();
            $imageLinkAt = strpos($cardText, $pattern, $patternFoundAt);
            $ib = strpos($cardText, $pattern, $patternFoundAt);
            if ($imageLinkAt != false) {
                $nextLink = $this->findNextLink($cardText, 0, $pattern);
                array_push($imageLinks, $nextLink[0]);
                while ($nextLink != false) {
                    $nextLink = $this->findNextLink($cardText, $nextLink[1], $pattern);
                    if ($nextLink == false) break;
                    array_push($imageLinks, $nextLink[0]);
                }
            }
            $orgDirectory = '/images/' . $org;
            if (!Storage::exists($orgDirectory)) {
                Storage::makeDirectory($orgDirectory);
            }

            foreach ($imageLinks as $thisImageLink) {
                $copyToLocation = $orgDirectory . '/' . $thisImageLink;
                Storage::copy('file/' . $thisImageLink, $copyToLocation);
                $tempFileReference = $thisConstants->Options['tempFileReference'] . $thisImageLink;
                $newImageLink = $thisConstants->Options['newImageLink'] . $org . "/" . $thisImageLink;
                $cardText = str_replace($tempFileReference, $newImageLink, $cardText);
                $imgDescription = "Link to image";
                $linkUrl = $newImageLink;
                $isExternal = false;
                $layoutLinkTo = $layoutId;
                $thisLink->saveLink($org, $layoutId, $cardId, $imgDescription, $linkUrl, $isExternal, $layoutLinkTo, 'I', $showOrder);
                $showOrder++;
            }


            $contentFileName = $this->storeContent($org,$cardText, $cardId );
            foreach ($decodedPost[1] as $key => $value) {
                if($key=='cardText'){
                    $thisInstanceParams->createInstanceParam($key, $contentFileName, $decodedPost[0], false, $domElement);
                }else{
                    $thisInstanceParams->createInstanceParam($key, $value, $decodedPost[0], false, $domElement);
                }
            }
        }else if($cardType=='pdf'){
            foreach ($decodedPost[1] as $key => $value) {
                $thisInstanceParams->createInstanceParam($key, $value, $decodedPost[0], false, $domElement);
            }
        }else if($cardType=='youTube'){
            foreach ($decodedPost[1] as $key => $value) {
                $thisInstanceParams->createInstanceParam($key, $value, $decodedPost[0], false, $domElement);
            }
            $thisCardInstance = new CardInstances();
            $thisCardName = $thisCardInstance->getCardName($cardId);
            $contentFileName = $this->storeContent($org,$thisCardName, $cardId );
            $thisInstanceParams->createInstanceParam('title', $thisCardName, $decodedPost[0], false, $domElement);
        }


/*
        try {
            foreach ($decodedPost[1] as $key => $value) {
                if($key == 'keyWords'){
                    $keyWords = $value;
                }
                if($key == 'indexFile'){
                    $indexFile = $value;
                }
                if($key == 'accessType'){
                    $accessType = $value;
                }
                if($key == 'documentType'){
                    $documentType = $value;
                }
                if($key == 'createDate'){
                    $createDate = $value;
                }
                if($key=='cardType'){
                    $cardType = $value;
                }
                if($key=='fileLocation'){
                    $fileLocation = $value;
                }
                if ($key == 'cardText') {
                    $cardType = "richText";
                    $cardTextContent = $value;
                    $pattern = "displayLayout/";
                    $patternFoundAt = 0;
                    $documentLinks = array();
                    $linkAt = strpos($value, $pattern, $patternFoundAt);
                    if ($linkAt != false) {
                        $nextLink = $this->findNextLink($value, 0, $pattern);
                        array_push($documentLinks, $nextLink[0]);
                        while ($nextLink != false) {
                            $nextLink = $this->findNextLink($value, $nextLink[1], $pattern);
                            if ($nextLink == false) break;
                            array_push($documentLinks, $nextLink[0]);
                        }
                    }
                    $thisLink = new Link;
                    $thisLink->removeLinksForCardId($cardId, 'U');
                    $showOrder = 0;
                    foreach ($documentLinks as $thisDocumentLink) {
                        $thisDescription = 'link from card:' . $cardId . ' to card:' . $thisDocumentLink;
                        $linkUrl = 'http://localhost:8080/displayLayout/' . $thisDocumentLink;
                        $isExternal = 0;
                        $layoutLinkTo = $thisDocumentLink;
                        $thisLink->saveLink($org, $layoutId, $cardId, $thisDescription, $linkUrl, $isExternal, $layoutLinkTo, 'U', $showOrder);
                        $showOrder++;
                    }
                    $pattern = "<img src=\"http://localhost:8000/storage/";
                    $patternFoundAt = 0;
                    $imageLinks = array();
                    $imageLinkAt = strpos($value, $pattern, $patternFoundAt);
                    if ($imageLinkAt != false) {
                        $nextLink = $this->findNextLink($value, 0, $pattern);
                        array_push($imageLinks, $nextLink[0]);
                        while ($nextLink != false) {
                            $nextLink = $this->findNextLink($value, $nextLink[1], $pattern);
                            if ($nextLink == false) break;
                            array_push($imageLinks, $nextLink[0]);
                        }
                    }
                    $orgDirectory = '/images/' . $org;
                    if (!Storage::exists($orgDirectory)) {
                        Storage::makeDirectory($orgDirectory);
                    }
                    foreach ($imageLinks as $thisImageLink) {
                        $copyToLocation = $orgDirectory . '/' . $thisImageLink;
                        Storage::copy('file/' . $thisImageLink, $copyToLocation);
                        $tempFileReference = "http://localhost:8000/storage/" . $thisImageLink;
                        $newImageLink = "http://localhost:8000/images/" . $org . "/" . $thisImageLink;
                        $value = str_replace($tempFileReference, $newImageLink, $value);
                        $imgDescription = "Link to image";
                        $linkUrl = $newImageLink;
                        $isExternal = false;
                        $layoutLinkTo = $layoutId;
                        $thisLink->saveLink($org, $layoutId, $cardId, $imgDescription, $linkUrl, $isExternal, $layoutLinkTo, 'I', $showOrder);
                        $showOrder++;
                    }


                    $orgDirectory = '/spcontent/' . $org;
                    if (!Storage::exists($orgDirectory)) {
                        Storage::makeDirectory($orgDirectory);
                        Storage::makeDirectory($orgDirectory . '/cardText');
                    }
                    $contentFileName = '/spcontent/' . $org . '/cardText/rtcontent' . $decodedPost[0];
                    Storage::disk('local')->put($contentFileName, $value);
                    $thisInstanceParams->createInstanceParam($key, $contentFileName, $decodedPost[0], false, $domElement);

                }else {
                    $thisInstanceParams->createInstanceParam($key, $value, $decodedPost[0], false, $domElement);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
/*
        if($cardType=='Document'){
            $thisCardInstance = new CardInstances();
            $thisCardInstance->updateDocumentTitle($cardId, $cardTitle);
        }
*/
        if($indexFile){
            $thisSolr = new Solr;
            if($cardType == 'pdf'){
                $contentFileName = $fileLocation;
            }
            if($cardType == 'youtube'){
                $contentFileName = $fileLocation;
            }

            $thisSolr->addFileToCollection($thisConstants->Options['collection'], $layoutId, $cardId, $contentFileName, $keyWords, $accessType, $documentType, $createDate  );
        }
        DB::commit();

        return "Ok";
    }

    private function storeContent($org, $content, $cardId){
        $orgDirectory = '/spcontent/' . $org;
        if (!Storage::exists($orgDirectory)) {
            Storage::makeDirectory($orgDirectory);
            Storage::makeDirectory($orgDirectory . '/cardText');
        }
        $contentFileName = '/spcontent/' . $org . '/cardText/rtcontent' . $cardId;
        Storage::disk('local')->put($contentFileName, $content);
        return $contentFileName;
    }
    private function findNextLink($content,$startingAt, $pattern){
//        $pattern = "displayLayout/";
        $patternFoundAt=0;
        $patternLength = strlen($pattern);
        $linkAt = strpos($content, $pattern, $startingAt);
        if($linkAt==false){
            return false;
        }
        $endQuote = chr(34);
        $endQuoteAt = strpos($content, $endQuote, $linkAt+$patternLength);
        $idLength = $endQuoteAt-($linkAt+$patternLength);
        $linkId = substr($content, $linkAt+$patternLength, $idLength);
        $nextPos = $endQuoteAt+1;
        return array($linkId, $nextPos);

    }


    public function getCsrf(Request $request){
        return $request->session()->token();
    }

    public function postCsrf(Request $request){
        $inData =  $request->all();
        $thisContent = $inData['content'];
        return $thisContent.' was posted';

    }

    public function serveTest(){
        return view('serveCrsfTest');
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

    private function computeCssFromCoordinates($topLeftRow, $topLeftCol, $bottomRightRow, $bottomRightCol){
        $thisHeight=0;
        $thisWidth=0;
        if($topLeftRow = $bottomRightRow){
            $thisHeight = 1;
        }else{
            $thisHeight = $bottomRightRow-$topLeftRow;
        }
        $thisWidth = $bottomRightCol-$topLeftCol;
        return $this->computeGridCss($topLeftRow, $topLeftCol, $thisHeight, $thisWidth);
    }




}
