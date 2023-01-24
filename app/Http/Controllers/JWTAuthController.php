<?php

namespace App\Http\Controllers;

use App\Layout;
use App\Org;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Validator;
use App\User;
use Response;
use Illuminate\Support\Facades\Log;


class JWTAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|between:2,100',
            'email' => 'required|email|unique:users|max:50',
            'password' => 'required|confirmed|string|min:6',
        ]);


        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'Successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
//        $message = 'login made it to: point A';
//        Log::debug($message);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
//        $message = 'login made it to: point B';
//        Log::debug($message);

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userInstance = new User();
        $userFound = $userInstance->findUserByEmail('GuestUser@nomail.com');
        $guestUserId = $userFound[0]->id;
//        $message = 'login made it to: point C';
//        Log::debug($message);


        $thisUserName = auth()->user()->name;
        $thisUserId =auth()->user()->id;
        $thisUserIsAdmin = auth()->user()->is_admin;
        $thisOrgInstance = new Org;
        $inData = $request->all();
        $defaultOrg = $inData['default_org'];
//        $message = 'login made it to: point D';
//        Log::debug($message);

        if(is_numeric($defaultOrg)){
            $orgId = $defaultOrg;
        }else{
            try {
                $orgId = $thisOrgInstance->getOrgId($defaultOrg);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unauthorized - unknown org'], 401);
            }
        }
        if($thisUserId!=$guestUserId){
            if(!$thisOrgInstance->isUserInOrg($thisUserId, $orgId)){
                return response()->json(['error' => 'Unauthorized - not in org'], 401);
            }
        }
//        $message = 'login made it to: point E';
//        Log::debug($message);



        if(is_numeric($defaultOrg)){
            $orgInfo = $thisOrgInstance->getOrgHomeFromOrgId($defaultOrg);
        }else{
            $orgInfo = $thisOrgInstance->getOrgHome($defaultOrg);
        }
//        $defaultOrg = 'root';

//        $message = 'login ';
//        Log::debug($message);


        $thisLayout = new Layout;
        $loginPerms = $thisLayout->summaryPermsForLayout($thisUserId,$orgInfo[0]->id,$orgInfo[0]->top_layout_id);
        if(count($orgInfo)>0){
            $message = 'login by '.$thisUserName;
            Log::debug($message);
            return Response::json(array('resultType'=>'Ok', 'userName'=>$thisUserName, 'orgId'=>$orgInfo[0]->id, 'orgHome'=>$orgInfo[0]->top_layout_id, 'loginPerms'=>$loginPerms, 'userId'=>$thisUserId, 'is_admin'=>$thisUserIsAdmin, 'access_token' => $token, 'token_type' => 'bearer', 'expires_in' => auth('api')->factory()->getTTL() * 60));
//            return Response::json(array('resultType'=>'Ok', 'userName'=>$thisUserName, 'orgId'=>$orgInfo[0]->id, 'orgHome'=>$orgInfo[0]->top_layout_id, 'loginPerms'=>$loginPerms, 'userId'=>$thisUserId, 'is_admin'=>$thisUserIsAdmin, 'access_token' => $token, 'token_type' => 'bearer', 'expires_in' => auth()->factory()->getTTL() * 600));
        }else{
            $noOrgMsg = $defaultOrg.' not known';
            return Response::json(array('resultType'=>$noOrgMsg));
        }


        return $this->createNewToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
