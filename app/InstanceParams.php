<?php

namespace App;

use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InstanceParams extends Model
{
    function createInstanceParam($key, $value, $instanceId, $isCss, $domElement){
//        $newParam = new InstanceParams;
//        $newParam->card_instance_id = $instanceId;
//        $newParam->parameter_key=$key;
//        $newParam->parameter_value=$value;
//        $newParam->isCss=$isCss;
//        $newParam->save();

            $newCardInstanceId =DB::table('instance_params')->insertGetId([
                'card_instance_id'=>$instanceId,
                'parameter_value'=>$value,
                'parameter_key'=>$key,
                'card_instance_id'=>$instanceId,
                'isCss'=>$isCss,
                'dom_element'=>$domElement,
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ]);
    }
    function getCardInstanceParams($CardId){
        if(DB::table('instance_params')->where([
            ['card_instance_id','=',$CardId]
        ])->exists()
        ){
            return  DB::table('instance_params')->where([
                ['card_instance_id','=',$CardId]
            ])->get();
        }else{
            return [];
        }
    }
    function hasInstanceParam($cardId, $paramKey){
        $query = "select id from instance_params where card_instance_id = ? and parameter_key = ?";
        try {
            $paramIdsFound = DB::select($query, [$cardId, $paramKey]);
        } catch (Exception $e) {
            throw new Exception('error - could not delete an instance param');
        }
        if(count($paramIdsFound)>0){
            return $paramIdsFound[0]->id;
//            return count($paramIdsFound);
        }else{
            return -1;
        }
    }
    function updateInstanceParam($paramId,$key, $value, $instanceId, $isCss, $domElement){
        $query = 'update instance_params set parameter_key = ?, parameter_value = ?, card_instance_id = ?, isCss=?, dom_element = ? where id = ?';

        try {
            DB::select($query, [$key, $value, $instanceId, $isCss, $domElement, $paramId]);
        } catch (Exception $e) {
            throw new Exception('error - could not update an instance param');
        }
    }
    function deleteInstanceParam($paramId){
        $query = "delete from instance_params where id = ?";
        try {
            DB::select($query, [$paramId]);
        } catch (Exception $e) {
            throw new Exception('error - could not delete an instance param');
        }
    }
}
