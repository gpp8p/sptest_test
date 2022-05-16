<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Archive extends Model
{
    public function getDocumentTypes(){
        $query = "select id, document_type from document_type";
        try {
            $documentTypes = DB::select($query);
            return $documentTypes;
        } catch (Exception $e) {
            throw new Exception('error in getDocumentTypes'.$e->getMessage());
        }
    }

    public function getFileTypes(){
        $query = "select id, file_type from file_type";
        try {
            $fileTypes = DB::select($query);
            return $fileTypes;
        } catch (Exception $e) {
            throw new Exception('error in getFileTypes'.$e->getMessage());
        }
    }
    public function getAccessTypes(){
        $query = "select id, access_type from archive_access";
        try {
            $accessTypes = DB::select($query);
            return $accessTypes;
        } catch (Exception $e) {
            throw new Exception('error in getAccessTypes'.$e->getMessage());
        }
    }
}
