<?php

class topapi_format_xml{

    public function __construct() {
        // $this->addHeaders('Content-Type', 'application/json;charset=utf-8');
    }
    
    public function formatData($result) {
        echo header("Content-type:text/xml;  charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo kernel::single('site_utility_xml')->array2xml($result, 'root');
        exit;
    }

}
