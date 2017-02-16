
local json = require("json")
input = json.decode(input)


local method = 'ome.aftersale.add'


function toOther( input, other )
	if input == nil then return other end
	return input
end


function getStatus( input )
	if input == nil then return 0 end
	local switch = {
		[0] = 1,
		[1] = 3,
		[2] = 3,
		[3] = 5,
		[4] = 4,
		[5] = 7,
		[6] = 9,
		[7] = 4,
	}
	return switch[tonumber(input)]
end


function afterasle(input)
	local sku = toOther(input.sku, {number=0, sku_bn="", sku_name=""})
	local result = {
		order_bn          		= toOther(tostring(input.tid), ""),
		return_bn         		= toOther(tostring(input.aftersales_bn), ""),
		title             		= toOther(input.reason, ""),
		content           		= toOther(input.description, ""),
		comment           		= toOther(input.comment, ""),
		add_time          		= toOther(tostring(input.created_time), ""),
		memo              		= toOther(input.memo, ""),
		status            		= getStatus(input.progress),
		member_uname      		= toOther(input.member_uname, ""),
		return_product_items 	= json.encode({{num=input.num, bn=sku.bn}}),
		attachment        		= toOther(input.attachment, ""),
		logistics_info    		= toOther(input.logistics_info, {{}}),
		is_return         		= toOther(input.is_return, false),
		modified          		= toOther(tostring(input.modified_time), ""),
		return_type   		    = toOther(input.aftersales_type, ""),
		logistics_info			= json.encode({{}})
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


output = main(input.data.result, input.permission)

