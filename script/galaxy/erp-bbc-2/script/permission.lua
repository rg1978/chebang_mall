
local json = require("json")

input = json.decode(input)



function result( input )
    return {
        data_from        =     'poll',
        to_node_id       =     input['to_node_id'],
        node_type        =     input['to_node_type'],
        to_certi         =     input['to_certi'],
        api_url          =     input['from_api_url'],
        to_url           =     input['to_api_url'],
        to_token         =     input['to_token'],
        from_certi       =     input['from_certi'],
        from_type        =     input['from_type'],
        from_token       =     input['from_token'],
        from_shop_name   =     input['from_auth_unikey'],
        from_secrt       =     input['from_api_secret'],
        from_node_id     =     input['from_node_id'],
        from_key         =     input['from_api_key'],
        status           =     input['status'],
        queue_type       =     input['queue_type'],
        release_version  =     input['release_version'],
        channel_ver      =     input['channel_ver']

    }
end


-- if #input == 0 then
--     output = {result(input)}
-- else
--     local tmp = {}
--     for k, v in pairs(input) do
--         table.insert(tmp, result(v))
--     end
-- end


output = result(input)




