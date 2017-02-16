<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use DebugBar\OpenHandler;

class debugbar_openHandler extends OpenHandler
{
    /**
     * Find operation
     */
    protected function find($request)
    {
        $max = 20;
        if (isset($request['max'])) {
            $max = $request['max'];
        }

        $offset = 0;
        if (isset($request['offset'])) {
            $offset = $request['offset'];
        }

        $filters = array();
        foreach (array('utime', 'datetime', 'ip', 'uri', 'method') as $key) {
            if (isset($request[$key])) {
                $filters[$key] = $request[$key];
            }
        }
        $response = $this->debugBar->getStorage()->find($filters, $max, $offset);

        $response = array_values(collect($response)->sortBy(function($product, $key) {
            return $product['utime'];
        }, SORT_REGULAR, true)->all());
        
        return $response;
    }
}
