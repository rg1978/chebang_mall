
local json = require("json")

input = json.decode(input)


function toString( input )
	if input == nil then return "" end
	return input
end

function toOther( input, other )
	if input == nil then return other end
	return input
end

function inItems(key, items)
    for _, v in pairs(items) do
        if key == v then return true end
    end
end

function getStatus( v )
    if v == '0' then
        return '0'
    end
    if v == '2' then
        return '6'
    end
    if v == '4' then
        return '3'
    end
    if inItems(v, {'1', '3', '5', '6'}) then
        return '4'
    end
end

function getType( v )
    if inItems(v, {'1', '3', '5', '6'}) then
        return 'refund'
    end
    return "apply"
end

function getRefund( v )
	if v == nil then return end

	return {
		refund_id              =  toOther(v.refund_bn,''),
        tid                    =  toOther(v.tid,''),
        buyer_id               =  toOther(v.user_id, ''),
        buyer_account          =  '',
        buyer_bank             =  '',
        buyer_name             =  '',
        currency               =  'CNY',
        refund_fee             =  toOther(v.refund_fee,''),
        paycost                =  '',
        currency_fee           =  toOther(v.total_price,''),
        pay_type               =  '',
        refund_type            =  getType(toOther(v.status,'')),
        payment_type           =  '',
        t_begin                =  toOther(v.created_time,''),
        t_sent                 =  '',
        t_received             =  '',
        status_old             =  getStatus(toOther(v.status,'')),
        memo                   =  '',
        outer_no               =  '',
        modified               =  toOther(v.modified_time,''),
        oid                    =  toOther(v.oid,''),
        shipping_type          =  '',
        cs_status              =  '',
        advance_status         =  '',
        split_fee              =  '',
        split_seller_fee       =  '',
        payment_id             =  '',
        total_fee              =  toOther(v.total_price,''),
        buyer_nick             =  '',
        seller_nick            =  '',
        created                =  toOther(v.created_time,''),
        status                 =  getStatus(toOther(v.status,'')),
        good_status            =  '',
        has_good_return        =  '',
        reason                 =  toOther(v.refunds_reason,''),
        desc                   =  '',
        good_return_time       =  '',
        logistics_company      =  '',
        logistics_no           =  '',
        company                =  '',
        receiver_address       =  '',
        refund_item_list       =  '',
	}
end

tmp = {}
table.insert(tmp, input.permission)

output = {
	msg_id = input.msg_id,
	refund_sdf = {
		refund = getRefund(input.data.result)
	},
	relations = tmp,
	["type"] = "zx_refund"
}

-- if input.data.result.status == '1' then
--     output = {result = 'return'}
-- end
