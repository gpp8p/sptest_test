<?php

namespace App\Classes;

class Constants{
    function __construct(){
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
    }
}

