<?php

class topapi_format_jsonp{

    protected $callback = '';

    /**
     * @param string $callback jsonp的回调函数名
     */
    public function __construct($callback) {
        $this->callback = $callback;
    }

    public function formatData($result) {
        echo header('Content-Type:text/javascript; charset=utf-8');
        echo $this->callback . '(' . json_encode($result) . ')';
        exit;
    }
}
