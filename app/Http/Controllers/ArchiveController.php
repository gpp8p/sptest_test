<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Archive;

class ArchiveController extends Controller
{

    public function getDocumentDefaults(){
        $thisArchive = new Archive;
        try {
            $currentDocumentTypes = $thisArchive->getDocumentTypes();
            $currentFileTypes = $thisArchive->getFileTypes();
            $accessTypes = $thisArchive->getAccessTypes();
            $allDocumentDefaults = array("documentTypes"=>$currentDocumentTypes,
                "fileTypes"=>$currentFileTypes,
                "accessTypes"=>$accessTypes);
            return json_encode($allDocumentDefaults);
        } catch (\Exception $e) {
            abort(500, $e);
        }
    }

}
