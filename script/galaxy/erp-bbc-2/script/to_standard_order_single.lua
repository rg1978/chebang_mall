-- input = [[
-- 	{"data":{"error":null,"result":{"adjust_fee":"0.000","anony":"0","area_id":"","buyer_area":"120100/120102","buyer_message":"","buyer_rate":"0","cancel_reason":"","consign_time":"","consume_point_fee":"0","corptmpl_code":"STO","corptmpl_name":"申通物流","created_time":"1442309339","disabled":"0","discount_fee":"0.000","dlytmpl_id":"24","dlytmpl_name":"申 通物流","end_time":"","invoice_main":"","invoice_name":"individual","invoice_type":"normal","ip":"192.168.51.220","is_clearing":"0","is_cod":"false","is_part_consign":"0","itemnum":"1","modified_time":"1442309394","need_invoice":"0","obtain_point_fee":"1","orders":[{"adjust_fee":"0.000","aftersales_status":"","anony":"0","bind_oid":"","bn":"hxkc01","buyer_rate":"1","cat_service_rate":"0.000","complaints_status":"NOT_COMPLAINTS","consign_time":"","discount_fee":"0.000","divide_order_fee":"0.000","end_time":"","invoice_no":"","is_oversold":"0","item_id":"675","logistics_company":"","modified_time":"1442309394","num":"1","oid":"1509151728420014","order_from":"pc","outer_iid":"","outer_sku_id":"","part_mjz_discount":"0.000","pay_time":"","payment":"0.010","pic_path":"","price":"0.010","refund_fee":"0.000","refund_id":"","seller_rate":"0","sendnum":"0","shipping_type":"","shop_id":"19","sku_id":"708","sku_properties_name":"","spec_nature_info":"","status":"WAIT_SELLER_SEND_GOODS","sub_stock":"0","tid":"1509151728410014","timeout_action_time":"","title":"回 写库存01","total_fee":"0.010","user_id":"14"}],"pay_time":"1442309394","pay_type":"online","payed_fee":"0.010","payment":"0.010","post_fee":"0.000","real_point_fee":"","receiver_address":"1231231","receiver_city":"河东区","receiver_district":"","receiver_mobile":"13112312311","receiver_name":"231","receiver_phone":"","receiver_state":"天津市","receiver_zip":"1231231","seller_rate":"0","send_time":"","shipping_type":"","shop_flag":"","shop_id":"19","shop_memo":"","status":"WAIT_SELLER_SEND_GOODS","step_paid_fee":"","step_trade_status":"","tid":"1509151728410014","timeout_action_time":"","title":"订单明细介绍","total_fee":"0.010","trade_from":"pc","trade_memo":"","user_id":"14"}},"msg_id":"751a9160-f617-4bdc-a131-33adcde30dd0","permission":{"api_url":"http://elrond.shopexprism.onex.software:8080","channel_ver":"","data_from":"poll","from_certi":"1406178932","from_key":"msogpibv","from_node_id":"1038373736","from_secrt":"orp5l5jcsn66jel2gkmv","from_token":"r5mcib45dmcnxqetxgndptpe","from_type":"bbc","node_type":"ecos.ome","queue_type":"ecos.ome","release_version":"tg","status":"true","to_certi":"1779706132","to_node_id":"1332373336","to_token":"e0fea3786d8a06a17e411f911ffe503364a04ca7b4c95d1ebd2011a94e1423a5","to_url":"http://192.168.41.98/omebugfix/index.php/api"}}
-- ]]
--  pipe/amq.detail.json
local json = require("json")

input = json.decode(input)


function toString( input )
	if input == nil then return "" end
	return input
end

function  toNumber( input)
	-- body
	if input == nil then return 0 end
	return tonumber(input)
end

function toOther( input, other )
	if input == nil then return other end
	return input
end

--获取订单的支付信息，只有纪录已经付款成功的支付单
function getPaymentsList(input)
    local payment_list = {}
    if input.payments == nil then return nil end
    for key, val in pairs(input.payments) do
        payment_list[key] = {
            payment_id = val.payment_id.."-"..val.paybill_id,
			tid = input.tid,
			seller_bank = val.bank,
			seller_account = val.account,
			buyer_id = input.user_id,
			buy_name = val.user_name,
			buyer_account = val.pay_account,
			pay_fee = val.payment,
			paycost = 0,
			currency = val.currency,
			currency_fee = input.payment,
			pay_type = input.pay_type,
			payment_code = val.pay_app_id,
			payment_name = val.pay_name,
			t_begin = "",
			t_end = val.payed_time,
			pay_time = input.pay_time,
			status = "PAY_FINISH",
			memo = "",
			outer_no = ""
        }
    end

    return payment_list
end


PromotionDetail = {{
	pmt_id = "",
	promotion_name = "",
	promotion_fee = "",
	promotion_desc = "",
	promotion_id = "",
	gift_item_id = "",
	gift_item_name = "",
	gift_item_num = "",
	pmt_type = "",
}}

