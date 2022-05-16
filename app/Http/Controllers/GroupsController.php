<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Org;
use Illuminate\Support\Facades\DB;

class GroupsController extends Controller
{
    public function getGroupMembers(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $thisGroupId = $inData['groupId'];
        $groupInstance = new Group;
        $members = $groupInstance->getUsersInGroup($thisGroupId);
        $groupInfo = $groupInstance->getGroupInfo($thisGroupId);
        $groupAdmin=FALSE;
        foreach($members as $thisMember){
            if($thisMember->id==$userId && $thisMember->is_admin==1) $groupAdmin=TRUE;
        }
        return json_encode(['members'=>$members, 'groupAdmin'=>$groupAdmin, 'groupDescription'=>$groupInfo[0]->description]);
    }

    public function getOrgGroups(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $thisOrgId = $inData['orgId'];
        $thisLayoutId = $inData['layoutId'];
        $groupInstance = new Group;
        $groups = $groupInstance->getOrganizationGroups($thisOrgId, $userId, $thisLayoutId);
        return json_encode($groups);
    }

    public function addUserToGroup(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $superAdmin = auth()->user()->is_admin;
        $notAdmin = 0;
        $inData =  $request->all();
        $groupId = $inData['params']['groupId'];
//        $orgId = $inData['params']['orgId'];
        $selectedUserId = $inData['params']['selectedUserId'];
        $groupInstance = new Group;
        $isAdmin = $groupInstance->isUserGroupAdmin($userId, $groupId);
        if(!$isAdmin && !$superAdmin){
            abort(401, "Not permitted -".$userId." must be group admin");
        }
        try {
            $groupInstance->addUserToGroup($selectedUserId, $groupId, $notAdmin);
            return "ok";
        }catch (Throwable $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }
    }

    public function removeUserFromGroup(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $groupId = $inData['params']['groupId'];
        $selectedUserId = $inData['params']['selectedUserId'];
        $groupInstance = new Group;
        try {
            $groupInstance->removeUserFromGroup($selectedUserId, $groupId);
            return "ok";
        } catch (Throwable $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }
    }

    public function removeUserFromOrg(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $userId = $inData['userId'];
        $groupInstance = new Group;
        $orgInstance = new Org;
        try {
            $orgGroups = $groupInstance->findOrgGroups($orgId);
        } catch (\Exception $e) {
            abort(500, 'Error finding oprg groups: '.$e->getMessage());
        }
        $allUserGroup = $groupInstance->allUserId();
        if(count($orgGroups)>0){
            $orgGroupIds = "(";
            foreach($orgGroups as $thisOrgGroup){
                if($thisOrgGroup->id!=$allUserGroup){
                    $orgGroupIds = $orgGroupIds.$thisOrgGroup->id.",";
                }
            }
            $orgGroupIds = substr($orgGroupIds, 0, -1);
            $orgGroupIds = $orgGroupIds.")";
            try {
                DB::beginTransaction();
                $groupInstance->removeUserFromGroups($userId, $orgGroupIds);
                $orgInstance->removeUserFromUserOrg($orgId, $userId);
                DB::commit();
                return "ok";
            } catch (\Exception $e) {
                DB::rollback();
                abort(500, 'Error deleteing user from usergroups '.$e->getMessage());
            }
        }else{
            try {
                $orgInstance->removeUserFromUserOrg($orgId, $userId);
                return "ok";
            } catch (\Exception $e) {
                abort(500, 'Error deleteing user from usergroups '.$e->getMessage());
            }

        }
    }
}
