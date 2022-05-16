<?php

namespace App\Http\Controllers;

use App\Layout;
use Illuminate\Http\Request;
use App\CardInstances;
use App\InstanceParams;
use App\Group;
use App\Org;
use Illuminate\Support\Facades\DB;
use Storage;
use File;
use App\User;
use App\link;
use App\Card;
use App\Classes\Constants;


class LayoutController extends Controller
{
    public function createBlankLayout(Request $request){
        $inData =  $request->all();
        $layoutName = $inData['name'];
        $height = $inData['height'];
        $width = $inData['width'];
//        $background = $inData['background'];
        $background = '#DBAA6E';
        $cardParams = [['background-color', $background, true],['color','blue', true]];
        $thisLayout = new Layout;
        $testLayoutDescription = "New Layout for test purposes";
        $newLayoutId = $thisLayout->createBlankLayout($layoutName, $height, $width, $cardParams, $testLayoutDescription);
        $thisCardInstance = new CardInstances;
        $newCardInstances = $thisCardInstance->getLayoutCardInstancesById($newLayoutId);
        return json_encode($newCardInstances);
    }

    public function createNewLayout(Request $request){
        $inData =  $request->all();
        $layoutName = $inData['name'];
        $height = $inData['height'];
        $width = $inData['width'];
        $testLayoutDescription = $inData['description'];
        $background = '#DBAA6E';
        $cardParams = [['background-color', $background, true],['color','blue', true]];
        $thisLayout = new Layout;
        $thisCardInstance = new CardInstances;
        $newLayoutId = $thisLayout->createBlankLayout($layoutName, $height, $width, $cardParams, $testLayoutDescription);
        $newCardInstances = $thisCardInstance->getLayoutCardInstancesById($newLayoutId);
        return json_encode([$newLayoutId]);
    }

