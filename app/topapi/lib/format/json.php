<?php

class topapi_format_json{

    public function __construct()
    {
        // $this->addHeaders('Content-Type', 'application/json;charset=utf-8');
    }


    public function formatData($result)
    {
        if( $result['data'] == array() || $result['data'] === '' )
        {
            $result['data'] = (object)[];
        }

        if( is_bool($result['data']) )
        {
            $result['data'] = null;
        }

        return response::json($result)->send();
    }

}
