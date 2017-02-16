local json = require("json")
input = json.decode(input)



function  bbcParams( v )
	if v == nil then return end
	if v.status == "REFUND" then
		return {
			status = 1,
			reason = v.memo,
			tid = v.tid
		}
	end
	-- body
end


bbcParams(input)