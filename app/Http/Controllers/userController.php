<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Org;
use App\Layout;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class userController extends Controller
{
    public function setupNewUser(Request $request){
        $inData = $request->all();
        $userName = $inData['params']['name'];
        $userEmail = $inData['params']['email'];
        $userPassword = $inData['params']['password'];
        $userOrg = $inData['params']['org'];
        $thisUserInstance = new User;
        try {
            $existingUser = $thisUserInstance->checkUserOrgMembership($userEmail);
        } catch (\Exception $e) {
            abort(500, 'Server error looking up user: '.$e->getMessage());
        }
        if(count($existingUser)>0){
            $returnData = $returnData = array('result'=>'userFound', 'userName'=>$userName, 'userEmail'=>$userEmail, 'userId'=>$existingUser->userId);
            return json_encode($returnData);

        }

/*
        try {
            $existingUser = $thisUserInstance->checkUserOrgMembership($userEmail);
        } catch (\Exception $e) {
            abort(500, 'Server error looking up user: '.$e->getMessage());
        }
        if(count($existingUser)>0){
            foreach($existingUser as $thisExistingUser){
                if($thisExistingUser->orgId==$userOrg){
                    $userFoundInfo = ['result'=>'userFound', 'userOrg'=>$thisExistingUser->description];
                    return json_encode($userFoundInfo);
                }
            }
            $thisOrg = new Org;
            try {
                $thisOrg->addUserToOrg($userOrg, $existingUser[0]->userId);
                $userFoundInfo = ['result'=>'userAddedToOrg'];
                return json_encode($userFoundInfo);
            } catch (\Exception $e) {
                abort(500, 'Server error adding user to org: '.$e->getMessage());
            }
        }
*/
        $newUserId=null;
        $userIsAdmin = 1;
        $userNotAdmin = 0;
        DB::beginTransaction();
        try {
            try {
                $newUserId = $thisUserInstance->createUser($userEmail, $userName, $userPassword);
            } catch (Exception $e) {
                throw $e;
            }
            $thisGroupInstance = new Group;
            try {
                $newPersonalGroupId = $thisGroupInstance->addNewPersonalGroup($userName, $userEmail);
            } catch (Exception $e) {
                throw $e;
            }
            try {
                $thisGroupInstance->addUserToGroup($newUserId, $newPersonalGroupId, $userIsAdmin);
            } catch (Exception $e) {
                throw $e;
            }
            $allUserGroupId = DB::table('groups')->where('group_label', 'AllUsers')->first()->id;
            try {
                $thisGroupInstance->addUserToGroup($newUserId, $allUserGroupId, $userNotAdmin);
            } catch (Exception $e) {
                throw $e;
            }
        }catch (\Exception $e) {
            DB::rollback();
            abort(500, 'Server error creating user: '.$e->getMessage());
        }
        $thisOrg = new Org;

        try {
            $thisOrg->addUserToOrg($userOrg, $newUserId);
            DB::commit();
            return response()->json([
                'result' => 'ok',
                'email' => $userEmail,
                'userId' => $newUserId,
                'userName' => $userName
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            abort(500, 'Server error adding user to org: '.$e->getMessage());
        }


    }
    public function checkUserExists(Request $request){
        $inData = $request->all();
        $userEmail = $inData['email'];
        $thisUserInstance = new User;
        try {
            $thisUser = $thisUserInstance->findUserByEmail($userEmail);
            If(count($thisUser)>0){
                $returnData = array('result'=>true, 'name'=>$thisUser[0]->name, 'email'=>$thisUser[0]->email, 'id'=>$thisUser[0]->id, 'is_admin'=>$thisUser[0]->is_admin);
            }else{
                $returnData = array('result'=>false);
            }
            return json_encode($returnData);
        } catch (\Exception $e) {
            abort(500, 'Server error checking user email: '.$e->getMessage());
        }

    }
    public function createUser(Request $request){
        $inData = $request->all();
        $userName = $inData['params']['name'];
        $userEmail = $inData['params']['email'];
        $userPassword = $inData['params']['password'];
        $thisUserInstance = new User;
        $userIsAdmin = 1;
        $userNotAdmin = 0;
        try {
            try {
                $newUserId = $thisUserInstance->createUser($userEmail, $userName, $userPassword);
            } catch (Exception $e) {
                throw $e;
            }
/*
            $thisOrgInstance = new Org;
            try {
                $thisOrgInstance->addUserToOrg($orgId, $newUserId);
            } catch (Exception $e) {
                throw $e;
            }
*/
            $thisGroupInstance = new Group;
            try {
                $newPersonalGroupId = $thisGroupInstance->addNewPersonalGroup($userName, $userEmail);
            } catch (Exception $e) {
                throw $e;
            }
            try {
                $thisGroupInstance->addUserToGroup($newUserId, $newPersonalGroupId,$userIsAdmin);
            } catch (Exception $e) {
                throw $e;
            }
            $allUserGroupId = DB::table('groups')->where('group_label', 'AllUsers')->first()->id;
            try {
                $thisGroupInstance->addUserToGroup($newUserId, $allUserGroupId,$userNotAdmin);
            } catch (Exception $e) {
                throw $e;
            }
/*
            $loggedInUserGroupId = DB::table('groups')->where('group_label', 'loggedInUsers')->first()->id;
            try {
                $thisGroupInstance->addUserToGroup($newUserId, $loggedInUserGroupId);
            } catch (Exception $e) {
                throw $e;
            }
*/
            return response()->json([
                'result'=>'ok',
                'email'=>$userEmail,
                'userId'=>$newUserId,
                'userName'=>$userName
            ]);
        } catch (Exception $e) {
            return response()->json([
                'result'=>'error',
                'errorDescription'=>$e>getMessage()
            ]);
        }
    }
    public function updatePassword(Request $request){
        $inData = $request->all();
        $userEmail = $inData['params']['email'];
        $userPassword = $inData['params']['password'];
        $encodedPassword = Hash::make($userPassword);
        $userInstance = new User;
        try {
            $userInstance->updatePassword($userEmail, $encodedPassword);
            return 'ok';
        } catch (\Exception $e) {
            abort(500, 'problem updating password');
        }
    }

    public function findUserByEmail(Request $request){
        $inData = $request->all();
        $userEmail = $inData['email'];

        $userInstance = new User;
        $userFound = $userInstance->findUserByEmail($userEmail);
        return json_encode($userFound);

    }

    public function addUserToOrg(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $userId = $inData['userId'];
        $thisOrg = new Org();
        try {
            $thisOrg->addUserToOrg($orgId, $userId);
        } catch (\Exception $e) {
            abort(500, 'Error adding user to org');
        }
        return "ok";

    }

}
