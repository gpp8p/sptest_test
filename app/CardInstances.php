<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\ViewType;
use Storage;


class CardInstances extends Model
{
    public function viewType()
    {
        return $this->belongsTo('App\ViewType');
    }

    public function params()
    {
        return $this->hasMany(InstanceParams::class);
    }

    public function getLayoutCardInstances($layoutName){
        $query = "select instance.id,parameter_key, parameter_value, card_component from card_instances as instance, instance_params as params, layouts as layouts ".
                "where params.card_instance_id = instance.id ".
                "and instance.layout_id = layouts.id ".
                "and layouts.menu_label = ? ".
                "order by instance.id";

        $retrievedCardInstances  =  DB::select($query, [$layoutName]);
        if(count($retrievedCardInstances)>0) {
            return $retrievedCardInstances;
        }else{
            return null;
        }
    }

    public function getLayoutCardInstancesById($layoutId, $orgId){

        $query = "select distinct instance.id,parameter_key, parameter_value, card_component, isCss, dom_element, cardlayout.col, cardlayout.row, cardlayout.height, cardlayout.width ".
                "from card_instances as instance, ".
                "instance_params as params, ".
                "layouts as layouts, ".
                "card_in_layout as cardlayout ".
                "where params.card_instance_id = instance.id ".
                "and instance.id = cardlayout.card_instance_id ".
                "and cardlayout.layout_id = ? ".
                "order by instance.id, dom_element";

/*
        $query = "select instance.id,parameter_key, parameter_value, card_component, isCss, dom_element, ".
            "instance.col, instance.row, instance.height, instance.width ".
            "from card_instances as instance, instance_params as params, layouts as layouts ".
            "where params.card_instance_id = instance.id ".
            "and instance.layout_id = layouts.id ".
            "and layouts.id = ? ".
            "order by instance.id, dom_element";
*/
        $retrievedCardInstances  =  DB::select($query, [$layoutId]);

        for($c=0;$c<count($retrievedCardInstances);$c++){
            $thisCardInstance = $retrievedCardInstances[$c];
            if($thisCardInstance->parameter_key=='cardText'){


                $orgDirectory = '/spcontent/'.$orgId;
                $contentFileName = $thisCardInstance->parameter_value;
                try {
                    $retrievedCardInstances[$c]->parameter_value = Storage::get($contentFileName);
                } catch (\Exception $e) {
                    $retrievedCardInstances[$c]->parameter_value = 'Error fetching content file:'.$contentFileName;
                }
            }
        }
        if(count($retrievedCardInstances)>0) {
            return $retrievedCardInstances;
        }else{
            return null;
        }
    }

    public function updateDocumentTitle($cardId, $newCardName){
        $query = 'update card_instances set card_name = ? where id = ?';

        try {
            $affected = DB::select($query, [$newCardName, $cardId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCardName($cardId){
        $query = 'select card_name from card_instances where id = ?';
        try {
            $selectedCard = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            throw $e;
        }
        return $selectedCard[0]->card_name;
    }


    public function createCardInstance($layoutId, $cardParams, $row, $column, $height, $width, $cardType, $cardName, $restricted){


        $viewType = ViewType::where('view_type_label', 'Web Browser')->first()->id;

        $newCardInstanceId =DB::table('card_instances')->insertGetId([
            'col'=>$column,
            'row'=>$row,
            'height'=>$height,
            'width'=>$width,
            'layout_id'=>$layoutId,
            'card_component'=>$cardType,
            'view_type_id'=>$viewType,
            'card_component'=>$cardType,
            'card_name'=>$cardName,
            'restricted'=>$restricted,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        $newCardLayoutId = DB::table('card_in_layout')->insertGetId([
            'col'=>$column,
            'row'=>$row,
            'height'=>$height,
            'width'=>$width,
            'card_instance_id'=>$newCardInstanceId,
            'layout_id'=>$layoutId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);



//        $thisCardInstance->save();
//        $newCardInstanceId = $thisCardInstance->id;
        foreach($cardParams as $thisParam){
            $thisInstanceParams = new InstanceParams;
            $thisInstanceParams->createInstanceParam($thisParam[0], $thisParam[1],$newCardInstanceId, $thisParam[2], 'main');
        }
        return $newCardInstanceId;

    }

    public function getCardNameFromId($cardId){
        $query = "select card_name from card_instances where id = ?";

        try {
            $cardData = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCardDimensionsFromId($cardId){
        $query = "select row, column, height, width from card_instances where id = ?";

        try {
            $cardData = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function updateCardName($cardId, $newCardName){
        $query = "update card_instances set card_name = ? where id = ?";
        try {
            $cardData = DB::select($query, [$newCardName, $cardId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateCardSize($cardId, $row, $column, $height, $width, $layoutId){
        $query = "update card_in_layout set row = ?, col = ?, height = ?, width = ? where layout_id = ? and card_instance_id = ?";

        try {
            $affected = DB::select($query, [$row, $column, $height, $width, $layoutId, $cardId]);
        } catch (\Exception $e) {
            throw new Exception($e);
        }


        /*
                $affected = DB::table('card_in_layout')
                    ->where([
                        ['layout_id','=', $layoutId],
                        ['card_instance_id','=',$cardId]
                    ])->update(['row' =>$row,
                            'col' => $column,
                            'height'=>$height,
                            'width'=>$width]
                    );



                $affected = DB::table('card_instances')
                    ->where('id', $cardId)
                    ->update(['row' =>$row,
                               'col' => $column,
                                'height'=>$height,
                                'width'=>$width]);
        */
        return $affected;
    }

    public function insertCard($cardId, $layoutId){
        $query = "select row, col, height, width from card_instances where id = ?";
        try {
            $cardData = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            throw $e;
        }
        $newCardLayoutId = DB::table('card_in_layout')->insertGetId([
            'col'=>$cardData[0]->col,
            'row'=>$cardData[0]->row,
            'height'=>$cardData[0]->height,
            'width'=>$cardData[0]->width,
            'card_instance_id'=>$cardId,
            'layout_id'=>$layoutId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);


    }

    public function getCardTypeById($cardId){
        $query = "select card_component from card_instances where id = ?";
        try {
            $cardType = DB::select($query, [$cardId]);
            return $cardType;
        } catch (\Exception $e) {
            throw $e;
        }

    }
    public function removeCardFromLayout($cardId, $layoutId){
        $query = 'delete from card_in_layout where card_instance_id = ? and layout_id = ? ';
        try {
            $affected = DB::select($query, [$cardId, $layoutId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteCard($cardId){
        DB::beginTransaction();
        $query = "delete from card_in_layout where card_instance_id = ?";
        try {
            $affected = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        $query = "delete from instance_params where card_instance_id = ?";
        try {
            $affected = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        $query = "delete from card_instances where id = ?";
        try {
            $affected = DB::select($query, [$cardId]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

    }
    public function getCardsForOrg($orgId, $restricted){
        if(!$restricted){
            $query = "select id, card_name, card_component from card_instances where layout_id in (select id from layouts where org_id = ?)";
        }else{
            $query = "select id, card_name, card_component from card_instances where layout_id in (select id from layouts where org_id = ?) and restricted = 'F'";
        }
        try {
            $selectedCards = DB::select($query, [$orgId]);
        } catch (\Exception $e) {
            throw $e;
        }
        return $selectedCards;

    }



}
