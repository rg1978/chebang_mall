
local json = require("json")
input = json.decode(input)

function toString( input )
	if input == nil then return "" end
	return input
end

output = {
	success =true,
	trade = input.order_sdf.trade,
}

for k, v in pairs(output.trade.orders.order) do

	v.order_items.orderitem = v.order_items.order_item
	v.order_items.order_item = nil
end
