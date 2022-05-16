<?php

namespace App\Classes;

class Constants{
    function __construct(){
        $requestSource = $_SERVER['HTTP_HOST'];
        if(str_contains($requestSource, 'localhost')){
            $this->Options  = [
                'urlBase'=>'http://localhost:8000/',
                'spacesBase'=>'http://localhost/spaces/',
                'solrBase'=>'http://localhost:8983/solr/',
                'collection'=>'test_collection_0422',
                'fileBase'=>'/Users/georgepipkin/Sites/sptest_dev/storage/app/',
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
                'urlBase'=>'http://sptests.org:8000/',
                'spacesBase'=>'http://sptests.org/spaces/',
                'solrBase'=>'http://sptests.org:8983/solr/',
                'collection'=>'spaces_test5',
                'fileBase'=>'/var/www/sptest_test/storage/app/',
                'linkUrlBase'=>'http://sptests.org:8080/displayLayout/',
                'storageLinkPattern'=>'<img src=\\"http://sptests.org:8000/storage/',
                'tempFileReference'=>'http://sptests.org:8000/storage/',
                'newImageLink'=>'http://sptests.org:8000/images/',
                'dynamicAddress'=>'http://sptests.org:8080/target/',
                'staticAddress'=>'http://sptests.org/spaces/',
                'imageLink'=>'http://sptests.org:8000/images/'
            ];
        }

    }
}

