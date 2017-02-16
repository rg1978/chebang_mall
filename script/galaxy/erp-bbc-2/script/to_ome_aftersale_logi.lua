
local json = require("json")
input = json.decode(input)


local method = 'ome.aftersale.logistics_update'


function toOther( input, other )
	if input == nil then return other end
	return input
end


function afterasle(input)
	local result = {
		order_bn          		= toOther(tostring(input.tid), ""),
		return_bn         		= toOther(tostring(input.aftersales_bn), ""),
		logistics_info			= json.encode({logi_company=input.corp_code, logi_no=input.logi_no})
	}
	return result
end


function update_table(dst, src)
	for k,v in pairs(src) do dst[k] = v end
end

function get_res_params(method,msg)
    local result = {
    	method = method,
    	from_node_id = toOther(msg.from_node_id, ""),
    	to_node_id = toOther(msg.to_node_id, ""),
    	node_id = toOther(msg.from_node_id, ""),
    	app_id = toOther(msg.node_type, ""),
    	date = toOther(msg.date, os.date("%Y-%m-%d %H:%M:%S"))
	}
    return result
end


function main(trade,relation)
	if trade == nil or relation ==nil then return "error format" end

	app_params = afterasle(trade)
	--
	update_table(app_params, get_res_params(method,relation))
	return app_params
	-- body
end

output = main(input.data, input.permission)