    public function createNewLayoutNoBlanks(Request $request){
        $inData =  $request->all();
        $layoutName = $inData['name'];
        $layoutHeight = $inData['height'];
        $layoutWidth = $inData['width'];
        $template = $inData['template'];
        $permType = $inData['permType'];
        $layoutId = $inData['layoutId'];
        $userIsAdmin = 1;
        $userNotAdmin = 0;


        $layoutDescription = $inData['description'];
        $userId = $inData['userId'];
        $orgId = $inData['orgId'];
        $currentLayoutId = $inData['layoutId'];
        $backgroundType = $inData['backgroundType'];
        if($inData['backgroundType']=='I'){
            $backgroundImage = $inData['backgroundImage'];
            if(isset($inData['backgroundDisplay'])){
                $backgroundDisplay = $inData['backgroundDisplay'];
            }else{
                $backgroundDisplay = 'cover';
            }
            $layoutBackgroundColor = '';
        }else{
            $backgroundImage = '';
            $layoutBackgroundColor = $inData['backgroundColor'];

            $backgroundDisplay='';
        }
        if($template){
            $isTemplate='Y';
        }else{
            $isTemplate='N';
        }

        $layoutInstance = new Layout;
        $newLayoutId = $layoutInstance->createLayoutWithoutBlanks($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $layoutBackgroundColor, $backgroundImage, $backgroundType, $orgId, $backgroundDisplay, $isTemplate);

        $thisGroup = new Group;
        try {
            $allUserGroupId = $thisGroup->returnAllUserGroupId();
        } catch (\Exception $e) {
            throw new \Exception('error identifying all user group');
        }
        if($permType=='default'){
            $personalGroupId = $thisGroup->returnPersonalGroupId($userId);
            $newLayoutGroupId = $thisGroup->addNewLayoutGroup($newLayoutId, $layoutName, $layoutDescription);
            $thisGroup->addOrgToGroup($orgId, $newLayoutGroupId);
            $thisGroup->addUserToGroup($userId, $newLayoutGroupId,$userIsAdmin);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'view', 1);
            $layoutInstance->editPermForGroup($newLayoutGroupId, $newLayoutId, 'view', 1);
            $userPersonalGroupId = $personalGroupId;
            $thisGroup->addOrgToGroup($orgId, $userPersonalGroupId);
            $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'view', 1);
            $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'author', 1);
            $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'admin', 1);
        }else{
            $thisGroup = new Group;
            $personalGroupId = $thisGroup->returnPersonalGroupId($userId);
            $thisLayoutGroupId = $thisGroup->getLayoutGroupId($layoutId);
//            $parentLayoutGroupId = $thisGroup->getLayoutGroupId($templateId);
            $layoutGroupPerms = $layoutInstance->getLayoutGroupPerms($layoutId, $thisLayoutGroupId);
            $allUserGroupPerms = $layoutInstance->getAllUserGroupPerms($layoutId, $allUserGroupId);
            $personalGroupPerms = $layoutInstance->getPersonalGroupPerms($layoutId, $personalGroupId);
            $layoutInstance->editPermForGroup($thisLayoutGroupId, $newLayoutId, 'view', $layoutGroupPerms->view);
            $layoutInstance->editPermForGroup($thisLayoutGroupId, $newLayoutId, 'author', $layoutGroupPerms->author);
            $layoutInstance->editPermForGroup($thisLayoutGroupId, $newLayoutId, 'admin', $layoutGroupPerms->admin);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'view', $allUserGroupPerms->view);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'author', $allUserGroupPerms->author);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'admin', $allUserGroupPerms->admin);
            $layoutInstance->editPermForGroup($personalGroupId, $newLayoutId, 'view', $personalGroupPerms->view);
            $layoutInstance->editPermForGroup($personalGroupId, $newLayoutId, 'author', $personalGroupPerms->author);
            $layoutInstance->editPermForGroup($personalGroupId, $newLayoutId, 'admin', $personalGroupPerms->admin);
        }


        return json_encode($newLayoutId);

    }

    public function addAccessForGroupToLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['params']['orgId'];
        $groupId = $inData['params']['groupId'];
        $layoutId = $inData['params']['layoutId'];
        $thisGroup = new Group;
        $layoutInstance = new Layout;
        DB::beginTransaction();
        try {
//            $thisGroup->addOrgToGroup($orgId, $groupId);
            $layoutInstance->editPermForGroup($groupId, $layoutId, 'view', 1);
            DB::commit();
        }catch (Throwable $e) {
            DB::rollback();
            abort(500, 'Server error: '.$e->getMessage());
        }
        return "ok";
    }

    public function getLayoutParams(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $layoutInstance = new Layout;
        try {
            $layoutParams = $layoutInstance->getParams($layoutId);
            return json_encode($layoutParams);

        } catch (\Exception $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }


    }
    public function updateLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        if($inData['backgroundType']=='I'){
            $backgroundColor='';
            $backgroundImage = $inData['backgroundImage'];
        }else{
            $backgroundColor = $inData['backgroundColor'];
            $backgroundImage = '';
        }
        $template = $inData['template'];
        if($template){
            $isTemplate='Y';
        }else{
            $isTemplate='N';
        }
        $layoutName = $inData['name'];
        $layoutHeight = $inData['height'];
        $layoutWidth = $inData['width'];
        $layoutDescription = $inData['description'];
        $backgroundType = $inData['backgroundType'];
        $orgId = $inData['orgId'];
        $layoutId = $inData['layoutId'];
        $backgroundDisplay = $inData['backgroundDisplay'];
        if(is_null($backgroundDisplay)){
            $backgroundDisplay='';
        }
        $layoutInstance = new Layout;
        $layoutInstance->updateLayout($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $backgroundColor, $backgroundImage, $backgroundType, $orgId, $backgroundDisplay, $layoutId, $isTemplate);

        return 'ok';
    }

    public function removePerm(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $groupId = $inData['params']['groupId'];
        $layoutId = $inData['params']['layoutId'];
        $layoutInstance = new Layout;
        try {
            $layoutInstance->removePermForGroup($layoutId, $groupId);
            return "ok";
        }catch (Throwable $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }

    }

    public function test($request){
        $inData =  $request->all();
        return 'ok';
    }

    public function getLayoutList(Request $request){
//        if(auth()->user()==null){
//            abort(401, 'Unauthorized action.');
//        }
        $returnList = array();
        $layoutInstance = new Layout;
        $allLayouts = $layoutInstance->all();
//        foreach($allLayouts as $thisLayout){
//            array_push($returnList, [$thisLayout->id,$thisLayout->menu_label,$thisLayout->description,$thisLayout->height, $thisLayout->width]);
//        }
        return json_encode($allLayouts);
    }
    public function getMySpaces(Request $request){

        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }

        $inData =  $request->all();
        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $thisLayout = new Layout;
        $viewableLayouts = $thisLayout->getViewableLayoutIds($userId, $orgId);
        return json_encode($viewableLayouts);
    }

    public function getMyDeletedSpaces(Request $request){

        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }

        $inData =  $request->all();
        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $thisLayout = new Layout;
        $viewableLayouts = $thisLayout->getDeletedLayoutIds($userId, $orgId);
        return json_encode($viewableLayouts);
    }
    public function undeleteThisSpace(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }

        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $thisLayout = new Layout;
        try {
            $thisLayout->undeleteThisSpace($layoutId);
        } catch (\Exception $e) {
            abort(500, 'Undelete failed'.$e);
        }
    }
    public function getLayoutInfoById(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }

        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $thisLayout = new Layout;
        try {
            $thisLayoutInfo = $thisLayout->getLayoutInfoById($layoutId);
            return json_encode($thisLayoutInfo);
        } catch (\Exception $e) {
            abort(500, 'layoutInfo failed'.$e);
        }
    }
    public function getViewableLayoutList(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $viewableLayouts = $thisLayout->getViewableLayoutIds($userId, $orgId);
        return json_encode($viewableLayouts);
    }
    public function publishOrg(Request $request){
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $groupInstance = new Group;
        try {
            $allUserGroupId = $groupInstance->allUserId();
        } catch (\Exception $e) {
            abort(500, 'could not find all user group id');
        }
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $userInstance = new User;
        $userFound = $userInstance->findUserByEmail('GuestUser@nomail.com');
        $guestUserId = $userFound[0]->id;


        $thisLayoutInstance = new Layout;
//        $returnedLayouts = $thisLayoutInstance->getPublishableLayoutsForOrg($orgId, $allUserGroupId);
        $returnedLayouts = $thisLayoutInstance->getViewableLayoutIds($userId, $orgId);
//        $viewableLayouts=array();
        if(count($returnedLayouts)==0){
            abort(500, 'no viewable layouts for this org');
        }
        $orgLayouts = '';
        foreach($returnedLayouts as $thisLayout){
//            array_push($viewableLayouts, $thisLayout->layout_id);
            $orgLayouts = $orgLayouts.$thisLayout->id.',';
        }
        $orgLayouts = substr($orgLayouts,0,(strlen($orgLayouts)-1));
        try {
            $viewableLayouts = $thisLayoutInstance->getViewableOrgLayouts($orgLayouts, $allUserGroupId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage()." while getting viewable org layouts");
        }
        $orgDirectory = '/published/'.$orgId;
        if(!Storage::exists($orgDirectory)) {
            Storage::makeDirectory($orgDirectory);
        }else{
            $existingFiles =   Storage::allFiles($orgDirectory);
            Storage::delete($existingFiles);
        }
        $orgImageDirectory = '/published/'.$orgId.'/images';
        if(!Storage::exists($orgImageDirectory)) {
            Storage::makeDirectory($orgImageDirectory);
        }else{
            $existingImageFiles = Storage::allFiles($orgImageDirectory);
            Storage::delete($existingImageFiles);
        }

        foreach($viewableLayouts as $thisViewableLayout){
            if($thisViewableLayout->layout_id==176){
                $a=0;
            }
            if($thisLayoutInstance->isDeleted($thisViewableLayout->layout_id)) continue;
            try {
                $layoutData = $thisLayoutInstance->publishThisLayout($thisViewableLayout, $orgId, $guestUserId, $orgImageDirectory, $viewableLayouts);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
            $height = $layoutData['layout']['height'];
            $width = $layoutData['layout']['width'];
            if(isset($layoutData['layout']['backgroundColor'])){
                $backgroundColor = $layoutData['layout']['backgroundColor'];
            }else{
                $backgroundColor = '';
            }
            if(isset($layoutData['layout']['backgroundImageUrl'])){
                $backgroundImageUrl = $layoutData['layout']['backgroundImageUrl'];
            }else{
                $backgroundImageUrl = '';
            }
            if(isset($layoutData['layout']['hzLinkMenuColor'])){
                $hzLinkMenuColor = $layoutData['layout']['hzLinkMenuColor'];
            }else{
                $hzLinkMenuColor = '';
            }

            $backgroundType = $layoutData['layout']['backgroundType'];
            $thisLayoutCss = $this->layoutCss($height, $width, $backgroundColor, $backgroundImageUrl, $backgroundType, $orgId);
            /*
                        foreach($layoutData['cards'] as $thisCard){
                            $cardType = $thisCard['card_component'];
                            switch($cardType){
                                case 'Headline':{
                                    break;
                                }
                                case 'RichText':{
                                    break;
                                }
                                case 'linkMenu':{
                                    break;
                                }
                            }
                        }
            */
            try {
                $viewHtml = view('layout', ['layoutId' => $thisViewableLayout, 'layoutCss' => $thisLayoutCss, 'hzLinkMenuColor'=>$hzLinkMenuColor, 'orgId'=>$orgId, 'cards' => $layoutData['cards']])->render();
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
                continue;
            }
            $thisOutPutFile = 'published/'.$orgId.'/'.$thisViewableLayout->layout_id.'.html';
            Storage::put($thisOutPutFile, $viewHtml);
        }
        return 'Ok';

    }
    private function layoutCss($height, $width, $backgroundColor, $backgroundImageUrl, $backgroundType, $orgId){
        $thisConstants = new Constants;
//        $urlBase = "http://localhost/spaces/".$orgId;
        $urlBase = $thisConstants->Options['spacesBase'].$orgId;
        $imageBase = $urlBase."/images/";
        $contentBase = $urlBase."/content/";
        $heightSize = number_format((100 / $height) ,2);
        $widthSize = number_format((100 / $width), 2);
        $gridHeightCss = "grid-template-rows: ";
        $gridWidthCss = "grid-template-columns: ";
        $x = 0;
        for($x = 0; $x < $height; $x++) {
            $gridHeightCss = $gridHeightCss.$heightSize."% ";
        }
        for ($y = 0; $y < $width; $y++) {
            $gridWidthCss = $gridWidthCss.$widthSize."% ";
        }
        if($backgroundType=='C'){
            $gridCss =
                "display: grid; grid-gap: 3px; background-color: ".$backgroundColor."; height: 98vh; color: #ffcd90; ".$gridHeightCss.";".$gridWidthCss.";";

        }else{
            $orgDirectory = '/images/'.$orgId;
            $backgroundComponents = explode('/', $backgroundImageUrl);
            $backgroundComponentsSize = count($backgroundComponents);
            $imageFileName = $backgroundComponents[$backgroundComponentsSize-1];
            $imageSource = $orgDirectory.'/'.$imageFileName;
            $copyToLocation = '/published/'.$orgId.'/images'.'/'.$imageFileName;
            Storage::copy($imageSource, $copyToLocation);
            $backgroundUrl = 'url('.$imageBase.$backgroundComponents[$backgroundComponentsSize-1].')';
            $gridCss = "display: grid; grid-gap: 3px; background-image:".$backgroundUrl."; background-size: cover; background-repeat: no-repeat; background-position: center; height: 98vh; color: #ffcd90; ". $gridHeightCss.";".$gridWidthCss.";";
        }
        return $gridCss;


    }
    public function getLayoutPerms(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $layoutId = $inData['layoutId'];
        $thisLayout = new Layout;
        $thisUserPerms = $thisLayout->getUserPermsForLayout($layoutId, $orgId, $userId);
        return $thisUserPerms;
    }
    public function setLayoutPerms(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['params']['layoutId'];
        $groupId = $inData['params']['groupId'];
        $permType = $inData['params']['permType'];
        $permValue = $inData['params']['permValue'];
        $thisLayout = new Layout;
        try {
            $thisLayout->editPermForGroup($groupId, $layoutId, $permType, $permValue);
        } catch (Exception $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }

    }

    public function getOrgLayouts(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $thisOrgLayouts = $thisLayout->getOrgLayouts($orgId);
        return json_encode($thisOrgLayouts);
    }

    public function summaryPerms(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $userId = $inData['userId'];
        $orgId = $inData['orgId'];
        $layoutId = $inData['layoutId'];
        $layoutInstance = new Layout;
        $returnPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        return json_encode($returnPerms);
    }

    public function deleteLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $layoutInfo = $thisLayout->getThisLayout($layoutId,$orgId,$userId, false);
        if(!$layoutInfo['perms']['admin']){
            return 'noAuth';
        }


        $thisLinkInstance = new Link;
        $selectedToLinks = $thisLinkInstance->getLinksToLayout($layoutId);
        $thisCardInstance = new CardInstances;
        foreach($selectedToLinks as $thisSelectedLink){
            $cardType = $thisCardInstance->getCardTypeById($thisSelectedLink->card_instance_id);
            if($cardType[0]->card_component == 'RichText'){
                $orgDirectory = '/spcontent/'.$orgId;
                $contentFileName = '/spcontent/'.$orgId.'/cardText/rtcontent'.$thisSelectedLink->card_instance_id;
                $thisRtContent = Storage::get($contentFileName);
                $textWithLinkRemoved = $this->removeLinkFromRichText($thisRtContent, $thisSelectedLink->link_url);
                Storage::put($contentFileName, $textWithLinkRemoved);
            }
            $thisLinkInstance->deleteLinksToLayout($layoutId);
        }
        $thisLayout->setDelete($layoutId);



    }
    public function removeCardFromLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $cardId = $inData['cardId'];
        $thisLayout = new Layout;
        $userPerms = $thisLayout->getUserPermsForLayout($layoutId, $orgId, $userId);
        $hasPermission = false;
        foreach($userPerms as $thisPerm){
            if($thisPerm->author>0){
                $hasPermission=true;
            }
            if($thisPerm->admin>0){
                $hasPermission=true;
            }
        }
        if(!$hasPermission){
            return ('not permitted');
        }
        $cardInstance = new CardInstances;
        try {
            $cardInstance->removeCardFromLayout($cardId, $layoutId);
            return 'ok';
        } catch (\Exception $e) {
            return 'error removing card from layout';
        }


    }
    public function deleteCard(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $cardId = $inData['cardId'];
        $thisLayout = new Layout;
        $userPerms = $thisLayout->getUserPermsForLayout($layoutId, $orgId, $userId);
        $hasPermission = false;
        foreach($userPerms as $thisPerm){
            if($thisPerm->author>0){
                $hasPermission=true;
            }
            if($thisPerm->admin>0){
                $hasPermission=true;
            }
        }
        if(!$hasPermission){
            return ('not permitted');
        }
        $cardInstance = new CardInstances;
        try {
            $cardInstance->deleteCard($cardId);
            return 'ok';
        } catch (\Exception $e) {
            return 'error deleting card';
        }

    }
    public function layoutTest(Request $request){
        $layoutInstance = new Layout;
        $layoutInstance->updateCardInLayout();
        return 'ok';
    }
    public function getAvailableTemplates(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $templateLayouts = $thisLayout->getTemplateLayouts($orgId);
        return json_encode($templateLayouts);
    }
    public function cloneTemplate(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['params']['orgId'];
        $templateId = $inData['params']['templateId'];
        $layoutId = $inData['params']['layoutId'];
        $description = $inData['params']['description'];
        $menu_label = $inData['params']['menu_label'];
        $permType = $inData['params']['permType'];
        $copyIt = $inData['params']['copyIt'];
//        $copyIt = true;
        $layoutInstance = new Layout;
        $thisLayoutData = $layoutInstance->getThisLayout($templateId, $orgId, $userId);
        $height = $thisLayoutData['layout']['height'];
        $width = $thisLayoutData['layout']['width'];
        $backgroundColor = $thisLayoutData['layout']['backgroundColor'];
        if(isset($thisLayoutData['layout']['backgroundImageUrl'])){
            $backgroundImageUrl = $thisLayoutData['layout']['backgroundImageUrl'];
        }else{
            $backgroundImageUrl = '';
        }

        $backgroundType = $thisLayoutData['layout']['backgroundType'];
        if(isset($thisLayoutData['layout']['backgroundDisplay'])){
            $backgroundDisplay = $thisLayoutData['layout']['backgroundDisplay'];
        }else{
            $backgroundDisplay = '';
        }
        $newLayoutId = $layoutInstance->createLayoutWithoutBlanks($menu_label, $height, $width, $description, $backgroundColor, $backgroundImageUrl, $backgroundType, $orgId, $backgroundDisplay, 'N');
        $thisGroup = new Group;
        try {
            $allUserGroupId = $thisGroup->returnAllUserGroupId();
        } catch (\Exception $e) {
            throw new \Exception('error identifying all user group');
        }
        $setTopLayout = 0;
        $personalGroupId = $thisGroup->returnPersonalGroupId($userId);
//        $parentLayoutGroupId = $thisGroup->getLayoutGroupId($templateId);
        $parentLayoutGroupId = $thisGroup->getLayoutGroupId($layoutId);
        if($parentLayoutGroupId<0){
            $permType = 'default';
            $setTopLayout = 1;
        }

        if($permType=='default'){


            $userIsAdmin = 1;
            $userNotAdmin = 0;

            $newLayoutGroupId = $thisGroup->addNewLayoutGroup($newLayoutId, $menu_label, $description);
            $thisGroup->addOrgToGroup($orgId, $newLayoutGroupId);
            $thisGroup->addUserToGroup($userId, $newLayoutGroupId,$userIsAdmin);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'view', 1);
            $layoutInstance->editPermForGroup($newLayoutGroupId, $newLayoutId, 'view', 1);
            $userPersonalGroupId = $personalGroupId;
            $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'view', 1);
            $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'author', 1);
            $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'admin', 1);
