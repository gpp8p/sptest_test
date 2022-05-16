<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    public function addNewLayoutGroup($layoutId, $layoutLabel, $layoutDescription){
        $thisGroupDescription = "Layout Group for ".$layoutLabel;
        $thisGroupLabel = $layoutLabel;
        $thisGroupId = DB::table('groups')->insertGetId([
            'group_label'=>$thisGroupLabel,
            'description'=>$thisGroupDescription,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
// add new perms here!!!
        return $thisGroupId;
    }
    public function getLayoutGroupId($layoutId){
        $query = "select group_id from perms where group_id in (select group_id from perms where isLayoutGroup=1) and layout_id = ?";
//        $query = "select group_id from perms where layout_id = ? and isLayoutGroup = 1";
        try {
            $queryResult = DB::select($query, [$layoutId]);
            if(count($queryResult)>0){
                return $queryResult[0]->group_id;
            }else{
                return -1;
            }
        } catch (\Exception $e) {
            throw e;
        }
    }
    public function setGroupLayout($groupId, $layoutId){
        $query = "update perms set isLayoutGroup=1 where group_id = ? and layout_id = ? ";
        try {
            $queryResult = DB::select($query, [$groupId, $layoutId]);
        } catch (\Exception $e) {
            throw e;
        }

    }
    public function returnPersonalGroupId($userId){
        $query = "select groups.id from `groups`, users, usergroup ".
            "where groups.group_label = users.email ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and users.id = ?";

        $personalGroupId  =  DB::select($query, [$userId]);
        return $personalGroupId[0]->id;
    }
    public function returnAllUserGroupId(){
        $query = "select id from `groups` where group_label = 'AllUsers'";
        $allUserGroupId = DB::select($query);
        return $allUserGroupId[0]->id;

    }

    public function addUserToGroup($userId, $groupId, $isAdmin){
        DB::table('usergroup')->insert([
            'group_id'=>$groupId,
            'user_id'=>$userId,
            'is_admin'=>$isAdmin,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
    }

    public function removeUserFromGroup($userId, $groupId){
        $query = "delete from usergroup where group_id = ? and user_id = ?";
        $queryResult = DB::select($query, [$groupId, $userId]);
        return;

    }

    public function removeUserFromGroups($userId, $groupList){
        $query = "delete from usergroup where user_id = ? and usergroup.group_id in ".$groupList;
        $queryResult = DB::select($query, [$userId]);
    }

    public function addNewPersonalGroup($userName, $userEmail){
        $thisGroupId = DB::table('groups')->insertGetId([
            'group_label'=>$userEmail,
            'description'=>$userName." personal group",
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        return $thisGroupId;
    }

    public function addOrgToGroup($orgId, $groupId){
        DB::table('grouporg')->insert([
            'group_id'=>$groupId,
            'org_id'=>$orgId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
    }

    public function getUsersInGroup($groupId){
        $query = "select users.id, users.name, users.email, usergroup.is_admin from users, usergroup ".
            "where users.id = usergroup.user_id ".
            "and usergroup.group_id=?";
        $users  =  DB::select($query, [$groupId]);
        return $users;

    }

    public function getGroupInfo($groupId){
        $query = "select group_label, description from `groups` where id = ?";
        $groupInfo = DB::select($query, [$groupId]);
        return $groupInfo;
    }

    public function getOrganizationGroups($orgId, $userId, $layoutId){
        /*
                $query = "select group_label, description, groups.id from groups, grouporg ".
                        "where grouporg.group_id = groups.id ".
                        "and grouporg.org_id = ?";
        */
        $query = "select group_label, description, groups.id from `groups`, grouporg ".
            "where grouporg.group_id = groups.id ".
            "and grouporg.org_id = ? ".
            "and groups.id NOT IN ( ".
            "select groups.id ".
            "from `groups`, perms, users, usergroup, userorg, org ".
            "where groups.id = perms.group_id ".
            "and usergroup.group_id = groups.id  ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and org.id = ? ".
            "and users.id=? ".
            "and perms.layout_id = ? ".
            ")";

        $groups  =  DB::select($query, [$orgId, $orgId, $userId, $layoutId]);
        return $groups;
    }
    public function allUserId(){
        $query = "select id from `groups` where description='All Users'";
        $allUserGroupId  =  DB::select($query);
        return $allUserGroupId[0]->id;
    }

    public function findOrgGroups($orgId){
        $query = "select distinct groups.id from `groups`, grouporg where groups.id in (select grouporg.group_id from grouporg where grouporg.org_id=?)  ";
        $orgGroups = DB::select($query, [$orgId]);
        return $orgGroups;
    }
    public function isUserGroupAdmin($userId, $groupId){
        $query = "select is_admin from usergroup where user_id = ? and group_id = ?";
        try {
            $isAdmin = DB::select($query, [$userId, $groupId]);
        } catch (\Exception $e) {
            throw new Exception($e);
        }
        return $isAdmin;
    }
}
