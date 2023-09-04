<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Solr;
use App\Layout;
use App\Classes\Constants;
use Illuminate\Support\Facades\Log;

class solrSearchController extends Controller
{
    public function simpleQuery(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $thisConstants = new Constants;
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        if($thisConstants->Options['runContext']=='local'){
            $thisQuery = $inData['query'].'&rows=500';
        }else{
            if(strlen($inData['query'])>0){
                $thisQuery = 'attr_content:'.$inData['query'].'&rows=500' ;
            }else{
                $thisQuery = 'attr_content:'.'*.*';
            }

        }

        $advancedQueryJson = $inData['advancedQuery'];
        $thisSolr = new Solr;
        if(is_null($advancedQueryJson)){
            $message = 'simple query was:'.$thisQuery;
            Log::debug($message);
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
                        if($thisConstants->Options['runContext']=='local'){
                            $fqQuery = 'keywords:' . $fqQuery . $keyWordValue . ' AND ';
                        }else{
                            $fqQuery = 'attr_keywords:' . $fqQuery . $keyWordValue . ' AND ';
                        }

                    }
                }else if($key=='fromDate') {
                    $fromDate = str_replace('-','', $value);
                }else if($key=='toDate') {
                    $toDate = str_replace('-', '', $value);
                }else if($key=='optSelected' && strlen($value)>0){
                    if($thisConstants->Options['runContext']=='local'){
                        $fqQuery = $fqQuery.'documenttypetype:'.$value.' AND ';
                    } else{
                        $fqQuery = $fqQuery.'attr_documenttypetype:'.$value.' AND ';
                    }

                }else{
                    if($thisConstants->Options['runContext']=='local'){
                        if(strlen($value)>0){
                            $fqQuery = $fqQuery.$key.':'.$value.' AND ';
                        }

                    }else{
                        if(strlen($value)>0){
                            $fqQuery = $fqQuery.'attr_'.$key.':'.$value.' AND ';
                        }
                    }

                }
            }
            $dateSpecification = '';
            if($thisConstants->Options['runContext']=='local'){
                if(strlen($fromDate)>0 && strlen($toDate)>0){
                    $dateSpecification = 'create_date:['.$fromDate.' TO '.$toDate.'] AND ';
                }else if(strlen($fromDate)>0){
                    $dateSpecification = 'create_date:'.$fromDate.' AND ';
                }else if(strlen($toDate)>0){
                    $dateSpecification = 'create_date:'.$toDate.' AND ';
                }
            }else{
                if(strlen($fromDate)>0 && strlen($toDate)>0){
                    $dateSpecification = 'attr_create_date:['.$fromDate.' TO '.$toDate.'] AND ';
                }else if(strlen($fromDate)>0){
                    $dateSpecification = 'attr_create_date:'.$fromDate.' AND ';
                }else if(strlen($toDate)>0){
                    $dateSpecification = 'attr_create_date:'.$toDate.' AND ';
                }
            }

            if(strlen($dateSpecification)>0){
                $fqQuery = $fqQuery.$dateSpecification;
            }
            $fqQueryLength = strlen($fqQuery);
            if($fqQueryLength>0){
                $fqKeep = $fqQueryLength -5;
                $fqQuery = substr($fqQuery, 0,$fqKeep);
            }
            $message = 'query was:'.$thisQuery;
            Log::debug($message);
            $message = 'fq query was:'. $fqQuery;
            Log::debug($message);
            $queryResults = $thisSolr->sendQueryToSolr($thisQuery, $fqQuery);
        }
        $allResults = '';
        $message = 'results number found:'.$queryResults->response->numFound;
        Log::debug($message);
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
        $message = 'results :'.$encodedLayouts;
        Log::debug($message);
        return $encodedLayouts;
    }
}
