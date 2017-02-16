<?php
/**
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_token_default implements topapi_interface_token {

    public $expire = '2592000';//60*60*24*30

    public function make($userId, $data)
    {
        $redis = redis::scene('topapi_token');

        $systemToken = base_certificate::token();
        $userKey = md5($systemToken.$userId);

        $randomId = str_random(32);
        $token = md5(sha1(
            json_encode(
                array(
                    $userId,
                    $data['account'],
                    $data['password'],
                    $data['deviceid'],
                    $randomId,
                )
            )
        )).$userKey;

        $value = json_encode(['user_id'=>$userId, 'deviceid'=>$data['deviceid'], 'expire'=>time()+$this->expire]);
        $redis->hset($userKey, $token, $value);

        return $token;
    }

    public  function check($token)
    {
        $redis = redis::scene('topapi_token');

        $userKey = substr($token, 32, 64);
        if( !$userKey )
        {
            throw new \RuntimeException('invalid token', 20001);
        }

        $userData = $redis->hget($userKey, $token);
        if( ! $userData )
        {
            throw new \RuntimeException('invalid token', 20001);
        }

        $data = json_decode($userData, true);
        if( $data['expire'] < time()  )
        {
            $redis->hdel($userKey, $token);
            throw new \RuntimeException('invalid token', 20001);
        }
        else
        {
            $value = json_encode(['user_id'=>$data['user_id'], 'deviceid'=>$data['deviceid'], 'expire'=>time()+$this->expire]);
            $redis->hset($userKey, $token, $value);
        }

        return $data['user_id'];
    }

    public function delete($token)
    {
        $redis = redis::scene('topapi_token');

        $userKey = substr($token, 32, 64);
        return $redis->hdel($userKey, $token);
    }
}

