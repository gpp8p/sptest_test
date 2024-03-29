<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Org extends Model
{
    public function getOrgId($orgName){



        try {
            $thisOrgValue = DB::table('org')->where('org_label', $orgName)->first();
            return $thisOrgValue->id;
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
    public function getRegistrationRestricted($orgId)
    {
        $query = "select registration_restricted from org where id = ?";
        try {
            $orgRestricted = DB::select($query, [$orgId]);
            return $orgRestricted;
        } catch (Exception $e) {
            throw new Exception('error in orgRestricted' . $e->getMessage());
        }
    }
    public function isRegistrationPermitted($orgId, $userEmail){
        $query = "select id from register_permitted where org_id = ? and email =?";
        try {
            $registrationPermittedList = DB::select($query, [$orgId, $userEmail]);
            if (count($registrationPermittedList) > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new Exception('error in isRegistrationP{ermitted -' . $e->getMessage());
        }
    }
    public function setOrgRestricted($orgId, $registrationRestricted){
        $query = "update org set registration_restricted = ? where id = ?";
        try {
            $rcdsUpdated = DB::select($query, [$registrationRestricted, $orgId]);
            $message = 'setOrgRestricted rcdsUpdated:';
            Log::debug($message);

            return $rcdsUpdated;
        } catch (Exception $e) {
            $message = 'setOrgRestricted error:'.$e->getMessage();
            Log::debug($message);
            throw new Exception('error in setOrgRestricted'.$e->getMessage());
        }
    }
    public function newAllowedRegistrant($orgId, $userName, $userEmail){
        $query = "insert into register_permitted (org_id, name, email) values (?,?,?)";
        try {
            DB::select($query,[$orgId, $userName, $userEmail]);
            return;
        } catch (\Exception $e) {
            throw new Exception('error inserting allowed registrants'.$e->getMessage());
        }
    }
    public function updateAllowedRegistrant($regId, $userName, $userEmail){
        $query = "update register_permitted set name = ?, email = ? where id = ?";
        try {
            DB::select($query,[$userName, $userEmail, $regId]);
            return;
        } catch (\Exception $e) {
            throw new Exception('error updating allowed registrants'.$e->getMessage());
        }
    }
    public function deleteAllowedRegistrant($regId){
        $query = "delete from register_permitted where id = ?";
        try {
            DB::select($query,[$regId]);
            return;
        } catch (\Exception $e) {
            throw new Exception('error deleting allowed registrant'.$e->getMessage());
        }
    }
    public function getAllowedRegistrants($orgId){
        $query = "select id, name, email from register_permitted where org_id = ?";
        try {
            $allowedRegistrants = DB::select($query,[$orgId]);
            return $allowedRegistrants;
        } catch (\Exception $e) {
            throw new Exception('error fetching allowed registrants'.$e->getMessage());
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
    public function isUserInOrg($userId, $orgId){
        $query = "select id from userorg where user_id = ? and org_id = ?";
        try {
            $orgUserList = DB::select($query,[$userId, $orgId]);
            if(count($orgUserList)>0){
                return true;
            }else{
                return false;
            }
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
