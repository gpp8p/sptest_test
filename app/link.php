<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class link extends Model
{
    public function getLinksForCardId($cardId){
        $query = "select id, isExternal, link_url, layout_link_to, description, type, show_order from links where card_instance_id = ? order by show_order";
        try {
            $linkInfo = DB::select($query, [$cardId]);
            return $linkInfo;
        } catch (Exception $e) {
            throw new Exception('error ".$e.getMessage()."loading links for'.$cardId);
        }
    }

    public function getMenuLabelForLink($layoutLinkTo){
        $query = "select menu_label from layouts where id = ?";
        try {
            $layoutMenuLabel = DB::select($query, [$layoutLinkTo]);
            return $layoutMenuLabel[0];
        } catch (Exception $e) {
            throw new Exception('error ".$e.getMessage()."loading links for'.$cardId);
        }
    }

    public function getLinkInfoForCardId($cardId){
        $query = "select links.id, links.isExternal, links.link_url, links.layout_link_to, links.description, links.type, links.show_order, layouts.menu_label from links, layouts ".
            "where card_instance_id = ? ".
            "and links.layout_link_to=layouts.id ".
            "order by show_order";
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
    public function updateShowOrder($cardId, $newShowOrder, $linkId){
        $query = 'update links set show_order = ? where card_instance_id = ? and id = ?';
        try {
            DB::select($query, [$newShowOrder, $cardId, $linkId]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' updating show_order in links from '. $cardId);
        }
    }
    public function linkExistsInCard($cardId, $layoutLinkTo){
        $query = "select id from links where card_instance_id = ? and layout_link_to = ?";
        try {
            $foundLinks = DB::select($query, [$cardId, $layoutLinkTo]);
            if(count($foundLinks>0)){
                return $foundLinks[0]->id;
            }else{
                return 0;
            }
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' removing old links from '. $cardId);
        }
    }

    public function saveLink($orgId, $layoutId, $cardInstanceId, $description, $linkUrl, $isExternal, $layoutLinkTo, $linkType, $thisShowOrder){
        try {
            $thisLayout = new Layout;
            $l = DB::table('links')->insertGetId([
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
            if($layoutLinkTo>0){
                $thisLayout->setUnDelete($layoutLinkTo);
            }

        } catch (\Exception $e) {
            throw $e;
        }
        return $l;
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
