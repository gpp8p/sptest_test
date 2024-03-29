<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \File;
use Illuminate\Support\Facades\Storage;
use Response;
use App\Classes\Constants;
use Illuminate\Support\Facades\Log;
use Exception;

class FileUploadController extends Controller
{


    public function recieveFile(Request $request){
        $message = 'at recieveFile - backgroundImage';
        Log::debug($message);
        $thisConstants = new Constants;
        $inData =  $request->all();
        $org = $inData['org'];
        $message = 'at recieveFile - fileRole -'.$inData['fileRole'].' - org - '.$org;
        Log::debug($message);

        switch($inData['fileRole']){
            case 'imageCard':
            case 'backgroundImage':{

                try {
                    $path = $request->file('file')->store('file');
                    $path = str_replace('file/', '', $path);
                    $message = 'at recieveFile - backgroundImage-' . $path;
                    Log::debug($message);
                    $orgDirectory = '/images/' . $org;
                    if (!Storage::exists($orgDirectory)) {
                        Storage::makeDirectory($orgDirectory);
                    }
                    $copyToLocation = $orgDirectory . '/' . $path;
                    $message = 'at recieveFile - copyToLocation-' . $copyToLocation;
                    Log::debug($message);
                    Storage::copy('file/' . $path, $copyToLocation);//                $accessLocation = "http://localhost:8000/images/" . $org . "/" . $path;
                    $accessLocation = $thisConstants->Options['imageLink'] . $org . "/" . $path;
                    $message = 'at recieveFile - accessLocation-' . $accessLocation;
                    Log::debug($message);
                } catch (\Exception $e) {
                    $message = 'at recieveFile - Exception -' . $e;
                    Log::debug($message);
                }

                break;
            }
            case 'document':{
                $path = $request->file('file')->store('file');
                $path = str_replace('file/', '', $path);
                $orgDirectory = '/spcontent/' . $org.'/cardText';
                if (!Storage::exists($orgDirectory)) {
                    Storage::makeDirectory($orgDirectory);
                }
                $copyToLocation = $orgDirectory . '/' . $path;
                Storage::copy('file/' . $path, $copyToLocation);
                $accessLocation = "/spcontent/" . $org . "/cardText/" . $path;


                break;
            }

        }
        return $accessLocation;
    }

    public function recieveFileCk(Request $request){
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

    function sendFile(Request $request){
        $inData =  $request->all();
        $path = $inData['path'];
        $fileContent = Storage::get($path);
        $statusCode = "200";
        $response = Response::make($fileContent, $statusCode);
//        $pdfType = 'application/pdf';
//        $response->header('Content-Type', $pdfType);
        return $response;
    }
    function removeUploadedFile(Request $request){
        $inData =  $request->all();
        $path = $inData['path'];
        Storage::delete($path);
        return 'ok';
    }
}
