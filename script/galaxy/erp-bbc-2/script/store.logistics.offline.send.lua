
local json = require("json")

input = json.decode(input)


function getItems( input )
	if input == nil then return end
	local result = {}
	for k, v in pairs(input) do
		table.insert(result, {
			oid = v.oid,
			bn = v.itemId,
			num = v.num
		})
	end
	return result
end


function getResult(input )
	
	if input == nil then return end
	return {
		format = "json",
		v = "v1",
		tid = input.tid,
		logi_no = input.logistics_no,
		msg_id = input.msg_id,
		items = getItems(json.decode(input.item_list))
	}
end

output = getResult(input.shopex_adapter)

output.corp_code = input.bbc_code or input.shopex_adapter.company_code