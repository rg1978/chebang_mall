
local json = require("json")
input = json.decode(input)

local method = 'ome.order.add'




function toString( input )
	if input == nil then return "" end
	return input
end

function toBool( input )
	if input == nil then return false end
	return input
end

function toOther( input, other )
	if input == nil then return other end
	return input
end

-- 有效订单总价
function pmt_goods(source_dic, source, version)
	if source_dic == nil then return 0 end
	local pmt = 0
	if version >= 2 and source ~= '360buy' then
		for _, v in pairs(source_dic.orders.order) do
			if v.status ~= 'close' then
				pmt = pmt + tonumber(v.discount_fee)
			end
		end
		return pmt
	else
		return toOther(source_dic.goods_discount_fee, 0)
	end
end

function pmt_order( source_dic, version )
	local pmt = 0
	if version >= 2 then
		return toString(source_dic.trade_discount_fee)
	else
		return toString(source_dic.orders_discount_fee)
	end
end

-- 支付单详情
function get_payments(payments_list)
	if payments_list == nil then return nil end
    payments = {}
    for k, v in pairs(payments_list) do
        payments[k] = {
            trade_no = toString(v.payment_id),
		    money = toString(v.pay_fee),
		    pay_time = toString(v.t_end),
		    account = toString(v.seller_account),
		    bank = toString(v.seller_bank),
		    currency = toString(v.currency),
		    pay_bn = toString(v.payment_id),
		    paycost = '',
		    pay_account = toString(v.buyer_account),
		    paymethod = toString(v.payment_name),
		    memo = '',
        }
    end

	return payments
end

function other_list(input)
	if input == nil then return '' end

	local result = {}
	if type(input.promotion_details) == 'table' then
		for _, v in pairs(input.promotion_details) do
			if v.gift_item_name ~= '' then
				table.insert(result, {
					['type'] = 'gift',
					id = toString(v.gift_item_id),
					name = toString(v.gift_item_name),
					num = toString(v.gift_item_num),
					pmt_id = toString(v.pmt_id),
					pmt_describe = toString(v.pmt_describe),
					pmt_amount = toString(v.pmt_amount),
				})
			end
		end
	end
	table.insert(result, {
		['type'] = 'unpaid',
		unpaidprice = toString(input.unpaidprice),
	})
	if input.orders ~= nil and input.orders.order ~= nil and type(input.orders.order) == 'table' then
		for _, v in pairs(input.orders.order) do
			if v.cid ~= '' then
				table.insert(result, {
					['type'] = 'category',
					cid = toString(v.cid),
					oid = toString(v.oid),
				})
			end
		end
	end

	return json.encode(result)
end

function ome_order_add_payment_detail(input)
	if input == nil then return end
	return json.encode({
		pay_account = toString(input.buyer_alipay_no),
		currency = toString(input.currency),
		paymethod = toString(input.payment_type),
		pay_time = tonumber(toString(input.pay_time)),
		trade_no = toString(input.alipay_no),
	})
end

function ome_format_status( source_dic )
	if source_dic == nil then return '' end
	if source_dic == 'TRADE_ACTIVE' then
		return 'active'
	elseif source_dic == 'TRADE_CLOSED' then
		return 'dead'
	elseif source_dic == 'TRADE_FINISHED' then
		return 'finish'
	end
end

function ome_format_paystatus( source_dic )
	local data = 0
	if source_dic =='PAY_NO' then
        data = 0
    elseif source_dic =='PAY_FINISH' then
        data = 1
    elseif source_dic =='PAY_TO_MEDIUM' then
        data = 2
    elseif source_dic =='PAY_PART' then
        data = 3
    elseif source_dic =='REFUND_PART' then
        data = 4
    elseif source_dic =='REFUND_ALL' then
        data = 5
    elseif source_dic =='REFUNDING' then
        data = 6
    end

    return data
end

