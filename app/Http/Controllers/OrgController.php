<?php

namespace App\Http\Controllers;

use App\Group;
use App\Org;
use Illuminate\Http\Request;
use App\Layout;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Classes\Constants;
use \File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OrgController extends Controller
{
    public function getOrgIdFromName(Request $request){
        $inData = $request->all();
        $orgName = $inData['orgName'];
        $thisOrg = new Org();
        try {
            $thisOrgId = $thisOrg->getOrgId($orgName);
            return response()->json([
                'orgId'=>$thisOrgId,
                'result'=>'ok'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'result'=>'error',
                'errorDescription'=>$e>getMessage()
            ]);
        }
    }
    public function getOrgHome(Request $request){
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $thisOrg = new Org();
        try {
            $orgHomeId = $thisOrg->getOrgHomeFromOrgId($orgId);
            return $orgHomeId[0]->top_layout_id;
        } catch (\Exception $e) {
            abort(500, 'org home id error'.$e);
        }
    }

    public function getOrgHomeFromName(Request $request){
        $inData = $request->all();
        $orgName = $inData['orgName'];
        $userId = $inData['userId'];
        $thisOrg = new Org();
        try {
            $thisOrgInfo = $thisOrg->getOrgHome($orgName);
        } catch (\Exception $e) {
            return response()->json([
                'result'=>'error',
                'errorDescription'=>$e>getMessage()
            ]);
        }
        $thisLayout = new Layout;
        try {
            $layoutPerms = $thisLayout->summaryPermsForLayout($userId, $thisOrgInfo[0]->id, $thisOrgInfo[0]->top_layout_id);
            return response()->json([
                'orgHome'=>$thisOrgInfo[0]->top_layout_id,
                'orgId'=>$thisOrgInfo[0]->id,
                'perms'=>$layoutPerms,
                'result'=>'ok',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result'=>'error',
                'errorDescription'=>$e>getMessage()
            ]);
        }
    }


    public function getOrgList(Request $request){
        $thisOrg = new Org();
        $allOrgs = $thisOrg->getOrgList();
        return json_encode($allOrgs);

    }

    public function getOrgUsers(Request $request){
        $message = 'getOrgUsers:';
        Log::debug($message);

        $inData = $request->all();
        $orgId = $inData['orgId'];
        $thisOrg = new Org();
        $allOrgUsers = $thisOrg->getOrgUsers($orgId);
        $thisOrgRestricted = $thisOrg->getRegistrationRestricted($orgId);
        if(count($thisOrgRestricted)==0) {
            return response()->json([
                'result'=>'error',
                'errorDescription'=>'Org not found'
            ]);
        }
        if(is_null($thisOrgRestricted[0]->registration_restricted)){
            $returnResult = ['orgUsers'=>$allOrgUsers, 'restricted'=>0];
        }else{
            $returnResult = ['orgUsers'=>$allOrgUsers, $thisOrgRestricted[0]];
        }


        return json_encode($returnResult);
    }
    public function setOrgRestrict(Request $request){
        $message = 'At setOrgRestrict:';
        Log::debug($message);
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $message = 'setOrgRestrict orgId:'.$orgId;
        Log::debug($message);
        $restricted = $inData['restricted'];
        $message = 'setOrgRestrict restrict:'.$restricted;
        Log::debug($message);

        $thisOrg = new Org();
        $rcdsUpdated = $thisOrg->setOrgRestricted($orgId, $restricted);
        return $rcdsUpdated;

    }
    public function getAvailableOrgUsers(Request $request){
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $groupId = $inData['groupId'];
        $thisOrg = new Org();
        $allOrgUsers = $thisOrg->getAvailableOrgUsers($groupId, $orgId);
        return json_encode($allOrgUsers);

    }
    public function getAllowedRegistrants(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $thisOrg = new Org();
        try {
            $allowedRegistrants = $thisOrg->getAllowedRegistrants($orgId);
            return json_encode($allowedRegistrants);
        } catch (\Exception $e) {
            abort(401, 'error fetching allowed users.'.$e->getMessage());
        }
    }
    public function saveRestrictedRegistrant(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $userName = $inData['name'];
        $userEmail = $inData['email'];
        $thisOrg = new Org();
        try {
            $thisOrg->newAllowedRegistrant($orgId, $userName, $userEmail);
            return "ok";
        } catch (\Exception $e) {
            return "error saving allowed registrant - ".$e->getMessage();
        }

    }
     public function updateAllowedRegistrant(Request $request){
         if(auth()->user()==null){
             abort(401, 'Unauthorized action.');
         }else{
             $userId = auth()->user()->id;
         }
         $inData = $request->all();
         $userName = $inData['name'];
         $userEmail = $inData['email'];
         $registrantId = $inData['regId'];
         $thisOrg = new Org();
         try {
             $thisOrg->updateAllowedRegistrant($registrantId, $userName, $userEmail);
             return "ok";
         } catch (\Exception $e) {
             return "error saving allowed registrant - ".$e->getMessage();
         }
     }
    public function deleteAllowedRegistrant(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData = $request->all();
        $registrantId = $inData['regId'];
        $thisOrg = new Org();
        try {
            $thisOrg->deleteAllowedRegistrant($registrantId);
            return "ok";
        } catch (\Exception $e) {
            return "error saving allowed registrant - ".$e->getMessage();
        }
    }
    public function registrationPermitted(Request $request){
/*
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
*/
        $inData = $request->all();
        $userEmail = $inData['userEmail'];
        $orgId = $inData['orgId'];
        $thisOrg = new Org();
        try {
            if ($thisOrg->isRegistrationPermitted($orgId, $userEmail)) {
                return 'Y';
            } else {
                return 'N';
            }
        } catch (\Exception $e) {
            return "error in registrationPermitted - ".$e->getMessage();
        }


    }
    public function allowOpenRegistration(Request $request){
/*
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
*/
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $thisOrg = new Org();
        try {
            $allowReg = $thisOrg->getRegistrationRestricted($orgId);
            if($allowReg[0]->registration_restricted==1){
                return 'N';
            }else{
                return 'Y';
            }
        } catch (\Exception $e) {
            return "error getting registrationRestricted - ".$e->getMessage();
        }
    }
     public function getAllUsers(Request $request){
         if(auth()->user()==null){
             abort(401, 'Unauthorized action.');
         }else{
             $userId = auth()->user()->id;
         }
        $thisUser = new User;
        $allUsers = $thisUser->getAllUsers();
         return json_encode($allUsers);

     }

    public function userInOrg(Request $request){
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $userId = $inData['userId'];
        $thisOrg = new Org();
        if($thisOrg->isUserInOrg($userId, $orgId)){
            return 'true';
        }else{
            return 'false';
        }

    }
    public function getAvailableUsersInOrg(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData = $request->all();
        $orgId = $inData['orgId'];
        $groupId = $inData['groupId'];
        $thisOrg = new Org();
        $allOrgUsers = $thisOrg->getAvailableOrgUsers($groupId, $orgId);
        return json_encode($allOrgUsers);


    }
     public function getAvailableUsers(Request $request){
         if(auth()->user()==null){
             abort(401, 'Unauthorized action.');
         }else{
             $userId = auth()->user()->id;
         }
         $inData = $request->all();
         $orgId = $inData['orgId'];
         $thisOrg = new Org();
         $availableUsers = $thisOrg->getAvailableUsers($orgId);
         return json_encode($availableUsers);

     }


     public function userOrgPerms(Request $request){
         $inData = $request->all();
         $orgId = $inData['orgId'];
         if(auth()->user()==null){
             abort(401, 'Unauthorized action.');
         }else{
             $userId = auth()->user()->id;
         }
         $thisOrg = new Org();
         try {
             $orgHome = $thisOrg->getOrgHomeFromOrgId($orgId);
         } catch (\Exception $e) {
             abort(500, 'Error looking up organization home');
         }
         $layoutInstance = new Layout;
         try {
             $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $orgHome[0]->top_layout_id);
         } catch (\Exception $e) {
             abort(500, 'Error getting admin perms for organization home');
         }
         return json_encode($thisLayoutPerms);


     }
     public function newOrg(Request $request){
         $inData = $request->all();
         $name = $inData['params']['name'];
         $description = $inData['params']['description'];
         $height = $inData['params']['height'];
         $width = $inData['params']['width'];
         $backgroundColor = $inData['params']['backgroundColor'];
         $adminUserId = $inData['params']['adminUserId'];
         $adminUserEmail = $inData['params']['adminUserEmail'];
         $adminUserName = $inData['params']['adminUserName'];
         $backgroundType = $inData['params']['backgroundType'];
         $backgroundDisplay = $inData['params']['backgroundDisplay'];
         $userIsAdmin = 1;
         $userNotAdmin = 0;
         if($backgroundType=='I'){
             $backgroundColor = '';
             $backgroundImage = $inData['params']['backgroundImage'];
         }else{
             $backgroundImage = '';
         }
         $layoutInstance = new Layout;
         $orgInstance = new Org;
         $committed=0;
         DB::beginTransaction();
         try {
             $newLayoutId = $layoutInstance->createLayoutWithoutBlanks($name, $height, $width, $description, $backgroundColor, $backgroundImage, $backgroundType, 0, $backgroundDisplay,'N');
             try {
                 $newOrgId = $orgInstance->createNewOrg($name, $description, $newLayoutId);
             } catch (\Exception $e) {
                 throw $e;
             }
             try {
                 $layoutInstance->updateLayoutOrg($newLayoutId, $newOrgId);
             } catch (\Exception $e) {
                 throw $e;
             }
             try {
                 $orgInstance->addUserToOrg($newOrgId, $adminUserId);
             } catch (\Exception $e) {
                 throw $e;
             }
             $thisGroup = new Group;
             $up = $thisGroup->returnPersonalGroupId($adminUserId);
             if($up==null){
                 throw new \Exception('no personal group');
             }
             try {
                 $allUserGroupId = $thisGroup->returnAllUserGroupId();
             } catch (\Exception $e) {
                 throw new \Exception('error identifying all user group');
             }
             if($allUserGroupId==null){
                 throw new \Exception('no all user group');
             }
             try {
                 $thisGroup->addOrgToGroup($newOrgId, $up);
             } catch (\Exception $e) {
                 throw $e;
             }
             try {
                 $newLayoutGroupId = $thisGroup->addNewLayoutGroup($newLayoutId, $name, $description);
             } catch (\Exception $e) {
                 throw $e;
             }
             try {
                 $thisGroup->addOrgToGroup($newOrgId, $newLayoutGroupId);
             } catch (\Exception $e) {
                 throw $e;
             }
             try {
                 $thisGroup->addOrgToGroup($newOrgId, $allUserGroupId);
             } catch (\Exception $e) {
                 throw $e;
             }

             try {
                 $thisGroup->addUserToGroup($adminUserId, $newLayoutGroupId, $userIsAdmin);
             } catch (\Exception $e) {
                 throw $e;
             }
             $regGroupLabel = 'reg users '.$name;
             $regGroupDescription = 'reg users '.$name;
             $regGroupId=0;
             try {
                 $registrationGroupId = $thisGroup->addNewGroup($regGroupLabel, $regGroupDescription);
                 $thisGroup->addOrgToGroup($newOrgId, $registrationGroupId);
                 $thisGroup->addUserToGroup($adminUserId,$registrationGroupId,false);
             } catch (\Exception $e) {
                 $message = 'Exception '.$e."adding registration group for org ".$newOrgId." to registered users group";
                 Log::debug($message);
                 throw $e;
             }


             try {
                 $layoutInstance->editPermForGroup($newLayoutGroupId, $newLayoutId, 'view', 1);
             } catch (\Exception $e) {
                 throw $e;
             }
             try {
                 $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'view', 1);
             } catch (\Exception $e) {
                 throw $e;
             }
             $userPersonalGroupId = $up;
             try {
                 $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'view', 1);
                 $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'author', 1);
                 $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'admin', 1);
             } catch (\Exception $e) {
                 throw $e;
             }
             $committed = 1;
             DB::commit();
