<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_abstract_hongbaoType
{

    public function beginTransaction()
    {
        $this->redis = redis::scene('hongbao');
        $this->commandQueue = [];
        return $this;
    }

    public function rollback()
    {
        foreach( $this->commandQueue as $row )
        {
            $cmd = strtolower($row[0]);
            $key = $row[1];
            if( isset($row[2]) )
            {
                if( $cmd == 'decrbyfloat' )
                {
                    $row[2] = -$row[2];
                    $cmd = 'incrbyfloat';
                }
                $result = $this->redis->$cmd($key, $row[2]);
            }
            else
            {
                $result = $this->redis->$cmd($key);
            }
        }

        return true;
    }

    public function execRedisCommad($cmd, $key, $value)
    {
        $cmd = strtolower($cmd);

        $discardCmd = [
            'decr' => 'incr',
            'decrby' => 'incrby',
            'incr' => 'decr',
            'incrby' => 'decrby',
            'decrbyfloat' => 'incrbyfloat',
            'incrbyfloat' => 'decrbyfloat',
        ];

        if( $value )
        {
            $this->commandQueue[] = [$discardCmd[$cmd], $key, $value];

            if( $cmd == 'decrbyfloat' )
            {
                $value = -$value;
                $cmd = 'incrbyfloat';
            }
            $result = $this->redis->$cmd($key, $value);
        }
        else
        {
            $this->commandQueue[] = [$discardCmd[$cmd], $key];
            $result = $this->redis->$cmd($key);
        }

        return $result;
    }

}
