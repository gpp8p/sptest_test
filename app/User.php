<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAllUsers(){
        $query = "select name, email, id from users";
        $allUsers = DB::select($query);
        return $allUsers;
    }

    public function createUser($userEmail, $userName, $userPassword){
        $newUserId=DB::table('users')->insertGetId([
            'name'=>    $userName,
            'email'=>   $userEmail,
            'password'=> Hash::make($userPassword),
//            'password'=> Hash::make('n1tad0g'),
            'is_admin'=>false,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        return $newUserId;


    }
    public function updatePassword($userEmail, $userPassword){
        $query = 'update users set password = ? where email = ?';
        try {
            $success = DB::select($query, [$userPassword, $userEmail]);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function findUserByEmail($email){
        $query = "Select name, email, id, is_admin from users where email = ?";
        $thisUser = DB::select($query, [$email]);
        return $thisUser;
    }

    public function checkUserOrgMembership($email){
        $query="select users.id as userId, org.id as orgId, org.description from users, userorg, org ".
            "where users.id = userorg.user_id ".
            "and org.id=userorg.org_id ".
            "and users.email=?";


        $thisUserInfo = DB::select($query, [$email]);
        return $thisUserInfo;
    }

}
