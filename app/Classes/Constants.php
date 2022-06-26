<?php

namespace App\Classes;

class Constants{
    function __construct(){
        $requestSource = $_SERVER['HTTP_HOST'];
        if(str_contains($requestSource, 'localhost')){
            $this->Options  = [
                 'runContext'=>'local',
                'urlBase'=>'http://localhost:8000/',
                'spacesBase'=>'http://localhost/spaces/',
                'solrBase'=>'http://localhost:8983/solr/',
                'collection'=>'sptest_local_0616',
//                'solrBase'=>'http://143.198.179.170:8983/solr/',
//                'collection'=>'core0611',
                'cardAttribute'=>'cardid',
                'fileBase'=>'/Users/georgepipkin/Sites/sptest_test/storage/app',
                'linkUrlBase'=>'http://localhost:8080/displayLayout/',
                'storageLinkPattern'=>'<img src=\\"http://localhost:8000/storage/',
                'tempFileReference'=>'http://localhost:8000/storage/',
                'newImageLink'=>'http://localhost:8000/images/',
                'dynamicAddress'=>'http://localhost:8080/target/',
                'staticAddress'=>'http://localhost/spaces/',
                'imageLink'=>'http://localhost:8000/images/'
            ];
        }else{
            $this->Options  = [
                'runContext'=>'remote',
                'urlBase'=>'http://sptests.org:8000/',
                'spacesBase'=>'http://sptests.org/spaces/',
//                'solrBase'=>'http://sptests.org:8983/solr/',
                'solrBase'=>'http://143.198.179.170:8983/solr/',
//                'collection'=>'spaces_test5',
                'collection'=>'core0611',
                'cardAttribute'=>'attr_cardid',
                'fileBase'=>'/var/www/sptest_test/storage/app/',
                'linkUrlBase'=>'http://sptests.org:8080/displayLayout/',
                'storageLinkPattern'=>'<img src=\\"http://sptests.org:8000/storage/',
                'tempFileReference'=>'http://sptests.org:8000/storage/',
                'newImageLink'=>'http://sptests.org:8000/images/',
                'dynamicAddress'=>'http://sptests.org/target/',
                'staticAddress'=>'http://sptests.org/spaces/',
                'imageLink'=>'http://sptests.org:8000/images/'
            ];
        }

    }
}