//            if($setTopLayout>0){
//                $thisGroup->setGroupLayout($newLayoutGroupId, $newLayoutId);
//            }
            $thisGroup->setGroupLayout($newLayoutGroupId, $newLayoutId);

        }else{
            $thisGroup = new Group;
//            $parentLayoutGroupId = $thisGroup->getLayoutGroupId($templateId);
            $layoutGroupPerms = $layoutInstance->getLayoutGroupPerms($layoutId, $parentLayoutGroupId);
            $allUserGroupPerms = $layoutInstance->getAllUserGroupPerms($layoutId, $allUserGroupId);
            $personalGroupPerms = $layoutInstance->getPersonalGroupPerms($layoutId, $personalGroupId);
            $layoutInstance->editPermForGroup($parentLayoutGroupId, $newLayoutId, 'view', $layoutGroupPerms->view);
            $layoutInstance->editPermForGroup($parentLayoutGroupId, $newLayoutId, 'author', $layoutGroupPerms->author);
            $layoutInstance->editPermForGroup($parentLayoutGroupId, $newLayoutId, 'admin', $layoutGroupPerms->admin);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'view', $allUserGroupPerms->view);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'author', $allUserGroupPerms->author);
            $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'admin', $allUserGroupPerms->admin);
            $layoutInstance->editPermForGroup($personalGroupId, $newLayoutId, 'view', $personalGroupPerms->view);
            $layoutInstance->editPermForGroup($personalGroupId, $newLayoutId, 'author', $personalGroupPerms->author);
            $layoutInstance->editPermForGroup($personalGroupId, $newLayoutId, 'admin', $personalGroupPerms->admin);
        }
        $cardInstance = new CardInstances;
        $thisInstanceParams = new InstanceParams;
        foreach($thisLayoutData['cards'] as $thisCard){
            switch($thisCard['card_component']){
                case 'linkMenu':{

                    try {
                        $cardInstance->insertCard($thisCard['id'], $newLayoutId);
                    } catch (Exception $e) {
                        $msg = 'Could not insert card:'.$e->getMessage();
                        abort(500, $msg);
                    }

                    break;
                }
                case 'NavigationMenu':{
                    if($copyIt){
                        try {
                            $cardInstance->insertCard($thisCard['id'], $newLayoutId);
                        } catch (Exception $e) {
                            $msg = 'Could not insert card:'.$e->getMessage();
                            abort(500, $msg);
                        }
                    }else{
//                        $cardDimensions = $cardInstance->getCardDimensionsFromId()
                        $sourceCardParameters = explode(';', $thisCard['card_parameters']['style']);
                        $newCardParameters = [];
                        foreach($sourceCardParameters as $thisSourceCardParameter){
                            if($thisSourceCardParameter==''){
                                continue;
                            }
                            $thisParameterPair = explode(':', $thisSourceCardParameter);
                            if($thisParameterPair[0]=='grid-area'){
                                $thisCardPosition = explode('/', $thisParameterPair[1]);
                            }else{
                                $thisNewCardParameter = array($thisParameterPair[0], $thisParameterPair[0].':'.$thisParameterPair[1].';', true );
                                array_push($newCardParameters, $thisNewCardParameter);
                            }
                        }
                        $row = $thisCard['card_position'][0];
                        $column = $thisCard['card_position'][1];
                        $height = $thisCard['card_position'][2];
                        $width = $thisCard['card_position'][3];
                        $newCardName = $thisCard['card_parameters']['content']['card_name'] .'_'.$newLayoutId;

                        $cardInstance->createCardInstance($newLayoutId, $newCardParameters, $row, $column, $height, $width, 'NavigationMenu', $newCardName, 'F');
                    }
                    break;
                }
                case 'Headline':{
                    try {
                        $cardInstance->insertCard($thisCard['id'], $newLayoutId);
                    } catch (Exception $e) {
                        $msg = 'Could not insert card:'.$e->getMessage();
                        abort(500, $msg);
                    }
                    break;
                }
                case 'RichText':{
                    $cardInstanceParams = $thisInstanceParams->getCardInstanceParams($thisCard['id']);
                    $textRemovedParams = array();
                    $row = $thisCard['card_position'][0];
                    $column = $thisCard['card_position'][1];
                    $cardHeight = $thisCard['card_position'][2];
                    $cardWidth = $thisCard['card_position'][3];
                    foreach($cardInstanceParams as $thisParam){
                        if($thisParam->parameter_key !='cardText'){
                            $newParam = array($thisParam->parameter_key, $thisParam->parameter_value, $thisParam->isCss);
                            $textRemovedParams[]=$newParam;
                        }
                    }
                    $cardInstance->createCardInstance($newLayoutId, $textRemovedParams, $row, $column, $cardHeight, $cardWidth, 'RichText', $thisCard['card_parameters']['content']['card_name'], 'F');
                    break;
                }
                case 'pdf':{
                    $cardInstanceParams = $thisInstanceParams->getCardInstanceParams($thisCard['id']);
                    $cardInstanceParamArray = array();
                    $row = $thisCard['card_position'][0];
                    $column = $thisCard['card_position'][1];
                    $cardHeight = $thisCard['card_position'][2];
                    $cardWidth = $thisCard['card_position'][3];
                    foreach($cardInstanceParams as $thisParam){
                        if($thisParam->parameter_key !='cardText'){
                            $newParam = array($thisParam->parameter_key, $thisParam->parameter_value, $thisParam->isCss);
                            $cardInstanceParamArray[]=$newParam;
                        }
                    }
                    $cardInstance->createCardInstance($newLayoutId, $cardInstanceParamArray, $row, $column, $cardHeight, $cardWidth, 'pdf', $thisCard['card_parameters']['content']['card_name'], 'F');
                    break;
                }
                case 'youTube':{
                    $cardInstanceParams = $thisInstanceParams->getCardInstanceParams($thisCard['id']);
                    $cardInstanceParamArray = array();
                    $row = $thisCard['card_position'][0];
                    $column = $thisCard['card_position'][1];
                    $cardHeight = $thisCard['card_position'][2];
                    $cardWidth = $thisCard['card_position'][3];
                    foreach($cardInstanceParams as $thisParam){
                        $newParam = array($thisParam->parameter_key, $thisParam->parameter_value, $thisParam->isCss);
                        $cardInstanceParamArray[]=$newParam;
                    }
                    $cardInstance->createCardInstance($newLayoutId, $cardInstanceParamArray, $row, $column, $cardHeight, $cardWidth, 'youTube', $thisCard['card_parameters']['content']['card_name'], 'F');

                    break;
                }
                case 'loginLink':{
                    $cardInstanceParams = $thisInstanceParams->getCardInstanceParams($thisCard['id']);
                    $cardInstanceParamArray = array();
                    $row = $thisCard['card_position'][0];
                    $column = $thisCard['card_position'][1];
                    $cardHeight = $thisCard['card_position'][2];
                    $cardWidth = $thisCard['card_position'][3];
                    foreach($cardInstanceParams as $thisParam){
                        $newParam = array($thisParam->parameter_key, $thisParam->parameter_value, $thisParam->isCss);
                        $cardInstanceParamArray[]=$newParam;
                    }
                    $cardInstance->createCardInstance($newLayoutId, $cardInstanceParamArray, $row, $column, $cardHeight, $cardWidth, 'loginLink', $thisCard['card_parameters']['content']['card_name'], 'F');


                    break;
                }
            }
        }

        return json_encode($newLayoutId);

    }

    private function removeLinkFromRichText($text, $link){
        $linkReferenceLocation = strpos($text, $link);
        $closingTagLocation = strpos($text, '</a>', $linkReferenceLocation);
        $remainingStringCount = strlen($text)-$closingTagLocation;
        $textStartLocation = strpos($text, '>', $linkReferenceLocation);
        $linkTextLength = strlen($text)-$textStartLocation-$remainingStringCount;
        $linkTextContent = substr($text,$textStartLocation+1,$linkTextLength-1 );
        $tagStart = $linkReferenceLocation-9;
        $tagEnd = $closingTagLocation+4;
        $entireTagLength = ($closingTagLocation-$tagStart)+4;
        $entireTag = substr($text, $tagStart,$entireTagLength);
        $replacementText = '['.$linkTextContent.']';
        $newText = substr($text, 0, $tagStart);
        $newText = $newText.'['.$linkTextContent.']';
        $newText = $newText.substr($text,$tagEnd);
        return $newText;
    }

}
