local json = require("json")
input = json.decode(input)


output = {}


function getItems( input )
	if input == nil then return end
	local result = {}
	for k,v in pairs(input) do
		table.insert(result, {
			iid = v.item_id,
	        outer_id = v.bn,
	        bn = v.bn,
	        num = v.store,
	        title = v.title,
	        cid = "",
	        shopcat_id = v.shop_cat_id,
	        brand_id = v.brand_id,
	        input_pids = "",
	        input_str = "",
	        detail_url = "",
	        default_img_url = v.image_default_id,
	        score = "",
	        supplier_id = "",
	        supplier_name = "",
	        barcode = v.barcode,
	        is_simple = "",
	        valid_thru = "",
	        costprice = v.cost_price,
	        list_time = v.list_time,
	        delist_time = v.delist_time,
	        stuff_status = "",
	        country = "",
	        state = "",
	        city = "",
	        district = "",
	        post_fee = "",
	        express_fee = "",
	        ems_fee = "",
	        has_discount = "",
	        freight_payer = "",
	        has_invoice = "",
	        has_warranty = "",
	        has_showcase = "",
	        increment = "",
	        auto_repost = "",
	        postage_id = "",
	        auction_point = "",
	        is_virtual = "",
	        seller_uname = "",
	        ["type"] = "",
	        props = "",
	        status = v.approve_status,
	        price = v.price,
	        mktprice = v.mkt_price,
	        unit = "",
	        modified = v.modified_time
		})
	end
	return result
end

-- 总数
output.totalResults = input.total_found


output.items = {}

output.items.item = getItems(input.list)





