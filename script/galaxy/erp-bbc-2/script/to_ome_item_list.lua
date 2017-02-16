local json = require("json")
input = json.decode(input)


output = {}


function getSkus( v )
	if v == nil then return end
	local result = {}
	result.sku = {}
	for k, v in pairs(v) do
		table.insert(result.sku, {
			sku_id = v.sku_id,
			iid = v.item_id,
			outer_id = v.bn,
			created = v.created_time,
			status = v.status,
			bn = v.bn,
			modified = v.modified_time,
			price = v.price,
			properties = v.properties,
			quantity = v.store
		})
	end
    return result
	-- body
end


function getItemImgs( v )
	if v == nil then return {item_img = {}} end
	local result = {}

	local  isD = false

	result.item_img = {}
	for k, v in pairs(v) do
		if k == 1 then
			isD = true
		else
			isD = false
		end
		table.insert(result.item_img, {
			image_id = k,
            big_url = v,
            thisuasm_url = v,
            small_url = v,
            is_default = isD,
		})
	end
    return result
	-- body
end

function getPropImgs()
	local result = {}
	result.propimg = {}
	return result
	-- body
end



function getItem(v)
	if v == nil then return end
    local result = {}
    for k, v in pairs(v) do
        table.insert(result, {
        iid = v.item_id,
        title = v.title,
        outer_id = v.bn,
        seller_uname = "",
        ["type"] = "",
        shopcat_id = v.shop_cat_id,
        input_pids = "",
        input_str = "",
        score = "",
        supplier_id = "",
        supplier_name = "",
        barcode = v.barcode,
        is_simple = "",
        valid_thru = "",
        mktprice = v.mkt_price,
        costprice = v.cost_price,
        has_showcase = "",
        auto_repost = "",
        auction_point = "",
        detail_url = "",
        bn = v.bn,
        brand_id = v.brand_id,
        cid = "",
        num = v.store,
        status = v.item_status.approve_status,
        price = v.price,
        unit = "",
        modified = v.modified_time,
        description = v.sub_title,
        default_img_url = v.image_default_id,
        item_imgs = getItemImgs(v.list_image),
        prop_imgs = getPropImgs(),
        delist_time = v.item_status.delist_time,
        props = "",
        stuff_status = "",
        country = "",
        state = "",
        city = "",
        district = "",
        freight_payer = "",
        postage_id = "",
        post_fee = "",
        express_fee = "",
        ems_fee = "",
        has_invoice = "",
        has_warranty = "",
        has_discount = "",
        increment = "",
        is_virtual = "",
        skus = getSkus(v.sku),
    })
    end
	return result
end

output.items = {}
output.items.item =  getItem(input)