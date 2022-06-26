<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use App\Classes\Constants;
use Illuminate\Support\Facades\Log;




class Solr extends Model
{
    public function removeFileFromCollection($collectionName, $layoutId, $cardId, $fileLocation, $keyWords,$accessType, $documentType, $createDate ){
        $message = 'at removeFileFromCollection'.$cardId;
        Log::debug($message);
        $client = new Client();
        $thisConstants = new Constants;
        $query = "<delete><query>".$thisConstants->Options['cardAttribute'].":".$cardId."</query></delete>";
        $create = $client->request('POST', $thisConstants->Options['solrBase'].$thisConstants->Options['collection'].'/update?commit=true', [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body' => $query
        ]);
        Log::debug($create);
    }
    public function addFileToCollection($collectionName, $layoutId, $cardId, $fileLocation, $keyWords,$accessType, $documentType, $createDate ){
        $client = new Client();
        $thisConstants = new Constants;
        $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/update/extract?literal.id=".$layoutId."&literal.cardId=".$cardId;
        if(strlen($keyWords)>0){
            $query = $query."&literal.keywords=".$keyWords;
        }
        if(strlen($accessType)>0){
            $query = $query."&literal.accessType=".$accessType;
        }
        if(strlen($documentType)>0){
            $query = $query."&literal.documentTypeType=".$documentType;
        }
        if($createDate ==''){
            $t=time();
            $createDate = date("Ymd",$t);
        }
        $query=$query."&literal.create_date=".$createDate;
        $query = $query."&commit=true";
        $filePath = $thisConstants->Options['fileBase'].$fileLocation;
        $thisFile = fopen($filePath, 'r');
        $client = new \GuzzleHttp\Client();


        $request = $client->post( $query, [
            'headers' => [],
            'multipart' => [
                [
                    'name'     => 'myfile',
                    'contents' => $thisFile,
                ]
            ]
        ]);



    }

    public function sendQueryToSolr($thisQuery,$fqQuery){

        $thisConstants = new Constants;
        $client = new Client();
        if($thisConstants->Options['runContext']=='local'){
            if(strlen($fqQuery)>0){
                if(strlen($thisQuery)>0){
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=".$thisQuery."& fq = ".$fqQuery;
                }else{
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=*.*& fq = ".$fqQuery;
                }
            }else{
                if(strlen($thisQuery)>0){
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=".$thisQuery;
                }else{
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=*.*";
                }
            }
        }else{
            if(strlen($fqQuery)>0){
                if(strlen($thisQuery)>0){
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=".$thisQuery."& fq = ".$fqQuery;
                }else{
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=*.*& fq = ".$fqQuery;
                }
            }else{
                if(strlen($thisQuery)>0){
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=".$thisQuery;
                }else{
                    $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=*.*";
                }
            }
        }

//        $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=".$query;
        $message = 'at client get '.$query;
        Log::debug($message);
        $response = $client->get($query);
        $body = $response->getBody();
        $responseContent = $body->getContents();
        $decodedResponseContent = json_decode($responseContent);
        return $decodedResponseContent;

    }


}
