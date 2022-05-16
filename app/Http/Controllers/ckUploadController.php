<?php

namespace App\Http\Controllers;

use App\Classes\Constants;
use Illuminate\Http\Request;
use \File;
use Illuminate\Support\Facades\Storage;

class ckUploadController extends Controller
{
    public function recieveFile(Request $request){
        $inData =  $request->all();
        $thisConstants = new Constants;
        $urlBase = 'http://localhost:8000/';
//        $urlBase = $thisConstants->Options['urlBase'];
        $pth = $urlBase.'storage/'.$request->file('upload')->store('file');
        $pth = str_replace('/file', '', $pth);
        $path['url'] = $pth;
        $uploadedFileName = str_replace($urlBase.'storage/','', $pth);
//        $thisFile = new File;
        $publicDirectoryLocation = public_path();
        $storageDirectoryLocation = storage_path();
        $storageLocation = $storageDirectoryLocation.'/app/file/'.$uploadedFileName;
        $publicFileName = $publicDirectoryLocation.'/storage/'.$uploadedFileName;
        File::copy($storageLocation, $publicFileName);
        $rval = json_encode($path);
        return $rval;
    }

}