function ordersDiscountFee( t )
    if t == nil then return end
	local result = 0
	for k, v in pairs(t) do
		if k == "discount_fee" then
			result = result +  v
		end
	end
	return result
end



function getOrder( t )
    if t == nil then return {} end
	local result = {}
	for k, v in pairs(t) do
		local tmp = {
				oid = v.oid,
				["type"] = "goods",
				orders_bn = v.bn,
				type_alias = "",
				iid = v.item_id,
				title = v.title,
				items_num = v.num,
				total_order_fee = v.total_fee,
				weight = tonumber(v.total_weight) *1000,
				discount_fee = v.discount_fee,
				status = toString(v.status),
				ship_status = "",
				refund_status = "",
				consign_time = v.consign_time,
				order_items = {
					order_item = {{
						sku_id = v.sku_id,
	                    item_type = "product",
	                    iid = v.item_id,
	                    bn = v.bn,
	                    name = v.title,
	                    sku_properties = v.spec_nature_info,
	                    weight = toOther(tonumber(v.total_weight),0) *1000,
	                    score = "",
	                    discount_fee = v.discount_fee,
	                    status = v.status,
	                    price = v.price,
	                    sale_price = v.total_fee,
	                    total_item_fee = v.total_fee,
	                    payment = v.payment,
	                    num = v.num,
	                    sendnum = v.sendnum,
	                    pic_path = v.pic_path,
	                    cid = ""
					}}
				},
				sale_price = "",
				is_oversold = ""
			}
		table.insert(result, tmp)
	end
	return result
end



function getStatus(input, v)

	--  原始订单没有status字段将报错
	if input == nil then return {status="error", pay_status="error", ship_status="error"} end

	local status, pay_status, ship_status
	--  status check
	if input == 'TRADE_FINISHED' then
		status = 'TRADE_FINISHED'
	elseif inItems(input, {'TRADE_CLOSED', 'TRADE_CLOSED_BY_SYSTEM'}) then
		status = 'TRADE_CLOSED'
	else
		status = 'TRADE_ACTIVE'
	end

-- local data = 0
-- 	if source_dic =='PAY_NO' or is_cod == 'true' then
--         data = 0
--     elseif source_dic =='PAY_FINISH' then
--         data = 1
--     elseif source_dic =='PAY_TO_MEDIUM' then
--         data = 2
--     elseif source_dic =='PAY_PART' then
--         data = 3
--     elseif source_dic =='REFUND_PART' then
--         data = 4
--     elseif source_dic =='REFUND_ALL' then
--         data = 5
--     elseif source_dic =='REFUNDING' then
--         data = 6
--     end

	if inItems(input, {'WAIT_SELLER_SEND_GOODS','TRADE_FINISHED', 'WAIT_BUYER_CONFIRM_GOODS'}) then
		pay_status = 'PAY_FINISH'
	elseif inItems(input, {'TRADE_CLOSED', 'TRADE_CLOSED_BY_SYSTEM'}) then
		pay_status = 'REFUND_ALL'
	else
		pay_status = 'PAY_NO'
	end

	if v == 'WAIT_PROCESS' then
		pay_status = 'REFUNDING'
	end

	if  inItems(input, {'WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED','TRADE_FINISHED'}) then
		ship_status = 'SHIP_FINISH'
	else
		ship_status = 'SHIP_NO'
	end

	return {status=status, pay_status=pay_status, ship_status=ship_status}
end

function inItems(key, items)
	for _, v in pairs(items) do
		if key == v then return true end
	end
end

function needInvoice(v)
	if tonumber(v) == 1 then return 'true' end
	return 'false'
end

function invoiceTitle(need_invoice, invoice_name, invoice_main)
	if needInvoice(need_invoice) == 'false' then return '' end
	if invoice_name == "individual" then
		return "个人"
	end
	return invoice_main
end

