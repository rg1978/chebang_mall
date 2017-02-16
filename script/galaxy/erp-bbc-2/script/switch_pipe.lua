print(input)
local json = require("json")
input = json.decode(input)


function inItems(key, items)
	for _, v in pairs(items) do
		if key == v then return true end
	end
end

output = {}

if inItems(input.other.prismNotifyName, {"tradeRefund", "tradeCreate", "tradePay", "tradeDelivery", "tradeConfirm", "tradeClose", "tradeEditPrice"}) then
	output.id = input.other.tid
	output.pipe = "prism.trade"
elseif inItems(input.other.prismNotifyName, {"refundCreated", "refundModified"}) then
	output.pipe = "prism.refund"
	output.id = input.other.refunds_id
elseif inItems(input.other.prismNotifyName, {"afterSalesCreated", "afterSalesCheck", "sellerSendGoods"}) then
	output.pipe = "prism.aftersale"
	output.id = input.other.aftersales_bn
elseif input.other.prismNotifyName == "buyerReturnGoods" then
	output.pipe = "prism.aftersale.logi"
	output.id = input.other.aftersales_bn
end


output.query = json.encode({
	api = "bbc_order_poll",
	from_api_key= input.key,
	from_api_secret= input.secret,
	from_type = "bbc",
	status = "true",
	bbc_user_id = tostring(input.other.shop_id),
})
--print(json.encode(output))
--print("单号: ",output.id)
