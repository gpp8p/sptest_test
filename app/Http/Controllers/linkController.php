<?php

namespace App\Http\Controllers;

use App\Layout;
use Illuminate\Http\Request;
use App\link;
use App\InstanceParams;
use Illuminate\Support\Facades\DB;
use App\Classes\Constants;

class linkController extends Controller
{
    public function getLinksByCardId(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['cardId'];
        $thisLink = new link();
        return $thisLink->getLinksForCardId($thisCardId);
    }

    public function getLinkInfo(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['cardId'];

        $thisLink = new link();
        $allLinks = $thisLink->getLinksForCardId($thisCardId);
        $atLink=0;
        foreach($allLinks as $link){
            if($link->isExternal==0){
                $thisLabel = $thisLink->getMenuLabelForLink($link->layout_link_to);
                $allLinks[$atLink]->menu_label = $thisLabel->menu_label;
            }else{
                $allLinks[$atLink]->menu_label = $link->description;
            }
            $atLink++;
        }
        return $allLinks;
    }


    public function createNewLink(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $thisLayoutLinkTo = $inData['layout_link_to'];
        if(! isset($inData['description'])){
            $layoutInstance = new Layout;
            $d = $layoutInstance->getLayoutDescription($thisLayoutLinkTo);
            $thisDescription = $d[0]->description;
        }else{
            $thisDescription = $inData['description'];
        }
        $thisIsExternal = $inData['is_external'];
        $thisLinkUrl = $inData['linkUrl'];

        $linkType = $inData['type'];
        $thisLayout = new Layout;
        $thisLinkInstance = new link;
        try {
            $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $thisDescription, $thisLinkUrl, $thisIsExternal, $thisLayoutLinkTo, $linkType, 1);
            return "ok";
        } catch (\Exception $e) {
            return "Error ".$e;
        }
    }
    public function addUpdateLink(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $isExternal = $inData['is_external'];
        $layoutLinkTo = $inData['layout_link_to'];
        $linkUrl = $inData['linkUrl'];
        $type= $inData['type'];
        $cardType=$inData['cardType'];
        $linkDescription = $inData['description'];
        $thisLinkInstance = new link;
        if($cardType=='imageCard'){
            $thisLinkInstance->removeLinksForCardId($thisCardId, 'U');
        }else{
            $existingLink = $thisLinkInstance->linkExistsInCard($thisCardId, $layoutLinkTo);
            if($existingLink>0){
                try {
                    $thisLinkInstance->deleteLink($existingLink);
                } catch (\Exception $e) {
                    abort(500, 'Could not update link - delete failed: '.$e->getMessage());
                }
            }
        }

        try {
            $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $linkDescription, $linkUrl, 0, $layoutLinkTo, 'U', 1);
            return "ok";
        } catch (\Exception $e) {
            abort(500, 'Could not update link - new link insert failed: '.$e->getMessage());
        }
    }

    public function addNewLink(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $isExternal = $inData['is_external'];
        $linkUrl = $inData['linkUrl'];
        $type= $inData['type'];
        $linkDescription = $inData['description'];
        $showOrder = $inData['insertPoint'];
        $addInsert = $inData['addInsert'];
        $layoutLinkTo = $inData['layout_link_to'];
        $thisLinkInstance = new link;

        $currentCardLinks = $thisLinkInstance->getLinksForCardId($thisCardId);
        if($addInsert=='add'){
            $nextShowOrderValue = count($currentCardLinks)+1;
            $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $linkDescription, $linkUrl, $isExternal, $layoutLinkTo, 'U', $nextShowOrderValue);
        }else{
            $currentCardLinks=$thisLinkInstance->getLinksForCardId($thisCardId);
//            $showOrder+=1;
            $linkOrderAt = 1;
            $insertDone=false;
            try {
                foreach ($currentCardLinks as $thisLink) {
                    if($thisLink->show_order == $showOrder) {
                        $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $linkDescription, $linkUrl, $isExternal, 0, 'U', $linkOrderAt);
                        $insertDone = true;
//                        $linkOrderAt+=1;
                    }else{
                        if ($insertDone) {
                            $thisLinkInstance->updateShowOrder($thisCardId, $linkOrderAt,$thisLink->id);
                        }
                    }
                    $linkOrderAt+=1;
                }
            } catch (\Exception $e) {
                return "error";
            }
        }

        return "ok";

    }


    public function getLinkLabel(Request $request){
        $inData = $request->all();
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $linkId = $inData['linkId'];
        $linkInstance = new Link();
        try {
            return $linkInstance->getLinkLabel($linkId);
        } catch (\Exception $e) {
        }
    }
    public function addCurrentLayout(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $thisLinkInstance = new link;
        $thisConstants = new Constants;
        if($thisLinkInstance->isLinkInCard($thisCardId, $thisLayoutId)){
            return 'already linked';
        }else{
            $currrentLinks = $thisLinkInstance->getLinksForCardId($thisCardId);
            $currentLinkCount = count($currrentLinks);
            $thisLayout = new Layout;
            $layoutInfo = $thisLayout->getLayoutDescription($thisLayoutId);
            $thisDescription = $layoutInfo[0]->description;
//            $thisLinkUrl = "http://localhost:8080/displayLayout/".$thisLayoutId;
            $thisLinkUrl = $thisConstants->Options['linkUrlBase'].$thisLayoutId;
            try {
                $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $thisDescription, $thisLinkUrl, 0, $thisLayoutId, 'U', $currentLinkCount);
                return "ok";
            } catch (\Exception $e) {
                return "Error ".$e;
            }
        }
    }

    public function deleteLink(Request $request){
        $inData =  $request->all();
        $linkIdToDelete = $inData['linkId'];
        $thisLinkInstance = new link;
        $thisLinkInstance->deleteLink($linkIdToDelete);
        return "ok";

    }
    public function updateCardLinks(Request $request){
        $inData =  $request->all();
        $allLinksJson = $inData['allLinks'];
        $allLinks = json_decode($allLinksJson);
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $thisOrient = $inData['orient'];
        $thisCardTitle = $inData['cardTitle'];
        $thisLinkInstance = new link;
        $layoutInstance = new Layout;
        $thisInstanceParams = new InstanceParams;
        $orientId = $thisInstanceParams->hasInstanceParam($thisCardId, 'orient');
        $cardTitleId = $thisInstanceParams->hasInstanceParam($thisCardId, 'linkMenuTitle');
        DB::beginTransaction();
        if($orientId>0){
            try {
                $thisInstanceParams->updateInstanceParam($orientId, 'orient', $thisOrient, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error updating instance_param: '.$e->getMessage());
            }
        }else{
            try {
                $thisInstanceParams->createInstanceParam('orient', $thisOrient, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error creating instance_param: '.$e->getMessage());
            }
        }
        if($cardTitleId>0 && (strlen($thisCardTitle)>0)){
            try {
                $thisInstanceParams->updateInstanceParam($cardTitleId, 'linkMenuTitle', $thisCardTitle, $thisCardId, 0, 'main');
     //           $thisInstanceParams->updateInstanceParam($orientId, 'linkMenuTitle', $thisCardTitle, $thisCardId, 0, 'main');
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
        try {
            $thisLinkInstance->removeLinksForCardId($thisCardId, 'U');
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'Server error: '.$e->getMessage());
        }
        try {
            $thisShowOrder=0;
            foreach ($allLinks as $thisLink) {
                $thisLinkInstance->saveLink(
                    $thisOrgId,
                    $thisLayoutId,
                    $thisCardId,
                    $thisLink->description,
                    $thisLink->link_url,
                    $thisLink->isExternal,
                    $thisLink->layout_link_to,
                    'U',
                    $thisShowOrder);
                $layoutInstance->updateMenuLabel($thisLink->layout_link_to, $thisLink->menu_label);
                $thisShowOrder++;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'Server error: '.$e->getMessage());
        }
        DB::commit();
        return 'ok';


    }
}
