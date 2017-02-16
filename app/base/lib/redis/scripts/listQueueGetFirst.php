<?php


use Predis\Command\ScriptCommand;

class base_redis_scripts_listQueueGetFirst extends ScriptCommand
{
    public function getKeysCount()
    {
        // Tell Predis to use all the arguments but the last one as arguments
        // for KEYS. The last one will be used to populate ARGV.
        return -1;
    }

    //类似栈结构地弹出(并删除)最左或最右的一个元素，并且将数据存储到一个有序表
    public function getScript()
    {
        return <<<LUA
local cmd = redis.call
local queue, newqueue, expire = KEYS[1], KEYS[2], ARGV[1]

local v = cmd('lpop', queue)

if v == false or v == nil then
    return v
end

local attempts = string.gsub(v,"(.*)attempts\":(%d+)(.*)","%2",1)
local newQueueData =  v

if tonumber(attempts) ~= nil then
    local attemptsStr = "attempts\":" .. attempts + 1
    newQueueData = string.gsub(v,"(.*)attempts\":(%d+)(.*)","%1"..attemptsStr.."%3",1)
end

cmd('zadd', newqueue, expire, newQueueData)
return  v
LUA;
    }
}