function ome_format_shipstatus( source_dic )
	local data = 0
    if source_dic =='SHIP_NO' then
        data = 0
    elseif source_dic =='SHIP_FINISH' then
        data = 1
    elseif source_dic =='SHIP_PART' then
        data = 2
    elseif source_dic =='RESHIP_PART' then
        data = 3
    elseif source_dic =='RESHIP_ALL' then
        data = 4
    end

    return data
end


function ome_order_add_pmt_detail( source_dic )
	if type(source_dic) == 'table' then
		local  result = {}
		for _, v in pairs(source_dic) do
			if v.gift_item_name ~= nil then
				table.insert(result, {
					pmt_type = 'gift',
					gift_item_id = toString(v.gift_item_id),
					gift_item_name = toString(v.gift_item_name),
					gift_item_num = toString(v.gift_item_num),
					pmt_id = toString(v.pmt_id),
					pmt_describe = toString(v.pmt_describe),
					pmt_amount = toString(v.pmt_amount),
				})
			else
				table.insert(result, {
					pmt_id = toString(v.pmt_id),
					pmt_describe = toString(v.pmt_describe),
					pmt_amount = toString(v.pmt_amount),
				})
			end
			return json.encode(result)
		end
	end
	return source_dic
end

function ome_order_add_member_info( source_dic )
	return {
		uname =            toString(source_dic.buyer_uname),
        name =             toString(source_dic.buyer_name),
        area_state =       toString(source_dic.buyer_state),
        area_city =        toString(source_dic.buyer_city),
        area_district =    toString(source_dic.buyer_district),
        addr =             toString(source_dic.buyer_address),
        mobile =           toString(source_dic.buyer_mobile),
        tel =              toString(source_dic.buyer_phone),
        email =            toString(source_dic.buyer_email),
        zip =              toString(source_dic.buyer_zip),
        alipay_no =        toString(source_dic.buyer_alipay_no),
	}
end

function ome_order_add_shipping( source_dic )
	return	json.encode({
		shipping_id = toString(source_dic.shipping_id),
		shipping_name = toString(source_dic.shipping_type),
		cost_shipping = toString(source_dic.shipping_fee),
		is_protect = toString(source_dic.is_protect),
		cost_protect = toOther(source_dic.protect_fee, "0.00"),
		is_cod =  toString(source_dic.is_cod),
	})
end

function ome_order_add_payinfo( source_dic )
	return json.encode({
		pay_name = toString(source_dic.payment_type),
		cost_payment = toOther(source_dic.pay_cost, '0.00'),
	})
end


function ome_order_add_consignee( source_dic )
	return json.encode({
		name          =   toString(source_dic.receiver_name),
        area_state    =   toString(source_dic.receiver_state),
        area_city     =   toString(source_dic.receiver_city),
        area_district =   toString(source_dic.receiver_district),
        addr          =   toString(source_dic.receiver_address),
        zip           =   toString(source_dic.receiver_zip),
        telephone     =   toString(source_dic.receiver_phone),
        email         =   toString(source_dic.receiver_email),
        mobile        =   toString(source_dic.receiver_mobile),
        r_time        =   toString(source_dic.receiver_time),
	})
end

function ome_order_add_orders( source_dic, version )
	if type(source_dic.orders.order) ~= 'table' then return end
	local result = {}
	for _, v in pairs(source_dic.orders.order) do
		local tmp = {
			oid           =   toString(v.oid),
            obj_type      =   toString(v.type),
            obj_alias     =   toString(v.type_alias),
            shop_goods_id =   toString(v.iid),
            bn            =   toString(v.bn),
            name          =   toString(v.title),
            price         =   toString(v.price),
            quantity      =   toString(v.items_num),
            amount        =   toOther(tonumber(v.price), 0) * toOther(tonumber(v.items_num), 0),
            weight        =   toString(v.weight),
            score         =   '',
            is_oversold   =   toOther(v.is_oversold,false),
            pmt_price     =   version >= 2 and 0.0 or toString(v.discount_fee),
            sale_price    =   toString(v.sale_price),
        }
        tmp.order_items = {}

        for _, v in pairs(v.order_items.order_item) do
        	table.insert(tmp.order_items, {
        		shop_product_id   =   toString(v.sku_id),
				shop_goods_id     =   toString(v.iid),
				specId            =   toString(v.iid),
				item_type         =   toString(v.item_type),
				promotion_id      =   toString(v.promotion_id),
				bn                =   toString(v.bn),
				name              =   toString(v.name),
				cost              =   toString(v.price),
				quantity          =   toString(v.num),
				sendnum           =   toString(v.sendnum),
				amount            =   toOther(tonumber(v.price),0)*toOther(tonumber(v.num),0),
				price             =   toString(v.price),
				score             =   toString(v.score),
				status            =   toString(v.status),
				sale_amount       =   toString(v.payment),
				product_attr      =   toString(v.sku_properties),
				original_str      =   toString(v.sku_properties_string),
				pmt_price         =   toString(v.discount_fee),
				sale_price        =   toString(v.sale_price),
    		})
        end

        table.insert(result, tmp)
	end
	return json.encode(result)
