<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Solr;
use App\Layout;

class solrSearchController extends Controller
{
    public function simpleQuery(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisQuery = $inData['query'];
        $advancedQueryJson = $inData['advancedQuery'];
        $thisSolr = new Solr;
        if(is_null($advancedQueryJson)){
            $queryResults = $thisSolr->sendQueryToSolr($thisQuery, '');
        }else{
            $advancedQuery = json_decode($advancedQueryJson);
            $fqQuery = '';
            $fromDate ='';
            $toDate = '';
            foreach($advancedQuery as $key=>$value){
                if($key=="keyWordSearch") {
                    if(strlen($value)>0){
                        $keyWordValue = str_replace(',', ' ', $value);
                        $fqQuery = 'keywords:' . $fqQuery . $keyWordValue . ' AND ';
                    }
                }else if($key=='fromDate') {
                    $fromDate = str_replace('-','', $value);
                }else if($key=='toDate') {
                    $toDate = str_replace('-', '', $value);
                }else if($key=='optSelected'){
                    $fqQuery = $fqQuery.'documenttypetype:'.$value.' AND ';
                }else{
                    $fqQuery = $fqQuery.$key.':'.$value.' AND ';
                }
            }
            $dateSpecification = '';
            if(strlen($fromDate)>0 && strlen($toDate)>0){
                $dateSpecification = 'create_date:['.$fromDate.' TO '.$toDate.'] AND ';
            }else if(strlen($fromDate)>0){
                $dateSpecification = 'create_date:'.$fromDate.' AND ';
            }else if(strlen($toDate)>0){
                $dateSpecification = 'create_date:'.$toDate.' AND ';
            }
            if(strlen($dateSpecification)>0){
                $fqQuery = $fqQuery.$dateSpecification;
            }
            $fqQueryLength = strlen($fqQuery);
            if($fqQueryLength>0){
                $fqKeep = $fqQueryLength -5;
                $fqQuery = substr($fqQuery, 0,$fqKeep);
            }
            $queryResults = $thisSolr->sendQueryToSolr($thisQuery, $fqQuery);
        }
        $allResults = '';
        if($queryResults->response->numFound>0){
            foreach($queryResults->response->docs as $thisQueryResult){
                $allResults=$allResults.$thisQueryResult->id.',';
            }
            $allResults = substr($allResults, 0, -1);
            $thisLayout = new Layout;
            $selectedLayouts = $thisLayout->getLayoutInfo($allResults, $orgId, $userId);
        }else{
            $selectedLayouts = [];
        }
        $encodedLayouts = json_encode($selectedLayouts);
        return $encodedLayouts;
    }
}