function getTrade( input )
	if input == nil then return {} end
	status = getStatus(input.status, input.cancel_status)
	return {
		tid = toString(input.tid),
		title = input.title,
		created = toString(input.created_time),
		-- py: order_detail_status_to_sdf
		status = status.status,
		pay_status = input.pay_status,
		ship_status = status.ship_status,
		--
		has_invoice = needInvoice(input.need_invoice),  --'false' if order_msg.get('invoice_name','') =='' else 'true',
		invoice_title = invoiceTitle(input.need_invoice, input.invoice_name, input.invoice_main),
		invoice_desc = "",
		invoice_fee = "", --TODO
		total_goods_fee = input.total_fee,
		total_trade_fee = input.payment,
		discount_fee = "",
		payed_fee = (status.pay_status == 'PAY_NO' or status.status == 'TRADE_CLOSED') and '' or input.payed_fee,
		currency = "CNY",
		--
		currency_rate = 1.0,
		total_currency_fee = input.payment,
		buyer_obtain_point_fee = input.obtain_point_fee,
		point_fee = input.consume_point_fee,
		total_weight = toNumber(input.total_weight) * 1000,
		shipping_tid = "",
		shipping_type = input.dlytmpl_name, -- TODO
		shipping_fee = input.post_fee,
		is_delivery = "",
		is_cod = input.is_cod,
		is_protect = "",
		protect_fee = "",
		payment_tid = "",
		payment_type = input.pay_type,
		pay_time = toString(input.pay_time),
		lastmodify = input.modified_time,
		modified = input.modified_time,
		end_time = input.end_time,
		confirm_time = "",
		timeout_action_time = input.timeout_action_time,
		goods_discount_fee = 0,
		-- orders_discount_fee = ordersDiscountFee(input.orders),
		orders_discount_fee = toOther(tonumber(input.discount_fee), 0) + toOther(tonumber(input.points_fee), 0),
		promotion_details = PromotionDetail,
		consign_time = input.consign_time,
		receiver_name = input.receiver_name,
		receiver_mobile = input.receiver_mobile,
		receiver_email = "",
		receiver_state = input.receiver_state,
		receiver_city = input.receiver_city,
		receiver_district = input.receiver_district,
		receiver_address = input.receiver_address,
		receiver_zip = input.receiver_zip,
		receiver_phone = input.receiver_phone,
		receiver_time = "",
		commission_fee = "",
		pay_cost = "",
		seller_rate = input.seller_rate,
		seller_uname = "",
		seller_alipay_no = "",
		seller_mobile = "",
		seller_phone = "",
		seller_name = "",
		seller_email = "",
		seller_memo = "",
		seller_flag = "",
		agent_name = "",
		agent_uname = "",
		agent_level = "",
		agent_sex = "",
		agent_birthdate = "",
		agent_phone = "",
		agent_mobile = "",
		agent_state = "",
		agent_city = "",
		agent_district = "",
		agent_address = "",
		agent_zip = "",
		agent_email = "",
		agent_qq = "",
		agent_shop_url = "",
		agent_shop_name = "",
		buyer_name = input.buyerInfo.username,
		buyer_alipay_no = "",
		buyer_id = "",
		buyer_uname = input.buyerInfo.uname,
		buyer_email = input.buyerInfo.email,
		buyer_memo = input.trade_memo,
		buyer_flag = "",
		buyer_message = input.buyer_message,
		buyer_rate = input.buyer_rate,
		buyer_mobile = input.buyerInfo.mobile,
		buyer_phone = "",
		buyer_state = "",
		buyer_city = "",
		buyer_district = "",
		buyer_address = "",
		buyer_zip = "",
		orders_number = input.itemnum,
		trade_memo = input.shop_memo,
		orders = {order=getOrder(input.orders)},
		logistics_no = "",
		payment_lists = {payment_list=getPaymentsList(input)},
		--payment_lists = {payment_list={{
		--	payment_id = "",
		--	tid = input.tid,
		--	seller_bank = "",
		--	seller_account = "",
		--	buyer_id = input.user_id,
		--	buy_name = "",
		--	buyer_account = "",
		--	pay_fee = input.payed_fee,
		--	paycost = 0,
		--	currency = "CNY",
		--	currency_fee = input.payed_fee,
		--	pay_type = input.pay_type,
		--	payment_code = "",
		--	payment_name = "",
		--	t_begin = "",
		--	t_end = "",
		--	pay_time = input.pay_time,
		--	status = status.pay_status,
		--	memo = "",
		--	outer_no = ""
		--}}},
		is_brand_sale = "",
		cod_status = "",
		trade_type = "",
		step_trade_status = input.step_trade_status,
		step_paid_fee = input.step_paid_fee,
		mark_desc = "",
		is_errortrade = "",
		passthrough = ""
	}
end

local trade = {}
if input.data ~= nil then
	trade = getTrade(input.data.result)

else
	trade = getTrade(input.result)
end


-- output = {
-- 	data = {
-- 		msg_id = input.msg_id,
-- 		order_sdf = {
-- 			trade = trade
-- 		},
-- 		relations = input.permission,
-- 		["type"] = "zx_order"
--  	},
--  	msg_id = input.msg_id,
--  	node = input.permission.from_node_id,
--  	step = "sdf",
--     tid = trade.tid,
--     time = tostring(os.time()),
--     ["type"] = "zx_order"
-- }


tmp = {}
table.insert(tmp, input.permission)

output = {
	msg_id = input.msg_id,
	order_sdf = {
		trade = trade
	},
	relations = tmp,
	["type"] = "zx_order"
}

if input.data ~= nil then
	if input.data.result.cancel_status == 'REFUND_PROCESS' then
		output = {result = 'return'}
	end
end


if input.result ~= nil then
	if input.result.cancel_status == 'REFUND_PROCESS' then
		output = {result = 'return'}
	end
end

