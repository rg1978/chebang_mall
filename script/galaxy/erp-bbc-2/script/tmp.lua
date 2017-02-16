

def order_detail_status_to_sdf(source_dic):
    if source_dic in ['WAIT_SELLER_SEND_GOODS','TRADE_NO_CREATE_PAY','WAIT_BUYER_PAY','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED']:
        status = 'TRADE_ACTIVE'
    elif source_dic in ['TRADE_CLOSED_BY_TAOBAO','TRADE_CLOSED','ALL_CLOSED']:
        status = 'TRADE_CLOSED'
    else:
        status = 'TRADE_FINISHED'


    if source_dic in ['TRADE_NO_CREATE_PAY','WAIT_BUYER_PAY','TRADE_CLOSED_BY_TAOBAO','ALL_CLOSED']:
        pay_status = 'PAY_NO'
    elif source_dic in ['TRADE_CLOSED']:
        pay_status = 'REFUND_ALL'
    else:
        pay_status = 'PAY_FINISH'

    if source_dic in ['WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED','TRADE_FINISHED']:
        ship_status = 'SHIP_FINISH'
    else:
        ship_status = 'SHIP_NO'

    return {'status':status,'pay_status':pay_status,'ship_status':ship_status}



function getStatus(input)
	local status, pay_status, ship_status
	--  status check
	if input = 'TRADE_FINISHED' then
		status = 'TRADE_FINISHED'
	elseif inItems(input, {'TRADE_CLOSED', 'TRADE_CLOSED_BY_SYSTEM'}) then
		status = 'TRADE_CLOSED'
	else
		status = 'TRADE_ACTIVE'
	end

	if input == 'TRADE_FINISHED' then
		pay_status = 'PAY_FINISH'
	elseif input == 'TRADE_CLOSED' then
		pay_status = 'REFUND_ALL'
	else
		pay_status = 'PAY_NO'
	end

	if  inItems(input, {'WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED','TRADE_FINISHED'}) then
		ship_status = 'SHIP_FINISH'
	else
		ship_status = 'SHIP_NO'
	end

	return {status, pay_status, ship_status}
end



function inItems(key, items)
	for _, v in pairs(items) do
		if key == v then return true end
	end
end
'WAIT_BUYER_PAY'等待买家付款
'WAIT_SELLER_SEND_GOODS'等待卖家发货,即:买家已付款
'WAIT_BUYER_CONFIRM_GOODS'等待买家确认收货,即:卖家已发货
'TRADE_BUYER_SIGNED'买家已签收,货到付款专用
'TRADE_FINISHED'交易成功
'TRADE_CLOSED'已关闭(退款关闭订单)
'TRADE_CLOSED_BY_SYSTEM'已关闭(卖家或买家主动关闭







