<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class link extends Model
{
    public function getLinksForCardId($cardId){
        $query = "select id, isExternal, link_url, layout_link_to, description, type from links where card_instance_id = ? order by show_order";
        try {
            $linkInfo = DB::select($query, [$cardId]);
            return $linkInfo;
        } catch (Exception $e) {
            throw new Exception('error ".$e.getMessage()."loading links for'.$cardId);
        }
    }
    public function removeLinksForCardId($cardId, $linkType){
        $query = 'delete from links where card_instance_id = ? and type = ?';
        try {
            DB::select($query, [$cardId, $linkType]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' removing old links from '. $cardId);
        }
    }

    public function saveLink($orgId, $layoutId, $cardInstanceId, $description, $linkUrl, $isExternal, $layoutLinkTo, $linkType, $thisShowOrder){
        try {
            $thisLayout = new Layout;
            $thisOrgId = DB::table('links')->insertGetId([
                'org_id' => $orgId,
//                'layout_id' => $layoutId,
                'card_instance_id' => $cardInstanceId,
                'description' => $description,
                'isExternal' => $isExternal,
                'link_url' => $linkUrl,
                'layout_link_to' => $layoutLinkTo,
                'type'=>$linkType,
                'layout_id'=>$layoutId,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'show_order' => $thisShowOrder
            ]);
            $thisLayout->setUnDelete($layoutLinkTo);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteLink($linkId){
        $query = 'delete from links where id = ?';
        try {
            DB::select($query, [$linkId]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' deleting links from '. $linkId);
        }
    }
    public function getLinkLabel($linkId){
        $query = "select description from links where id = ?";
        try {
            $selectedLinkLabel = DB::select($query, [$linkId]);
            return $selectedLinkLabel[0];
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' getting linkLabel ');
        }
    }

    public function getLinksToLayout($toLayoutId){
        $query = "select org_id, layout_id, card_instance_id, link_url from links where layout_link_to = ?";
        try {
            $selectedToLinks = DB::select($query, [$toLayoutId]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' removing old links to '. $toLayoutId);
        }
        return $selectedToLinks;
    }
    public function deleteLinksToLayout($toLayoutId){
        $query = "delete from links where layout_link_to = ?";
        try {
            $selectedToLinks = DB::select($query, [$toLayoutId]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' removing old links to '. $toLayoutId);
        }

    }
    public function isLinkInCard($cardId, $layoutId){
        $query = "select id from links where card_instance_id = ? and layout_id = ?";
        try {
            $selectedLinks = DB::select($query, [$cardId, $layoutId]);
            if(count($selectedLinks)>0){
                return true;
            }else{
                return false;
            }
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().'Checking existance of a link');
        }

    }

}
