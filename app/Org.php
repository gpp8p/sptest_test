<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Org extends Model
{
    public function getOrgId($orgName){

        try {
            $thisOrgId = DB::table('org')->where('org_label', $orgName)->first()->value('id');
            return $thisOrgId;
        } catch (Exception $e) {
            throw new Exception('org not found');
        }

    }

    public function getOrgHomeOld($orgName){
        try {
            $thisOrgHome = DB::table('org')->where('org_label', $orgName)->value('top_layout_id');
            return $thisOrgHome;
        } catch (Exception $e) {
            throw new Exception('org not found');
        }

    }

    public function getOrgHome($orgName){
        $query = "select id, top_layout_id from org where org_label = ?";
        try {
            $orgInfo = DB::select($query, [$orgName]);
            return $orgInfo;
        } catch (Exception $e) {
            throw new Exception('error - org not found');
        }
    }

    public function getOrgHomeFromOrgId($orgId){
        $query = "select id, top_layout_id from org where org.id = ?";
        try {
            $orgInfo = DB::select($query, [$orgId]);
            return $orgInfo;
        } catch (Exception $e) {
            throw new Exception('error - org not found');
        }
    }

    public function getOrgList(){
        $query = "select * from org ";
        try {
            $orgList = DB::select($query);
            return $orgList;
        } catch (Exception $e) {
            throw new Exception('error in orgList'.$e->getMessage());
        }
    }

    public function getOrgUsers($orgId){
//        $query = "select * from userorg, users where users.id = userorg.user_id and userorg.org_id = ?";
        $query = "select users.id, users.name, users.email from userorg, users where users.id = userorg.user_id and userorg.org_id = ?";
        try {
            $orgUserList = DB::select($query,[$orgId]);
            return $orgUserList;
        } catch (\Exception $e) {
            throw new Exception('error in orgUserList'.$e->getMessage());
        }
    }


    public function getAvailableOrgUsers($groupId, $orgId){
        $query="select users.id, users.name, users.email from userorg, users ".
            "where users.id = userorg.user_id ".
            "and userorg.org_id = ? ".
            "and users.id NOT IN ( ".
	        "select users.id from users, usergroup where users.id = usergroup.user_id and usergroup.group_id=? ".
            ")  ";
        try {
            $orgUserList = DB::select($query,[$orgId, $groupId]);
            return $orgUserList;
        } catch (\Exception $e) {
            throw new Exception('error in orgUserList'.$e->getMessage());
        }
    }

    public function getAvailableUsers($orgId){
        $query = "select distinct users.name, users.email, users.id from users, userorg ".
                  "where users.id not in ".
                  "(select users.id from userorg, users ".
                  "where users.id = userorg.user_id and userorg.org_id = ? ".
                  ")";
       try {
            $availableUsers = DB::select($query,[$orgId]);
            return $availableUsers;
        } catch (\Exception $e) {
            throw new Exception('error in orgUserList'.$e->getMessage());
        }
    }

    public function createNewOrg($orgName, $orgDescription, $topLayoutId){
        $thisOrgId = DB::table('org')->insertGetId([
            'org_label'=>$orgName,
            'description'=>$orgDescription,
            'top_layout_id'=>$topLayoutId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        return $thisOrgId;

    }

    public function addUserToOrg($orgId, $userId){
        DB::table('userorg')->insert([
            'org_id'=>$orgId,
            'user_id'=>$userId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
    }

    public function removeUserFromUserOrg($orgId, $userId){
        $query = "delete from userorg where org_id = ? and user_id = ?";
        $queryResult = DB::select($query, [$orgId, $userId]);
        return;
    }

}
