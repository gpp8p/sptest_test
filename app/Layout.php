<?php

namespace App;

use App\CardInstances;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Classes\SpCard;
use App\Classes\SpRichTextCard;
use App\Group;
use Storage;
use File;
use App\Classes\Constants;





class Layout extends Model
{
    public function CardInstances()
    {
        return $this->hasMany(CardInstances::class);
    }

    public function createLayoutWithoutBlanks($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $backgroundColor, $backgroundImage, $backgroundType, $orgId, $backgroundDisplay, $isTemplate){
        $newlayoutId =db::table('layouts')->insertgetid([
            'menu_label'=>$layoutName,
            'description'=>$layoutDescription,
            'height'=>$layoutHeight,
            'width'=>$layoutWidth,
            'backgroundColor'=>$backgroundColor,
            'backgroundUrl'=>$backgroundImage,
            'backgroundType'=>$backgroundType,
            'backgroundDisplay'=>$backgroundDisplay,
            'org_id'=>$orgId,
            'template'=>$isTemplate,
            'deleted'=>'N',
            'created_at'=>\carbon\carbon::now(),
            'updated_at'=>\carbon\carbon::now()
        ]);
        return $newlayoutId;
    }
    public function updateLayout($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $backgroundColor, $backgroundImage, $backgroundType, $orgId, $backgroundDisplay, $layoutId, $isTemplate){
        $affected = DB::table('layouts')
            ->where('id', $layoutId)
            ->update([
                'menu_label'=>$layoutName,
                'description'=>$layoutDescription,
                'height'=>$layoutHeight,
                'width'=>$layoutWidth,
                'backgroundColor'=>$backgroundColor,
                'backgroundUrl'=>$backgroundImage,
                'backgroundType'=>$backgroundType,
                'template'=>$isTemplate,
                'backgroundDisplay'=>$backgroundDisplay,
                'org_id'=>$orgId,
                'updated_at'=>\carbon\carbon::now()
            ]);
    }
    public function undeleteThisSpace($layoutId){
        $query = "update layouts set deleted = 'N' where id = ?";
        try {
            DB::select($query, [$layoutId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
//($layoutName, $height, $width, $cardParams, $testLayoutDescription)
    public function createBlankLayout($layoutName, $layoutHeight, $layoutWidth, $cardParams, $layoutDescription)
    {
        $thisNewLayout = new Layout;
        $thisNewLayout->menu_label = $layoutName;
        $thisNewLayout->height = $layoutHeight;
        $thisNewLayout->width = $layoutWidth;
        $thisNewLayout->description = $layoutDescription;

        $newLayoutId =DB::table('layouts')->insertGetId([
            'menu_label'=>$layoutName,
            'description'=>$layoutDescription,
            'height'=>$layoutHeight,
            'width'=>$layoutWidth,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);


//        $thisNewLayout->save();
        $totalNumberOfCells = $layoutHeight * $layoutWidth;
        $row = 1;
        $column = 1;
        for ($x = 0; $x < $totalNumberOfCells; $x++) {
//            $blankLayoutStyle = "grid-area:" . $row . " / " . $column . " / " . $row . " / " . ($column + 1) . ";" . $blankLayoutBackground;
//            $blankLayoutStyle = $blankLayoutBackground;
//            $fontColorCss = "color: blue;";
//            $newParams = [['key'=>'style', 'value'=>$blankLayoutStyle]];
//            $newParams = [['style',$blankLayoutStyle],[]]
            $thisCardInstance = new CardInstances;
            $thisCardInstance->createCardInstance($newLayoutId, $cardParams, $row, $column, 1,1, 'simpleCard');
//           $fontColorCss = "color: blue;";
//            $newParams = [['key'=>'style', 'value'=>$fontColorCss]];
//            $thisCardInstance = new CardInstances;
//            $thisCardInstance->createCardInstance($thisNewLayout->id, $cardParams, $row, $column, 1,1);

            $column++;
            if($column>$layoutWidth){
                $column=1;
                $row++;
            }
        }
        return $newLayoutId;
    }
    public function updateLayoutOrg($layoutId, $orgId){
        $query = "update layouts set org_id = ? where id = ?";

        try {
            DB::select($query, [$orgId, $layoutId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getLayoutList(){
        return App/Layout::all();
    }

    public function getAllPermsForUser($userId, $layoutId, $orgId){

        $query = "select distinct users.name, groups.group_label, groups.id as group_id, perms.view, perms.admin, perms.author, perms.opt1, perms.opt2, perms.opt3 from layouts, `groups`, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and layouts.id=? ".
            "and users.id = ? ".
            "and org.id = ? ";

        $retrievedPerms  =  DB::select($query, [$layoutId, $userId, $orgId]);
        return $retrievedPerms;


    }

    public function getPermsSummaryForUser($userId, $layoutId, $orgId){

        $query = "select distinct sum(perms.view) as view, sum(perms.admin) as admin, sum(perms.author) as author, sum(perms.opt1) as opt1, sum(perms.opt2) as opt2, sum(perms.opt3) as opt3 from layouts, `groups`, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and layouts.id=? ".
            "and users.id = ? ".
            "and org.id = ? ";

        $retrievedPerms  =  DB::select($query, [$layoutId, $userId, $orgId]);
        return $retrievedPerms;
    }

    public function editPermForGroup($groupId, $layoutId, $permType, $permValue){

        try {
            DB::table('perms')
                ->updateOrInsert(
                    ['layout_id' => $layoutId, 'group_id' => $groupId],
                    [$permType => $permValue, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]
                );
            return 'ok';
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function removePermForGroup($layoutId, $groupId){
        $query = "delete from perms where group_id = ? and layout_id=?";
        try {
            $deletedPerms  =  DB::select($query, [$groupId, $layoutId]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllUserAccessibleLayouts($allUserGroupId, $orgId){
/*        $query = "select layouts.id, layouts.menu_label, layouts.description, layouts.menu_label, layouts.height, layouts.width from layouts ".
            "where layouts.id in ( select distinct layout_id from perms where group_id = ? and view = 1)";
*/
        $query = "select distinct layout_id from perms,`groups`,grouporg ".
        "where groups.id = ? ".
        "and perms.group_id = groups.id ".
        "and grouporg.group_id = groups.id ".
        "and grouporg.org_id = ? ".
        "and perms.view = 1";

        try {
            $retrievedLayouts = DB::select($query, [$allUserGroupId, $orgId]);
        } catch (\Exception $e) {
            throw $e;
        }
        return $retrievedLayouts;
    }

    public function getViewableLayoutIds($orgId, $userId){


        $query = "select layouts.id, layouts.menu_label, layouts.description, layouts.menu_label, layouts.height, layouts.width from layouts ".
            "where layouts.id in ( ".
	            "select distinct layouts.id from layouts, `groups`, usergroup, users, userorg, org, perms ".
	            "where perms.layout_id = layouts.id ".
                "and layouts.deleted != 'Y'".
	            "and perms.group_id = groups.id ".
	            "and usergroup.group_id = groups.id ".
                "and groups.id!=1 ".
	            "and usergroup.user_id = users.id ".
	            "and userorg.user_id = users.id ".
	            "and userorg.org_id = org.id ".
	            "and perms.view=1 ".
	            "and org.id = ? ".
	            "and users.id=? ".
                ")";

        $retrievedLayouts  =  DB::select($query, [$orgId, $userId]);
        return $retrievedLayouts;

    }
    public function getMyViewableLayoutIds($orgId, $userId){


        $query = "select layouts.id, layouts.menu_label, layouts.description, layouts.menu_label, layouts.height, layouts.width from layouts ".
            "where layouts.id in ( ".
                "select distinct layouts.id from layouts, `groups`, usergroup, users, userorg, org, perms ".
                "where perms.layout_id = layouts.id ".
                "and layouts.deleted != 'Y' ".
                "and perms.group_id = groups.id ".
                "and usergroup.group_id = groups.id ".
                "and usergroup.user_id = users.id ".
                "and userorg.user_id = users.id ".
                "and userorg.org_id = org.id ".
                "and perms.view=1 ".
                "and org.id = ? ".
                "and users.id= ? ".
            ")";

        $retrievedLayouts  =  DB::select($query, [$orgId, $userId]);
        return $retrievedLayouts;

    }


    public function getDeletedLayoutIds($userId, $orgId){


        $query = "select layouts.id, layouts.menu_label, layouts.description, layouts.menu_label, layouts.height, layouts.width from layouts ".
            "where layouts.id in ( ".
            "select distinct layouts.id from layouts, `groups`, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and layouts.deleted = 'Y'".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and groups.id!=1 ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and perms.view=1 ".
            "and org.id = ? ".
            "and users.id=? ".
            ")";

        $retrievedLayouts  =  DB::select($query, [$orgId, $userId]);
        return $retrievedLayouts;

    }
    public function getViewableOrgLayouts($orgLayouts, $allUserGroupId){
        $query = "select layout_id from perms where group_id = ? and layout_id in (".$orgLayouts.") and view=1";
        try {
            $viewableOrgLayoutIds  =  DB::select($query, [$allUserGroupId]);
        } catch (Exception $e) {
            throw $e;
        }
        return $viewableOrgLayoutIds;
    }

    public function getLayoutGroups($layoutId, $orgId, $userId){
        $query = "select groups.description, groups.id, perms.view, perms.author, perms.admin, perms.opt1, perms.opt2, perms.opt3 from `groups`, perms, users, usergroup, userorg, org ".
            "where groups.id = perms.group_id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and org.id = ? ".
            "and users.id=? ".
            "and perms.layout_id = ?";

        $retrievedGroups  =  DB::select($query, [$orgId, $userId, $layoutId]);
        return $retrievedGroups;

    }
    public function getLayoutGroupPerms($layoutId, $layoutGroupId){
        $query = "select perms.view, perms.author, perms.admin from perms where group_id = ? and layout_id = ?";
        try {
            $queryResult = DB::select($query, [$layoutGroupId, $layoutId]);
            if(count($queryResult)>0){
                return $queryResult[0];
            }else{
                return -1;
            }
        } catch (\Exception $e) {
            throw e;
        }
    }

    public function getAllUserGroupPerms($layoutId, $allUserGroupId){
        $query = "select perms.view, perms.author, perms.admin from perms where group_id = ? and layout_id = ?";
        try {
            $queryResult = DB::select($query, [$allUserGroupId, $layoutId]);
            if(count($queryResult)>0){
                return $queryResult[0];
            }else{
                return -1;
            }
        } catch (\Exception $e) {
            throw e;
        }
    }

    public function getPersonalGroupPerms($layoutId, $personalGroupId){
        $query = "select perms.view, perms.author, perms.admin from perms where group_id = ? and layout_id = ?";
        try {
            $queryResult = DB::select($query, [$personalGroupId, $layoutId]);
            if(count($queryResult)>0){
                return $queryResult[0];
            }else{
                return -1;
            }
        } catch (\Exception $e) {
            throw e;
        }
    }

    public function getUserPermsForLayout($layoutId, $orgId, $userId){
/*
        $query = "select groups.description, groups.id, perms.view, perms.author, perms.admin, perms.opt1, perms.opt2, perms.opt3 from groups, perms, users, usergroup, userorg, org ".
                "where groups.id = perms.group_id ".
                "and usergroup.group_id = groups.id ".
                "and usergroup.user_id = users.id ".
                "and userorg.user_id = users.id ".
                "and userorg.org_id = org.id ".
                "and org.id = ? ".
                "and users.id=? ".
                "and perms.layout_id = ?";
*/
        $query = "select groups.description, groups.id, perms.view, perms.author, perms.admin, perms.opt1, perms.opt2, perms.opt3, perms.group_id from `groups`,   perms  ".
                "where groups.id = perms.group_id ".
                "and perms.layout_id = ?";


//        $retrievedPerms  =  DB::select($query, [$orgId, $userId, $layoutId]);
        $retrievedPerms  =  DB::select($query, [$layoutId]);
        return $retrievedPerms;
    }

    public function getOrgLayouts($orgId){

        $query = "select distinct layouts.id, layouts.menu_label, layouts.description, layouts.height, layouts.width from layouts, perms, `groups`, org, grouporg ".
                "where layouts.id = perms.layout_id ".
                "and perms.view=1 ".
                "and perms.group_id = groups.id ".
                "and grouporg.group_id = groups.id ".
                "and grouporg.org_id = ?";

        $retrievedLayouts  =  DB::select($query, [$orgId]);
        return $retrievedLayouts;
    }
    public function getTemplateLayouts($orgId){
        $query = "select distinct layouts.id, layouts.description from layouts, perms, `groups`, org, grouporg ".
            "where layouts.id = perms.layout_id ".
            "and perms.view=1 ".
            "and perms.group_id = groups.id ".
            "and grouporg.group_id = groups.id ".
            "and layouts.template = 'Y'".
            "and grouporg.org_id = ?";

        $retrievedLayouts  =  DB::select($query, [$orgId]);
        return $retrievedLayouts;

    }
    public function getPublishableLayoutsForOrg($orgId, $allUserGroup){
        $query = "select perms.layout_id from perms, grouporg ".
            "where perms.group_id = grouporg.group_id ".
            "and grouporg.org_id = ? ".
            "and perms.view=1 ".
            "and perms.group_id=?";
        $retrievedLayouts  =  DB::select($query, [$orgId, $allUserGroup]);
        return $retrievedLayouts;

    }


    public function summaryPermsForLayout($userId, $orgId, $layoutId){
/*
        $query = "select sum(perms.view) as viewperms, sum(perms.author) as authorperms, sum(perms.admin) as adminperms, ".
            " sum(perms.opt1) as opt1perms, sum(perms.opt2) as opt2perms, sum(perms.opt3) as opt3perms ".
            "from groups, perms, users, usergroup, userorg, org ".
            "where groups.id = perms.group_id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and org.id = ? ".
            "and users.id=? ".
            "and perms.layout_id = ?";
*/
        $query = "select sum(perms.view) as viewperms, sum(perms.author) as authorperms, sum(perms.admin) as adminperms, ".
            "sum(perms.opt1) as opt1perms, sum(perms.opt2) as opt2perms, sum(perms.opt3) as opt3perms ".
            "from perms, `groups`, usergroup, grouporg ".
            "where groups.id = perms.group_id ".
            "and usergroup.user_id = ? ".
            "and usergroup.group_id = groups.id ".
            "and perms.group_id = groups.id ".
            "and grouporg.group_id = groups.id ".
            "and grouporg.org_id = ? ".
            "and perms.layout_id = ?";

//        $retrievedPerms  =  DB::select($query, [$orgId, $userId, $layoutId]);
        $retrievedPerms  =  DB::select($query, [$userId, $orgId, $layoutId]);
        return $this->booleanPerms($retrievedPerms[0]);
    }


    protected function booleanPerms($perms){
        $returnPerms = array('view'=>false, 'author'=>false, 'admin'=>false, 'opt1'=>false, 'opt2'=>false, 'opt3'=>false);
        if($perms->viewperms>0){
            $returnPerms['view']=true;
        }
        if($perms->authorperms>0){
            $returnPerms['author']=true;
        }
        if($perms->adminperms>0){
            $returnPerms['admin']=true;
        }
        if($perms->opt1perms>0){
            $returnPerms['opt1']=true;
        }
        if($perms->opt2perms>0){
            $returnPerms['opt2']=true;
        }
        if($perms->opt3perms>0){
            $returnPerms['opt3']=true;
        }
        return $returnPerms;
    }
    public function getParams($layoutId){

        $query = "select menu_label, description, height, width, backgroundType, backgroundColor, backgroundUrl, backgroundDisplay, customcss, template from layouts where id = ?";
        try {
            $retrievedParams = DB::select($query, [$layoutId]);
            return $retrievedParams;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getThisLayout($layoutId, $orgId, $userId)
    {
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
        $thisBackgroundDisplay = $layoutInfo[0]->backgroundDisplay;
        $thisLayoutImageUrl = $layoutInfo[0]->backgroundUrl;
        $thisLayoutBackgroundType = $layoutInfo[0]->backgroundType;
        $thisLayoutLabel = $layoutInfo[0]->menu_label;
        $thisLayoutTemplate = $layoutInfo[0]->template;
        $thisCardInstance = new CardInstances;
        $thisLayoutCardInstances = $thisCardInstance->getLayoutCardInstancesById($layoutId, $orgId);
        if ($thisLayoutCardInstances == null) {
            $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutWidth, 'backgroundColor' => $thisLayoutBackgroundColor, 'backgroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType, 'backgroundDisplay'=>$thisBackgroundDisplay);
            $isAdmin = $this->isUserSuperAdmin($userId);
            if($isAdmin==0){
                $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
            }else{
                $thisLayoutPerms = array('view'=>true, 'author'=>true, 'admin'=>true, 'opt1'=>true, 'opt2'=>true, 'opt3'=>true);
            }
            $returnData = array('cards' => [], 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
            return $returnData;
        }
        $cardsReadIn = array();
        $cardSubElementProperties = array();
        $allCardInstances = array();
        foreach ($thisLayoutCardInstances as $card) {
            $thisId = strval($card->id);
            $thisCardData = array($card->parameter_key, $card->parameter_value, $card->isCss, $card->card_component, $card->col, $card->row, $card->height, $card->width, $card->id);
            if ($card->dom_element == 'main') {
                if (!array_key_exists($thisId, $cardsReadIn)) {
                    $cardsReadIn[$thisId] = [$thisCardData];
                } else {
                    array_push($cardsReadIn[$thisId], $thisCardData);
                }
            } else {
                if (!array_key_exists($thisId, $cardSubElementProperties)) {
                    $cardSubElementProperties[$thisId][$card->dom_element] = array();
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                } else {
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                }
            }
        }
        foreach ($cardsReadIn as $thisCardArray) {
            $thisCardCss = "";
            $thisCardProperties = "";
            $thisCardContent = array();
            foreach ($thisCardArray as $thisCard) {
                if ($thisCard[2] == 1) {
                    $thisCardCss = $thisCardCss . $thisCard[1];
                } else {
                    $thisCardProperties = $thisCardProperties . $thisCard[1];
                    $thisCardContent[$thisCard[0]] = $thisCard[1];
                }
                $thisCardIsCss = $thisCard[2];
                $thisCardParameterKey = $thisCard[0];
                $thisCardComponent = $thisCard[3];
                if ($thisCardComponent == "linkMenu" | $thisCardComponent == "Headline" | $thisCardComponent == "NavigationMenu" | $thisCardComponent== "imageCard") {
                    $thisLink = new link();
                    $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                    $thisCardContent['availableLinks'] = $cardLinks;
                }
                $thisCardCol = $thisCard[4];
                $thisCardRow = $thisCard[5];
                $thisCardHeight = $thisCard[6];
                $thisCardWidth = $thisCard[7];
                $thisCardId = $thisCard[8];
            }
            $cssGridParams = $this->computeGridCss($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth) . ";";
            $thisCardInstance = new CardInstances();
            $thisCardName = $thisCardInstance->getCardName($thisCardId);
            $thisCardContent['card_name']=$thisCardName;
/*
            if(isset($thisCardContent['fileType']) && $thisCardContent['fileType']=='PDF'){
                $pdfFileLocation = base64_encode($thisCardContent['fileLocation']);
                $thisCardContent['fileLocation']=$pdfFileLocation;
            }
*/
            $thisCardParameters = array(
                'style' => $cssGridParams . $thisCardCss,
                'properties' => '',
                'content' => $thisCardContent
            );
            $thisCardPosition = array($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth);


            $thisCardData = array(
                'id' => $thisCardId,
                'card_component' => $thisCardComponent,
                'card_parameters' => $thisCardParameters,
                'card_position' => $thisCardPosition
            );
            array_push($allCardInstances, $thisCardData);
        }
        $subElementStyles = array();
        foreach ($cardSubElementProperties as $key => $value) {
            $cardSubElement = $value;
            $cardId = $key;
            $thisSubElementStyle = '';
            foreach ($cardSubElement as $key => $value) {
                foreach ($value as $styleElement) {
                    $thisSubElementStyle = $thisSubElementStyle . $styleElement[1];
                }
                if (!array_key_exists($cardId, $subElementStyles)) {
                    $subElementStyles[$cardId][$key] = array();
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                } else {
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                }
            }
        }
        foreach ($allCardInstances as $key => $value) {
            $thisCardId = $key;
            foreach ($subElementStyles as $key => $value) {
                if ($allCardInstances[$thisCardId]['id'] == $key) {
                    $allCardInstances[$thisCardId]['elementStyles'] = $value;
                }
            }
        }
        $isAdmin = $this->isUserSuperAdmin($userId);
        if($isAdmin==0){
            $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        }else{
            $thisLayoutPerms = array('view'=>true, 'author'=>true, 'admin'=>true, 'opt1'=>true, 'opt2'=>true, 'opt3'=>true);
        }

        $thisGroup = new Group;
        $lg = $thisGroup->getLayoutGroupId($layoutId);
        if($lg<0){
            $isLayoutGroup = 0;
        }else{
            $isLayoutGroup = 1;
        }


        $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutWidth, 'backgroundColor' => $thisLayoutBackgroundColor, 'backgroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType, 'template'=>$thisLayoutTemplate, 'isLayoutGroup'=>$isLayoutGroup, 'backgroundDisplay'=>$thisBackgroundDisplay );
        $returnData = array('cards' => $allCardInstances, 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
        return $returnData;
    }
/*
    public function getCardInLayoutDimensions($cardInstanceId, $layoutId){
        $query = "select row, column, height, width from card_inLayout where  card_instance_id = ? and layout_id = ?";
        try {
            $cardDimensions = DB::select($query, [$cardInstanceId, $layoutId]);
            return $cardDimensions;
        } catch (\Exception $e) {
            throw new Exception('error getting card in layout dimensionsl'.$e->getMessage());
        }
    }
*/
    public function setBackgroundUrl($backgroundUrl, $layoutId){
        $query = 'update layouts set backgroundUrl = ? where id = ?';
        try {
            $recordsUpdated = DB::select($query, [$backgroundUrl, $layoutId]);
            return $recordsUpdated;
        } catch (\Exception $e) {
            throw new Exception('error updating backgroundUrl'.$e->getMessage());
        }
    }


    public function publishThisLayout($layoutId, $orgId, $userId, $imageDirectory, $publishableLayouts)
    {

//        $dynamicAddress = 'http://localhost:8080/target/';
//        $staticAddress = 'http://localhost/spaces/';
        $thisConstants = new Constants;
        $dynamicAddress = $thisConstants->Options['dynamicAddress'];
        $staticAddress = $thisConstants->Options['staticAddress'];


//    public function getLayoutById(Request $request){
//        $inData =  $request->all();
//        $layoutId = $inData['layoutId'];
//        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $layoutInstance = new Layout;
        $layoutInfo = $layoutInstance->where('id', $layoutId->layout_id)->get();
        $thisLayoutDescription = $layoutInfo[0]->description;
        $thisLayoutWidth = $layoutInfo[0]->width;
        $thisLayoutHeight = $layoutInfo[0]->height;
        $thisLayoutBackgroundColor = $layoutInfo[0]->backgroundColor;
        $thisLayoutImageUrl = $layoutInfo[0]->backgroundUrl;
        $thisLayoutBackgroundType = $layoutInfo[0]->backgroundType;
        $thisLayoutLabel = $layoutInfo[0]->menu_label;
        $hzLinkMenuColor='';
        $thisCardInstance = new CardInstances;
        $thisLayoutCardInstances = $thisCardInstance->getLayoutCardInstancesById($layoutId->layout_id, $orgId);
        if ($thisLayoutCardInstances == null) {
            $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutHeight, 'backgroundColor' => $thisLayoutBackgroundColor, 'backgroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType);
            $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId->layout_id);
            $returnData = array('cards' => [], 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
            return $returnData;
        }
        $cardsReadIn = array();
        $cardSubElementProperties = array();
        $allCardInstances = array();
        foreach ($thisLayoutCardInstances as $card) {
            $thisId = strval($card->id);
            $thisCardData = array($card->parameter_key, $card->parameter_value, $card->isCss, $card->card_component, $card->col, $card->row, $card->height, $card->width, $card->id);
            if ($card->dom_element == 'main') {
                if (!array_key_exists($thisId, $cardsReadIn)) {
                    $cardsReadIn[$thisId] = [$thisCardData];
                } else {
                    array_push($cardsReadIn[$thisId], $thisCardData);
                }
            } else {
//                $cardSubElementProperties[$thisId][$card->dom_element] = $thisCardData;

                if (!array_key_exists($thisId, $cardSubElementProperties)) {
                    $cardSubElementProperties[$thisId][$card->dom_element] = array();
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                } else {
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                }

            }
        }
        foreach ($cardsReadIn as $thisCardArray) {

/*
            $thisCardCss = "";
            $thisCardProperties = "";
            $thisCardContent = array();
            foreach ($thisCardArray as $thisCard) {
                if ($thisCard[2] == 1) {
                    $thisCardCss = $thisCardCss . $thisCard[1];
                } else {
                    $thisCardProperties = $thisCardProperties . $thisCard[1];
                    $thisCardContent[$thisCard[0]] = $thisCard[1];
                }
                $thisCardIsCss = $thisCard[2];
                $thisCardParameterKey = $thisCard[0];
                $thisCardComponent = $thisCard[3];
                if ($thisCardComponent == "linkMenu") {
                    $thisLink = new link();
                    $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                    $thisCardContent['availableLinks'] = $cardLinks;
                }
                $thisCardCol = $thisCard[4];
                $thisCardRow = $thisCard[5];
                $thisCardHeight = $thisCard[6];
                $thisCardWidth = $thisCard[7];
                $thisCardId = $thisCard[8];
            }
            if($thisCardComponent=='RichText'){
                $orgDirectory = '/images/'.$orgId;
                $thisLink = new link();
                $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                if(isset($thisCardContent['cardText'])){
                    $content = $thisCardContent['cardText'];
                    foreach($cardLinks as $thisCardLink){
                        if($thisCardLink->type=="U"){
                            $linkIsPublishable = false;
                            foreach($publishableLayouts as $thisPublishableLayout){
                                if($thisPublishableLayout->id == $thisCardLink->layout_link_to){
                                    $linkIsPublishable = true;
                                    break;
                                }
                            }
                            if($linkIsPublishable){
                                $newLink = $dynamicAddress.'/'.$thisCardLink->layout_link_to;

                            }else{
                                $newLink = $staticAddress.$orgId.'/'.$thisCardLink->layout_link_to.'.html';
                            }
                            $content = str_replace($thisCardLink->link_url, $newLink, $content);
                        }else if($thisCardLink->type=="I"){
                            $imageLink = $thisCardLink->link_url;
                            $imageFileNameAt = strpos($imageLink, 'images/'.$orgId.'/');
                            if($imageFileNameAt!=false){
                                $imageFileNameAt = strlen('http://localhost:8000/images/'.$orgId.'/');
                                $imageFileName = substr($imageLink, $imageFileNameAt);
                                $imageSource = $orgDirectory.'/'.$imageFileName;
                                $copyToLocation = '/published/'.$orgId.'/images'.'/'.$imageFileName;
                                Storage::copy($imageSource, $copyToLocation);

                            }

                        }
                    }
                    $thisCardContent['cardText']= $content;

                }else{
                    $thisCardContent['cardText']='';
                }

            }
            $cssGridParams = $this->computeGridCss($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth) . ";";
            $thisCardParameters = array(
                'style' => $cssGridParams . $thisCardCss,
                'properties' => $thisCardProperties,
                'content' => $thisCardContent
            );




            $thisCardPosition = array($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth);
            $thisCardData = array(
                'id' => $thisCardId,
                'card_component' => $thisCardComponent,
                'card_parameters' => $thisCardParameters,
                'card_position' => $thisCardPosition
            );
*/
            $thisSpCard = new SpCard($thisCardArray, $orgId, $publishableLayouts, $cardSubElementProperties, $layoutId);
            $thisCardData = $thisSpCard->getCardData();
            array_push($allCardInstances, $thisCardData);
        }
        $subElementStyles = array();
        foreach ($cardSubElementProperties as $key => $value) {
            $cardSubElement = $value;
            $cardId = $key;
            $thisSubElementStyle = '';
            foreach ($cardSubElement as $key => $value) {
                foreach ($value as $styleElement) {
                    if($styleElement[0]=='color' && $styleElement[3]=='linkMenu'){
                        $hzLinkMenuColor=$styleElement[1];
                    }else{
                        $thisSubElementStyle = $thisSubElementStyle . $styleElement[1];
                    }

                }
                if (!array_key_exists($cardId, $subElementStyles)) {
                    $subElementStyles[$cardId][$key] = array();
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                } else {
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                }
            }
        }
        foreach ($allCardInstances as $key => $value) {
            $thisCardId = $key;
            foreach ($subElementStyles as $subKey => $subValue) {
                if ($allCardInstances[$thisCardId]['id'] == $subKey) {
                    $realValue = $this->extractElementStyles($subValue);
                    $allCardInstances[$thisCardId]['elementStyles'] = $realValue;
                    $test= $allCardInstances[$thisCardId]['elementStyles'];
                }
            }
        }
        $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId->layout_id);
        $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutHeight, 'backgroundColor' => $thisLayoutBackgroundColor, 'backgroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType, 'hzLinkMenuColor'=>$hzLinkMenuColor);
        $returnData = array('cards' => $allCardInstances, 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
        return $returnData;
    }
    private function isUserSuperAdmin($userId){
        $query = 'select is_admin from users where id = ? and is_admin=1';
        try {
            $isAdminValues = DB::select($query, [$userId]);
            return count($isAdminValues);
        } catch (\Exception $e) {

        }


    }
    private function extractElementStyles($val){
        $result = array();
        foreach($val as $key=>$value){
            $result[$key]=$value[0];
        }
        return $result;
    }

    public function setDelete($layoutId){
        $query = "update layouts set deleted = 'Y' where id=?";

        try {
            DB::select($query, [$layoutId]);
        } catch (\Exception $e) {

        }

    }

    public function setUnDelete($layoutId){
        $query = "update layouts set deleted = ' ' where id=?";

        try {
            DB::select($query, [$layoutId]);
        } catch (\Exception $e) {

        }
    }

    public function isDeleted($layoutId){
        $query = "select deleted from layouts where id=?";
        try {
            $layoutIsDeleted = DB::select($query, [$layoutId]);
        } catch (\Exception $e) {

        }
        if(!isset($layoutIsDeleted)){
            return false;
        }
        if($layoutIsDeleted==NULL){
            return false;
        }
        if($layoutIsDeleted[0]->deleted == 'Y'){
            return true;
        }else{
            return false;
        }
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
    public function getLayoutDescription($layoutId){
        $query = "select description, menu_label, template from layouts where id = ?";
        try {
            $selectedLayouts = DB::select($query, [$layoutId]);
            return $selectedLayouts;
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' fetching layout description: '.$layoutId);
        }

    }

    public function updateCardInLayout(){
        return;   // don't want to run this again
        $query = "select * from card_instances";
        try {
            $allCardInstances = DB::select($query);
            foreach($allCardInstances as $thisCardInstance){
                $cardId = $thisCardInstance->id;
                $row = $thisCardInstance->row;
                $col = $thisCardInstance->col;
                $height = $thisCardInstance->height;
                $width = $thisCardInstance->width;
                $layout_id = $thisCardInstance->layout_id;
                $thisCardInLayoutId = DB::table('card_in_layout')->insertGetId([
                    'card_instance_id'=>$cardId,
                    'layout_id'=>$layout_id,
                    'width'=>$width,
                    'height'=>$height,
                    'col'=>$col,
                    'row'=>$row,
                    'created_at'=>\Carbon\Carbon::now(),
                    'updated_at'=>\Carbon\Carbon::now()
                ]);
            }

        } catch (\Exception $e) {

        }
    }
    public function getLayoutInfoById($layoutId){
        $query = "select description, menu_label, height, width from layouts where id = ?";
        try {
            $layoutSpecs = DB::select($query, [$layoutId]);
            if(count($layoutSpecs)==0){
                throw new Exception('not found');
            }else{
                return $layoutSpecs[0];
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getLayoutInfo($layoutIds, $orgId, $userId){
        $query ="select distinct layouts.id from layouts, `groups`, usergroup, users, userorg, org, perms ".
	        "where perms.layout_id = layouts.id ".
	        "and perms.group_id = groups.id ".
	        "and usergroup.group_id = groups.id ".
	        "and usergroup.user_id = users.id ".
	        "and userorg.user_id = users.id ".
	        "and userorg.org_id = org.id ".
	        "and perms.view=1 ".
	        "and org.id = ? ".
	        "and users.id=?	";
        try {
            $viewableLayouts = DB::select($query, [$orgId, $userId]);
            $allViewableLayouts = array();
            foreach($viewableLayouts as $thisViewableLayout){
                array_push($allViewableLayouts, $thisViewableLayout->id);
            }
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' fetching viewableLayouts');
        }


        $query = "select id, menu_label, description from layouts where id in (".$layoutIds.")";
        try {
            $selectedLayouts = DB::select($query);
            $accessableSelectedLayouts = array();
            foreach($selectedLayouts as $thisSelectedLayout){
                if(in_array($thisSelectedLayout->id, $allViewableLayouts)){
                    array_push($accessableSelectedLayouts, $thisSelectedLayout);
                }
            }
            return $accessableSelectedLayouts;
        }catch (Exception $f){
            throw new Exception('error '.$f.getMessage().' fetching layout descriptions');
        }
    }

    public function getViewableLayoutCount($orgId, $userId){

        $query = "select count(distinct layouts.id) from layouts, `groups`, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and layouts.deleted != 'Y' ".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and perms.view=1 ".
            "and org.id = ? ".
            "and users.id= ? ";

        try {
            $viewableLayoutCount = DB::select($query, [$orgId, $userId]);
            $resultKey = key($viewableLayoutCount[0]);
            return $viewableLayoutCount[0]->$resultKey;
        } catch (\Exception $e) {
            throw new Exception('error '.$f.getMessage().' getting viewableLayoutCount');
        }

    }

    public function getMyViewableLayoutIdsPaged($orgId, $userId, $limit, $offset){


        $query = "select layouts.id, layouts.menu_label, layouts.description, layouts.menu_label, layouts.height, layouts.width from layouts ".
            "where layouts.id in ( ".
            "select distinct layouts.id from layouts, `groups`, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and layouts.deleted != 'Y' ".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and perms.view=1 ".
            "and org.id = ? ".
            "and users.id= ? ".
            ") limit ? ".
            " offset ?";

        $retrievedLayouts  =  DB::select($query, [$orgId, $userId, $limit, $offset]);
        return $retrievedLayouts;

    }


}