end

function ome(trade, source, version)
	if trade == nil then return end
	return {
		order_source= source,
		order_bn= trade.tid,
		title= toString(trade.title),
		is_delivery= toString(trade.is_delivery),
		is_tax= toString(trade.has_invoice),
		tax_title= toString(trade.invoice_title),
		cost_tax= toString(trade.invoice_fee),
		cost_item= toString(trade.total_goods_fee),
		total_amount= toString(trade.total_trade_fee),
		memeber_id= toString(trade.seller_uname),
		payed= toString(trade.payed_fee),
		currency= toString(trade.currency),
		cur_rate= toString(trade.currency_rate),
		cur_amount= toString(trade.total_currency_fee),
		score_g= toString(trade.buyer_obtain_point_fee),
		score_u= toString(trade.point_fee),
		mark_text= toString(trade.trade_memo),
		weight= toString(trade.total_weight),
		discount= toString(trade.discount_fee),
		pmt_goods= toString(pmt_goods(trade, source, version)),
		pmt_order= toString(trade.orders_discount_fee),
		custom_mark= toString(trade.buyer_memo),
		mark_type= toString(trade.seller_flag),
		payments= get_payments(trade.payment_lists),
		is_lgtype= toBool(trade.is_lgtype),
		is_force_wlb= toBool(trade.is_force_wlb),
		order_type= toOther(trade.is_brand_sale, 'normal'),
		signfor_status= toOther(trade.cod_status, '0'),
		step_trade_status= toString(trade.step_trade_status),
		step_paid_fee= toString(trade.step_paid_fee),
		errortrade_desc= toString(trade.mark_desc),
		trade_type= toString(trade.trade_type),
		is_errortrade= toOther(trade.is_errortrade, 'false'),
		t_type= 'fixed',
		other_list = other_list(trade),
		payment_detail = ome_order_add_payment_detail(trade),
		createtime = tonumber(toString(trade.created)),
		modified = tonumber(toString(trade.modified)),
		lastmodify = tonumber(toString(trade.modified)),
		status = ome_format_status(toString(trade.status)),
		pay_status = ome_format_paystatus(toString(trade.pay_status)),
		ship_status = ome_format_shipstatus(toString(trade.ship_status)),
		pmt_detail = ome_order_add_pmt_detail(toString(trade.promotion_details)),
		member_info = ome_order_add_member_info(trade),
		shipping = ome_order_add_shipping(trade),
		payinfo = ome_order_add_payinfo(trade),
		consignee = ome_order_add_consignee(trade),
		order_objects = ome_order_add_orders(trade, version),
		order_limit_time = tonumber(toString(trade.trade_valid_time)),
	}
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
	local source = input.relations.from_type
	local version = 0
	if relation.release_version == 'tg' or relation.release_version == 'tg.pro' then
		version = 2.2
	end
	app_params = ome(trade, source, version)
	--

	update_table(app_params, get_res_params(method,relation))
	return app_params
	-- body
end

output = main(input.order_sdf.trade, input.relations[1])

-- print(json.encode(output))
