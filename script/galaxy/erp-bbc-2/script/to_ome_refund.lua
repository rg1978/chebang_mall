local json = require("json")
input = json.decode(input)

local method = 'ome.refund.add'


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

function refund( input )
	-- body
	local result = {
                    order_bn         =  input.tid,
                    refund_bn        =  input.refund_id,
                    buyer_id         =  toOther(input.buyer_id, ""),
                    account          =  toOther(input.buyer_account, ""),
                    bank             =  toOther(input.buyer_bank, ""),
                    pay_account      =  toOther(input.buyer_name,""),
                    money            =  toOther(input.refund_fee, ""),
                    pay_type         =  toOther(input.pay_type,""),
                    currency         =  toOther(input.currency,'CNY'),
                    cur_money        =  toOther(input.currency_fee,''),
                    paymethod        =  toOther(input.payment_type,''),
                    memo             =  toOther(input.memo,''),
                    trade_no         =  toOther(input.outer_no,''),
                    refund_type      =  toOther(input.refund_type,''),
                    t_ready          =  tonumber(toOther(input.t_begin,'')),
                    t_sent           =  tonumber(toOther(input.t_sent,'')),
                    t_received       =  tonumber(toOther(input.t_received,'')),
                    modified         =  tonumber(toOther(input.modified,'')),
                    status           =  toOther(input.status_old,''),
                    payment          =  toOther(input.payment_tid,''),
                    oid              =  toOther(input.oid,'')
                 }
    return result
end



function main(trade,relation)

	if trade == nil or relation ==nil then return "error format" end

	app_params = refund(trade)
	--
	update_table(app_params, get_res_params(method,relation))
	return app_params
	-- body
end



output = main(input.refund_sdf.refund, input.relations[1])
