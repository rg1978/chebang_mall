local json = require("json")
input = json.decode(input)

output = {}

function getSku(v)
	return { sku = {
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
	}}
end


output = getSku(input)