/*
             if($backgroundType=='I' && $committed>0){
                 try {
                     $fixedBackgroundUrl = $this->fileFixup($backgroundImage, $newOrgId);
                 } catch (\Exception $e) {
                     throw $e;
                 }
//                 $layoutInstance->setBackgroundUrl($fixedBackgroundUrl, $newLayoutId);
             }
*/
             return json_encode($newOrgId);
         } catch (\Exception $e) {
             DB::rollBack();
             return response()->json([
                 'result'=>'error',
                 'errorDescription'=>$e>getMessage()
             ]);
         }

     }

    private function fixupBackgroundUrl($existingUrl, $newOrg){
        $pieces = explode("/", $existingUrl);
        $pieces[4]=$newOrg;
        $fileName = $pieces[5];
        $thisConstants = new Constants();
        $orgDirectory = '/images/' . $newOrg;
        if (!Storage::exists($orgDirectory)) {
            Storage::makeDirectory($orgDirectory);
        }
        $newFileDestination = $orgDirectory.'/'.$fileName;
        $oldOrgDirectory = '/images/' . '1';
        $oldFileSource = $oldOrgDirectory.'/'.$fileName;
        File::copy($oldFileSource, $newFileDestination);
        return  implode('/', $pieces);

    }

    private function fileFixup($path, $newOrg){
        $message = 'at recieveFile - backgroundImage-' . $path;
        Log::debug($message);
        $orgDirectory = '/images/' . $newOrg;
        if (!Storage::exists($orgDirectory)) {
            Storage::makeDirectory($orgDirectory);
        }
        $copyToLocation = $orgDirectory . '/' . $path;
        $message = 'at recieveFile - copyToLocation-' . $copyToLocation;
        Log::debug($message);
        Storage::copy('file/' . $path, $copyToLocation);//                $accessLocation = "http://localhost:8000/images/" . $org . "/" . $path;
        $thisConstants = new Constants();
        $accessLocation = $thisConstants->Options['imageLink'] . $newOrg . "/" . $path;
        $message = 'at recieveFile - accessLocation-' . $accessLocation;
        Log::debug($message);
        return $accessLocation;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Org  $org
     * @return \Illuminate\Http\Response
     */
    public function show(Org $org)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Org  $org
     * @return \Illuminate\Http\Response
     */
    public function edit(Org $org)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Org  $org
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Org $org)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Org  $org
     * @return \Illuminate\Http\Response
     */
    public function destroy(Org $org)
    {
        //
    }
}
